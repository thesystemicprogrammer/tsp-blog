<?php
/**
 * Page View Counter API Endpoint
 * 
 * GDPR-Compliant view tracking with enhanced fingerprinting
 * - 15-minute deduplication window
 * - Server-side only fingerprinting (no cookies, no IP logging)
 * - Moderate bot filtering
 * - Random initial views (7-15) for new posts
 * 
 * Usage:
 *   GET  /api/counter/count.php?page=/posts/my-article/&title=My Article
 *   POST /api/counter/count.php with page and title parameters
 * 
 * Response:
 *   {
 *     "success": true,
 *     "count": 1234,
 *     "formatted": "1,234"
 *   }
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://thesystemicprogrammer.org');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
try {
    // Load security-sensitive credentials from outside web root
    $credentialsFile = __DIR__ . '/../../../../php_api_config.php';
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
        'formatted' => '0',
        'error' => 'Configuration error. Please contact the site administrator.'
    ];
    if (($config['debug_enabled'] ?? false) && isset($_GET['debug'])) {
        $response['debug_error'] = $e->getMessage();
    }
    echo json_encode($response);
    error_log('View counter config error: ' . $e->getMessage());
    exit;
}

// Check debug mode
$debugMode = $config['debug_enabled'] && isset($_GET['debug']);

// Initialize response
$response = [
    'success' => false,
    'count' => 0,
    'formatted' => '0',
    'error' => null
];

try {
    // Validate request and filter bots
    if (!isValidRequest($config)) {
        throw new Exception('Invalid request or bot detected');
    }
    
    // Get page ID from request
    $pageId = $_GET['page'] ?? $_POST['page'] ?? null;
    if (!$pageId) {
        throw new Exception('Missing page parameter');
    }
    
    // Get title from request (optional for backward compatibility)
    $title = $_GET['title'] ?? $_POST['title'] ?? null;
    
    // Sanitize page ID
    $pageId = filter_var($pageId, FILTER_SANITIZE_URL);
    if (!$pageId || strlen($pageId) > 255) {
        throw new Exception('Invalid page parameter');
    }
    
    // Sanitize title if provided
    if ($title !== null) {
        $title = trim($title);
        // Remove any HTML tags for security
        $title = strip_tags($title);
        // Limit length to 500 characters (matches DB column)
        if (strlen($title) > 500) {
            $title = substr($title, 0, 500);
        }
        // If title is empty string after sanitization, set to null
        if ($title === '') {
            $title = null;
        }
    }
    
    // Generate enhanced fingerprint hash
    $hash = generateDedupHash($pageId, $config);
    
    // Connect to database
    $pdo = getDB($config);
    
    // Increment view count (or return current if duplicate)
    $count = incrementView($pdo, $pageId, $hash, $config, $title);
    
    // Build successful response
    $response['success'] = true;
    $response['count'] = $count;
    $response['formatted'] = number_format($count);
    
    // Add debug information if enabled
    if ($debugMode) {
        $response['debug'] = [
            'page_id' => $pageId,
            'hash' => $hash,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown',
            'os' => extractOS($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'dedup_window' => $config['dedup_window'] . ' seconds',
            'timestamp' => date('Y-m-d H:i:s'),
            'time_bucket' => floor(time() / $config['dedup_window'])
        ];
    }
    
} catch (Exception $e) {
    $response['error'] = $debugMode ? $e->getMessage() : 'An error occurred';
    error_log('View counter error: ' . $e->getMessage());
}

// Output JSON response
echo json_encode($response, $debugMode ? JSON_PRETTY_PRINT : 0);

// ============================================================================
// FUNCTIONS
// ============================================================================

/**
 * Validate request and filter bots
 * 
 * Implements moderate bot filtering:
 * - Checks required HTTP headers
 * - Validates User-Agent against bot patterns
 * - Ensures Accept header is present
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
    
    // Check User-Agent is not empty or too short (bots often have short UAs)
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
    
    // Validate Accept header (bots often don't send proper Accept headers)
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (empty($accept)) {
        error_log('Missing Accept header');
        return false;
    }
    
    // Additional validation: check for common bot behavior
    // Bots often have no referer and specific accept patterns
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (empty($referer) && strpos($accept, 'text/html') === false) {
        error_log('Suspicious request: no referer and no HTML accept');
        return false;
    }
    
    return true;
}

/**
 * Generate enhanced privacy-preserving deduplication hash
 * 
 * Creates a unique fingerprint using GDPR-compliant server-side data:
 * - User-Agent: Browser and OS information
 * - Accept-Language: Browser language preference
 * - Accept-Encoding: Compression support
 * - Referrer: Where the user came from
 * - OS: Extracted from User-Agent
 * - Time bucket: 15-minute window (auto-expires)
 * 
 * All data is hashed with SHA-256 (one-way, cannot reverse)
 * No personal data, no IP addresses, no tracking
 * 
 * @param string $pageId The page being viewed
 * @param array $config Configuration array
 * @return string SHA-256 hash (64 characters)
 */
function generateDedupHash($pageId, $config) {
    // Collect browser fingerprint elements
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Extract OS from User-Agent for better fingerprinting
    // This helps differentiate same browser on different devices
    $os = extractOS($userAgent);
    
    // Time bucket: creates 15-minute windows
    // Same user within 15 min = same bucket = same hash = deduplicated
    // After 15 min, new bucket = new hash = counted as new view
    $timeBucket = floor(time() / $config['dedup_window']);
    
    // Combine all elements into fingerprint
    // Order matters - same elements always produce same hash
    $fingerprint = implode('|', [
        $pageId,              // Page-specific (same user = 1 view per page)
        $userAgent,           // Browser + version + OS
        $acceptLang,          // Language preference
        $acceptEncoding,      // Compression support (gzip, br, etc.)
        $referrer,            // Where they came from
        $os,                  // Extracted OS for consistency
        $timeBucket           // Time window (auto-expires)
    ]);
    
    // One-way hash: cannot reverse to get original data
    // GDPR-compliant: not personally identifiable
    return hash('sha256', $fingerprint);
}

