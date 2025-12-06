<?php
/**
 * API Configuration for Page View Counter
 * 
 * NON-SENSITIVE PARAMETERS ONLY
 * ==============================
 * Database credentials and admin password are loaded from:
 * public_html/php_api_config.php (outside web root)
 * 
 * This file contains API behavior settings that are safe to version control.
 * 
 * Protected by .htaccess - should never be accessible via web browser
 * 
 * GDPR Compliance: No personal data is stored or processed
 * Only anonymized, temporary fingerprint hashes for deduplication
 */

return [
    // ========================================================================
    // Database Credentials - LOADED FROM EXTERNAL FILE
    // ========================================================================
    // The following database credentials are loaded from php_api_config.php
    // (located outside the web root at: public_html/php_api_config.php)
    //
    // Fields loaded from external file:
    // - host         Database server hostname (usually 'localhost')
    // - database     Database name
    // - username     Database username
    // - password     Database password
    // - charset      Character encoding (utf8mb4)
    //
    // See: static/api/counter/php_api_config.php_template for setup instructions
    
    // ========================================================================
    // Application Settings
    // ========================================================================
    
    // Deduplication window in seconds (15 minutes = 900 seconds)
    'dedup_window' => 900,
    
    // Probability of triggering cleanup on each request (1-100%)
    // 10% = cleanup runs approximately once every 10 requests
    'cleanup_probability' => 10,
    
    // Initial view count range for new posts
    'initial_views_min' => 7,
    'initial_views_max' => 15,
    
    // ========================================================================
    // Bot Filtering
    // ========================================================================
    
    // User-Agent patterns to identify and block bots
    'bot_patterns' => [
        // Generic bot indicators
        'bot', 'crawl', 'spider', 'scraper', 'curl', 'wget', 'python',
        'java', 'perl', 'ruby', 'php', 'go-http-client', 'okhttp',
        
        // Search engine bots
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
        'yandexbot', 'sogou', 'exabot', 'facebot', 'ia_archiver',
        
        // Social media bots
        'facebookexternalhit', 'twitterbot', 'linkedinbot', 'pinterest',
        'whatsapp', 'telegram', 'slackbot', 'discordbot', 'skypebot',
        
        // SEO/monitoring bots
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'rogerbot',
        'screaming frog', 'seobility', 'serpstatbot', 'petalbot',
        
        // Archive/research bots
        'archive.org_bot', 'archive', 'wayback', 'bibliothek',
        
        // Misc bots
        'headless', 'phantom', 'selenium', 'webdriver'
    ],
    
    // Required HTTP headers for valid requests
    // Bots often don't send complete headers
    'required_headers' => [
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT'
    ],
    
    // ========================================================================
    // Admin Dashboard - PASSWORD LOADED FROM EXTERNAL FILE
    // ========================================================================
    // The admin_password is loaded from php_api_config.php
    // (located outside the web root at: public_html/php_api_config.php)
    //
    // This password protects access to:
    // https://thesystemicprogrammer.org/api/counter/admin.php
    //
    // See: static/api/counter/php_api_config.php_template for setup instructions
    
    // ========================================================================
    // Debug Mode
    // ========================================================================
    
    // Enable debug mode (set to false in production)
    // When true, allows ?debug=1 parameter to show detailed info
    'debug_enabled' => true,
    
    // ========================================================================
    // GDPR Compliance Notes
    // ========================================================================
    
    // This system is GDPR-compliant because:
    // 1. No IP addresses are stored or logged
    // 2. No cookies are used for tracking
    // 3. Fingerprint hashes are:
    //    - One-way (cannot reverse to identify user)
    //    - Temporary (auto-expire after 15 minutes)
    //    - Not personally identifiable
    // 4. No cross-site tracking
    // 5. No third-party data sharing
    // 6. User consent not required (legitimate interest basis)
];
