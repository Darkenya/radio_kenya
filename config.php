<?php
// config.php - Updated for Supabase (PostgreSQL)

function getDBConnection() {
    // Connection string using your provided credentials
    $connection_url = "postgresql://postgres:SDD5euAbv81BYjnF@db.jbjihmsfjfxdespsbhgc.supabase.co:5432/postgres";

    // Use pg_connect for PostgreSQL instead of mysqli
    $conn = pg_connect($connection_url);
    
    if (!$conn) {
        die(json_encode(['error' => 'Connection failed: ' . pg_last_error()]));
    }
    
    return $conn;
}

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
?>
