<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['action']) || !isset($input['sessionToken']) || !isset($input['userId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$action = $input['action'];
$sessionToken = $input['sessionToken'];
$userId = $input['userId'];

try {
    // 1. REDIS: Verify session from Redis Cloud
    $redis = getRedisConnection();
    $sessionKey = "session:" . $sessionToken;
    $sessionData = $redis->get($sessionKey);
    
    if (!$sessionData) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired session']);
        $redis->close();
        exit;
    }
    
    $session = json_decode($sessionData, true);
    
    // Security check: Verify userId matches the session
    if ($session['userId'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        $redis->close();
        exit;
    }
    
    // Handle different actions
    switch ($action) {
        case 'verify':
            echo json_encode(['success' => true, 'message' => 'Session valid']);
            break;
            
    case 'get':
    $mongodb = getMongoDBConnection();
    $collection = $mongodb->profiles;
    
    // Use (int) to match your Atlas number format
    $profile = $collection->findOne(['user_id' => (int)$userId]);
    
    if ($profile) {
        // Convert MongoDB Document to a plain PHP Array manually
        // This prevents the Fatal Error in BSONArray.php
        echo json_encode([
            'success' => true,
            'profile' => [
                'fullName' => (string)($profile['fullName'] ?? $profile['full_name'] ?? ''),
                'age'      => (int)($profile['age'] ?? 0),
                'dob'      => (string)($profile['dob'] ?? ''),
                'contact'  => (string)($profile['contact'] ?? ''),
                'address'  => (string)($profile['address'] ?? '')
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'profile' => null]);
    }
    break;
        
    case 'update':
        $profileData = [
            'fullName' => isset($input['fullName']) ? trim($input['fullName']) : '',
            'age' => isset($input['age']) ? (int)$input['age'] : null,
            'dob' => isset($input['dob']) ? trim($input['dob']) : '',
            'contact' => isset($input['contact']) ? trim($input['contact']) : '',
            'address' => isset($input['address']) ? trim($input['address']) : '',
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
    
            $mongodb = getMongoDBConnection();
            $collection = $mongodb->profiles;
    
        $result = $collection->updateOne(
            ['user_id' => (int)$userId], // Ensure this is (int)
            ['$set' => $profileData],
            ['upsert' => true]
        );
    
            echo json_encode(['success' => true, 'message' => 'Profile updated!']);
            break;
            
        case 'logout':
            // 1. REDIS: Delete the session
            $redis->del($sessionKey);
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
    $redis->close(); // Always close your connections
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
