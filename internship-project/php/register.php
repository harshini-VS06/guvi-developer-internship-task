<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

// Validate username
if (strlen($username) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Username must be at least 3 characters long'
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

// Validate password
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

try {
    // Get MySQL connection
    $conn = getMySQLConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // 1. STORE REGISTERED DATA IN MYSQL (STRICT RULE)
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        // Get the generated ID to link to MongoDB
        $userId = $conn->insert_id;
        $stmt->close();
        $conn->close();

        // 2. STORE USER PROFILE IN MONGODB (STRICT RULE)
        try {
            $mongoDB = getMongoDBConnection();
            $profiles = $mongoDB->selectCollection('profiles');
            
            $profiles->insertOne([
                'user_id' => $userId, // The link to MySQL
                'username' => $username,
                'full_name' => '', // Details to be filled later
                'bio' => 'Welcome to my profile!',
                'profile_pic' => 'default.png',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! MySQL and MongoDB data synced.'
            ]);
        } catch (Exception $mongoEx) {
            // If MongoDB fails, we notify, but the MySQL account exists
            echo json_encode([
                'success' => true,
                'message' => 'User created, but MongoDB profile failed: ' . $mongoEx->getMessage()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'MySQL Registration failed.']);
        $stmt->close();
        $conn->close();
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
