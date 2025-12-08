# PHP Hosting Deployment Guide

## Option 1: InfinityFree (FREE)

### Steps:
1. Go to https://infinityfree.net
2. Sign up (no credit card needed)
3. Create account → Choose subdomain or use your domain
4. Upload files via File Manager or FTP

### Upload Files:
- Upload all files to `htdocs` folder
- Keep folder structure intact

### Database Setup:
1. Go to Control Panel → MySQL Databases
2. Create new database
3. Import `database.sql` via phpMyAdmin
4. Update `includes/config.php`:
```php
define('DB_HOST', 'sqlXXX.infinityfreeapp.com');
define('DB_USER', 'epizXXXXX_interno');
define('DB_PASS', 'your_password');
define('DB_NAME', 'epizXXXXX_interno');
```

---

## Option 2: 000webhost (FREE)

### Steps:
1. Go to https://www.000webhost.com
2. Sign up free
3. Create website
4. Upload files via File Manager

### Database:
1. Manage → Database
2. Create MySQL database
3. Import `database.sql`
4. Update `includes/config.php` with new credentials

---

## Option 3: Hostinger (₹59/month)

### Steps:
1. Go to https://hostinger.in
2. Buy hosting plan
3. Access cPanel
4. Upload via File Manager or FTP

### Database:
1. cPanel → MySQL Databases
2. Create database and user
3. Import `database.sql` via phpMyAdmin
4. Update `includes/config.php`

---

## Files to Upload:

✅ Upload:
- All `.php` files
- `assets/` folder
- `includes/` folder
- `admin/` folder
- `user/` folder
- `.htaccess`

❌ Don't Upload:
- `database.sql` (import separately)
- `README.md`
- `HOSTING_GUIDE.md`

---

## After Upload:

1. **Test website**: `http://your-domain.com`
2. **Test admin**: `http://your-domain.com/admin/dashboard.php`
3. **Login**: username: `admin`, password: `password`
4. **Change admin password** immediately

---

## FTP Upload (Alternative)

### Using FileZilla:
1. Download FileZilla: https://filezilla-project.org
2. Get FTP credentials from hosting control panel
3. Connect:
   - Host: ftp.yourdomain.com
   - Username: your_ftp_user
   - Password: your_ftp_pass
   - Port: 21
4. Upload all files to `public_html` or `htdocs`

---

## Troubleshooting:

### Database Connection Error:
- Check `includes/config.php` credentials
- Ensure database is created
- Verify database user has permissions

### 500 Internal Server Error:
- Check `.htaccess` file
- Verify PHP version (7.4+)
- Check file permissions (644 for files, 755 for folders)

### Images Not Loading:
- Upload `assets/images/` folder
- Check file permissions
- Verify image paths in database

---

## Quick Checklist:

- [ ] Files uploaded
- [ ] Database created
- [ ] database.sql imported
- [ ] config.php updated
- [ ] Website loads
- [ ] Admin login works
- [ ] Products display
- [ ] Images show
- [ ] Cart works
- [ ] Changed admin password
