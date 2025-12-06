# GDPR-Compliant Page View Counter - Setup Guide

## 🎉 System Overview

You now have a fully functional, privacy-first page view counter system that:
- ✅ Tracks page views with 15-minute deduplication
- ✅ 100% GDPR compliant (no personal data stored)
- ✅ Enhanced fingerprinting for accuracy
- ✅ MariaDB backend for reliability
- ✅ Random initial views (7-15) for new posts
- ✅ Admin dashboard with Tailwind styling
- ✅ Moderate bot filtering
- ✅ Debug mode for testing

---

## 📁 Files Created

### Backend (PHP + MariaDB)
```
static/api/counter/
├── init.sql                       # Database initialization script
├── config.php                     # API configuration (non-sensitive)
├── php_api_config.php_template    # Template for credentials file
├── count.php                      # Main API endpoint
├── admin.php                      # Admin dashboard
├── top-posts.php                  # Top posts endpoint
└── .htaccess                      # Security configuration
```

**Note:** Database credentials are stored in `php_api_config.php` outside the web root (not in git).

### Frontend (Hugo Templates)
```
layouts/
├── shortcodes/
│   └── viewcount.html         # Shortcode for manual use
├── partials/
│   └── viewcount-inline.html  # Partial for post cards
├── posts/
│   └── single.html            # Updated with counter
└── partials/
    └── post-card.html         # Updated with counter

content/
└── privacy.md                 # Updated privacy policy
```

---

## 🚀 Installation Steps

### Step 0: Create Secure Credentials File (DO THIS FIRST!)

**IMPORTANT:** This must be done BEFORE deploying the application.

#### 1. Connect to your server via SSH or FTP

```bash
ssh youruser@thesystemicprogrammer.org
```

#### 2. Navigate to the directory ONE LEVEL ABOVE your web root

```bash
cd ~/public_html
# You should be in public_html/, NOT public_html/tsp/
pwd  # Should show: /home/youruser/public_html
```

#### 3. Create the credentials file

```bash
nano php_api_config.php
```

#### 4. Copy and fill in the template

Use the template from `static/api/counter/php_api_config.php_template` and fill in these fields:

| Field | Description | Where to Find | Example |
|-------|-------------|---------------|---------|
| `host` | Database server | Usually 'localhost' for shared hosting | `localhost` |
| `database` | Database name | cPanel → MySQL Databases | `tomber_tspblog` |
| `username` | Database username | cPanel → MySQL Databases | `tomber_tspblog` |
| `password` | Database password | You set this when creating the DB user | `YourSecurePass123!` |
| `charset` | Character encoding | Leave as shown in template | `utf8mb4` |
| `admin_password` | Dashboard password | Create a strong password (12+ chars) | `AdminPass456!@#` |

**Complete template:**

```php
<?php
return [
    // Database Credentials
    'host' => 'localhost',
    'database' => 'YOUR_DATABASE_NAME',
    'username' => 'YOUR_DATABASE_USERNAME',
    'password' => 'YOUR_DATABASE_PASSWORD',
    'charset' => 'utf8mb4',
    
    // Admin Dashboard Security
    'admin_password' => 'YOUR_ADMIN_PASSWORD',
];
```

**Replace all `YOUR_*` placeholders with your actual values!**

#### 5. Set secure file permissions

```bash
chmod 600 ~/public_html/php_api_config.php
```

This ensures ONLY your user account can read the file (not even other users on the shared server).

#### 6. Verify file location and permissions

```bash
# Check file exists
ls -la ~/public_html/php_api_config.php

# Should show: -rw------- 1 youruser yourgroup ... php_api_config.php
#              ^^^^^^^^^
#              These permissions (600) are correct!
```

#### 7. Verify PHP syntax is valid

```bash
php -l ~/public_html/php_api_config.php
# Should output: No syntax errors detected in /home/youruser/public_html/php_api_config.php
```

#### 8. Test file is NOT web-accessible

```bash
# Try to access via web (should fail)
curl https://thesystemicprogrammer.org/../php_api_config.php
# Expected: 403 Forbidden or 404 Not Found ✅

curl https://thesystemicprogrammer.org/php_api_config.php  
# Expected: 404 Not Found ✅
```

**✅ Security Check Passed:** If both curl commands return 403/404, your credentials file is properly secured!

---

### Step 1: Setup Database

1. **Log into phpMyAdmin** on your shared hosting
2. **Select database**: `tomber_tspblog`
3. **Go to SQL tab**
4. **Copy the entire contents** of `static/api/counter/init.sql`
5. **Paste into SQL editor** and click "Go"
6. **Verify**: You should see 3 new tables:
   - `page_views`
   - `dedup_hashes`
   - `view_history`

