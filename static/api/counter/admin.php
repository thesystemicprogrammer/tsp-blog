<?php
/**
 * Admin Dashboard for Page View Counter
 * 
 * Features:
 * - Password-protected (session-based)
 * - View counter statistics
 * - Top pages by views
 * - Recent activity
 * - Tailwind CSS styling (matches blog design)
 * 
 * Access: https://thesystemicprogrammer.org/api/counter/admin.php
 */

session_start();

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
    $requiredFields = ['host', 'database', 'username', 'password', 'admin_password'];
    foreach ($requiredFields as $field) {
        if (empty($config[$field])) {
            throw new Exception("Required configuration field '$field' is missing.");
        }
    }
    
} catch (Exception $e) {
    // Graceful error handling - show error in login page
    error_log('Admin config error: ' . $e->getMessage());
    $error = 'Configuration error. Please contact the site administrator.';
    if (($config['debug_enabled'] ?? false)) {
        $error .= ' (Debug: ' . $e->getMessage() . ')';
    }
    // Set minimal config to prevent further errors in HTML rendering
    $config = $config ?? ['debug_enabled' => false];
    $authenticated = false;
}

// Handle login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $config['admin_password']) {
        $_SESSION['admin_authenticated'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check authentication
$authenticated = $_SESSION['admin_authenticated'] ?? false;

// Get statistics if authenticated
$stats = null;
if ($authenticated) {
    try {
        $pdo = getDB($config);
        $stats = getStatistics($pdo);
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

/**
 * Get database connection
 */
function getDB($config) {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['database'],
        $config['charset']
    );
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    return new PDO($dsn, $config['username'], $config['password'], $options);
}

/**
 * Get statistics from database
 */
function getStatistics($pdo) {
    $stats = [];
    
    // Total views across all pages
    $stmt = $pdo->query("SELECT SUM(view_count) as total FROM page_views");
    $stats['total_views'] = (int)$stmt->fetch()['total'];
    
    // Total pages tracked
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM page_views");
    $stats['total_pages'] = (int)$stmt->fetch()['count'];
    
    // Top 20 pages by views
    $stmt = $pdo->query(
        "SELECT page_id, view_count, created_at, last_updated 
         FROM page_views 
         ORDER BY view_count DESC 
         LIMIT 20"
    );
    $stats['top_pages'] = $stmt->fetchAll();
    
    // Recent activity (last 24 hours)
    $stmt = $pdo->query(
        "SELECT COUNT(*) as count 
         FROM view_history 
         WHERE viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    );
    $stats['views_24h'] = (int)$stmt->fetch()['count'];
    
    // Recent activity (last 7 days)
    $stmt = $pdo->query(
        "SELECT COUNT(*) as count 
         FROM view_history 
         WHERE viewed_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    $stats['views_7d'] = (int)$stmt->fetch()['count'];
    
    // Current active dedup hashes (within 15 min)
    $stmt = $pdo->query(
        "SELECT COUNT(*) as count FROM dedup_hashes 
         WHERE timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    );
    $stats['active_dedups'] = (int)$stmt->fetch()['count'];
    
    // Total dedup hashes (all time - for cleanup monitoring)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dedup_hashes");
    $stats['total_dedups'] = (int)$stmt->fetch()['count'];
    
    // Daily view trend (last 7 days)
    $stmt = $pdo->query(
        "SELECT DATE(viewed_at) as date, COUNT(*) as views 
         FROM view_history 
         WHERE viewed_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY DATE(viewed_at)
         ORDER BY date DESC"
    );
    $stats['daily_trend'] = $stmt->fetchAll();
    
    return $stats;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Counter Admin - The Systemic Programmer</title>
    
    <!-- Link to blog's Tailwind CSS -->
    <link rel="stylesheet" href="../../css/main.css">
    
    <!-- Favicon -->
    <link rel="icon" href="../../favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="../../favicon.ico" type="image/x-icon">
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    
    <?php if (!$authenticated): ?>
        <!-- Login Page -->
        <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h1 class="text-center text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Admin Login
                    </h1>
                    <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        Page View Counter Dashboard
                    </p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 dark:text-red-400"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="mt-8 space-y-6">
                    <div class="rounded-md shadow-sm">
                        <input type="password" 
                               name="password" 
                               required 
                               autofocus
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 
                                      placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 
                                      bg-white dark:bg-gray-800 rounded-md focus:outline-none focus:ring-2 
                                      focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Enter password">
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent 
                                       text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard Page -->
        <div class="min-h-full">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        View Counter Statistics
                    </h1>
                    <a href="?logout" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium 
                              rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 
                              focus:ring-offset-2 focus:ring-red-500">
                        Logout
                    </a>
                </div>
            </header>
            
            <!-- Main Content -->
            <main>
                <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                    <?php if ($stats): ?>
                        
                        <!-- Stats Grid -->
                        <div class="px-4 py-6 sm:px-0">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                
                                <!-- Total Views -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                            Total Views
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                            <?= number_format($stats['total_views']) ?>
                                        </dd>
                                    </div>
                                </div>
                                
                                <!-- Total Pages -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                            Total Pages
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                            <?= number_format($stats['total_pages']) ?>
                                        </dd>
                                    </div>
                                </div>
                                
                                <!-- Views (24h) -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                            Views (24 hours)
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                            <?= number_format($stats['views_24h']) ?>
                                        </dd>
                                    </div>
                                </div>
                                
                                <!-- Views (7 days) -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                            Views (7 days)
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                            <?= number_format($stats['views_7d']) ?>
                                        </dd>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                        <!-- System Info -->
                        <div class="px-4 py-6 sm:px-0">
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm text-blue-700 dark:text-blue-400">
                                            <strong>Active Sessions:</strong> <?= number_format($stats['active_dedups']) ?> (last 15 minutes) | 
                                            <strong>Total Dedup Hashes:</strong> <?= number_format($stats['total_dedups']) ?>
                                            <?php if ($stats['total_dedups'] > 1000): ?>
                                                <span class="text-orange-600 dark:text-orange-400"> - Consider manual cleanup</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Daily Trend -->
                        <?php if (!empty($stats['daily_trend'])): ?>
                        <div class="px-4 py-6 sm:px-0">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Daily View Trend (Last 7 Days)</h2>
                            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Views
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($stats['daily_trend'] as $day): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    <?= date('l, M d, Y', strtotime($day['date'])) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <?= number_format($day['views']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Top Pages -->
                        <div class="px-4 py-6 sm:px-0">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Top Pages by Views</h2>
                            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Page
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Views
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Last Updated
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($stats['top_pages'] as $page): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                                    <a href="https://thesystemicprogrammer.org<?= htmlspecialchars($page['page_id']) ?>" 
                                                       target="_blank"
                                                       class="hover:underline">
                                                        <?= htmlspecialchars($page['page_id']) ?>
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <?= number_format($page['view_count']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    <?= date('M d, Y H:i', strtotime($page['last_updated'])) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <div class="px-4 py-6 sm:px-0">
                            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                                <p class="text-sm text-red-700 dark:text-red-400">
                                    Unable to load statistics. Please check database connection.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </main>
        </div>
    <?php endif; ?>
    
</body>
</html>
