<?php
namespace App\Controllers;

use System\Core\Controller;
use System\Core\Database;

class PlayerBuildingController extends Controller {

    public function index() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;

        $activeCategory = $_GET['category'] ?? 'Tesis ve Fabrika';

        $playerStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $playerStmt->execute([$userId]);
        $player = $playerStmt->fetch(\PDO::FETCH_ASSOC);

        // 1. MySQL veritabanından kesin anlık zaman damgasını al
        $timeStmt = $db->query("SELECT UNIX_TIMESTAMP(NOW()) as db_now");
        $now = (int)$timeStmt->fetchColumn();
        if (!$now) {
            $now = time();
        }

        // 2. Süresi dolan üretim döngülerini işle
        $this->processProductionCycles($db, $userId, $now);

        // 3. Tesisleri çek
        $query = "SELECT pb.*, pb.id as building_id, 
                         UNIX_TIMESTAMP(pb.created_at) as created_ts, 
                         UNIX_TIMESTAMP(pb.last_produced_at) as last_produced_ts,
                         bt.name as building_name, bt.category, bt.complexity_type, 
                         bt.energy_consumption, bt.water_consumption, bt.labor_required, 
                         bt.base_cost, bt.production_rate, bt.unit_type, bt.image as building_image, 
                         bt.produces_product_id, bt.consumes_product_id, bt.consumption_amount,
                         p.name as product_name, p.image as product_image, p.unit_cost as product_base_cost,
                         cp.name as input_product_name,
                         prov.name as province_name, prov.population, prov.labor_cost_multiplier,
                         prov.agri_multiplier, prov.ind_multiplier, prov.mine_multiplier, prov.latitude, prov.longitude
                  FROM user_buildings pb
                  JOIN building_types bt ON pb.building_type_id = bt.id
                  LEFT JOIN products p ON bt.produces_product_id = p.id
                  LEFT JOIN products cp ON bt.consumes_product_id = cp.id
                  JOIN provinces prov ON pb.province_id = prov.id
                  WHERE pb.user_id = ?
                  ORDER BY pb.id DESC";

        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $rawBuildings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $my_buildings = [];
        $cycleSeconds = 600; // 10 dakika

