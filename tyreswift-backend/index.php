<?php
/**
 * Main API router for TyreSwift backend.
 *
 * Routes requests like:
 * /tyreswift-backend/create_request
 * /tyreswift-backend/get_pending_requests
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

if ($scriptDir !== '' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}

$route = trim($path, '/');

$routes = [
    '' => __DIR__ . '/api/get_pending_requests.php', // default route
    'create_request' => __DIR__ . '/api/create_request.php',
    'get_nearest_technician' => __DIR__ . '/api/get_nearest_technician.php',
    'accept_request' => __DIR__ . '/api/accept_request.php',
    'update_status' => __DIR__ . '/api/update_status.php',
    'cancel_request' => __DIR__ . '/api/cancel_request.php',
    'get_request_status' => __DIR__ . '/api/get_request_status.php',
    'get_pending_requests' => __DIR__ . '/api/get_pending_requests.php',
];

if (!array_key_exists($route, $routes)) {
    jsonResponse(404, [
        'success' => false,
        'message' => 'Route not found.',
        'available_routes' => array_keys($routes),
    ]);
}

require $routes[$route];
