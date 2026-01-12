<?php
// api.php - Main API Handler (Updated for Neon PostgreSQL)
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Ensure we always return JSON
header('Content-Type: application/json');

switch ($action) {
    case 'get_stations':
        getStations($conn);
        break;
    case 'get_station':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        getStation($conn, $id);
        break;
    case 'search_stations':
        $query = isset($_GET['q']) ? $_GET['q'] : '';
        searchStations($conn, $query);
        break;
    case 'get_trending':
        getTrendingStations($conn);
        break;
    case 'get_genres':
        getGenres($conn);
        break;
    case 'get_cities':
        getCities($conn);
        break;
    case 'increment_listeners':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        incrementListeners($conn, $id);
        break;
    case 'get_favorites':
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        getFavorites($conn, $userId);
        break;
    case 'toggle_favorite':
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $stationId = isset($_GET['station_id']) ? intval($_GET['station_id']) : 0;
        toggleFavorite($conn, $userId, $stationId);
        break;
    case 'get_recent':
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        getRecentlyPlayed($conn, $userId);
        break;
    default:
        echo json_encode(array('error' => 'Invalid action'));
}

pg_close($conn);

// --- Functions ---

function getStations($conn) {
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE ORDER BY listeners DESC";
    $result = pg_query($conn, $sql);
    $stations = pg_fetch_all($result);
    echo json_encode($stations ?: []);
}

function getStation($conn, $id) {
    if ($id <= 0) {
        echo json_encode(array('error' => 'Invalid station ID'));
        return;
    }
    $result = pg_query_params($conn, "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE id = $1 AND is_active = TRUE", array($id));
    $station = pg_fetch_assoc($result);
    echo json_encode($station ?: new stdClass());
}

function searchStations($conn, $query) {
    $searchQuery = "%" . $query . "%";
    // Using ILIKE for case-insensitive search in Postgres
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE AND (name ILIKE $1 OR genre ILIKE $1 OR city ILIKE $1) ORDER BY listeners DESC";
    $result = pg_query_params($conn, $sql, array($searchQuery));
    $stations = pg_fetch_all($result);
    echo json_encode($stations ?: []);
}

function getTrendingStations($conn) {
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE ORDER BY listeners DESC LIMIT 20";
    $result = pg_query($conn, $sql);
    $stations = pg_fetch_all($result);
    echo json_encode($stations ?: []);
}

function getGenres($conn) {
    $sql = "SELECT genre, COUNT(*) as count FROM stations WHERE is_active = TRUE GROUP BY genre ORDER BY count DESC";
    $result = pg_query($conn, $sql);
    $genres = pg_fetch_all($result);
    echo json_encode($genres ?: []);
}

function getCities($conn) {
    $sql = "SELECT city, COUNT(*) as count FROM stations WHERE is_active = TRUE GROUP BY city ORDER BY count DESC";
    $result = pg_query($conn, $sql);
    $cities = pg_fetch_all($result);
    echo json_encode($cities ?: []);
}

function incrementListeners($conn, $id) {
    if ($id <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid station ID'));
        return;
    }
    // In Postgres we call functions with SELECT
    $result = pg_query_params($conn, "SELECT increment_listeners($1)", array($id));
    echo json_encode(array('success' => (bool)$result));
}

function getFavorites($conn, $userId) {
    if (empty($userId)) {
        echo json_encode(array('error' => 'Invalid User ID'));
        return;
    }
    $sql = "SELECT s.* FROM stations s INNER JOIN favorites f ON s.id = f.station_id WHERE f.user_id = $1 AND s.is_active = TRUE ORDER BY f.added_at DESC";
    $result = pg_query_params($conn, $sql, array($userId));
    $stations = pg_fetch_all($result);
    echo json_encode($stations ?: []);
}

function toggleFavorite($conn, $userId, $stationId) {
    if (empty($userId) || $stationId <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid IDs'));
        return;
    }
    $result = pg_query_params($conn, "SELECT toggle_favorite($1, $2)", array($userId, $stationId));
    echo json_encode(array('success' => (bool)$result));
}

function getRecentlyPlayed($conn, $userId) {
    if (empty($userId)) {
        echo json_encode(array('error' => 'Invalid User ID'));
        return;
    }
    $sql = "SELECT s.*, r.played_at FROM stations s INNER JOIN recently_played r ON s.id = r.station_id WHERE r.user_id = $1 ORDER BY r.played_at DESC LIMIT 10";
    $result = pg_query_params($conn, $sql, array($userId));
    $stations = pg_fetch_all($result);
    echo json_encode($stations ?: []);
}
?>
