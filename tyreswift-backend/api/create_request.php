<?php
/**
 * Create a new tyre service request with Pending status.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['success' => false, 'message' => 'Method not allowed. Use POST.']);
}

try {
    $pdo = getPDO();
    $data = getJsonInput();

    $userId = requirePositiveInt($data, 'user_id');
    $latitude = requireCoordinate($data, 'latitude', -90.0, 90.0);
    $longitude = requireCoordinate($data, 'longitude', -180.0, 180.0);

    // Validate user exists before creating request.
    $userCheck = $pdo->prepare('SELECT id FROM users WHERE id = :id');
    $userCheck->execute([':id' => $userId]);
    if (!$userCheck->fetch()) {
        jsonResponse(404, ['success' => false, 'message' => 'User not found.']);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO service_requests (user_id, latitude, longitude, status) 
         VALUES (:user_id, :latitude, :longitude, :status)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':status' => 'Pending',
    ]);

    jsonResponse(201, [
        'success' => true,
        'message' => 'Service request created successfully.',
        'data' => [
            'request_id' => (int)$pdo->lastInsertId(),
            'status' => 'Pending',
        ],
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
