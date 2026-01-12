<?php
// api.php - Updated for Supabase (PostgreSQL)
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_stations': getStations($conn); break;
    case 'get_station': getStation($conn, isset($_GET['id']) ? intval($_GET['id']) : 0); break;
    case 'search_stations': searchStations($conn, isset($_GET['q']) ? $_GET['q'] : ''); break;
    case 'get_trending': getTrendingStations($conn); break;
    case 'get_genres': getGenres($conn); break;
    case 'get_cities': getCities($conn); break;
    case 'increment_listeners': incrementListeners($conn, isset($_GET['id']) ? intval($_GET['id']) : 0); break;
    default: echo json_encode(array('error' => 'Invalid action'));
}

pg_close($conn);

function getStations($conn) {
    $sql = "SELECT * FROM stations WHERE is_active = TRUE ORDER BY listeners DESC";
    $result = pg_query($conn, $sql);
    echo json_encode(pg_fetch_all($result) ?: []);
}

function getStation($conn, $id) {
    $result = pg_query_params($conn, "SELECT * FROM stations WHERE id = $1 AND is_active = TRUE", array($id));
    echo json_encode(pg_fetch_assoc($result) ?: new stdClass());
}

function searchStations($conn, $query) {
    $search = "%$query%";
    $sql = "SELECT * FROM stations WHERE is_active = TRUE AND (name ILIKE $1 OR genre ILIKE $1 OR city ILIKE $1) ORDER BY listeners DESC";
    $result = pg_query_params($conn, $sql, array($search));
    echo json_encode(pg_fetch_all($result) ?: []);
}

function getTrendingStations($conn) {
    // In Postgres, views are queried like tables
    $result = pg_query($conn, "SELECT * FROM trending_stations");
    echo json_encode(pg_fetch_all($result) ?: []);
}

function incrementListeners($conn, $id) {
    // Calling the Postgres function we created earlier
    $result = pg_query_params($conn, "SELECT increment_listeners($1)", array($id));
    echo json_encode(array('success' => (bool)$result));
}
