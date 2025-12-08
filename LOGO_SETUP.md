# Logo Setup Instructions

## Current Status
✅ Logo placeholders have been added to all required locations:
- Header navigation (main logo)
- Footer (with white filter for dark background)
- Admin dashboard header
- Favicon/tab icon references

## To Use Your Actual Logo

1. **Rename and copy your logo file:**
   - Copy `furniture-line-art-logo-icon-symbol-with-emblem-vector-illustration-design_893105-324.jpg`
   - Rename it to `logo.jpg` or `logo.png`
   - Place it in: `assets/images/`

2. **Update the logo references:**
   - Open `includes/header.php`
   - Change `logo.svg` to `logo.jpg` (or your file extension)
   - Open `includes/footer.php` 
   - Change `logo.svg` to `logo.jpg`
   - Open `admin/dashboard.php`
   - Change `logo.svg` to `logo.jpg`

3. **For favicon (tab icon):**
   - Create a small 32x32px version of your logo
   - Save as `favicon.png` or `favicon.ico`
   - Place in: `assets/images/`
   - Update `includes/header.php` favicon references

## Current Logo Locations
- **Main Header:** `includes/header.php` (line with logo-img class)
- **Footer:** `includes/footer.php` (with white filter)
- **Admin Panel:** `admin/dashboard.php` (with white filter)
- **Favicon:** `includes/header.php` (favicon references)

## Logo Styling
The logo is styled to:
- Height: 40px in header, 32px in footer/admin
- Auto width to maintain aspect ratio
- Hover effects (slight scale on hover)
- White filter in dark areas (footer/admin)

## Quick Replace Command
Once you have your logo file ready:
```
copy "your-logo-file.jpg" "assets\images\logo.jpg"
```

Then update the file extensions in the PHP files from `.svg` to `.jpg`