/**
 * Extract operating system from User-Agent string
 * 
 * Helps create more consistent fingerprints across browser versions
 * while maintaining GDPR compliance (OS alone is not personal data)
 * 
 * @param string $userAgent User-Agent header value
 * @return string OS identifier (windows|macos|linux|ios|android|other)
 */
function extractOS($userAgent) {
    $ua = strtolower($userAgent);
    
    // Mobile operating systems (check first, more specific)
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
        return 'ios';
    }
    if (strpos($ua, 'android') !== false) {
        return 'android';
    }
    
    // Desktop operating systems
    if (strpos($ua, 'windows') !== false) {
        return 'windows';
    }
    if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os') !== false) {
        return 'macos';
    }
    if (strpos($ua, 'linux') !== false) {
        return 'linux';
    }
    
    return 'other';
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
 * Increment view count with deduplication
 * 
 * Process:
 * 1. Check if hash exists in dedup_hashes (within 15 min window)
 * 2. If exists: duplicate view, return current count
 * 3. If new: increment count, store hash, log to history
 * 4. For new pages: initialize with random count (7-15)
 * 5. Probabilistically trigger cleanup of old hashes
 * 
 * @param PDO $pdo Database connection
 * @param string $pageId Page identifier
 * @param string $hash Deduplication hash
 * @param array $config Configuration array
 * @param string|null $title Page title (optional, stored in database)
 * @return int Current view count
 * @throws Exception On database error
 */
function incrementView($pdo, $pageId, $hash, $config, $title = null) {
    try {
        $pdo->beginTransaction();
        
        // Check if this is a duplicate view (within dedup window)
        $stmt = $pdo->prepare(
            "SELECT id FROM dedup_hashes 
             WHERE page_id = ? AND hash_value = ? 
             AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)
             LIMIT 1"
        );
        $stmt->execute([$pageId, $hash, $config['dedup_window']]);
        
        if ($stmt->fetch()) {
            // Duplicate detected - return current count without incrementing
            $pdo->rollBack();
            return getCurrentCount($pdo, $pageId);
        }
        
        // New unique view - check if page exists in database
        $currentCount = getCurrentCount($pdo, $pageId);
        
        if ($currentCount === 0) {
            // Brand new page - initialize with random count (7-15)
            $initialCount = rand($config['initial_views_min'], $config['initial_views_max']);
            
            $stmt = $pdo->prepare(
                "INSERT INTO page_views (page_id, title, view_count) VALUES (?, ?, ?)"
            );
            $stmt->execute([$pageId, $title, $initialCount]);
            
            // Now add the current view (+1)
            $newCount = $initialCount + 1;
            
            $stmt = $pdo->prepare(
                "UPDATE page_views SET view_count = ? WHERE page_id = ?"
            );
            $stmt->execute([$newCount, $pageId]);
            
        } else {
            // Existing page - increment by 1 and update title
            if ($title !== null) {
                // Update both count and title
                $stmt = $pdo->prepare(
                    "UPDATE page_views 
                     SET view_count = view_count + 1, title = ? 
                     WHERE page_id = ?"
                );
                $stmt->execute([$title, $pageId]);
            } else {
                // Update only count
                $stmt = $pdo->prepare(
                    "UPDATE page_views SET view_count = view_count + 1 WHERE page_id = ?"
                );
                $stmt->execute([$pageId]);
            }
            
            $newCount = $currentCount + 1;
        }
        
        // Store deduplication hash (prevents double-counting for 15 min)
        $stmt = $pdo->prepare(
            "INSERT INTO dedup_hashes (page_id, hash_value) VALUES (?, ?)"
        );
        $stmt->execute([$pageId, $hash]);
        
        // Store in view history (for analytics - only timestamp, no user data)
        $stmt = $pdo->prepare(
            "INSERT INTO view_history (page_id) VALUES (?)"
        );
        $stmt->execute([$pageId]);
        
        $pdo->commit();
        
        // Probabilistic cleanup: 10% chance to remove old hashes
        // Reduces overhead vs cleaning on every request
        if (rand(1, 100) <= $config['cleanup_probability']) {
            cleanupOldHashes($pdo, $config);
        }
        
        return $newCount;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('incrementView error: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Get current view count for a page
 * 
 * @param PDO $pdo Database connection
 * @param string $pageId Page identifier
 * @return int View count (0 if page doesn't exist)
 */
function getCurrentCount($pdo, $pageId) {
    $stmt = $pdo->prepare("SELECT view_count FROM page_views WHERE page_id = ? LIMIT 1");
    $stmt->execute([$pageId]);
    $result = $stmt->fetch();
    return $result ? (int)$result['view_count'] : 0;
}

/**
 * Cleanup old deduplication hashes
 * 
 * Removes hashes older than the deduplication window (15 minutes)
 * Called probabilistically (10% of requests) to reduce overhead
 * 
 * @param PDO $pdo Database connection
 * @param array $config Configuration array
 */
function cleanupOldHashes($pdo, $config) {
    try {
        $stmt = $pdo->prepare(
            "DELETE FROM dedup_hashes 
             WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$config['dedup_window']]);
        
        $deleted = $stmt->rowCount();
        if ($deleted > 0) {
            error_log("Cleaned up {$deleted} old deduplication hashes");
        }
        
    } catch (Exception $e) {
        // Cleanup failure shouldn't break the main request
        // Just log the error and continue
        error_log('Cleanup error: ' . $e->getMessage());
    }
}
