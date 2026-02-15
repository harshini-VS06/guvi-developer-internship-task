<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_management');

// Redis configuration
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);

// MongoDB configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles');

/**
 * Get MySQL database connection with prepared statement support
 */
function getMySQLConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Get Redis connection for session management
 */
function getRedisConnection() {
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        return $redis;
    } catch (Exception $e) {
        throw new Exception("Redis Connection failed: " . $e->getMessage());
    }
}

/**
 * Get MongoDB connection for profile data
 */
function getMongoDBConnection() {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $mongoClient = new MongoDB\Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
        return $mongoClient->selectDatabase(MONGO_DB);
    } catch (Exception $e) {
        throw new Exception("MongoDB Connection failed: " . $e->getMessage());
    }
}

/**
 * Initialize database tables
 */
function initializeDatabase() {
    $conn = getMySQLConnection();
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    $conn->close();
    return true;
}
?>
