<?php

namespace App\Services;

class LogisticsService {

    /**
     * İki il arasındaki mesafeyi koordinatlara göre dinamik hesaplar (Haversine Formülü)
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Dünya yarıçapı (km)

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $straightDistance = $earthRadius * $c;

        // Karayolu / Coğrafi Pürüz Çarpanı (Kuş uçuşu mesafe genelde %20 daha uzundur)
        $roadMultiplier = 1.25; 

        return round($straightDistance * $roadMultiplier);
    }
}