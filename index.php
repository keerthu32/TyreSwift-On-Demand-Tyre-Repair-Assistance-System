<?php
/**
 * Entry helper for deployments where this repository root is served directly.
 * Redirects to the actual backend router directory.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$backendEntry = __DIR__ . '/tyreswift-backend/index.php';

if (!is_file($backendEntry)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Backend entrypoint not found.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require $backendEntry;
