<?php
/**
 * Fetch nearest available technician using Haversine formula.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

enforceMethod('POST');

try {
    $pdo = getPDO();
    $data = getJsonInput();

    $latitude = requireCoordinate($data, 'latitude', -90.0, 90.0);
    $longitude = requireCoordinate($data, 'longitude', -180.0, 180.0);

    // Haversine formula calculates distance in kilometers.
    $sql = "SELECT id, name, phone, latitude, longitude,
                (6371 * ACOS(
                    COS(RADIANS(:latitude)) * COS(RADIANS(latitude)) *
                    COS(RADIANS(longitude) - RADIANS(:longitude)) +
                    SIN(RADIANS(:latitude)) * SIN(RADIANS(latitude))
                )) AS distance_km
            FROM technicians
            WHERE is_available = 1
            ORDER BY distance_km ASC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':latitude' => $latitude,
        ':longitude' => $longitude,
    ]);

    $technician = $stmt->fetch();

    if (!$technician) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'No available technician found.',
        ]);
    }

    $technician['id'] = (int)$technician['id'];
    $technician['distance_km'] = round((float)$technician['distance_km'], 2);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Nearest technician found.',
        'data' => $technician,
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