        foreach ($rawBuildings as $b) {
            $category = strtolower(trim($b['category']));
            $regionMult = 1.0;

            if (str_contains($category, 'agri') || str_contains($category, 'tarım') || str_contains($category, 'bahçe')) {
                $regionMult = (float)($b['agri_multiplier'] ?? 1.0);
            } elseif (str_contains($category, 'ind') || str_contains($category, 'sanayi')) {
                $regionMult = (float)($b['ind_multiplier'] ?? 1.0);
            } elseif (str_contains($category, 'mine') || str_contains($category, 'maden')) {
                $regionMult = (float)($b['mine_multiplier'] ?? 1.0);
            }
            if ($regionMult <= 0) $regionMult = 1.0;

            $baseProd = (int)($b['production_rate'] ?? 8) * (int)($b['level'] ?? 1);
            $healthFactor = (float)($b['condition_health'] ?? 100) / 100.0;
            $productionAmount = round($baseProd * $regionMult * $healthFactor, 2);
            $unitLabel = $b['unit_type'] ?? 'Adet';

            // Görünümün ihtiyaç duyduğu hammadde alanları (Hata almamak için eklendi)
            $hasEnoughInput = true;
            $availableStock = 0;
            $warningMessage = '';

            if (!empty($b['consumes_product_id'])) {
                $invCheck = $db->prepare("SELECT quantity FROM user_inventories WHERE user_id = ? AND product_id = ?");
                $invCheck->execute([$userId, $b['consumes_product_id']]);
                $invRow = $invCheck->fetch(\PDO::FETCH_ASSOC);
                $availableStock = (float)($invRow['quantity'] ?? 0);
                $requiredAmount = (float)($b['consumption_amount'] ?? 0);

                if ($availableStock < $requiredAmount) {
                    $hasEnoughInput = false;
                    $inputName = $b['input_product_name'] ?? 'Girdi Ürün';
                    $warningMessage = "Hammadde Bulunamadı: {$inputName} (Gereken: {$requiredAmount}, Mevcut: {$availableStock})";
                }
            }

            $logStmt = $db->prepare("SELECT bl.*, p.name as product_name FROM building_logs bl LEFT JOIN products p ON bl.product_id = p.id WHERE bl.building_id = ? ORDER BY bl.id DESC LIMIT 10");
            $logStmt->execute([$b['building_id']]);
            $logs = $logStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // --- KALAN SÜRE HESABI ---
            $lastProducedTs = (int)($b['last_produced_ts'] ?? 0);
            if ($lastProducedTs <= 0) {
                $lastProducedTs = (int)($b['created_ts'] ?? $now);
            }

            $elapsedTime = $now - $lastProducedTs;
            if ($elapsedTime < 0) {
                $elapsedTime = 0;
            }

            $mod = $elapsedTime % $cycleSeconds;
            $remainingSeconds = $cycleSeconds - $mod;

            if ($remainingSeconds <= 0 || $remainingSeconds > $cycleSeconds) {
                $remainingSeconds = $cycleSeconds;
            }

            $progress = (($cycleSeconds - $remainingSeconds) / $cycleSeconds) * 100;

            $b['id'] = $b['building_id'];
            $b['formula_details'] = [
                'base_production' => $baseProd,
                'region_label' => 'Sektörel Bölge Çarpanı (' . ucfirst($b['province_name']) . ')',
                'region_multiplier' => $regionMult,
                'unit_label' => $unitLabel,
                'has_enough_input' => $hasEnoughInput,
                'available_stock' => $availableStock,
                'input_product_name' => $b['input_product_name'] ?? 'Hammadde Gerekmiyor',
                'consumption_amount' => $b['consumption_amount'] ?? 0,
                'health_factor' => $healthFactor,
                'warning_message' => $warningMessage
            ];

            $b['production_amount'] = $productionAmount;
            $b['remaining_seconds'] = $remainingSeconds; 
            $b['production_progress'] = $progress;
            $b['image'] = $b['building_image'];
            $b['logs'] = $logs;

            $my_buildings[] = $b;
        }

