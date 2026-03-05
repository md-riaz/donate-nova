# Deployment Guide for Donate Nova

This guide will help you deploy the Donate Nova application to a production server.

## Prerequisites

- VPS or shared hosting with SSH access
- PHP 8.0 or higher
- Composer
- Node.js & NPM
- MySQL or PostgreSQL database
- Domain/subdomain (e.g., donate.nova.org.bd)
- SSL certificate (Let's Encrypt recommended)
- bKash merchant account with production credentials

## Step 1: Server Preparation

### Install Required Software

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 and required extensions
sudo apt install php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
```

## Step 2: Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/md-riaz/donate-nova.git
cd donate-nova
```

## Step 3: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build assets
npm run build
```

## Step 4: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file with production settings:

```env
APP_NAME="Donate Nova"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://donate.nova.org.bd

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=donate_nova
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# bKash Production Configuration
BKASH_SANDBOX=false
BKASH_APP_KEY=your_production_app_key
BKASH_APP_SECRET=your_production_app_secret
BKASH_USERNAME=your_production_username
BKASH_PASSWORD=your_production_password
```

## Step 5: Database Setup

```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE donate_nova CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'donate_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON donate_nova.* TO 'donate_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Run migrations
php artisan migrate --force
```

## Step 6: Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/donate-nova
sudo chmod -R 755 /var/www/donate-nova

# Storage and cache permissions
sudo chmod -R 775 /var/www/donate-nova/storage
sudo chmod -R 775 /var/www/donate-nova/bootstrap/cache
```

## Step 7: Configure Nginx

Create nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/donate-nova
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name donate.nova.org.bd;
    root /var/www/donate-nova/public;

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/donate-nova /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Step 8: SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d donate.nova.org.bd

# Auto-renewal is configured automatically
```

## Step 9: Optimize Laravel

```bash
cd /var/www/donate-nova

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## Step 10: Setup Queue Worker (Optional)

If using queues, set up a supervisor configuration:

```bash
sudo nano /etc/supervisor/conf.d/donate-nova.conf
```

```ini
[program:donate-nova-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/donate-nova/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/donate-nova/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start donate-nova-worker:*
```

## Step 11: Setup Cron Jobs

```bash
sudo crontab -e
```

Add the following line:

```
* * * * * cd /var/www/donate-nova && php artisan schedule:run >> /dev/null 2>&1
```

## Step 12: Configure Monitoring

### Log Rotation

```bash
sudo nano /etc/logrotate.d/donate-nova
```

```
/var/www/donate-nova/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## Step 13: Test bKash Integration

1. Create a test donation with a small amount
2. Complete payment in bKash sandbox
3. Verify transaction is recorded in database
4. Check logs for any errors: `tail -f storage/logs/laravel.log`

## Step 14: Go Live

1. Switch `BKASH_SANDBOX=false` in `.env`
2. Update bKash credentials to production
3. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```
4. Re-cache for production:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Security Checklist

- [ ] SSL certificate installed and working
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database password set
- [ ] File permissions set correctly (755/775)
- [ ] Firewall configured (UFW or similar)
- [ ] SSH key authentication enabled
- [ ] Regular backups configured
- [ ] Log monitoring enabled
- [ ] bKash production credentials secured
- [ ] `.env` file secured (not in git)

## Maintenance

### Update Application

```bash
cd /var/www/donate-nova
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
```

### Database Backup

```bash
# Backup
mysqldump -u donate_user -p donate_nova > backup-$(date +%Y%m%d).sql

# Restore
mysql -u donate_user -p donate_nova < backup-YYYYMMDD.sql
```

### Monitor Logs

```bash
# Laravel logs
tail -f /var/www/donate-nova/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

## Troubleshooting

### 500 Internal Server Error
- Check Laravel logs: `storage/logs/laravel.log`
- Check Nginx error logs: `/var/log/nginx/error.log`
- Verify file permissions
- Clear and recache config

### bKash Payment Fails
- Verify bKash credentials in `.env`
- Check `BKASH_SANDBOX` setting matches environment
- Review logs for API errors
- Ensure callback URL is accessible from internet

### Database Connection Error
- Verify database credentials in `.env`
- Check database service is running
- Verify user has correct permissions

## Support

For issues or questions, please contact the development team or open an issue on GitHub.
