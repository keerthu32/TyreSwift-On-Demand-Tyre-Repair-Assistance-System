<?php
/**
 * Return all pending requests sorted by oldest first.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['success' => false, 'message' => 'Method not allowed. Use GET.']);
}

try {
    $pdo = getPDO();

    $sql = 'SELECT sr.id, sr.user_id, sr.latitude, sr.longitude, sr.status, sr.created_at,
                   u.name AS user_name, u.phone AS user_phone, u.vehicle_type
            FROM service_requests sr
            JOIN users u ON u.id = sr.user_id
            WHERE sr.status = :status
            ORDER BY sr.created_at ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'Pending']);
    $requests = $stmt->fetchAll();

    jsonResponse(200, [
        'success' => true,
        'message' => 'Pending requests fetched successfully.',
        'count' => count($requests),
        'data' => $requests,
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
