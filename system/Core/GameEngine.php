<?php
namespace App\Services;

use System\Core\Database;

class GameEngine {

    /**
     * Tüm aktif oyuncuların tesis döngüsünü (tick) çalıştırır.
     * @param int|null $userId Belirli bir oyuncu için çalıştırmak istenirse ID verilebilir.
     */
    public static function runTick($userId = null) {
        $db = Database::getInstance();

        // 1. Sistem ayarlarından KM başı taşıma ücretini dinamik çek
        $settingStmt = $db->query("SELECT setting_key, setting_value FROM game_settings");
        $settings = [];
        while ($row = $settingStmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $kmBasiTasimaUcreti = (float)($settings['transport_unit_price'] ?? 25.00);

        // 2. Ana depo (İzmir) koordinatlarını veritabanından çek
        $warehouseStmt = $db->query("SELECT latitude, longitude FROM provinces WHERE name = 'İzmir' LIMIT 1");
        $warehouse = $warehouseStmt->fetch(\PDO::FETCH_ASSOC);
        $whLat = (float)($warehouse['latitude'] ?? 38.4192);
        $whLon = (float)($warehouse['longitude'] ?? 27.1287);

        // 3. Aktif binaları, tesis tiplerini, ürün taban maliyetini, il çarpanlarını ve kullanıcı bilgilerini çekiyoruz
        $query = "SELECT pb.*, 
                         bt.category, bt.production_rate, bt.labor_required, bt.produces_product_id,
                         p.base_cost as product_base_cost,
                         prov.population, prov.latitude, prov.longitude,
                         prov.agri_multiplier, prov.ind_multiplier, prov.mine_multiplier, prov.labor_cost_multiplier,
                         u.id as user_id, u.money
                  FROM user_buildings pb
                  JOIN building_types bt ON pb.building_type_id = bt.id
                  LEFT JOIN products p ON bt.produces_product_id = p.id
                  JOIN provinces prov ON pb.province_id = prov.id
                  JOIN users u ON pb.user_id = u.id
                  WHERE pb.status = 'active'";
        
        if ($userId) {
            $query .= " AND pb.user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->query($query);
        }

        $buildings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($buildings as $building) {
            self::processBuildingCycle($db, $building, $whLat, $whLon, $kmBasiTasimaUcreti);
        }
    }

    /**
     * Tek bir tesisin üretim, lojistik ve gerçek birim maliyet döngüsünü hesaplar ve uygular.
     */
    private static function processBuildingCycle($db, $building, $whLat, $whLon, $kmBasiTasimaUcreti) {
        // 1. Kategoriye Göre Sektörel Uzmanlık Çarpanını Belirleme
        $sectorMultiplier = 1.0;
        $category = strtolower(trim($building['category']));
        
        if (str_contains($category, 'agri') || str_contains($category, 'tarım') || str_contains($category, 'tarim')) {
            $sectorMultiplier = (float)$building['agri_multiplier'];
        } elseif (str_contains($category, 'ind') || str_contains($category, 'sanayi') || str_contains($category, 'fabrika')) {
            $sectorMultiplier = (float)$building['ind_multiplier'];
        } elseif (str_contains($category, 'mine') || str_contains($category, 'maden')) {
            $sectorMultiplier = (float)$building['mine_multiplier'];
        }
        if ($sectorMultiplier <= 0) $sectorMultiplier = 1.0;

        // 2. Üretim Miktarı Formülü: Temel Oran * Seviye * Sektörel Çarpan
        $baseRate = (float)($building['production_rate'] ?? 10);
        $level = (int)$building['level'];
        $productionOutput = round($baseRate * $level * $sectorMultiplier, 2);

        // 3. Mesafe Hesaplama (Haversine - İzmir Merkez Depo ile Tesis Arası)
        $lat = (float)($building['latitude'] ?? 39.0);
        $lon = (float)($building['longitude'] ?? 35.0);
        $mesafeKm = self::calculateHaversineDistance($whLat, $whLon, $lat, $lon);

        // 4. Dönemsel Üretim ve Lojistik Maliyet Hesabı (Bina maliyeti hariç)
        $population = (float)($building['population'] ?? 1000000);
        $laborMultiplier = (float)($building['labor_cost_multiplier'] ?? 1.0);
        $urunTabanMaliyeti = (float)($building['product_base_cost'] ?? 1000.00);

        // A. Üretim Maliyeti Kısmı
        $nufusUssuUretim = pow($population / 1000000.0, 0.2);
        $uretimMaliyeti = $urunTabanMaliyeti * $nufusUssuUretim * ($laborMultiplier / $sectorMultiplier);

        // B. Lojistik Maliyet Kısmı (İzmir Depoya Taşıma)
        $lojistikMaliyet = $mesafeKm * $kmBasiTasimaUcreti;

        // C. Toplam Dönemsel Gider ve Birim Fiyat
        $toplamDonemselGider = $uretimMaliyeti + $lojistikMaliyet;
        $birimFiyat = $productionOutput > 0 ? ($toplamDonemselGider / $productionOutput) : 0;

        // D. Kasadan Düşülecek Toplam Maliyet (Üretilen adet x Birim fiyat)
        $kasadanDusulecekTutar = round($productionOutput * $birimFiyat, 2);

        // 5. Veritabanı Güncellemesi (Transaction Güvencesiyle)
        $db->beginTransaction();
        try {
            // Hesaplanan toplam dönemsel üretim + lojistik maliyetini oyuncunun bütçesinden düş
            $updUser = $db->prepare("UPDATE users SET money = money - ? WHERE id = ?");
            $updUser->execute([$kasadanDusulecekTutar, $building['user_id']]);

            // Üretilen ürünü oyuncunun envanterine/deposuna ekle
            if (!empty($building['produces_product_id']) && $productionOutput > 0) {
                $invCheck = $db->prepare("SELECT id, quantity FROM player_inventories WHERE user_id = ? AND product_id = ?");
                $invCheck->execute([$building['user_id'], $building['produces_product_id']]);
                $inv = $invCheck->fetch(\PDO::FETCH_ASSOC);

                if ($inv) {
                    $updInv = $db->prepare("UPDATE player_inventories SET quantity = quantity + ? WHERE id = ?");
                    $updInv->execute([$productionOutput, $inv['id']]);
                } else {
                    $insInv = $db->prepare("INSERT INTO player_inventories (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $insInv->execute([$building['user_id'], $building['produces_product_id'], $productionOutput]);
                }
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }
    }

    private static function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin(dLon / 2) * sin(dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}