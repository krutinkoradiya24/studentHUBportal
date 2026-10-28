<?php
/**
 * db.php - Root Database Connector Wrapper
 * 
 * Proxies to includes/db.php for multi-port failover and robust MySQL connectivity.
 */

require_once __DIR__ . '/includes/db.php';

try {
    $conn = get_db();
} catch (Throwable $e) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    exit(json_encode(["status" => "error", "message" => $e->getMessage()]));
}
