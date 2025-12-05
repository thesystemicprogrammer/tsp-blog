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
├── init.sql           # Database initialization script
├── db-config.php      # Database credentials & settings
├── count.php          # Main API endpoint
├── admin.php          # Admin dashboard
└── .htaccess          # Security configuration
```

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

### Step 2: Configure Security

1. **Edit** `static/api/counter/db-config.php`:
   ```php
   'admin_password' => 'ChangeThisSecurePassword123!',  // Line 70
   ```
   Change to a strong, unique password

2. **Set debug mode** (line 80):
   ```php
   'debug_enabled' => true,   // For testing
   // Change to false after deployment:
   'debug_enabled' => false,  // Production
   ```

### Step 3: Deploy to Server

Your GitHub Actions workflow will automatically deploy the files when you push to your repository.

**Files that will be deployed:**
- `static/api/counter/*` → `/api/counter/` on server
- Updated templates → part of Hugo build
- Updated privacy.md → part of Hugo build

**After deployment**, verify file permissions via SSH:
```bash
cd /path/to/your/site
chmod 755 static/api/counter
chmod 644 static/api/counter/*.php
chmod 644 static/api/counter/.htaccess
chmod 600 static/api/counter/db-config.php  # Extra protection
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

### Adjust Deduplication Window

Edit `static/api/counter/db-config.php`:
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

### Issue: "Database connection failed"

**Solution:**
1. Verify database credentials in `db-config.php`
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
curl https://thesystemicprogrammer.org/api/counter/db-config.php
```

Should return: `403 Forbidden`

If not, check `.htaccess`:
```apache
<FilesMatch "^(db-config)\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

---

## 🔒 Security Best Practices

### 1. Change Admin Password
```php
// In db-config.php - use a strong password
'admin_password' => 'Use-A-Very-Strong-Password-Here-123!@#',
```

### 2. Disable Debug Mode in Production
```php
'debug_enabled' => false,  // Never true in production
```

### 3. Enable HTTPS Enforcement (Optional)
Uncomment in `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 4. Regular Database Backups
```bash
# Backup view counts
mysqldump -u tomber_tspblog -p tomber_tspblog page_views > backup_$(date +%Y%m%d).sql
```

### 5. Monitor Error Logs
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
- [ ] Database tables created successfully
- [ ] Admin password changed from default
- [ ] Debug mode disabled (`'debug_enabled' => false`)
- [ ] API endpoint tested and working
- [ ] Admin dashboard accessible with password
- [ ] View counter displays on posts
- [ ] View counter displays on homepage cards
- [ ] Privacy policy updated and deployed
- [ ] `.htaccess` protecting `db-config.php`
- [ ] File permissions set correctly
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
