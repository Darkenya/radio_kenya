<?php
// config.php - Neon Connection Setup

function getDBConnection() {
    // 1. Look for the Vercel Environment Variable
    $connection_url = getenv('DATABASE_URL');

    // 2. Fallback to your Neon string if running locally
    if (!$connection_url) {
        $connection_url = 'postgresql://neondb_owner:npg_CwBGz3gD1arA@ep-plain-field-ahzbskq1-pooler.c-3.us-east-1.aws.neon.tech/neondb?sslmode=require';
    }

    // Connect using the PostgreSQL driver
    $conn = pg_connect($connection_url);

    if (!$conn) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Could not connect to Neon: ' . pg_last_error()]));
    }

    return $conn;
}

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
?>
