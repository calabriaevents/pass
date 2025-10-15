<?php
// Database Configuration File
// IMPORTANT: This file should be placed outside the web root or protected by .htaccess

// Load database configuration from environment variables or config file
function getDatabaseConfig() {
    // First try to get from environment variables (recommended for production)
    $host = getenv('DB_HOST');
    $dbname = getenv('DB_NAME');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    
    // If environment variables are not set, use default configuration
    // IMPORTANT: Change these values and move to environment variables in production
    if (!$host) {
        $host = 'db5018301966.hosting-data.io';
        $dbname = 'dbs14504718';
        $username = 'dbu1167357';
        $password = 'Barboncino692@@'; // TODO: Move to environment variable immediately!
    }
    
    return [
        'host' => $host,
        'dbname' => $dbname,
        'username' => $username,
        'password' => $password
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