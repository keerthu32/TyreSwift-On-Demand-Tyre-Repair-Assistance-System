<?php
/**
 * Main API router for TyreSwift backend.
 *
 * Supports both styles:
 * 1) /tyreswift-backend/create_request          (with .htaccess rewrite)
 * 2) /tyreswift-backend/index.php/create_request (without rewrite)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseName = trim((string)basename(__DIR__), '/'); // tyreswift-backend

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

// Handle accidental duplicated base URL like /tyreswift-backend/tyreswift-backend/
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
        'note' => 'Use only one /tyreswift-backend segment in URL. Example: /tyreswift-backend/create_request',
        'usage' => [
            'rewrite_enabled' => '/tyreswift-backend/create_request',
            'rewrite_disabled' => '/tyreswift-backend/index.php/create_request',
        ],
        'available_routes' => array_values(array_filter(array_keys($routes))),
    ]);
}

if (!array_key_exists($route, $routes) || !is_string($routes[$route])) {
    jsonResponse(404, [
        'success' => false,
        'message' => 'Route not found.',
        'hint' => 'If you opened /tyreswift-backend/tyreswift-backend/, remove one tyreswift-backend segment.',
        'available_routes' => array_values(array_filter(array_keys($routes))),
    ]);
}

require $routes[$route];
