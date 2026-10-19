<?php
/**
 * Database Connection Module (php/db.php)
 * Uses PHP Data Objects (PDO) with prepared statements, strict error handling,
 * and automatic port fallback.
 */

require_once __DIR__ . '/config.php';

$globalPdoInstance = null;
$activeDbPort      = null;

/**
 * Establish and return a secure PDO database connection.
 *
 * @param string|null $overrideDbName Optional database name override
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(?string $overrideDbName = null): PDO {
    global $globalPdoInstance, $activeDbPort;

    $targetDb = ($overrideDbName !== null) ? $overrideDbName : DB_NAME;

    if ($globalPdoInstance !== null && $overrideDbName === null) {
        return $globalPdoInstance;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET . ' COLLATE utf8mb4_unicode_ci'
    ];

    $portsToTry = [DB_PORT];
    if (defined('DB_FALLBACK_PORT') && DB_FALLBACK_PORT !== DB_PORT) {
        $portsToTry[] = DB_FALLBACK_PORT;
    }

    $lastException = null;

    foreach ($portsToTry as $port) {
        try {
            // First connect without database to ensure DB creation if needed
            $dsnWithoutDb = sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, $port, DB_CHARSET);
            $tempPdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, $options);

            if (!empty($targetDb)) {
                $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$targetDb` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
            }

            // Connect directly to target database
            $dsnWithDb = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, $port, $targetDb, DB_CHARSET);
            $pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);

            // Ensure users table exists
            ensureUsersTable($pdo);

            $activeDbPort = $port;
            if ($overrideDbName === null) {
                $globalPdoInstance = $pdo;
            }

            return $pdo;
        } catch (PDOException $e) {
            $lastException = $e;
            log_app_error("Failed connecting to MySQL on port $port", $e);
            continue;
        }
    }

    log_app_error('CRITICAL: All database connection attempts failed.', $lastException);
    throw new PDOException('Database service unavailable. Please ensure MySQL is running.');
}

/**
 * Automatically creates the users table if it does not exist.
 *
 * @param PDO $pdo
 * @return void
 */
function ensureUsersTable(PDO $pdo): void {
    $tableSql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(100) NOT NULL,
        `username` VARCHAR(50) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `uq_users_username` UNIQUE (`username`),
        CONSTRAINT `uq_users_email` UNIQUE (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($tableSql);
}

/**
 * Diagnostic helper to check database health.
 *
 * @return array
 */
function checkDbConnection(): array {
    global $activeDbPort;
    try {
        $pdo = getDbConnection();
        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        return [
            'status' => 'connected',
            'port'   => $activeDbPort,
            'server' => $serverVersion,
            'db'     => DB_NAME,
            'user'   => DB_USER
        ];
    } catch (Exception $e) {
        return [
            'status'  => 'error',
            'message' => 'Unable to connect to database. Technical details logged privately.'
        ];
    }
}
