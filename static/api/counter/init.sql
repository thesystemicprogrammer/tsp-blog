-- ============================================================================
-- GDPR-Compliant Page View Counter - Database Schema
-- Database: tomber_tspblog
-- ============================================================================

-- WARNING: Drop existing tables for fresh start
-- Only run this if you want to reset all data!
DROP TABLE IF EXISTS view_history;
DROP TABLE IF EXISTS dedup_hashes;
DROP TABLE IF EXISTS page_views;

-- Table 1: Page view counts
-- Stores aggregate view count for each page
CREATE TABLE IF NOT EXISTS page_views (
    page_id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(500) NULL DEFAULT NULL,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page (page_id),
    INDEX idx_updated (last_updated),
    INDEX idx_count (view_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: Deduplication hashes
-- Temporary storage for 15-minute deduplication window
-- Hashes are anonymized fingerprints, auto-cleaned after 15 minutes
CREATE TABLE IF NOT EXISTS dedup_hashes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id VARCHAR(255) NOT NULL,
    hash_value CHAR(64) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hash (page_id, hash_value),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: View history
-- Optional detailed log for analytics (timestamps only, no user data)
-- GDPR-compliant: only stores page_id and timestamp
CREATE TABLE IF NOT EXISTS view_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id VARCHAR(255) NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_time (page_id, viewed_at),
    INDEX idx_time (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Verification Queries (optional - run to verify tables were created)
-- ============================================================================

-- Show all tables
SHOW TABLES LIKE '%view%';

-- Describe table structures
DESCRIBE page_views;
DESCRIBE dedup_hashes;
DESCRIBE view_history;

-- Check if tables are empty (should return 0 rows)
SELECT COUNT(*) as page_views_count FROM page_views;
SELECT COUNT(*) as dedup_hashes_count FROM dedup_hashes;
SELECT COUNT(*) as view_history_count FROM view_history;
