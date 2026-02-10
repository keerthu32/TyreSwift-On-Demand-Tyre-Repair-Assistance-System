<?php
/**
 * Database and API helper configuration for TyreSwift backend.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

const DB_HOST = '127.0.0.1';
const DB_NAME = 'tyreswift_db';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Returns PDO connection with safe defaults.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

/**
 * Reads and decodes JSON body into array.
 */
function getJsonInput(): array
{
    $rawInput = file_get_contents('php://input');

    if ($rawInput === false || trim($rawInput) === '') {
        return [];
    }

    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        jsonResponse(400, [
            'success' => false,
            'message' => 'Invalid JSON payload provided.',
        ]);
    }

    return $data;
}

/**
 * Standardized JSON response helper.
 */
function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Ensures endpoint is called with expected HTTP method.
 */
function enforceMethod(string $expectedMethod): void
{
    $actualMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $expectedMethod = strtoupper($expectedMethod);

    if ($actualMethod === $expectedMethod) {
        return;
    }

    jsonResponse(405, [
        'success' => false,
        'message' => "Method not allowed. Use {$expectedMethod}.",
        'expected_method' => $expectedMethod,
        'received_method' => $actualMethod,
        'hint' => 'Do not open write endpoints directly in browser address bar. Use Postman or cURL.',
    ]);
}

/**
 * Returns a validated integer value from input.
 */
function requirePositiveInt(array $data, string $field): int
{
    if (!isset($data[$field]) || filter_var($data[$field], FILTER_VALIDATE_INT) === false) {
        jsonResponse(422, [
            'success' => false,
            'message' => "Field '{$field}' must be a valid integer.",
        ]);
    }

    $value = (int)$data[$field];

    if ($value <= 0) {
        jsonResponse(422, [
            'success' => false,
            'message' => "Field '{$field}' must be greater than zero.",
        ]);
    }

    return $value;
}

/**
 * Returns a validated latitude/longitude decimal value.
 */
function requireCoordinate(array $data, string $field, float $min, float $max): float
{
    if (!isset($data[$field]) || !is_numeric($data[$field])) {
        jsonResponse(422, [
            'success' => false,
            'message' => "Field '{$field}' must be numeric.",
        ]);
    }

    $value = (float)$data[$field];

    if ($value < $min || $value > $max) {
        jsonResponse(422, [
            'success' => false,
            'message' => "Field '{$field}' must be between {$min} and {$max}.",
        ]);
    }

    return $value;
}
