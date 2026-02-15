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
    // Verify session from Redis
    $redis = getRedisConnection();
    $sessionKey = "session:" . $sessionToken;
    $sessionData = $redis->get($sessionKey);
    
    if (!$sessionData) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired session'
        ]);
        exit;
    }
    
    $session = json_decode($sessionData, true);
    
    // Verify userId matches session
    if ($session['userId'] != $userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }
    
    // Handle different actions
    switch ($action) {
        case 'verify':
            echo json_encode([
                'success' => true,
                'message' => 'Session valid'
            ]);
            break;
            
        case 'get':
            // Get profile from MongoDB
            $mongodb = getMongoDBConnection();
            $collection = $mongodb->profiles;
            
            $profile = $collection->findOne(['userId' => (int)$userId]);
            
            if ($profile) {
                echo json_encode([
                    'success' => true,
                    'profile' => [
                        'fullName' => $profile['fullName'] ?? '',
                        'age' => $profile['age'] ?? '',
                        'dob' => $profile['dob'] ?? '',
                        'contact' => $profile['contact'] ?? '',
                        'address' => $profile['address'] ?? ''
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'profile' => null
                ]);
            }
            break;
            
        case 'update':
            // Get profile data from input
            $fullName = isset($input['fullName']) ? trim($input['fullName']) : '';
            $age = isset($input['age']) ? (int)$input['age'] : null;
            $dob = isset($input['dob']) ? trim($input['dob']) : '';
            $contact = isset($input['contact']) ? trim($input['contact']) : '';
            $address = isset($input['address']) ? trim($input['address']) : '';
            
            // Update profile in MongoDB
            $mongodb = getMongoDBConnection();
            $collection = $mongodb->profiles;
            
            $profileData = [
                'userId' => (int)$userId,
                'fullName' => $fullName,
                'age' => $age,
                'dob' => $dob,
                'contact' => $contact,
                'address' => $address,
                'updatedAt' => new MongoDB\BSON\UTCDateTime()
            ];
            
            $result = $collection->updateOne(
                ['userId' => (int)$userId],
                ['$set' => $profileData],
                ['upsert' => true]
            );
            
            if ($result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'No changes made to profile'
                ]);
            }
            break;
            
        case 'logout':
            // Delete session from Redis
            $redis->del($sessionKey);
            
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
