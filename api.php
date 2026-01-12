<?php
// api.php - Updated for Supabase (PostgreSQL)
require_once 'config.php';

$conn = getDBConnection();
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_stations':
        getStations($conn);
        break;
    // ... other cases ...
}

// Replace the old MySQL Close
pg_close($conn);

function getStations($conn) {
    // 1. PostgreSQL query
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description 
            FROM stations WHERE is_active = TRUE ORDER BY listeners DESC";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        echo json_encode(["error" => "Query failed"]);
        return;
    }

    // 2. Fetch all rows (Postgres specific)
    $stations = pg_fetch_all($result);
    
    // 3. Output clean JSON
    echo json_encode($stations ?: []);
}
?>
