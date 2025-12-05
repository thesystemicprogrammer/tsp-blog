# 🚀 Page View Counter - Deployment Checklist

## Quick Start

Your GDPR-compliant page view counter has been successfully created! Follow these steps to deploy it.

---

## 📋 Pre-Deployment Checklist

### 1. Review Configuration
- [ ] Open `static/api/counter/db-config.php`
- [ ] Change admin password (line 70):
  ```php
  'admin_password' => 'YOUR-SECURE-PASSWORD-HERE',
  ```
- [ ] Verify database credentials are correct (lines 15-19)
- [ ] Set debug mode to `false` for production (line 80)

### 2. Commit and Push to GitHub
```bash
git add .
git commit -m "Add GDPR-compliant page view counter system"
git push origin main
```

This will trigger your GitHub Actions workflow to deploy to your server.

---

## 🗄️ Database Setup (REQUIRED)

After deployment, you MUST initialize the database:

1. **Log into phpMyAdmin**
   - URL: Your hosting provider's phpMyAdmin URL
   - Database: `tomber_tspblog`

2. **Run SQL Initialization**
   - Click "SQL" tab
   - Copy entire contents of `static/api/counter/init.sql`
   - Paste into SQL editor
   - Click "Go"

3. **Verify Tables Created**
   You should see 3 new tables:
   - ✅ `page_views`
   - ✅ `dedup_hashes`
   - ✅ `view_history`

---

## ✅ Post-Deployment Testing

### Test 1: API Endpoint
```bash
curl "https://thesystemicprogrammer.org/api/counter/count.php?page=/posts/test/"
```

**Expected Result:**
```json
{
  "success": true,
  "count": 8,
  "formatted": "8"
}
```
(Count will be random 8-16 on first call)

### Test 2: Admin Dashboard
1. Visit: `https://thesystemicprogrammer.org/api/counter/admin.php`
2. Enter your admin password
3. Verify you see the dashboard with statistics

### Test 3: Frontend Display
1. Visit any blog post
2. Look for eye icon with view count next to reading time
3. Refresh page - count should NOT increase (15-min deduplication)
4. Check browser console for errors (F12)

### Test 4: Security Check
```bash
# This should return 403 Forbidden
curl "https://thesystemicprogrammer.org/api/counter/db-config.php"
```

---

## 🔧 Configuration Reference

### Location of Key Files

**Backend:**
- API: `/static/api/counter/count.php`
- Admin: `/static/api/counter/admin.php`
- Config: `/static/api/counter/db-config.php`
- Security: `/static/api/counter/.htaccess`
- Database: `/static/api/counter/init.sql`

**Frontend:**
- Shortcode: `/layouts/shortcodes/viewcount.html`
- Partial: `/layouts/partials/viewcount-inline.html`
- Post template: `/layouts/posts/single.html` (line 72)
- Card template: `/layouts/partials/post-card.html` (line 74)

**Content:**
- Privacy policy: `/content/privacy.md` (updated)

---

## 🎛️ Adjustable Settings

Edit `static/api/counter/db-config.php`:

```php
// Deduplication window (seconds)
'dedup_window' => 900,  // 15 minutes (default)
                        // 300 = 5 min, 1800 = 30 min

// Cleanup frequency
'cleanup_probability' => 10,  // 10% chance per request

// Initial view range for new posts
'initial_views_min' => 7,
'initial_views_max' => 15,

// Admin password
'admin_password' => 'CHANGE-THIS',

// Debug mode (DISABLE in production)
'debug_enabled' => false,
```

---

## 🐛 Common Issues & Solutions

### "Database connection failed"
- Check credentials in `db-config.php`
- Verify database exists in phpMyAdmin
- Test database login separately

### View count shows "-- views"
- Check browser console for JavaScript errors
- Test API endpoint with curl
- Enable debug mode temporarily: `?debug=1`

### Count increments on every page load
- Deduplication may not be working
- Check `dedup_hashes` table has entries
- Browser in incognito mode? (Creates new fingerprint)

### Admin page shows blank/error
- Check `.htaccess` isn't blocking it
- Check file permissions: should be 644
- Enable PHP error display temporarily

---

## 📊 Understanding the Data

### Tables Explained

**`page_views`**
- Stores total view count per page
- Grows slowly (one row per unique page)
- Safe to keep indefinitely

**`dedup_hashes`**
- Temporary hashes for 15-minute deduplication
- Auto-cleaned probabilistically
- Should stay relatively small (< 1000 rows typically)

**`view_history`**
- Logs each view with timestamp (no user data)
- Used for analytics (daily trends, etc.)
- Can grow large - consider archiving after 1 year

---

## 🔐 Security Notes

### ✅ Good Security Practices
- Admin password is strong and unique
- Debug mode disabled in production
- `.htaccess` protects `db-config.php`
- Database credentials not in git (they are, but repo is private - consider using env variables for production)
- HTTPS enforced (optional but recommended)

### ⚠️ Before Going Public
- [ ] Change admin password from default
- [ ] Disable debug mode
- [ ] Test security: config file should be inaccessible
- [ ] Monitor logs for unusual activity
- [ ] Set up regular database backups

---

## 📈 Monitoring & Maintenance

### Weekly Tasks
- Check admin dashboard for anomalies
- Review top pages
- Monitor active sessions count

### Monthly Tasks
- Manual cleanup if dedup table > 1000 rows:
  ```sql
  DELETE FROM dedup_hashes 
  WHERE timestamp < DATE_SUB(NOW(), INTERVAL 15 MINUTE);
  ```
- Optimize database tables:
  ```sql
  OPTIMIZE TABLE page_views, dedup_hashes, view_history;
  ```

### Quarterly Tasks
- Review bot detection patterns
- Archive old view_history data if needed
- Check for PHP/database updates

---

## 🌟 Features Summary

### What Users See
- 👁️ View counter next to reading time
- Formatted numbers (1,234 views)
- Smooth loading animation
- Works on post pages and homepage cards

### What You Get
- Admin dashboard with statistics
- Top pages by views
- Daily trends (last 7 days)
- Active session monitoring
- View history analytics

### Privacy Features
- No IP addresses stored
- No cookies used
- 15-minute deduplication window
- GDPR-compliant fingerprinting
- Automatic data expiration
- No cross-site tracking

---

## 📚 Additional Resources

- Full documentation: `/static/api/counter/README.md`
- Database schema: `/static/api/counter/init.sql`
- Privacy policy: `/content/privacy.md`

---

## ✨ You're All Set!

Once you complete the checklist above, your page view counter will be live!

**Questions?**
Check the detailed README.md in the counter directory for troubleshooting and advanced configuration.

---

**Last Updated:** December 6, 2025  
**System Version:** 1.0  
**Author:** Built with OpenCode
