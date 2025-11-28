<?php
// Database configuration (for future MySQL integration)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'soeknadssystem');

// Session settings (must be set before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Application settings
define('APP_NAME', 'Hjelpelærer Søknadssystem');
define('APP_URL', 'http://localhost/soeknadssystem');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/soeknadssystem');
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('UPLOAD_DIR_WEB', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('BASE_URL', '/soeknadssystem');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Oslo');

?>