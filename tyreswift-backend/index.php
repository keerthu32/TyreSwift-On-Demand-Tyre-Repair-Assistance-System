<?php
/**
 * Main API router for TyreSwift backend.
 *
 * Supports both styles:
 * 1) <base>/create_request            (with .htaccess rewrite)
 * 2) <base>/index.php/create_request  (without rewrite)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseName = trim((string)basename(__DIR__), '/'); // e.g. backend or tyreswift-backend
$publicBase = $scriptDir === '' ? '/' : $scriptDir; // e.g. /tyreswift/backend

// Remove base directory from full path if present.
if ($scriptDir !== '' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}

$path = '/' . ltrim($path, '/');

// Normalize /index.php/<route> and /index.php patterns.
if (strpos($path, '/index.php') === 0) {
    $path = substr($path, strlen('/index.php'));
    $path = '/' . ltrim($path, '/');
}

$route = trim($path, '/');


// Support legacy direct-style paths like /api/accept_request.php by normalizing to route name.
if (strpos($route, 'api/') === 0) {
    $route = substr($route, strlen('api/'));
}

if (substr($route, -4) === '.php') {
    $route = substr($route, 0, -4);
}

// Handle accidental duplicated base segment like /.../backend/backend/
if ($route === $baseName || strpos($route, $baseName . '/') === 0) {
    $route = trim(substr($route, strlen($baseName)), '/');
}

$routes = [
    '' => null,
    'create_request' => __DIR__ . '/api/create_request.php',
    'get_nearest_technician' => __DIR__ . '/api/get_nearest_technician.php',
    'accept_request' => __DIR__ . '/api/accept_request.php',
    'update_status' => __DIR__ . '/api/update_status.php',
    'cancel_request' => __DIR__ . '/api/cancel_request.php',
    'get_request_status' => __DIR__ . '/api/get_request_status.php',
    'get_pending_requests' => __DIR__ . '/api/get_pending_requests.php',
];

if ($route === '') {
    jsonResponse(200, [
        'success' => true,
        'message' => 'TyreSwift backend is running.',
        'base_path' => $publicBase,
        'note' => "Use a single base segment only. Example: {$publicBase}/create_request",
        'usage' => [
            'rewrite_enabled' => $publicBase . '/create_request',
            'rewrite_disabled' => $publicBase . '/index.php/create_request',
        ],
        'available_routes' => array_values(array_filter(array_keys($routes))),
    ]);
}

if (!array_key_exists($route, $routes) || !is_string($routes[$route])) {
    jsonResponse(404, [
        'success' => false,
        'message' => 'Route not found.',
        'base_path' => $publicBase,
        'hint' => "If your URL has duplicate '/{$baseName}/{$baseName}/', remove one segment.",
        'available_routes' => array_values(array_filter(array_keys($routes))),
    ]);
}

require $routes[$route];
