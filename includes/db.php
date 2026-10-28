<?php
/**
 * includes/db.php - Central Database Connector
 * 
 * Provides unified, resilient MySQLi and PDO database connections
 * with automated multi-port failover (3306, 3307) and strict error handling.
 * 
 * Target Database: user_registration_db
 * Extension Support: MySQLi & PDO_MYSQL
 */

if (!defined('DB_INITIALIZED')) {
    define('DB_INITIALIZED', true);

    mysqli_report(MYSQLI_REPORT_OFF); // We handle errors gracefully

    /**
     * Get active MySQLi connection
     * @return mysqli
     * @throws Exception if connection fails
     */
    function get_db(): mysqli {
        static $mysqliInstance = null;
        if ($mysqliInstance instanceof mysqli && @$mysqliInstance->ping()) {
            return $mysqliInstance;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $passEnv = getenv('DB_PASS');
        $passwords = ($passEnv !== false) ? [$passEnv, ''] : ['', 'root', 'admin123'];
        $dbName = getenv('DB_NAME') ?: 'user_registration_db';
        $ports = [(int)(getenv('DB_PORT') ?: 3307), 3306];

        $lastErr = '';

        foreach ($ports as $port) {
            foreach ($passwords as $pwd) {
                try {
                    $conn = @new mysqli($host, $user, $pwd, '', $port);
                    if (!$conn->connect_error) {
                        // Ensure DB exists
                        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $conn->select_db($dbName);
                        $conn->set_charset("utf8mb4");
                        $mysqliInstance = $conn;
                        return $mysqliInstance;
                    } else {
                        $lastErr = $conn->connect_error;
                    }
                } catch (Throwable $e) {
                    $lastErr = $e->getMessage();
                }
            }
        }

        // Log error
        $logPath = dirname(__DIR__) . '/data/app_errors.log';
        @file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] DB connection error: $lastErr\n", FILE_APPEND);
        
        throw new Exception("Unable to connect to MySQL database. Please ensure MySQL/MariaDB is running in XAMPP. Details: " . $lastErr);
    }

    /**
     * Get active PDO connection
     * @return PDO
     * @throws PDOException if connection fails
     */
    function get_pdo(): PDO {
        static $pdoInstance = null;
        if ($pdoInstance instanceof PDO) {
            return $pdoInstance;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $passEnv = getenv('DB_PASS');
        $passwords = ($passEnv !== false) ? [$passEnv, ''] : ['', 'root', 'admin123'];
        $dbName = getenv('DB_NAME') ?: 'user_registration_db';
        $ports = [(int)(getenv('DB_PORT') ?: 3307), 3306];

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $lastErr = null;

        foreach ($ports as $port) {
            foreach ($passwords as $pwd) {
                try {
                    $dsnBase = "mysql:host=$host;port=$port;charset=utf8mb4";
                    $temp = new PDO($dsnBase, $user, $pwd, $options);
                    $temp->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    $dsnDb = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
                    $pdoInstance = new PDO($dsnDb, $user, $pwd, $options);
                    return $pdoInstance;
                } catch (Throwable $e) {
                    $lastErr = $e;
                }
            }
        }

        $logPath = dirname(__DIR__) . '/data/app_errors.log';
        @file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] PDO connection error: " . ($lastErr ? $lastErr->getMessage() : '') . "\n", FILE_APPEND);
        throw new PDOException("Database connection failed: " . ($lastErr ? $lastErr->getMessage() : 'Unavailable'));
    }

    // Compatibility aliases for existing scripts
    function get_db_connection(): mysqli {
        return get_db();
    }

    function getHubDb(): PDO {
        return get_pdo();
    }
}
