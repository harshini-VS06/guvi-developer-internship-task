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
            // 2. MONGODB: Fetch Profile Details (The Strict Rule)
            $mongodb = getMongoDBConnection();
            $collection = $mongodb->profiles;
            
            // Note: Using 'user_id' to match our register.php logic
            $profile = $collection->findOne(['user_id' => (int)$userId]);
            
            if ($profile) {
                echo json_encode([
                    'success' => true,
                    'profile' => [
                        'fullName' => $profile['fullName'] ?? '',
                        'age' => $profile['age'] ?? '',
                        'dob' => $profile['dob'] ?? '',
                        'contact' => $profile['contact'] ?? '',
                        'address' => $profile['address'] ?? '',
                        'bio' => $profile['bio'] ?? ''
                    ]
                ]);
            } else {
                echo json_encode(['success' => true, 'profile' => null]);
            }
            break;
            
        case 'update':
            // Prepare update data
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
            
            // 2. MONGODB: Update or Create Profile Details
            $result = $collection->updateOne(
                ['user_id' => (int)$userId],
                ['$set' => $profileData],
                ['upsert' => true]
            );
            
            echo json_encode([
                'success' => true,
                'message' => ($result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0) 
                             ? 'Profile updated successfully!' 
                             : 'No changes made'
            ]);
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
