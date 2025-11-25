# Nginx Configuration for Multi-Site Setup

Based on your existing sites (`cool.micronet.mv`, `mmgweb`), here's how to add the MicroNET Operations site.

## Step 1: Check Your Existing Config

First, let's look at one of your existing configs to match the style:

```bash
# View an existing config to understand your setup
cat /etc/nginx/sites-available/cool.micronet.mv
# or
cat /etc/nginx/sites-available/mmgweb
```

This will show you:
- PHP-FPM socket path (php8.1-fpm.sock or php8.2-fpm.sock)
- Any custom settings you use
- SSL configuration style

## Step 2: Check PHP-FPM Socket

```bash
# Check which PHP version you're using
ls -la /var/run/php/
```

You'll see something like:
- `/var/run/php/php8.1-fpm.sock` or
- `/var/run/php/php8.2-fpm.sock`

## Step 3: Domain

Using main domain: `micronet.mv`

## Step 4: Create Nginx Configuration

Based on your existing setup, create the config file:

```bash
sudo nano /etc/nginx/sites-available/micronet.mv
```

**Paste this configuration** (adjust PHP-FPM socket path based on what you found):

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
        # Change to php8.2-fpm.sock if that's what you're using
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Important**: Replace `php8.1-fpm.sock` with the correct socket path from Step 2.

## Step 5: Enable the Site

```bash
# Create symlink to enable
sudo ln -s /etc/nginx/sites-available/micronet.mv /etc/nginx/sites-enabled/

# ALWAYS test first!
sudo nginx -t

# Only reload if test passes
sudo systemctl reload nginx
```

## Step 6: Set Up SSL

```bash
sudo certbot --nginx -d micronet.mv -d www.micronet.mv
```

Certbot will automatically update your config with SSL settings.

---

## Quick Reference

After setup, your sites will be:
- `cool.micronet.mv` - Micro Cool AC Services
- `mmgweb` (or your domain) - Micro Moto Garage
- `micronet.mv` - MicroNET Operations (main domain)

All will work independently without affecting each other.

