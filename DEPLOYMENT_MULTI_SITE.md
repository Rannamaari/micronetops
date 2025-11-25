# Deployment Guide for DigitalOcean (Multi-Site Setup)

This guide is for deploying MicroNET Sales to a droplet that **already hosts other Laravel websites**.

## Prerequisites

- ✅ DigitalOcean droplet with existing Laravel sites
- ✅ DigitalOcean managed PostgreSQL database (already configured)
- ✅ Domain/subdomain pointing to your droplet IP
- ✅ SSH access to your droplet
- ✅ PHP, Composer, Node.js, Nginx already installed

## Database Configuration

Your database is already configured:
- **Host**: `micronetdb-do-user-24249606-0.d.db.ondigitalocean.com`
- **Port**: `25060`
- **Database**: `micromoto_ops`
- **Username**: `doadmin`
- **Password**: `your_database_password_here`
- **SSL Mode**: `require`

---

## Step 1: Connect to Your Droplet

```bash
ssh root@your-droplet-ip
# or
ssh your-username@your-droplet-ip
```

---

## Step 2: Verify Existing Setup

Check what's already installed:

```bash
# Check PHP version
php -v

# Check Composer
composer --version

# Check Node.js
node -v
npm -v

# Check Nginx
nginx -v
sudo systemctl status nginx

# Check PHP-FPM (usually php8.2-fpm or php8.1-fpm)
sudo systemctl status php8.2-fpm
# or
sudo systemctl status php8.1-fpm
```

**Note the PHP-FPM version** - you'll need it for Nginx configuration.

Check existing Nginx sites:
```bash
ls -la /etc/nginx/sites-available/
ls -la /etc/nginx/sites-enabled/
```

This shows you the naming convention used for other sites.

---

## Step 3: Install Missing PHP Extensions (if needed)

If PostgreSQL extension is missing:

```bash
# For PHP 8.2
sudo apt install php8.2-pgsql -y

# For PHP 8.1
sudo apt install php8.1-pgsql -y

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
# or
sudo systemctl restart php8.1-fpm
```

---

## Step 4: Clone Your Repository

```bash
# Navigate to web root (usually /var/www)
cd /var/www

# Clone your repository
sudo git clone https://github.com/Rannamaari/micronetops.git

# Set ownership to www-data (Nginx user)
sudo chown -R www-data:www-data /var/www/micronetops
sudo chmod -R 755 /var/www/micronetops
```

**Note**: If your other sites use a different user (like `nginx` or a custom user), use that instead of `www-data`.

---

## Step 5: Install Dependencies

```bash
cd /var/www/micronetops

# Install PHP dependencies (production mode)
sudo -u www-data composer install --optimize-autoloader --no-dev

# Install Node dependencies
sudo -u www-data npm install

# Build assets for production
sudo -u www-data npm run build
```

---

## Step 6: Configure Environment File

```bash
# Copy example environment file
sudo -u www-data cp .env.example .env

# Edit the .env file
sudo nano .env
```

Update these values in `.env`:
```env
APP_NAME="MicroNET Sales"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
# or subdomain like: https://ops.your-domain.com

DB_CONNECTION=pgsql
DB_HOST=micronetdb-do-user-24249606-0.d.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=micromoto_ops
DB_USERNAME=doadmin
DB_PASSWORD=your_database_password_here
DB_SSLMODE=require

TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_CHAT_ID=your_telegram_chat_id_here

APP_VERSION=1.0.0-beta
APP_BUILD=1
```

**Generate application key:**
```bash
cd /var/www/micronetops
sudo -u www-data php artisan key:generate
```

---

## Step 7: Run Migrations and Cleanup

```bash
cd /var/www/micronetops

# Run database migrations
sudo -u www-data php artisan migrate --force

# Clean up: Remove non-admin users and all inventory items
sudo -u www-data php artisan app:cleanup-for-deployment

# Create storage symlink
sudo -u www-data php artisan storage:link

# Optimize for production
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## Step 8: Set Proper File Permissions

```bash
cd /var/www/micronetops

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Special permissions for storage and cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## Step 9: Configure Nginx (Add New Site Block)

**IMPORTANT**: We're adding a NEW site block, NOT replacing existing ones.

### 9.1: Check PHP-FPM Socket

First, find your PHP-FPM socket path:

```bash
# Check PHP 8.2
ls -la /var/run/php/php8.2-fpm.sock

# Or PHP 8.1
ls -la /var/run/php/php8.1-fpm.sock
```

**Note the exact path** - you'll need it in the Nginx config.

### 9.2: Create Nginx Configuration

Create a new site configuration (following your existing naming convention):

```bash
sudo nano /etc/nginx/sites-available/micronetops
```

