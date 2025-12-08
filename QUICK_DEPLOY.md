# Quick Deployment Steps

## FREE Hosting - InfinityFree (Recommended)

### 1. Sign Up (2 minutes)
```
https://infinityfree.net
→ Sign up
→ Create account
→ Choose subdomain: yourname.infinityfreeapp.com
```

### 2. Upload Files (5 minutes)
```
Control Panel → File Manager
→ Go to htdocs folder
→ Upload all PHP files and folders
→ Keep structure intact
```

### 3. Create Database (3 minutes)
```
Control Panel → MySQL Databases
→ Create Database
→ Note: database name, username, password
```

### 4. Import Database (2 minutes)
```
Control Panel → phpMyAdmin
→ Select your database
→ Import tab
→ Choose database.sql
→ Click Go
```

### 5. Update Config (1 minute)
```
Edit: includes/config.php

Change:
DB_HOST = 'sqlXXX.infinityfreeapp.com'
DB_USER = 'epizXXXXX_interno'
DB_PASS = 'your_password'
DB_NAME = 'epizXXXXX_interno'
```

### 6. Test Website
```
Visit: http://yourname.infinityfreeapp.com
Login: admin / password
```

---

## Total Time: ~15 minutes

## Your Website Will Be Live! 🚀

### Default Login:
- Username: `admin`
- Password: `password`
- **Change immediately after first login**

### Support:
- InfinityFree Forum: https://forum.infinityfree.net
- Check HOSTING_GUIDE.md for detailed instructions
