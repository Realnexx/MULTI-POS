<?php
// This loader deliberately has no database or schema side effects.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
try {
    $file = __DIR__ . '/config.local.php';
    if (!is_file($file)) {
        throw new RuntimeException('Private application configuration is missing.');
    }
    $appConfig = require $file;
    if (!is_array($appConfig) || !in_array($appConfig['environment'] ?? '', ['local', 'production'], true)) {
        throw new RuntimeException('Invalid application environment.');
    }
    foreach (['host', 'user', 'password', 'name'] as $key) {
        if (!isset($appConfig['database'][$key]) || !is_string($appConfig['database'][$key])) {
            throw new RuntimeException('Incomplete database configuration.');
        }
    }
    $base = $appConfig['base_path'] ?? null;
    if (!is_string($base) || !preg_match('~^/(?:[A-Za-z0-9_-]+/)*$~D', $base)) {
        throw new RuntimeException('Base path must be / or a path such as /pos/.');
    }
    define('APP_ENV', $appConfig['environment']);
    define('APP_BASE_PATH', $base);
    if (APP_ENV === 'local') {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }
} catch (Throwable $error) {
    error_log('Application configuration error: ' . $error->getMessage());
    http_response_code(503);
    exit('Service temporarily unavailable.');
}