Paste this configuration (replace `your-domain.com` and PHP-FPM socket path):

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    # or use subdomain: ops.your-domain.com
    
    root /var/www/micronetops/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        # Change to php8.1-fpm.sock if that's what you're using
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Key points:**
- Use your actual domain or subdomain
- Match the PHP-FPM socket path to your PHP version
- The `root` points to `/var/www/micronetops/public` (Laravel's public directory)

### 9.3: Enable the Site

```bash
# Create symlink to enable the site
sudo ln -s /etc/nginx/sites-available/micronetops /etc/nginx/sites-enabled/

# Test Nginx configuration (VERY IMPORTANT - don't skip this!)
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

**⚠️ Important**: Always run `nginx -t` before reloading to ensure you don't break existing sites!

---

## Step 10: Set Up SSL with Let's Encrypt

```bash
# Install Certbot (if not already installed)
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate (replace with your domain)
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Certbot will automatically update your Nginx config
# Follow the prompts to complete SSL setup
```

Certbot will automatically modify your Nginx config file to add SSL. It won't affect other sites.

---

## Step 11: Test Your Deployment

1. Visit `https://your-domain.com` — should show the landing page
2. Visit `https://your-domain.com/ops` — should redirect to login
3. Test the contact form — should send to your Telegram bot
4. Login and verify admin access

**Check other sites still work:**
- Visit your other Laravel sites to ensure they're still functioning
- If any site is broken, check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`

---

## Step 12: Verify Database Connection

Test the database connection from your droplet:

```bash
cd /var/www/micronetops
sudo -u www-data php artisan tinker
```

In tinker:
```php
DB::connection()->getPdo();
// Should return PDO object without errors

\App\Models\Customer::count();
// Should return a number (even if 0)
```

If you get SSL errors, check:
1. Database firewall allows your droplet's IP
2. `DB_SSLMODE=require` is set in `.env`

---

## Future Deployments

### Quick Deployment Script

Copy `deploy.sh` to your droplet:

```bash
# On your droplet
cd /var/www/micronetops
sudo nano deploy.sh
# Paste the deploy.sh content from the repository
sudo chmod +x deploy.sh
```

### Deployment Workflow

1. **On your local machine**, commit and push:
   ```bash
   git add .
   git commit -m "Version 1.0.1-beta Build 2"
   git push origin main
   ```

2. **SSH into droplet** and run:
   ```bash
   cd /var/www/micronetops
   ./deploy.sh
   ```

---

## Troubleshooting

### Check Nginx Error Logs
```bash
sudo tail -f /var/log/nginx/error.log
```

### Check PHP-FPM Status
```bash
sudo systemctl status php8.2-fpm
# or
sudo systemctl status php8.1-fpm
```

### Check Laravel Logs
```bash
tail -f /var/www/micronetops/storage/logs/laravel.log
```

### If Permissions Issues
```bash
cd /var/www/micronetops
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
cd /var/www/micronetops
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
```

### Database Connection Issues

If you get SSL connection errors:

1. **Check firewall rules** in DigitalOcean:
   - Go to your database dashboard
   - Add your droplet's IP to the trusted sources

2. **Test connection manually**:
   ```bash
   # Install PostgreSQL client (if not installed)
   sudo apt install postgresql-client -y
   
   # Test connection
   psql -h micronetdb-do-user-24249606-0.d.db.ondigitalocean.com \
        -p 25060 \
        -U doadmin \
        -d micromoto_ops \
        "sslmode=require"
   ```

3. **Verify `.env` settings**:
   ```bash
   cd /var/www/micronetops
   sudo cat .env | grep DB_
   ```

### If Nginx Test Fails

If `sudo nginx -t` fails:
1. Check the error message
2. Look for syntax errors in `/etc/nginx/sites-available/micronetops`
3. Compare with your working site configs
4. Fix the error before reloading

### If Other Sites Break

If your other sites stop working after adding this site:

1. **Check Nginx config**:
   ```bash
   sudo nginx -t
   ```

2. **Check enabled sites**:
   ```bash
   ls -la /etc/nginx/sites-enabled/
   ```

3. **Temporarily disable the new site**:
   ```bash
   sudo rm /etc/nginx/sites-enabled/micronetops
   sudo nginx -t
   sudo systemctl reload nginx
   ```

4. **Fix the config and re-enable**:
   ```bash
   sudo nano /etc/nginx/sites-available/micronetops
   # Fix issues
   sudo ln -s /etc/nginx/sites-available/micronetops /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

---

## Quick Reference Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm  # or php8.1-fpm

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm

# View logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/www/micronetops/storage/logs/laravel.log

# List all Nginx sites
ls -la /etc/nginx/sites-available/
ls -la /etc/nginx/sites-enabled/
```

---

## Important Notes

1. **Multi-site setup**: This guide adds a new site without affecting existing ones
2. **PHP version**: Make sure to use the correct PHP-FPM socket path
3. **Domain/subdomain**: You can use a subdomain like `ops.yourdomain.com` or a separate domain
4. **Cleanup command**: Run `php artisan app:cleanup-for-deployment` after migrations
5. **Database firewall**: Ensure your droplet's IP is allowed in DigitalOcean database firewall
6. **Always test**: Run `sudo nginx -t` before reloading Nginx

---

## Security Checklist

- [x] `APP_DEBUG=false` in production
- [x] Strong database passwords (already set)
- [ ] SSL certificate installed
- [ ] Firewall configured (if not already)
- [ ] Regular backups of database
- [x] `.env` file not in git

---

**Ready to deploy!** Follow these steps, and your application will be live alongside your other sites. 🚀

