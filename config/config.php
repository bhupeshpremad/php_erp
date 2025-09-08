<?php

// Define ROOT_PATH and ROOT_DIR_PATH early
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/'); // Absolute path to php_erp/
// define('BASE_PATH', ROOT_PATH); // Removed, as it's redundant

if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', rtrim(ROOT_PATH, '/') . DIRECTORY_SEPARATOR);
}

if (!class_exists('AppConfig')) {
    // Load environment variables if .env exists - moved here to ensure they are available for getConfig
    if (file_exists(__DIR__ . '/../.env')) {
        $env = @parse_ini_file(__DIR__ . '/../.env');
        if ($env && is_array($env)) {
            foreach ($env as $key => $value) {
                $_ENV[$key] = $value;
            }
        }
    }
    
    // Auto-detect localhost and default to local env when running on a dev machine
    if (!isset($_ENV['APP_ENV'])) {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            $_ENV['APP_ENV'] = 'local';
        }
    }

    class AppConfig {
        public static function getConfig() {
            // Determine environment from APP_ENV, default to 'production'
            $appEnv = $_ENV['APP_ENV'] ?? 'production';

            if ($appEnv === 'local') {
                // Local configuration
                return [
                    'host' => $_ENV['DB_HOST_LOCAL'] ?? 'localhost',
                    'db' => $_ENV['DB_NAME_LOCAL'] ?? 'php_erp3_db',
                    'user' => $_ENV['DB_USER_LOCAL'] ?? 'root',
                    'pass' => $_ENV['DB_PASS_LOCAL'] ?? '',
                    'charset' => $_ENV['DB_CHARSET_LOCAL'] ?? 'utf8mb4',
                    'base_url' => $_ENV['BASE_URL_LOCAL'] ?? 'http://localhost/Comparing/php_erp3/',
                    'subdirectory' => 'php_erp3'
                ];
            } else {
                // Live/Production configuration
                return [
                    'host' => $_ENV['DB_HOST_LIVE'] ?? 'localhost',
                    'db' => $_ENV['DB_NAME_LIVE'] ?? 'u404997496_erp_u404997496',
                    'user' => $_ENV['DB_USER_LIVE'] ?? 'u404997496_erp_u404997496',
                    'pass' => $_ENV['DB_PASS_LIVE'] ?? 'Purewood@2025#',
                    'charset' => $_ENV['DB_CHARSET_LIVE'] ?? 'utf8mb4',
                    'base_url' => $_ENV['BASE_URL_LIVE'] ?? 'https://crm.purewood.in/',
                    'subdirectory' => ''
                ];
            }
        }

        public static function baseUrl() {
            $config = self::getConfig();
            return rtrim($config['base_url'], '/') . '/';
        }
        
        public static function isLocalhost() {
            $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
            return in_array($host, ['localhost', '127.0.0.1']);
        }

        public static function getDsn() {
            $config = self::getConfig();
            return "mysql:host=" . $config['host'] . ";dbname=" . $config['db'] . ";charset=" . $config['charset'];
        }

        public static function getUser() {
            return self::getConfig()['user'];
        }

        public static function getPass() {
            return self::getConfig()['pass'];
        }
    }
}

// Determine APP_ENV for error reporting
$appEnv = $_ENV['APP_ENV'] ?? 'production';

if ($appEnv === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

$dsn = AppConfig::getDsn();
$user = AppConfig::getUser();
$pass = AppConfig::getPass();

$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Explicitly disable emulation
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("DB Connection Failed: " . $e->getMessage());
    if ($appEnv === 'local') {
        exit("DB Connection Failed: " . $e->getMessage());
    } else {
        exit("An unexpected error occurred. Please try again later.");
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', AppConfig::baseUrl());
}

// SMTP configuration
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? 'crm@thepurewood.com');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? 'Rusty@2014');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 465);
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? 'crm@thepurewood.com');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'Purewood Admin');