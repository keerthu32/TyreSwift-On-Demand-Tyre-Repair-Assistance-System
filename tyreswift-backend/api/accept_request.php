<?php
/**
 * Accept a pending request and assign technician.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

enforceMethod('POST');

try {
    $pdo = getPDO();
    $data = getJsonInput();

    $requestId = requirePositiveInt($data, 'request_id');
    $technicianId = requirePositiveInt($data, 'technician_id');

    $pdo->beginTransaction();

    $requestStmt = $pdo->prepare('SELECT id, status FROM service_requests WHERE id = :id FOR UPDATE');
    $requestStmt->execute([':id' => $requestId]);
    $request = $requestStmt->fetch();

    if (!$request) {
        $pdo->rollBack();
        jsonResponse(404, ['success' => false, 'message' => 'Service request not found.']);
    }

    if ($request['status'] !== 'Pending') {
        $pdo->rollBack();
        jsonResponse(409, ['success' => false, 'message' => 'Only pending requests can be accepted.']);
    }

    $techStmt = $pdo->prepare('SELECT id, is_available FROM technicians WHERE id = :id FOR UPDATE');
    $techStmt->execute([':id' => $technicianId]);
    $technician = $techStmt->fetch();

    if (!$technician) {
        $pdo->rollBack();
        jsonResponse(404, ['success' => false, 'message' => 'Technician not found.']);
    }

    if ((int)$technician['is_available'] !== 1) {
        $pdo->rollBack();
        jsonResponse(409, ['success' => false, 'message' => 'Technician is not available.']);
    }

    $updateRequest = $pdo->prepare(
        'UPDATE service_requests SET technician_id = :technician_id, status = :status WHERE id = :request_id'
    );
    $updateRequest->execute([
        ':technician_id' => $technicianId,
        ':status' => 'Accepted',
        ':request_id' => $requestId,
    ]);

    $updateTech = $pdo->prepare('UPDATE technicians SET is_available = 0 WHERE id = :id');
    $updateTech->execute([':id' => $technicianId]);

    $pdo->commit();

    jsonResponse(200, [
        'success' => true,
        'message' => 'Request accepted successfully.',
        'data' => ['request_id' => $requestId, 'technician_id' => $technicianId, 'status' => 'Accepted'],
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
