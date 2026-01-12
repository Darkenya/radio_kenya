<?php
// config.php - Neon Connection with Error Handling
function getDBConnection() {
    $connection_url = getenv('DATABASE_URL') ?: 'postgresql://neondb_owner:npg_CwBGz3gD1arA@ep-plain-field-ahzbskq1-pooler.c-3.us-east-1.aws.neon.tech/neondb?sslmode=require';

    try {
        // Use @ to suppress the warning so we can handle it cleanly
        $conn = @pg_connect($connection_url);
        
        if (!$conn) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed. Check your DATABASE_URL.']);
            exit;
        }
        return $conn;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
        exit;
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
