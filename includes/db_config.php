<?php
// Database Configuration File

// IMPORTANT: Do not commit changes to this file with local credentials.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'passione_calabria');
define('DB_USER', 'passione_user');
define('DB_PASSWORD', 'password');

// The rest of the file can remain as is, since the constants are what's used by the application.

// Load database configuration from environment variables or config file
function getDatabaseConfig() {
    return [
        'host' => defined('DB_HOST') ? DB_HOST : '127.0.0.1',
        'dbname' => defined('DB_NAME') ? DB_NAME : 'passione_calabria',
        'username' => defined('DB_USER') ? DB_USER : 'passione_user',
        'password' => defined('DB_PASSWORD') ? DB_PASSWORD : 'password'
    ];
}

// Function to test database connection
function testDatabaseConnection() {
    $config = getDatabaseConfig();
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        return ['success' => true, 'message' => 'Connection successful'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
    }
}
?>