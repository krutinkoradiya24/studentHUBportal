<?php
/**
 * Configuration File: Database Credentials & Session Security
 * Part of Secure User Registration System
 */

// Prevent direct script access if needed
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// -------------------------------------------------------------
// Database Configuration
// -------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'user_registration_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Default MySQL port is 3306. If port 3306 is occupied, auto-fallback to 3307.
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_FALLBACK_PORT', 3307);

// -------------------------------------------------------------
// Session Security Settings
// -------------------------------------------------------------
function init_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $cookieParams = [
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        session_set_cookie_params($cookieParams);
        session_start();
    }
}

// -------------------------------------------------------------
// CSRF Protection Helpers
// -------------------------------------------------------------
function generate_csrf_token(): string {
    init_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    init_secure_session();
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// -------------------------------------------------------------
// Error Logging Configuration
// -------------------------------------------------------------
define('APP_LOG_FILE', dirname(__DIR__) . '/data/app_errors.log');

function log_app_error(string $message, ?Throwable $exception = null): void {
    $timestamp = date('[Y-m-d H:i:s]');
    $logEntry  = "$timestamp $message";
    if ($exception !== null) {
        $logEntry .= ' | Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine();
    }
    $logEntry .= PHP_EOL;
    error_log($logEntry, 3, APP_LOG_FILE);
}
