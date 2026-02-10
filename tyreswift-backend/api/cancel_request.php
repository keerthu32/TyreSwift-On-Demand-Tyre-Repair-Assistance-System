<?php
/**
 * Cancel a request and release assigned technician if available.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

enforceMethod('POST');

try {
    $pdo = getPDO();
    $data = getJsonInput();

    $requestId = requirePositiveInt($data, 'request_id');

    $pdo->beginTransaction();

    $requestStmt = $pdo->prepare('SELECT id, technician_id, status FROM service_requests WHERE id = :id FOR UPDATE');
    $requestStmt->execute([':id' => $requestId]);
    $request = $requestStmt->fetch();

    if (!$request) {
        $pdo->rollBack();
        jsonResponse(404, ['success' => false, 'message' => 'Service request not found.']);
    }

    if ($request['status'] === 'Cancelled') {
        $pdo->rollBack();
        jsonResponse(409, ['success' => false, 'message' => 'Request is already cancelled.']);
    }

    $updateRequest = $pdo->prepare('UPDATE service_requests SET status = :status WHERE id = :id');
    $updateRequest->execute([
        ':status' => 'Cancelled',
        ':id' => $requestId,
    ]);

    if (!empty($request['technician_id'])) {
        $releaseTech = $pdo->prepare('UPDATE technicians SET is_available = 1 WHERE id = :id');
        $releaseTech->execute([':id' => (int)$request['technician_id']]);
    }

    $pdo->commit();

    jsonResponse(200, [
        'success' => true,
        'message' => 'Request cancelled successfully.',
        'data' => ['request_id' => $requestId, 'status' => 'Cancelled'],
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
