# Quick Deployment Steps for Your Droplet

Based on your existing setup, here's a streamlined deployment guide.

## Prerequisites Check

You already have:

-   ✅ Nginx installed and configured
-   ✅ PHP-FPM running
-   ✅ Other Laravel sites working
-   ✅ Git, Composer, Node.js (likely installed)

## Step 1: Verify PHP Version

```bash
# Check PHP version
php -v

# Check PHP-FPM socket
ls -la /var/run/php/
```

Note the socket path (e.g., `php8.1-fpm.sock` or `php8.2-fpm.sock`).

## Step 2: Check Existing Config Style

```bash
# Look at one of your existing configs
cat /etc/nginx/sites-available/cool.micronet.mv
```

This shows you the PHP-FPM socket path and any custom settings.

## Step 3: Install PostgreSQL Extension (if needed)

```bash
# Check if pgsql extension is installed
php -m | grep pgsql

# If not installed, install it (adjust version)
sudo apt install php8.1-pgsql -y
# or
sudo apt install php8.2-pgsql -y

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
# or
sudo systemctl restart php8.2-fpm
```

## Step 4: Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/Rannamaari/micronetops.git
sudo chown -R www-data:www-data /var/www/micronetops
sudo chmod -R 755 /var/www/micronetops
```

## Step 5: Install Dependencies

```bash
cd /var/www/micronetops
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build
```

## Step 6: Configure Environment

```bash
sudo -u www-data cp .env.example .env
sudo nano .env
```

Update these values:

```env
APP_NAME="MicroNET Sales"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://micronet.mv

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

Generate key:

```bash
sudo -u www-data php artisan key:generate
```

## Step 7: Run Migrations

```bash
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan app:cleanup-for-deployment
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

## Step 8: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/micronetops
sudo chmod -R 775 /var/www/micronetops/storage
sudo chmod -R 775 /var/www/micronetops/bootstrap/cache
```

## Step 9: Create Nginx Config

```bash
sudo nano /etc/nginx/sites-available/micronet.mv
```

**Paste this** (replace PHP-FPM socket with your actual path):

```nginx
server {
    listen 80;
    server_name micronet.mv www.micronet.mv;

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
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        # ⚠️ CHANGE THIS to match your PHP version (php8.1-fpm.sock or php8.2-fpm.sock)
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Step 10: Enable and Test

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/micronet.mv /etc/nginx/sites-enabled/

# CRITICAL: Test configuration first!
sudo nginx -t

# If test passes, reload
sudo systemctl reload nginx
```

## Step 11: SSL Certificate

```bash
sudo certbot --nginx -d micronet.mv -d www.micronet.mv
```

## Step 12: Test

1. Visit `https://micronet.mv` - should show landing page
2. Visit `https://micronet.mv/ops` - should redirect to login
3. Verify other sites still work:
    - `https://cool.micronet.mv`
    - Your mmgweb site

## Troubleshooting

### If Nginx test fails:

```bash
# Check error
sudo nginx -t

# View error log
sudo tail -f /var/log/nginx/error.log
```

### If PHP errors:

```bash
# Check PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
# or
sudo tail -f /var/log/php8.2-fpm.log
```

### If database connection fails:

```bash
# Test from droplet
cd /var/www/micronetops
sudo -u www-data php artisan tinker
# Then in tinker:
DB::connection()->getPdo();
```

### If other sites break:

```bash
# Temporarily disable new site
sudo rm /etc/nginx/sites-enabled/micronet.mv
sudo nginx -t
sudo systemctl reload nginx
# Fix config and re-enable
```

---

## Domain DNS Setup

Make sure your DNS is configured for `micronet.mv`:

**For main domain:**

```
Type: A
Name: @ (or micronet.mv)
Value: your-droplet-ip
TTL: 3600
```

**For www subdomain:**

```
Type: A
Name: www
Value: your-droplet-ip
TTL: 3600
```

Or use CNAME for www:

```
Type: CNAME
Name: www
Value: micronet.mv
TTL: 3600
```

---

That's it! Your site should be live. 🚀
