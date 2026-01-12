<?php
// api.php - Main API Handler
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

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
    case 'filter_stations':
        filterStations($conn, $_GET);
        break;
    case 'increment_listeners':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        incrementListeners($conn, $id);
        break;
    case 'get_favorites':
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        getFavorites($conn, $userId);
        break;
    case 'toggle_favorite':
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $stationId = isset($_GET['station_id']) ? intval($_GET['station_id']) : 0;
        toggleFavorite($conn, $userId, $stationId);
        break;
    case 'add_to_recent':
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $stationId = isset($_GET['station_id']) ? intval($_GET['station_id']) : 0;
        // The duration is typically sent separately when the user stops listening, 
        // but for now, we only log the play event.
        addToRecentlyPlayed($conn, $userId, $stationId);
        break;
    case 'get_recent':
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        getRecentlyPlayed($conn, $userId);
        break;
    default:
        echo json_encode(array('error' => 'Invalid action'));
}

$conn->close();

// --- Functions ---

// Get all active stations
function getStations($conn) {
    // logo field is now MEDIUMTEXT
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE ORDER BY listeners DESC";
    $result = $conn->query($sql);
    $stations = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stations[] = $row;
        }
    }
    
    echo json_encode($stations);
}

// Get a single station
function getStation($conn, $id) {
    if ($id <= 0) {
        echo json_encode(array('error' => 'Invalid station ID'));
        return;
    }
    
    $stmt = $conn->prepare("SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $station = $result->fetch_assoc();
    
    echo json_encode($station);
}

// Search stations
function searchStations($conn, $query) {
    $searchQuery = "%" . $query . "%";
    $stmt = $conn->prepare("SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE AND (name LIKE ? OR genre LIKE ? OR city LIKE ?) ORDER BY listeners DESC");
    $stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $stations = array();
    
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
    }
    
    echo json_encode($stations);
}

// Get trending stations (top 20)
function getTrendingStations($conn) {
    // Uses the 'trending_stations' VIEW
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM trending_stations";
    $result = $conn->query($sql);
    $stations = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stations[] = $row;
        }
    }
    
    echo json_encode($stations);
}

// Get distinct genres
function getGenres($conn) {
    $sql = "SELECT genre, COUNT(*) as count FROM stations WHERE is_active = TRUE GROUP BY genre ORDER BY count DESC";
    $result = $conn->query($sql);
    $genres = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $genres[] = $row;
        }
    }
    
    echo json_encode($genres);
}

// Get distinct cities/counties
function getCities($conn) {
    $sql = "SELECT city, COUNT(*) as count FROM stations WHERE is_active = TRUE GROUP BY city ORDER BY count DESC";
    $result = $conn->query($sql);
    $cities = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row;
        }
    }
    
    echo json_encode($cities);
}

// Filter stations by genre and/or city
function filterStations($conn, $filters) {
    $sql = "SELECT id, name, frequency, genre, city, stream_url, logo, color, listeners, description FROM stations WHERE is_active = TRUE";
    $params = array();
    $types = '';
    
    if (!empty($filters['genre'])) {
        $sql .= " AND genre = ?";
        $params[] = $filters['genre'];
        $types .= 's';
    }
    
    if (!empty($filters['city'])) {
        $sql .= " AND city = ?";
        $params[] = $filters['city'];
        $types .= 's';
    }
    
    $sql .= " ORDER BY listeners DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stations = array();
    
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
    }
    
    echo json_encode($stations);
}

// Increment listeners
function incrementListeners($conn, $id) {
    if ($id <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid station ID'));
        return;
    }
    
    // Call stored procedure
    $stmt = $conn->prepare("CALL IncrementListeners(?)");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    echo json_encode(array('success' => $success));
}

// Add to recently played (Uses stored procedure AddToRecentlyPlayed)
function addToRecentlyPlayed($conn, $userId, $stationId) {
    if ($userId <= 0 || $stationId <= 0) {
        echo json_encode(array('success' => false, 'error' => 'Invalid IDs'));
        return;
    }
    
    $stmt = $conn->prepare("CALL AddToRecentlyPlayed(?, ?)");
    $stmt->bind_param("ii", $userId, $stationId);
    $success = $stmt->execute();
    
    echo json_encode(array('success' => $success));
}

// Get user favorites
function getFavorites($conn, $userId) {
    if ($userId <= 0) {
        echo json_encode(array('error' => 'Invalid User ID'));
        return;
    }
    
    $stmt = $conn->prepare("SELECT s.* FROM stations s INNER JOIN favorites f ON s.id = f.station_id WHERE f.user_id = ? AND s.is_active = TRUE ORDER BY f.added_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stations = array();
    
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
    }
    
    echo json_encode($stations);
}

// Toggle favorite
function toggleFavorite($conn, $userId, $stationId) {
    $stmt = $conn->prepare("CALL ToggleFavorite(?, ?)");
    $stmt->bind_param("ii", $userId, $stationId);
    $success = $stmt->execute();
    
    echo json_encode(array('success' => $success));
}

// Get recently played
function getRecentlyPlayed($conn, $userId) {
    $stmt = $conn->prepare("SELECT s.*, r.played_at FROM stations s INNER JOIN recently_played r ON s.id = r.station_id WHERE r.user_id = ? ORDER BY r.played_at DESC LIMIT 10");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stations = array();
    
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
    }
    
    echo json_encode($stations);
}