<?php
/**
 * Get full request status details by request_id (GET).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

enforceMethod('GET');

try {
    if (!isset($_GET['request_id']) || filter_var($_GET['request_id'], FILTER_VALIDATE_INT) === false) {
        jsonResponse(422, ['success' => false, 'message' => 'request_id is required and must be a valid integer.']);
    }

    $requestId = (int)$_GET['request_id'];
    if ($requestId <= 0) {
        jsonResponse(422, ['success' => false, 'message' => 'request_id must be greater than zero.']);
    }

    $pdo = getPDO();

    $sql = 'SELECT sr.id, sr.user_id, sr.technician_id, sr.latitude, sr.longitude, sr.status, sr.created_at,
                   u.name AS user_name, u.phone AS user_phone,
                   t.name AS technician_name, t.phone AS technician_phone
            FROM service_requests sr
            JOIN users u ON u.id = sr.user_id
            LEFT JOIN technicians t ON t.id = sr.technician_id
            WHERE sr.id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        jsonResponse(404, ['success' => false, 'message' => 'Service request not found.']);
    }

    jsonResponse(200, [
        'success' => true,
        'message' => 'Request details fetched successfully.',
        'data' => $request,
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