        $inventoryStmt = $db->prepare("SELECT inv.*, p.name as product_name FROM user_inventories inv JOIN products p ON inv.product_id = p.id WHERE inv.user_id = ?");
        $inventoryStmt->execute([$userId]);
        $inventory = $inventoryStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $building_types = $db->query("SELECT bt.*, p.name as product_name, p.image as product_image, p.unit_cost as product_base_cost FROM building_types bt LEFT JOIN products p ON bt.produces_product_id = p.id ORDER BY bt.name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $provinces = $db->query("SELECT * FROM provinces ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        $warehouse = $db->query("SELECT latitude, longitude FROM provinces WHERE name = 'İzmir' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $warehouseIzmir = ['lat' => (float)($warehouse['latitude'] ?? 38.4192), 'lon' => (float)($warehouse['longitude'] ?? 27.1287)];

        $settings = [];
        foreach ($db->query("SELECT setting_key, setting_value FROM game_settings") as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('app/playerbuilding/index', [
            'my_buildings' => $my_buildings,
            'building_types' => $building_types,
            'provinces' => $provinces,
            'player' => $player,
            'warehouse_izmir' => $warehouseIzmir,
            'game_settings' => $settings,
            'inventory' => $inventory,
            'active_category' => $activeCategory,
            'cycle_seconds' => $cycleSeconds
        ]);
    }

    private function processProductionCycles($db, $userId, $now) {
        $cycleSeconds = 600;

        $stmt = $db->prepare("SELECT pb.*, pb.id as building_id, 
                                     UNIX_TIMESTAMP(pb.last_produced_at) as last_produced_ts,
                                     UNIX_TIMESTAMP(pb.created_at) as created_ts,
                                     bt.produces_product_id, bt.consumes_product_id, bt.consumption_amount, bt.production_rate, 
                                     prov.agri_multiplier, prov.ind_multiplier, prov.mine_multiplier, bt.category 
                              FROM user_buildings pb 
                              JOIN building_types bt ON pb.building_type_id = bt.id 
                              JOIN provinces prov ON pb.province_id = prov.id 
                              WHERE pb.user_id = ?");
        $stmt->execute([$userId]);
        $buildings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($buildings as $b) {
            $lastTime = (int)($b['last_produced_ts'] ?? 0);
            if ($lastTime <= 0) {
                $lastTime = (int)($b['created_ts'] ?? 0);
            }
            if ($lastTime <= 0) continue;
            
            $diff = $now - $lastTime;

            if ($diff >= $cycleSeconds) {
                $cyclesCompleted = floor($diff / $cycleSeconds);
                $this->executeProduction($db, $userId, $b, $cyclesCompleted, $lastTime, $now);
            }
        }
    }

    private function executeProduction($db, $userId, $b, $cyclesCompleted, $lastTime, $now) {
        $cycleSeconds = 600;
        $category = strtolower(trim($b['category']));
        $regionMult = 1.0;
        if (str_contains($category, 'agri') || str_contains($category, 'tarım')) $regionMult = (float)$b['agri_multiplier'];
        elseif (str_contains($category, 'ind') || str_contains($category, 'sanayi')) $regionMult = (float)$b['ind_multiplier'];
        elseif (str_contains($category, 'mine') || str_contains($category, 'maden')) $regionMult = (float)$b['mine_multiplier'];
        if ($regionMult <= 0) $regionMult = 1.0;

        $baseProd = (int)$b['production_rate'] * (int)$b['level'];
        $healthFactor = (float)$b['condition_health'] / 100.0;
        $prodAmount = round($baseProd * $regionMult * $healthFactor, 2) * max(1, $cyclesCompleted);

        $bId = $b['building_id'];
        
        try {
            $db->beginTransaction();

            if (!empty($b['produces_product_id']) && $prodAmount > 0) {
                $invCheck = $db->prepare("SELECT id FROM user_inventories WHERE user_id = ? AND product_id = ?");
                $invCheck->execute([$userId, $b['produces_product_id']]);
                if ($invCheck->fetch()) {
                    $db->prepare("UPDATE user_inventories SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?")
                       ->execute([$prodAmount, $userId, $b['produces_product_id']]);
                } else {
                    $db->prepare("INSERT INTO user_inventories (user_id, product_id, quantity) VALUES (?, ?, ?)")
                       ->execute([$userId, $b['produces_product_id'], $prodAmount]);
                }

                $db->prepare("INSERT INTO building_logs (building_id, action_type, product_id, amount, description, created_at) VALUES (?, 'production', ?, ?, 'Üretim Tamamlandı', NOW())")
                   ->execute([$bId, $b['produces_product_id'], $prodAmount]);
            }

            $newTimestamp = $lastTime + (max(1, $cyclesCompleted) * $cycleSeconds);
            if ($newTimestamp > $now) {
                $newTimestamp = $now;
            }
            
            $db->prepare("UPDATE user_buildings SET last_produced_at = FROM_UNIXTIME(?) WHERE id = ?")->execute([$newTimestamp, $bId]);
            $db->commit();
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();
            $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;

            $buildingTypeId = (int)($_POST['building_id'] ?? $_POST['building_type_id'] ?? 0);
            $provinceId = (int)($_POST['province_id'] ?? 0);

            try {
                $db->beginTransaction();
                $db->prepare("INSERT INTO user_buildings (user_id, building_type_id, province_id, level, condition_health, status, created_at, last_produced_at) VALUES (?, ?, ?, 1, 100.0, 'active', NOW(), NOW())")->execute([$userId, $buildingTypeId, $provinceId]);
                $db->commit();

                echo json_encode(['status' => 'success']);
                exit;
            } catch (\Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                http_response_code(400);
                echo $e->getMessage();
                exit;
            }
        }
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}