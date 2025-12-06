<?php
/**
 * Top Posts API Endpoint
 * 
 * Returns the most viewed posts by view count
 * 
 * Usage:
 *   GET /api/counter/top-posts.php?limit=5
 * 
 * Response:
 *   {
 *     "success": true,
 *     "count": 5,
 *     "posts": [
 *       {
 *         "url": "/posts/my-article/",
 *         "title": "My Article",
 *         "view_count": 1234,
 *         "formatted_count": "1,234",
 *         "date": "Dec 5, 2024"
 *       }
 *     ]
 *   }
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://thesystemicprogrammer.org');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
try {
    // Load security-sensitive credentials from outside web root
    $credentialsFile = __DIR__ . '/../../../php_api_config.php';
    if (!file_exists($credentialsFile)) {
        throw new Exception('Configuration file not found. Please ensure php_api_config.php exists in the correct location.');
    }
    $credentials = require_once $credentialsFile;
    
    // Load API configuration
    $apiConfig = require_once __DIR__ . '/config.php';
    
    // Merge configurations (credentials override any defaults)
    $config = array_merge($apiConfig, $credentials);
    
    // Validate required credentials exist
    $requiredFields = ['host', 'database', 'username', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($config[$field])) {
            throw new Exception("Required configuration field '$field' is missing.");
        }
    }
    
} catch (Exception $e) {
    // Graceful error handling
    $response = [
        'success' => false,
        'count' => 0,
        'posts' => [],
        'error' => 'Configuration error. Please contact the site administrator.'
    ];
    if (($config['debug_enabled'] ?? false) && isset($_GET['debug'])) {
        $response['debug_error'] = $e->getMessage();
    }
    echo json_encode($response);
    error_log('Top posts config error: ' . $e->getMessage());
    exit;
}

// Check debug mode
$debugMode = $config['debug_enabled'] && isset($_GET['debug']);

// Initialize response
$response = [
    'success' => false,
    'count' => 0,
    'posts' => [],
    'error' => null
];

try {
    // Validate request and filter bots
    if (!isValidRequest($config)) {
        throw new Exception('Invalid request or bot detected');
    }
    
    // Get limit parameter (default: 5, max: 20)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $limit = max(1, min($limit, 20)); // Clamp between 1 and 20
    
    // Connect to database
    $pdo = getDB($config);
    
    // Fetch top posts
    $posts = getTopPosts($pdo, $limit);
    
    // Build successful response
    $response['success'] = true;
    $response['count'] = count($posts);
    $response['posts'] = $posts;
    
    // Add debug information if enabled
    if ($debugMode) {
        $response['debug'] = [
            'limit' => $limit,
            'timestamp' => date('Y-m-d H:i:s'),
            'total_posts_in_db' => getTotalPostCount($pdo)
        ];
    }
    
} catch (Exception $e) {
    $response['error'] = $debugMode ? $e->getMessage() : 'An error occurred';
    error_log('Top posts error: ' . $e->getMessage());
}

// Output JSON response
echo json_encode($response, $debugMode ? JSON_PRETTY_PRINT : 0);

// ============================================================================
// FUNCTIONS
// ============================================================================

/**
 * Validate request and filter bots
 * 
 * @param array $config Configuration array
 * @return bool True if valid request, false if bot or invalid
 */
function isValidRequest($config) {
    // Check required headers exist
    foreach ($config['required_headers'] as $header) {
        if (empty($_SERVER[$header])) {
            error_log('Missing required header: ' . $header);
            return false;
        }
    }
    
    // Get and validate User-Agent
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    
    // Check User-Agent is not empty or too short
    if (strlen($userAgent) < 10) {
        error_log('User-Agent too short: ' . $userAgent);
        return false;
    }
    
    // Check against known bot patterns
    foreach ($config['bot_patterns'] as $pattern) {
        if (strpos($userAgent, strtolower($pattern)) !== false) {
            error_log('Bot detected: ' . $pattern . ' in ' . $userAgent);
            return false;
        }
    }
    
    // Validate Accept header
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (empty($accept)) {
        error_log('Missing Accept header');
        return false;
    }
    
    return true;
}

/**
 * Get database connection (singleton pattern)
 * 
 * @param array $config Configuration array
 * @return PDO Database connection
 * @throws Exception If connection fails
 */
function getDB($config) {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['database'],
                $config['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }
    
    return $pdo;
}

/**
 * Get top posts by view count
 * 
 * @param PDO $pdo Database connection
 * @param int $limit Number of posts to return
 * @return array Array of post data
 */
function getTopPosts($pdo, $limit) {
    $stmt = $pdo->prepare(
        "SELECT page_id, view_count, created_at, last_updated 
         FROM page_views 
         ORDER BY view_count DESC 
         LIMIT ?"
    );
    $stmt->execute([$limit]);
    
    $posts = [];
    while ($row = $stmt->fetch()) {
        $posts[] = [
            'url' => $row['page_id'],
            'title' => extractTitle($row['page_id']),
            'view_count' => (int)$row['view_count'],
            'formatted_count' => number_format($row['view_count']),
            'date' => formatDate($row['created_at'])
        ];
    }
    
    return $posts;
}

/**
 * Extract title from page URL
 * 
 * Converts /posts/my-article/ or /posts/2025-12-05-my-article/ to "My Article"
 * 
 * @param string $pageId Page URL path
 * @return string Formatted title
 */
function extractTitle($pageId) {
    // Extract filename from path: /posts/my-article/ → my-article
    $basename = basename(rtrim($pageId, '/'));
    
    // Remove date prefix if exists: 2025-12-05-my-article → my-article
    $basename = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', $basename);
    
    // Convert hyphens to spaces: my-article → my article
    $title = str_replace('-', ' ', $basename);
    
    // Capitalize words: my article → My Article
    $title = ucwords($title);
    
    // Fallback for empty titles
    if (empty($title)) {
        $title = 'Untitled';
    }
    
    return $title;
}

/**
 * Format date in short format
 * 
 * @param string $timestamp MySQL timestamp
 * @return string Formatted date (e.g., "Dec 5, 2024")
 */
function formatDate($timestamp) {
    if (empty($timestamp)) {
        return '';
    }
    
    return date('M j, Y', strtotime($timestamp));
}

/**
 * Get total number of posts in database (for debug)
 * 
 * @param PDO $pdo Database connection
 * @return int Total post count
 */
function getTotalPostCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM page_views");
    $result = $stmt->fetch();
    return (int)$result['count'];
}
