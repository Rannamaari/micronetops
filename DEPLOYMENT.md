# Deployment Guide for DigitalOcean

This guide will help you deploy the MicroNET Sales application to your DigitalOcean droplet.

## Prerequisites

-   ✅ DigitalOcean droplet with Ubuntu 22.04 or later
-   ✅ DigitalOcean managed PostgreSQL database (already configured)
-   ✅ Domain name pointing to your droplet IP
-   ✅ SSH access to your droplet

## Database Configuration

Your database is already configured:

-   **Host**: `micronetdb-do-user-24249606-0.d.db.ondigitalocean.com`
-   **Port**: `25060`
-   **Database**: `micromoto_ops`
-   **Username**: `doadmin`
-   **Password**: `your_database_password_here`
-   **SSL Mode**: `require`

---

## Step 1: Connect to Your Droplet

```bash
ssh root@your-droplet-ip
# or
ssh your-username@your-droplet-ip
```

---

## Step 2: Install Required Software

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and required extensions
sudo apt install php8.2-fpm php8.2-cli php8.2-common php8.2-pgsql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-tokenizer -y

# Install Composer
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js and npm (for asset compilation)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx (if not already installed)
sudo apt install nginx -y

# Install Git (if not already installed)
sudo apt install git -y
```

---

## Step 3: Clone Your Repository

```bash
# Navigate to web root
cd /var/www

# Clone your repository
sudo git clone https://github.com/Rannamaari/micronetops.git

# Set ownership to www-data (Nginx user)
sudo chown -R www-data:www-data /var/www/micronetops
sudo chmod -R 755 /var/www/micronetops
```

---

## Step 4: Install Dependencies

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

## Step 5: Configure Environment File

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

## Step 6: Run Migrations and Cleanup

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

## Step 7: Set Proper File Permissions

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

## Step 8: Configure Nginx

Create Nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/micronetops
```

Paste this configuration (replace `your-domain.com` with your actual domain):

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
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
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/micronetops /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## Step 9: Set Up SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate (replace with your domain)
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Certbot will automatically update your Nginx config
# Follow the prompts to complete SSL setup
```

---

## Step 10: Test Your Deployment

1. Visit `https://your-domain.com` — should show the landing page
2. Visit `https://your-domain.com/ops` — should redirect to login
3. Test the contact form — should send to your Telegram bot
4. Login and verify admin access

---

## Future Deployments

### Quick Deployment Script

Create a deployment script:

```bash
sudo nano /var/www/micronetops/deploy.sh
```

Add this content:

```bash
#!/bin/bash
cd /var/www/micronetops

# Pull latest code
sudo -u www-data git pull origin main

# Install/update dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build

# Run migrations
sudo -u www-data php artisan migrate --force

# Clear and cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache

echo "Deployment completed!"
```

Make it executable:

```bash
sudo chmod +x /var/www/micronetops/deploy.sh
```

### Deployment Workflow

1. **Update version in `.env`** (on your local machine):

    ```bash
    nano .env
    # Change APP_VERSION and APP_BUILD
    ```

2. **Commit and push to GitHub**:

    ```bash
    git add .
    git commit -m "Version 1.0.1-beta Build 2"
    git push origin main
    ```

3. **SSH into droplet and run**:
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

If you get SSL connection errors, verify:

1. Database host, port, username, password in `.env`
2. `DB_SSLMODE=require` is set
3. Your droplet's IP is allowed in DigitalOcean database firewall rules

---

## Security Checklist

-   [x] `APP_DEBUG=false` in production
-   [x] Strong database passwords (already set)
-   [ ] SSL certificate installed
-   [ ] Firewall configured (UFW)
-   [ ] Regular backups of database
-   [x] `.env` file not in git

### Configure Firewall (Optional but Recommended)

```bash
# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## Quick Reference Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm

# View logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/www/micronetops/storage/logs/laravel.log
```

---

## Notes

-   The cleanup command (`app:cleanup-for-deployment`) removes all non-admin users and all inventory items
-   Only users with the "admin" role will remain after cleanup
-   You'll need to manually add inventory items (services and parts) after deployment
-   Make sure to update `APP_URL` in `.env` with your actual domain

---

**Ready to deploy!** Follow these steps in order, and your application should be live on DigitalOcean. 🚀
