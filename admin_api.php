<?php
// admin_api.php - Admin API Handler
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start(); // NEW: Start session for security check

// SECURITY CHECK: Ensure user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Respond with a 401 Unauthorized status code
    http_response_code(401); 
    header('Content-Type: application/json'); // Set JSON header before outputting JSON error
    echo json_encode(array('error' => 'Unauthorized access. Please log in.'));
    exit;
}
// END SECURITY CHECK

// Set Content-Type here explicitly for API output
header('Content-Type: application/json');

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'add_station':
        if ($method === 'POST') {
            addStation($conn);
        }
        break;
    case 'get_all_stations':
        getAllStations($conn);
        break;
    case 'toggle_status':
        if ($method === 'POST') {
            toggleStationStatus($conn);
        }
        break;
    case 'delete_station':
        if ($method === 'POST') {
            deleteStation($conn);
        }
        break;
    default:
        // Set header again just in case, though it should be set above
        if (!headers_sent()) {
             header('Content-Type: application/json');
        }
        echo json_encode(array('error' => 'Invalid action'));
}

$conn->close();

// Add new station
function addStation($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = isset($data['name']) ? $data['name'] : '';
    $frequency = isset($data['frequency']) ? $data['frequency'] : '';
    $genre = isset($data['genre']) ? $data['genre'] : '';
    $city = isset($data['city']) ? $data['city'] : '';
    $stream_url = isset($data['stream_url']) ? $data['stream_url'] : '';
    $logo = isset($data['logo']) ? $data['logo'] : '📻';
    $color = isset($data['color']) ? $data['color'] : '#667eea';
    $description = isset($data['description']) ? $data['description'] : '';
    
    // Validate required fields
    if (empty($name) || empty($frequency) || empty($genre) || empty($city) || empty($stream_url) || empty($description)) {
        echo json_encode(array('success' => false, 'error' => 'All fields are required'));
        return;
    }
    
    $sql = "INSERT INTO stations (name, frequency, genre, city, stream_url, logo, color, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $name, $frequency, $genre, $city, $stream_url, $logo, $color, $description);
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'id' => $conn->insert_id));
    } else {
        echo json_encode(array('success' => false, 'error' => $stmt->error));
    }
    
    $stmt->close();
}

// Get all stations (including inactive)
function getAllStations($conn) {
    $sql = "SELECT * FROM stations ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $stations = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stations[] = $row;
        }
    }
    
    echo json_encode($stations);
}

// Toggle station status
function toggleStationStatus($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid station ID'));
        return;
    }
    
    $sql = "UPDATE stations SET is_active = NOT is_active WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => $stmt->error));
    }
    
    $stmt->close();
}

// Delete station
function deleteStation($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid station ID'));
        return;
    }
    
    $sql = "DELETE FROM stations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => $stmt->error));
    }
    
    $stmt->close();
}