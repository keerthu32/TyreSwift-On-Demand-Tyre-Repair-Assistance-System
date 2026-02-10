<?php
/**
 * Update request status and free technician when job is completed/cancelled.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

enforceMethod('POST');

$allowedStatuses = ['Pending', 'Accepted', 'On the Way', 'Arrived', 'Completed', 'Cancelled'];

try {
    $pdo = getPDO();
    $data = getJsonInput();

    $requestId = requirePositiveInt($data, 'request_id');

    if (!isset($data['status']) || !is_string($data['status'])) {
        jsonResponse(422, ['success' => false, 'message' => "Field 'status' is required and must be a string."]);
    }

    $status = trim($data['status']);

    if (!in_array($status, $allowedStatuses, true)) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Invalid status value.',
            'allowed_statuses' => $allowedStatuses,
        ]);
    }

    $pdo->beginTransaction();

    $requestStmt = $pdo->prepare('SELECT id, technician_id FROM service_requests WHERE id = :id FOR UPDATE');
    $requestStmt->execute([':id' => $requestId]);
    $request = $requestStmt->fetch();

    if (!$request) {
        $pdo->rollBack();
        jsonResponse(404, ['success' => false, 'message' => 'Service request not found.']);
    }

    $updateRequest = $pdo->prepare('UPDATE service_requests SET status = :status WHERE id = :id');
    $updateRequest->execute([
        ':status' => $status,
        ':id' => $requestId,
    ]);

    // Reset technician availability for final states.
    if (($status === 'Completed' || $status === 'Cancelled') && !empty($request['technician_id'])) {
        $updateTechnician = $pdo->prepare('UPDATE technicians SET is_available = 1 WHERE id = :id');
        $updateTechnician->execute([':id' => (int)$request['technician_id']]);
    }

    $pdo->commit();

    jsonResponse(200, [
        'success' => true,
        'message' => 'Request status updated successfully.',
        'data' => ['request_id' => $requestId, 'status' => $status],
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, ['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
