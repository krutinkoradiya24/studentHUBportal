<?php
/**
 * includes/session.php - Hardened Session & Security Management
 * 
 * Enforces OWASP-compliant strict sessions, 15-minute inactivity timeout,
 * session fixation prevention, cryptographically strong CSRF protection,
 * and XSS-safe template output escaping.
 */

if (!defined('SESSION_INITIALIZED')) {
    define('SESSION_INITIALIZED', true);

    define('SESSION_TIMEOUT_SECONDS', 900); // 15 minutes

    /**
     * Initialize secure session with hardened cookie parameters
     */
    function init_secure_session(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure hardened session parameters prior to start
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        session_set_cookie_params([
            'lifetime' => 0,          // Cleared upon browser close
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,   // Enabled dynamically on HTTPS
            'httponly' => true,       // Inaccessible to JavaScript
            'samesite' => 'Lax'       // CSRF barrier
        ]);

        session_start();

        // Enforce inactivity timeout
        check_session_timeout();
    }

    /**
     * Check if user session has expired after 15 minutes of inactivity
     */
    function check_session_timeout(): void {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $now = time();
        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
            // Session expired
            clear_user_session();
            
            // Redirect to login with timeout notice
            $redirectUrl = '/STUDENTPORTAL/login.php?error=timeout';
            if (headers_sent()) {
                echo "<script>window.location.href='$redirectUrl';</script>";
            } else {
                header("Location: $redirectUrl");
            }
            exit;
        }

        // Update last activity timestamp
        $_SESSION['last_activity'] = $now;
    }

    /**
     * Wipe session data and destroy session cookie
     */
    function clear_user_session(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Generate or return session CSRF token
     */
    function generate_csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate submitted CSRF token using timing-safe comparison
     */
    function validate_csrf_token(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Output a hidden CSRF token input tag
     */
    function csrf_field(): string {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Sanitize output against Cross-Site Scripting (XSS)
     */
    function e(?string $string): string {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Set a flash notification message in session
     */
    function set_flash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type'    => $type, // 'success', 'error', 'warning', 'info'
            'message' => $message
        ];
    }

    /**
     * Retrieve and clear flash notification message
     */
    function get_flash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
