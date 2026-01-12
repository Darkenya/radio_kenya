-- ============================================
-- RADIO STATION DATABASE - MySQL/XAMPP
-- EMPTY TABLES (NO SAMPLE DATA)
-- ============================================

-- Create Database
-- CREATE DATABASE IF NOT EXISTS radio_kenya;
USE if0_40504290_radio_kenya;

-- ============================================
-- TABLE 1: Stations
-- ============================================
CREATE TABLE IF NOT EXISTS stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    frequency VARCHAR(20) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    stream_url VARCHAR(255) NOT NULL,
    -- CORRECTION: logo changed from VARCHAR(10) to MEDIUMTEXT to store Base64 data URLs
    logo MEDIUMTEXT DEFAULT '📻',
    color VARCHAR(7) DEFAULT '#667eea',
    listeners INT DEFAULT 0,
    description TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_genre (genre),
    INDEX idx_listeners (listeners)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 2: Users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 3: Favorites
-- ============================================
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, station_id),
    INDEX idx_user (user_id),
    INDEX idx_station (station_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 4: Recently Played
-- ============================================
CREATE TABLE IF NOT EXISTS recently_played (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_seconds INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_station (station_id),
    INDEX idx_played_at (played_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 5: Listening History
-- ============================================
CREATE TABLE IF NOT EXISTS listening_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    listen_date DATE NOT NULL,
    total_minutes INT DEFAULT 0,
    listen_count INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_daily_listen (user_id, station_id, listen_date),
    INDEX idx_date (listen_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 6: Podcasts
-- ============================================
CREATE TABLE IF NOT EXISTS podcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    host VARCHAR(100),
    category VARCHAR(50),
    logo VARCHAR(255),
    rss_feed VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 7: Podcast Episodes
-- ============================================
CREATE TABLE IF NOT EXISTS podcast_episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    podcast_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    audio_url VARCHAR(255) NOT NULL,
    duration_seconds INT,
    episode_number INT,
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (podcast_id) REFERENCES podcasts(id) ON DELETE CASCADE,
    INDEX idx_podcast (podcast_id),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 8: Admin Users
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 9: Comments/Reviews
-- ============================================
CREATE TABLE IF NOT EXISTS station_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    comment TEXT NOT NULL,
    rating TINYINT CHECK (rating >= 1 AND rating <= 5),
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    INDEX idx_station (station_id),
    INDEX idx_user (user_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 10: Playlists
-- ============================================
CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 11: Playlist Stations
-- ============================================
CREATE TABLE IF NOT EXISTS playlist_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    station_id INT NOT NULL,
    position INT DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_playlist_station (playlist_id, station_id),
    INDEX idx_playlist (playlist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER //

-- Procedure to increment station listeners
CREATE PROCEDURE IncrementListeners(IN station_id INT)
BEGIN
    UPDATE stations 
    SET listeners = listeners + 1 
    WHERE id = station_id;
END //

-- Procedure to add station to recently played
CREATE PROCEDURE AddToRecentlyPlayed(IN user_id INT, IN station_id INT)
BEGIN
    INSERT INTO recently_played (user_id, station_id) 
    VALUES (user_id, station_id);
    
    -- Increment station listeners
    CALL IncrementListeners(station_id);
END //

-- Procedure to toggle favorite
CREATE PROCEDURE ToggleFavorite(IN user_id INT, IN station_id INT)
BEGIN
    DECLARE favorite_exists INT;
    
    SELECT COUNT(*) INTO favorite_exists 
    FROM favorites 
    WHERE user_id = user_id AND station_id = station_id;
    
    IF favorite_exists > 0 THEN
        DELETE FROM favorites 
        WHERE user_id = user_id AND station_id = station_id;
    ELSE
        INSERT INTO favorites (user_id, station_id) 
        VALUES (user_id, station_id);
    END IF;
END //

-- Procedure to get user statistics
CREATE PROCEDURE GetUserStats(IN user_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM favorites WHERE user_id = user_id) as total_favorites,
        (SELECT COUNT(DISTINCT station_id) FROM recently_played WHERE user_id = user_id) as total_stations_played,
        (SELECT SUM(duration_seconds) FROM recently_played WHERE user_id = user_id) as total_listen_time,
        (SELECT COUNT(*) FROM playlists WHERE user_id = user_id) as total_playlists;
END //

-- Procedure to clean old recently played records (older than 30 days)
CREATE PROCEDURE CleanOldRecentlyPlayed()
BEGIN
    DELETE FROM recently_played 
    WHERE played_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
END //

DELIMITER ;

-- ============================================
-- VIEWS
-- ============================================

-- View for trending stations
CREATE OR REPLACE VIEW trending_stations AS
SELECT * FROM stations 
WHERE is_active = TRUE 
ORDER BY listeners DESC 
LIMIT 20;

-- View for stations with favorite count
CREATE OR REPLACE VIEW stations_with_favorites AS
SELECT 
    s.*,
    COUNT(f.id) as favorite_count
FROM stations s
LEFT JOIN favorites f ON s.id = f.station_id
WHERE s.is_active = TRUE
GROUP BY s.id
ORDER BY favorite_count DESC;

-- View for stations with ratings
CREATE OR REPLACE VIEW stations_with_ratings AS
SELECT 
    s.*,
    AVG(c.rating) as average_rating,
    COUNT(c.id) as total_reviews
FROM stations s
LEFT JOIN station_comments c ON s.id = c.station_id AND c.is_approved = TRUE
WHERE s.is_active = TRUE
GROUP BY s.id;

-- View for popular genres
CREATE OR REPLACE VIEW popular_genres AS
SELECT 
    genre,
    COUNT(*) as station_count,
    SUM(listeners) as total_listeners
FROM stations
WHERE is_active = TRUE
GROUP BY genre
ORDER BY total_listeners DESC;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

-- Trigger to update station timestamp
CREATE TRIGGER before_station_update
BEFORE UPDATE ON stations
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

-- Trigger to update user timestamp
CREATE TRIGGER before_user_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

-- Trigger to log when station is played (listening history)
CREATE TRIGGER after_recently_played_insert
AFTER INSERT ON recently_played
FOR EACH ROW
BEGIN
    -- Update or insert into listening_history
    INSERT INTO listening_history (user_id, station_id, listen_date, total_minutes, listen_count)
    VALUES (NEW.user_id, NEW.station_id, CURDATE(), NEW.duration_seconds / 60, 1)
    ON DUPLICATE KEY UPDATE 
        total_minutes = total_minutes + (NEW.duration_seconds / 60),
        listen_count = listen_count + 1;
END //

-- Trigger to validate rating before insert
CREATE TRIGGER before_comment_insert
BEFORE INSERT ON station_comments
FOR EACH ROW
BEGIN
    IF NEW.rating < 1 OR NEW.rating > 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Rating must be between 1 and 5';
    END IF;
END //

DELIMITER ;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

-- Additional indexes for faster queries
CREATE INDEX idx_station_active_listeners ON stations(is_active, listeners DESC);
CREATE INDEX idx_favorites_user_station ON favorites(user_id, station_id);
CREATE INDEX idx_recently_played_user_date ON recently_played(user_id, played_at DESC);
CREATE INDEX idx_comments_station_approved ON station_comments(station_id, is_approved);
CREATE INDEX idx_playlists_user_public ON playlists(user_id, is_public);

-- ============================================
-- EXAMPLE QUERIES FOR REFERENCE
-- ============================================

/*
-- Get all active stations
SELECT * FROM stations WHERE is_active = TRUE ORDER BY listeners DESC;

-- Get user's favorite stations
SELECT s.* FROM stations s 
INNER JOIN favorites f ON s.id = f.station_id 
WHERE f.user_id = 1;

-- Get trending stations (top 10 by listeners)
SELECT * FROM stations WHERE is_active = TRUE ORDER BY listeners DESC LIMIT 10;

-- Search stations
SELECT * FROM stations 
WHERE (name LIKE '%kiss%' OR genre LIKE '%kiss%' OR city LIKE '%kiss%') 
AND is_active = TRUE;

-- Get user's recently played stations (last 10)
SELECT s.*, r.played_at FROM stations s
INNER JOIN recently_played r ON s.id = r.station_id
WHERE r.user_id = 1
ORDER BY r.played_at DESC LIMIT 10;

-- Get stations by city
SELECT * FROM stations WHERE city = 'Nairobi' AND is_active = TRUE;

-- Get stations by genre
SELECT * FROM stations WHERE genre = 'Hip Hop' AND is_active = TRUE;

-- Count total listeners per genre
SELECT genre, SUM(listeners) as total_listeners 
FROM stations WHERE is_active = TRUE 
GROUP BY genre ORDER BY total_listeners DESC;

-- Get station with average rating
SELECT s.*, AVG(c.rating) as avg_rating, COUNT(c.id) as review_count
FROM stations s
LEFT JOIN station_comments c ON s.id = c.station_id AND c.is_approved = TRUE
WHERE s.id = 1
GROUP BY s.id;

-- Get user statistics
CALL GetUserStats(1);

-- Toggle favorite
CALL ToggleFavorite(1, 5);

-- Add to recently played
CALL AddToRecentlyPlayed(1, 3);
*/

-- ============================================
-- CONFIGURATION SETTINGS
-- ============================================

-- Set timezone (adjust according to your region)
SET time_zone = '+03:00'; -- East Africa Time (EAT)

-- ============================================
-- DATABASE CREATED SUCCESSFULLY!
-- All tables are empty and ready for data
-- ============================================

-- Verify tables were created
SHOW TABLES;