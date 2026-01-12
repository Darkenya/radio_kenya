<?php
// admin_api.php - Updated for Supabase (PostgreSQL)
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(array('error' => 'Unauthorized'));
    exit;
}

header('Content-Type: application/json');
require_once 'config.php';
$conn = getDBConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_station': addStation($conn); break;
    case 'get_all_stations': getAllStations($conn); break;
    case 'delete_station': deleteStation($conn); break;
}

function addStation($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO stations (name, frequency, genre, city, stream_url, logo, color, description) 
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id";
    
    $params = array($data['name'], $data['frequency'], $data['genre'], $data['city'], $data['stream_url'], $data['logo'], $data['color'], $data['description']);
    $result = pg_query_params($conn, $sql, $params);
    
    if ($result) {
        $row = pg_fetch_assoc($result);
        echo json_encode(array('success' => true, 'id' => $row['id']));
    } else {
        echo json_encode(array('success' => false, 'error' => pg_last_error($conn)));
    }
}

function getAllStations($conn) {
    $result = pg_query($conn, "SELECT * FROM stations ORDER BY created_at DESC");
    echo json_encode(pg_fetch_all($result) ?: []);
}

function deleteStation($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = pg_query_params($conn, "DELETE FROM stations WHERE id = $1", array($data['id']));
    echo json_encode(array('success' => (bool)$result));
}
