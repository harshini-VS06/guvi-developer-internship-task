<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

try {
    // Get MySQL connection
    $conn = getMySQLConnection();
    
    // Get user from database using prepared statement
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Generate session token
    $sessionToken = bin2hex(random_bytes(32));
    $userId = $user['id'];
    
    // Store session in Redis
    $redis = getRedisConnection();
    $sessionKey = "session:" . $sessionToken;
    $sessionData = json_encode([
        'userId' => $userId,
        'username' => $user['username'],
        'email' => $user['email'],
        'loginTime' => time()
    ]);
    
    // Set session in Redis with 24 hour expiration
    $redis->setex($sessionKey, 86400, $sessionData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'sessionToken' => $sessionToken,
        'userId' => $userId,
        'username' => $user['username'],
        'email' => $user['email']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
