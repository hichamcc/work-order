# Work Order Management System - Deployment Guide

## Server Requirements
- PHP 8.2 or higher
- Composer
- MySQL 5.7 or higher
- Node.js & NPM
- Nginx or Apache
- SSL certificate (recommended)

## Initial Server Setup

### Update System
```bash
sudo apt update
sudo apt upgrade
```

### Install Required Software
```bash
# Install PHP and extensions
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl

# Install MySQL
sudo apt install mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# Install Nginx
sudo apt install nginx
```

### Configure MySQL
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
mysql -u root -p
CREATE DATABASE workorder;
CREATE USER 'workorder_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON workorder.* TO 'workorder_user'@'localhost';
FLUSH PRIVILEGES;
```

## Project Deployment

### Clone and Configure Project
```bash
# Clone repository
git clone [your-repository-url]
cd [project-directory]

# Install PHP dependencies
composer install --no-dev

# Install Node.js dependencies and build assets
npm install
npm run build

# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure .env file
nano .env

# Key settings to update in .env:
APP_NAME="Work Order Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workorder
DB_USERNAME=workorder_user
DB_PASSWORD=your_strong_password

# Configure mail settings if needed
MAIL_MAILER=smtp
MAIL_HOST=your-mail-server
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Database Setup
```bash
# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage link
php artisan storage:link
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/[project-directory]/public;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### SSL Setup (Using Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is installed by default
```

### Cache Configuration
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Supervisor Setup for Queue Worker
```bash
# Install Supervisor
sudo apt install supervisor

# Create configuration file
sudo nano /etc/supervisor/conf.d/workorder-worker.conf

# Add configuration:
[program:workorder-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/[project-directory]/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/[project-directory]/storage/logs/worker.log

# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "workorder-worker:*"
```

## Post-Deployment Checklist
1. Verify application is accessible via domain
2. Test SSL certificate is working
3. Confirm all assets are loading
4. Test user registration and login
5. Verify file uploads are working
6. Check email sending functionality
7. Test admin and worker features
8. Verify database connections
9. Check queue processing

## Maintenance Commands
```bash
# Update application
git pull origin main
composer install --no-dev
npm install
npm run build
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Monitor logs
tail -f storage/logs/laravel.log

# Restart services if needed
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart all
```

## Backup Setup
```bash
# Create backup directory
mkdir -p /var/backups/workorder

# Database backup script
mysqldump -u workorder_user -p workorder > /var/backups/workorder/db-backup-$(date +%Y%m%d).sql

# File backup
tar -czf /var/backups/workorder/files-backup-$(date +%Y%m%d).tar.gz /var/www/[project-directory]

# Set up cron job for daily backups
0 0 * * * /path/to/backup-script.sh
```

## Default Admin Credentials
Email: admin@example.com
Password: password

IMPORTANT: Change the default admin password immediately after deployment!

## Security Notes
1. Ensure firewall is configured properly
2. Keep system and packages updated
3. Use strong passwords
4. Monitor log files regularly
5. Set up automatic security updates
6. Implement rate limiting
7. Configure proper file permissions