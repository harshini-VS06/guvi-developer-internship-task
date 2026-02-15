<?php
// Must be at the very top for the MongoDB library to work
use MongoDB\Driver\ServerApi;

// --- DATABASE CONFIGURATION ---
// We pull these directly from Render Environment Variables
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_PORT', getenv('DB_PORT') ?: 4000);

// Redis Cloud Configuration
define('REDIS_HOST', getenv('REDIS_HOST')); 
define('REDIS_PORT', getenv('REDIS_PORT'));
define('REDIS_PASS', getenv('REDIS_PASS'));

// MongoDB Configuration
define('MONGO_DB', getenv('MONGO_DB') ?: 'user_profiles');

/**
 * Get MySQL connection (TiDB Cloud)
 * STRICT RULE: Used for Registered Data (Login/Identity)
 */
function getMySQLConnection() {
    $conn = mysqli_init();

    // TiDB requires SSL. 
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

    if (!$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT, NULL, MYSQLI_CLIENT_SSL)) {
        throw new Exception("TiDB Connection failed: " . mysqli_connect_error());
    }

    // AUTO-RESOLVE: Create the users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Get Redis connection
 * STRICT RULE: Used for Session Management
 */
function getRedisConnection() {
    $redis = new Redis();
    if (!$redis->connect(REDIS_HOST, (int)REDIS_PORT)) {
        throw new Exception("Redis connection failed.");
    }
    if (REDIS_PASS && !$redis->auth(REDIS_PASS)) {
        throw new Exception("Redis authentication failed.");
    }
    return $redis;
}

/**
 * Get MongoDB connection
 * STRICT RULE: Used for User Profiles (JSON/BSON data)
 */
function getMongoDBConnection() {
    // Load Composer's autoloader for MongoDB library
    require_once __DIR__ . '/../vendor/autoload.php';

    $uri = getenv('MONGO_URI');
    $apiVersion = new ServerApi(ServerApi::V1);
    
    // Create client with Stable API
    $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);

    return $client->selectDatabase(MONGO_DB);
}
?>
