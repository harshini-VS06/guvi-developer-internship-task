<?php
// Must be at the very top for the MongoDB library to work
use MongoDB\Driver\ServerApi;

// --- DATABASE CONFIGURATION ---
// These now check Render's environment variables first, then fallback to local
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// Redis configuration
// Redis Cloud Configuration
define('REDIS_HOST', getenv('REDIS_HOST')); 
define('REDIS_PORT', getenv('REDIS_PORT'));
define('REDIS_PASS', getenv('REDIS_PASS')); // Add this line!

// MongoDB configuration
define('MONGO_DB', getenv('MONGO_DB') ?: 'user_profiles');

/**
 * Get MySQL database connection
 */
/**
 * Get MySQL connection for TiDB Cloud
 */
function getMySQLConnection() {
    // 1. Get credentials from Render Environment Variables
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $name = getenv('DB_NAME') ?: 'test'; // TiDB default DB is usually 'test'
    $port = getenv('DB_PORT') ?: 4000;

    $conn = mysqli_init();

    // 2. TiDB requires SSL for security. This tells PHP to use it.
    // On Render/Linux, you don't usually need a certificate file path.
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

    // 3. Establish the connection
    if (!$conn->real_connect($host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL)) {
        throw new Exception("TiDB Connection failed: " . mysqli_connect_error());
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Get Redis connection with Authentication
 */
function getRedisConnection() {
    try {
        $redis = new Redis();
        
        // 1. Connect using the Host and Port from your Render Env
        $redis->connect(REDIS_HOST, (int)REDIS_PORT);
        
        // 2. Authenticate! This is the "Pro" step required for Cloud.
        // We use REDIS_PASS which you should define in your config.
        if (defined('REDIS_PASS') && REDIS_PASS) {
            if (!$redis->auth(REDIS_PASS)) {
                throw new Exception("Redis authentication failed.");
            }
        }
        
        return $redis;
    } catch (Exception $e) {
        // In a strict internship, you should log this and throw a clear message
        throw new Exception("Redis Connection failed: " . $e->getMessage());
    }
}

/**
 * Get MongoDB connection (Stable API Version)
 */
function getMongoDBConnection() {
    try {
        // Ensure Composer's autoloader is loaded
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        // Get URI from Render Environment Variable
        $uri = getenv('MONGO_URI') ?: 'mongodb://localhost:27017';

        // Set the version of the Stable API on the client
        $apiVersion = new ServerApi(ServerApi::V1);
        
        // Create client with Stable API (ServerApi::V1)
        $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);

        // Optional: Ping test to verify connection
        // $client->selectDatabase('admin')->command(['ping' => 1]);

        return $client->selectDatabase(MONGO_DB);
    } catch (Exception $e) {
        throw new Exception("MongoDB Connection failed: " . $e->getMessage());
    }
}

/**
 * Automatically configure PHP to use Redis for Sessions
 */
function setupRedisSessions() {
    try {
        // Build the connection string for the PHP session handler
        // Format: tcp://host:port?auth=password
        $savePath = "tcp://" . REDIS_HOST . ":" . REDIS_PORT;
        if (REDIS_PASS) {
            $savePath .= "?auth=" . REDIS_PASS;
        }

        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $savePath);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    } catch (Exception $e) {
        // Fallback to standard sessions if Redis fails
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

/**
 * Initialize database tables
 */
function initializeDatabase() {
    $conn = getMySQLConnection();
    
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