### Step 2: Verify Configuration

The database credentials and admin password are now configured in `php_api_config.php` (outside web root).

API behavior settings are in `static/api/counter/config.php` (in git, deployed with your site).

**No additional configuration needed** - the sensitive parameters are already set in Step 0!

### Step 3: Deploy to Server

**IMPORTANT:** Make sure you completed Step 0 (created `php_api_config.php`) BEFORE deploying!

Your GitHub Actions workflow will automatically deploy the files when you push to your repository.

**Files that will be deployed:**
- `static/api/counter/*` → `/api/counter/` on server
- Updated templates → part of Hugo build
- Updated privacy.md → part of Hugo build

**Note:** The `php_api_config.php` file is NOT deployed (it's not in git). You created it manually in Step 0.

**After deployment**, verify file permissions via SSH:
```bash
cd /path/to/your/site
chmod 755 static/api/counter
chmod 644 static/api/counter/*.php
chmod 644 static/api/counter/.htaccess

# Verify credentials file is still secure
chmod 600 ~/public_html/php_api_config.php
ls -la ~/public_html/php_api_config.php  # Should show: -rw-------
```

### Step 4: Test the System

#### Test 1: API Endpoint
```bash
curl "https://thesystemicprogrammer.org/api/counter/count.php?page=/posts/test/"
```

**Expected response:**
```json
{
  "success": true,
  "count": 8,
  "formatted": "8"
}
```
(Count will be random between 8-16 for first view)

#### Test 2: Debug Mode
```bash
curl "https://thesystemicprogrammer.org/api/counter/count.php?page=/posts/test/&debug=1"
```

**Expected response includes:**
```json
{
  "success": true,
  "count": 8,
  "formatted": "8",
  "debug": {
    "page_id": "/posts/test/",
    "hash": "abc123...",
    "user_agent": "curl/7.x.x",
    "referrer": "direct",
    ...
  }
}
```

#### Test 3: Admin Dashboard
1. Visit: `https://thesystemicprogrammer.org/api/counter/admin.php`
2. Enter the password you set in `db-config.php`
3. Verify you can see statistics

#### Test 4: Frontend Display
1. Visit any blog post
2. You should see: `👁️ X views` next to the reading time
3. Check browser console for any errors
4. Refresh the page - count should NOT increase (within 15 min)
5. Wait 15 minutes or clear browser data - count should increment

---

## 🔧 Configuration Options

**Note:** These settings are in `static/api/counter/config.php` (API behavior settings).  
Database credentials are in `~/public_html/php_api_config.php` (outside web root, not in git).

### Adjust Deduplication Window

Edit `static/api/counter/config.php`:
```php
'dedup_window' => 900,  // 15 minutes (in seconds)
// Options:
// 300  = 5 minutes
// 900  = 15 minutes (default)
// 1800 = 30 minutes
```

### Adjust Initial View Range

```php
'initial_views_min' => 7,   // Minimum initial views
'initial_views_max' => 15,  // Maximum initial views
```

### Adjust Cleanup Frequency

```php
'cleanup_probability' => 10,  // 10% chance per request
// Higher = more frequent cleanup, more overhead
// Lower = less frequent, larger dedup table
```

### Add More Bot Patterns

```php
'bot_patterns' => [
    // Add your custom patterns here
    'my-custom-bot',
    'another-bot-name',
],
```

---

## 📊 Using the Admin Dashboard

### Access
`https://thesystemicprogrammer.org/api/counter/admin.php`

### Features
- **Total Views**: Aggregate views across all pages
- **Total Pages**: Number of pages being tracked
- **Views (24h/7d)**: Recent activity metrics
- **Active Sessions**: Current dedup hashes (within 15 min)
- **Daily Trend**: Views per day for last 7 days
- **Top Pages**: Top 20 pages by view count

### Monitoring
- **Active Sessions** should be relatively low (< 100 typically)
- **Total Dedup Hashes**: If > 1000, consider manual cleanup:

```sql
DELETE FROM dedup_hashes WHERE timestamp < DATE_SUB(NOW(), INTERVAL 15 MINUTE);
```

---

## 🐛 Troubleshooting

### Issue: "Configuration file not found"

**Error message in browser:** "Configuration error. Please contact the site administrator."

**Error message in logs:** "Configuration file not found. Please ensure php_api_config.php exists in the correct location."

**Solution:**

1. Verify file exists:
   ```bash
   ls -la ~/public_html/php_api_config.php
   ```
   
   If file doesn't exist, go back to **Step 0** above and create it.

2. Verify path structure matches expected deployment:
   ```bash
   # Your credentials config should be here:
   ~/public_html/php_api_config.php
   
   # Your API files should be here:
   ~/public_html/tsp/api/counter/count.php
   ~/public_html/tsp/api/counter/admin.php
   ~/public_html/tsp/api/counter/top-posts.php
   ```
   
   The path from API files to config is: `../../../php_api_config.php`

3. Verify file permissions (should be 600):
   ```bash
   stat ~/public_html/php_api_config.php
   # Output should include: Access: (0600/-rw-------)
   ```
   
   If wrong, fix with:
   ```bash
   chmod 600 ~/public_html/php_api_config.php
   ```

4. Verify file contains valid PHP syntax:
   ```bash
   php -l ~/public_html/php_api_config.php
   # Should output: No syntax errors detected in ...
   ```
   
   If syntax errors, edit the file and fix them:
   ```bash
   nano ~/public_html/php_api_config.php
   ```

5. Test the relative path resolution from API directory:
   ```bash
   cd ~/public_html/tsp/api/counter
   php -r "var_dump(file_exists(__DIR__ . '/../../../php_api_config.php'));"
   # Should output: bool(true)
   ```
   
   If returns `bool(false)`, your directory structure doesn't match expectations.

---

### Issue: "Required configuration field 'X' is missing"

**Error message:** "Required configuration field 'host' is missing." (or database, username, password, admin_password)

**Cause:** Your `php_api_config.php` is missing required fields or has placeholder values.

**Solution:**

1. Check file contains all required fields:
   ```bash
   grep -E "(host|database|username|password|admin_password)" ~/public_html/php_api_config.php
   ```
   
   Should show all 6 fields with actual values.

2. Ensure no fields are still set to placeholders:
   ```bash
   grep -i "YOUR_" ~/public_html/php_api_config.php
   # Should return NOTHING (no matches)
   ```
   
   If you see `YOUR_DATABASE_NAME` or similar, you forgot to fill in actual values!

3. Compare your file against the template:
   - Template location: `static/api/counter/php_api_config.php_template`
   - Verify all fields are present and filled with actual values

4. **Required fields checklist:**
   ```php
   'host' => 'localhost',              // ✓ Usually 'localhost'
   'database' => 'actual_db_name',     // ✓ Your database name (NOT 'YOUR_DATABASE_NAME')
   'username' => 'actual_username',    // ✓ Your database user (NOT 'YOUR_DATABASE_USERNAME')
   'password' => 'actual_password',    // ✓ Your database password (NOT 'YOUR_DATABASE_PASSWORD')
   'charset' => 'utf8mb4',             // ✓ Must be 'utf8mb4'
   'admin_password' => 'admin_pass',   // ✓ Your admin password (NOT 'YOUR_ADMIN_PASSWORD')
   ```

5. Test database connection with provided credentials:
   ```bash
   mysql -h localhost -u YOUR_USERNAME -p YOUR_DATABASE
   # Enter password when prompted
   # If connection fails, your credentials are wrong
   ```

---

### Issue: Database connection failed

**Solution:**
1. Verify database credentials in `php_api_config.php` (NOT config.php)
2. Check database exists: `tomber_tspblog`
3. Check user has proper permissions
4. Test connection via phpMyAdmin

### Issue: View count shows "-- views"

**Possible causes:**
1. JavaScript error - check browser console
2. API endpoint not accessible - test curl command
3. CORS issue - check browser network tab
4. Bot filtering too aggressive

**Debug:**
```javascript
// Add to browser console
fetch('/api/counter/count.php?page=/posts/test/')
  .then(r => r.json())
  .then(console.log)
```

### Issue: Count increments on every refresh

**Possible causes:**
1. Deduplication not working
2. Browser changing fingerprint (e.g., incognito mode)
3. Time bucket rolling over (wait 15 min)

**Debug:**
```bash
# Check dedup hashes are being created
mysql -u tomber_tspblog -p tomber_tspblog
SELECT COUNT(*) FROM dedup_hashes;
```

### Issue: Admin page won't load

**Solution:**
1. Check `.htaccess` isn't blocking admin.php
2. Check file permissions (should be 644)
3. Check PHP errors: add to top of admin.php:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Issue: config file accessible via web

**URGENT - Security issue!**

Test:
```bash
# Test config.php is protected by .htaccess
curl https://thesystemicprogrammer.org/api/counter/config.php
# Should return: 403 Forbidden

# Test php_api_config.php is not accessible (outside web root)
curl https://thesystemicprogrammer.org/../php_api_config.php
# Should return: 403 Forbidden or 404 Not Found

curl https://thesystemicprogrammer.org/php_api_config.php
# Should return: 404 Not Found
```

**If config.php returns content instead of 403:**

Check `.htaccess`:
```apache
<FilesMatch "^(config)\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

**If php_api_config.php is accessible via web:**

This means you put it in the WRONG location! It should be:
- ✅ Correct: `~/public_html/php_api_config.php` (outside web root)
- ❌ Wrong: `~/public_html/tsp/php_api_config.php` (inside web root)

Move it immediately:
```bash
mv ~/public_html/tsp/php_api_config.php ~/public_html/php_api_config.php
chmod 600 ~/public_html/php_api_config.php
```

---

## 🔒 Security Best Practices

### 1. Protect Credentials File

```bash
# Verify file is outside web root (correct location)
ls ~/public_html/php_api_config.php         # ✅ Should exist
ls ~/public_html/tsp/php_api_config.php     # ❌ Should NOT exist

# Verify strict permissions (600 = only you can read)
chmod 600 ~/public_html/php_api_config.php
stat ~/public_html/php_api_config.php
# Should show: Access: (0600/-rw-------)

# Verify not in git repository
cd /path/to/your/local/repo
git ls-files | grep php_api_config.php
# Should return: nothing (file not tracked)

# Verify web inaccessibility
curl https://thesystemicprogrammer.org/../php_api_config.php
curl https://thesystemicprogrammer.org/php_api_config.php
# Both should return: 403 Forbidden or 404 Not Found
```

### 2. Use Strong Passwords

For admin_password in `php_api_config.php`:
```php
// ❌ Bad passwords:
'admin_password' => 'admin123',
'admin_password' => 'password',
'admin_password' => 'ChangeThisSecurePassword123!',  // Default from docs!

// ✅ Good passwords:
'admin_password' => 'xK9$mL#2pN@v8qR3wT!yZ7',  // Random, 20+ chars
'admin_password' => 'correct-horse-battery-staple-2025',  // Passphrase
```

Generate strong passwords:
```bash
# Using pwgen (install with: apt-get install pwgen)
pwgen -s 20 1

# Using OpenSSL
openssl rand -base64 18

# Using /dev/urandom
tr -dc 'A-Za-z0-9!@#$%^&*()_+=' < /dev/urandom | head -c 20
```

### 3. Change Admin Password

To update your admin password:

1. **Edit the credentials file:**
   ```bash
   nano ~/public_html/php_api_config.php
   ```

2. **Update the admin_password field:**
   ```php
   'admin_password' => 'YourNewStrongPassword123!',
   ```

3. **Save and test:**
   ```bash
   # Verify syntax
   php -l ~/public_html/php_api_config.php
   
   # Test login at:
   # https://thesystemicprogrammer.org/api/counter/admin.php
   ```

### 4. Disable Debug Mode in Production
```php
'debug_enabled' => false,  // Never true in production
```

Edit in `static/api/counter/config.php` (the API config file, NOT the credentials file)

### 5. Enable HTTPS Enforcement (Optional)
Uncomment in `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 6. Regular Database Backups
```bash
# Backup view counts
mysqldump -u tomber_tspblog -p tomber_tspblog page_views > backup_$(date +%Y%m%d).sql
```

### 7. Monitor Error Logs
Check your hosting's PHP error logs regularly for:
- Database connection failures
- Suspicious bot patterns
- Unusual traffic spikes

---

## 📈 Future Enhancements (Optional)

### Add Cron Job for Cleanup
Instead of probabilistic cleanup, use a cron job:

**Create:** `static/api/counter/cleanup.php`
```php
<?php
require_once 'db-config.php';
$config = require 'db-config.php';
$pdo = new PDO(/*...*/);
$stmt = $pdo->prepare("DELETE FROM dedup_hashes WHERE timestamp < DATE_SUB(NOW(), INTERVAL 900 SECOND)");
$stmt->execute();
echo "Cleaned up " . $stmt->rowCount() . " hashes\n";
```

**Add to crontab:**
```bash
0 * * * * curl -s https://thesystemicprogrammer.org/api/counter/cleanup.php
```

### Add HTTP Basic Auth to Admin

**Create:** `static/api/counter/.htpasswd`
```bash
htpasswd -c .htpasswd admin
```

**Update:** `.htaccess`
```apache
<Files "admin.php">
    AuthType Basic
    AuthName "Admin Area"
    AuthUserFile /full/path/to/.htpasswd
    Require valid-user
</Files>
```

### Export View Data

Add to admin.php:
```php
// Export as CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="views.csv"');
    $stmt = $pdo->query("SELECT * FROM page_views ORDER BY view_count DESC");
    // Output CSV...
}
```

---

## 📝 Manual Usage

### Use shortcode in any markdown file:
```markdown
Check out my popular post!

{{< viewcount >}}
```

### Use in custom templates:
```go
{{ partial "viewcount-inline.html" . }}
```

---

## 🎯 Expected Behavior

### First visitor to new post:
1. Random initial count generated (7-15)
2. Count incremented by 1
3. Display shows: 8-16 views
4. Hash stored in `dedup_hashes`
5. Timestamp logged in `view_history`

### Same visitor refreshes within 15 minutes:
1. Hash found in `dedup_hashes`
2. Count NOT incremented
3. Display shows same count
4. No new entries in database

### Different visitor or after 15 minutes:
1. Hash not found (expired or different)
2. Count incremented by 1
3. New hash stored
4. New timestamp logged

### Bots:
1. Detected by User-Agent pattern matching
2. Rejected with error response
3. NOT counted
4. Logged in error log

---

## 📞 Support & Maintenance

### Logs to Monitor
- **PHP error log**: Check for database errors, bot detections
- **Apache access log**: Monitor API endpoint traffic
- **Database slow query log**: Check for performance issues

### Database Maintenance
```sql
-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'tomber_tspblog'
AND table_name LIKE '%view%';

-- Optimize tables (run monthly)
OPTIMIZE TABLE page_views;
OPTIMIZE TABLE dedup_hashes;
OPTIMIZE TABLE view_history;
```

### Performance Tips
- **Dedup hashes** should auto-cleanup, but manual cleanup recommended weekly
- **View history** can grow large - consider archiving after 1 year
- **Page views** table is small and can stay indefinitely

---

## ✅ Final Checklist

Before going live:
- [ ] `php_api_config.php` created outside web root (`~/public_html/php_api_config.php`)
- [ ] `php_api_config.php` filled with actual credentials (no YOUR_* placeholders)
- [ ] `php_api_config.php` permissions set to 600 (`chmod 600`)
- [ ] `php_api_config.php` NOT accessible via HTTP (test with curl)
- [ ] `php_api_config.php` NOT in git repository
- [ ] Database tables created successfully
- [ ] Admin password is strong and unique (in php_api_config.php)
- [ ] Debug mode disabled in production (`config.php`: `'debug_enabled' => false`)
- [ ] API endpoint tested and working
- [ ] Admin dashboard accessible with password
- [ ] View counter displays on posts
- [ ] View counter displays on homepage cards
- [ ] Privacy policy updated and deployed
- [ ] `.htaccess` protecting `config.php` (returns 403)
- [ ] File permissions set correctly on server
- [ ] SSL/HTTPS working
- [ ] Tested deduplication (refresh doesn't increment)
- [ ] Checked browser console for errors

---

## 🎓 Learning & Customization

### Understanding the Fingerprint

The system creates a hash from:
```
page_id | user_agent | accept_language | accept_encoding | referrer | os | time_bucket
```

**Example:**
```
/posts/my-article/ | Mozilla/5.0... | en-US,en | gzip,deflate,br | https://google.com | windows | 12345
```

This creates a unique-enough identifier for 15 minutes without tracking the user.

### GDPR Compliance Explained

**Why no consent needed:**
1. No IP addresses stored
2. No cookies used
3. Hash is one-way (cannot identify person)
4. Data expires automatically
5. Only aggregate statistics kept
6. Legitimate interest basis applies

**Under GDPR Article 6(1)(f):**
- Processing is necessary for legitimate interests
- Interest: Understanding content performance
- No override of user's rights (data is anonymous)

---

## 🌟 Success!

Your GDPR-compliant page view counter is now ready to deploy!

**Next steps:**
1. Commit all changes to git
2. Push to GitHub (triggers deployment)
3. Run database initialization in phpMyAdmin
4. Test API endpoint
5. Access admin dashboard
6. Monitor for 24-48 hours
7. Disable debug mode if not already done

**Questions or issues?**
Check the troubleshooting section above or review the inline code comments.

---

**Built with:**
- PHP 8.4+
- MariaDB/MySQL
- Hugo Static Site Generator
- Tailwind CSS
- Privacy-first principles ❤️
