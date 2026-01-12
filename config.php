<?php
// config.php - Database Configuration
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40504290');
define('DB_PASS', '5qnXTb61F7x'); // Default XAMPP password is empty
define('DB_NAME', 'if0_40504290_radio_kenya');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // This will still output JSON error for API files that include this
        die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// NOTE: The 'Content-Type: application/json' header has been removed from here.
// It will now be set in admin_api.php and api.php explicitly.
?>