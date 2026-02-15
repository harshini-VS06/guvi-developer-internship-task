<?php
/**
 * Database Setup Script
 * Run this once after deployment to initialize the MySQL database
 */

require_once 'php/config.php';

echo "Starting database setup...\n\n";

try {
    // Initialize MySQL database
    echo "Creating MySQL database and tables...\n";
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql)) {
        echo "✓ Database created or already exists\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select database
    $conn->select_db(DB_NAME);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Users table created or already exists\n";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    $conn->close();
    
    // Test Redis connection
    echo "\nTesting Redis connection...\n";
    $redis = new Redis();
    if ($redis->connect(REDIS_HOST, REDIS_PORT)) {
        echo "✓ Redis connection successful\n";
        $redis->ping();
        echo "✓ Redis is responding\n";
        $redis->close();
    } else {
        echo "⚠ Warning: Could not connect to Redis\n";
    }
    
    // Test MongoDB connection
    echo "\nTesting MongoDB connection...\n";
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $mongoClient = new MongoDB\Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
        $mongodb = $mongoClient->selectDatabase(MONGO_DB);
        
        // Create collection if it doesn't exist
        $collections = iterator_to_array($mongodb->listCollections());
        $collectionExists = false;
        
        foreach ($collections as $collection) {
            if ($collection->getName() === 'profiles') {
                $collectionExists = true;
                break;
            }
        }
        
        if (!$collectionExists) {
            $mongodb->createCollection('profiles');
            echo "✓ MongoDB 'profiles' collection created\n";
        } else {
            echo "✓ MongoDB 'profiles' collection already exists\n";
        }
        
        echo "✓ MongoDB connection successful\n";
        
    } catch (Exception $e) {
        echo "⚠ Warning: MongoDB connection failed: " . $e->getMessage() . "\n";
        echo "  MongoDB features will not work until connection is established\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Setup completed successfully!\n";
    echo "========================================\n";
    echo "\nYou can now access the application:\n";
    echo "- Registration: http://your-domain/register.html\n";
    echo "- Login: http://your-domain/login.html\n";
    echo "- Index: http://your-domain/\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
