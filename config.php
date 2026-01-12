<?php
// config.php
function getDBConnection() {
    // Make sure there are no spaces in this string
    $connection_url = "postgresql://postgres:SDD5euAbv81BYjnF@db.jbjihmsfjfxdespsbhgc.supabase.co:5432/postgres";

    $conn = pg_connect($connection_url);
    
    if (!$conn) {
        // If this fails, it outputs JSON, which is what the frontend expects
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Database connection failed']));
    }
    
    return $conn;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
?>
