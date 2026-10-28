<?php
/**
 * logout.php - Secure Session Terminator
 * 
 * Safely clears authenticated session variables, invalidates the session cookie,
 * destroys the server-side session, and redirects to the login screen.
 */

require_once __DIR__ . '/includes/session.php';

init_secure_session();

clear_user_session();

header("Location: /STUDENTPORTAL/login.php?message=logged_out");
exit;
