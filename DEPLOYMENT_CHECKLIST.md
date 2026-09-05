# Deployment Checklist - Havells ERP+CRM

## Pre-Deployment Checklist

### ✅ Local Development Setup
- [ ] Composer dependencies installed (`composer install`)
- [ ] .env file configured with database credentials
- [ ] Application key generated (`php artisan key:generate`)
- [ ] Database created
- [ ] Migrations run successfully (`php artisan migrate`)
- [ ] Database seeded (`php artisan db:seed`)
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Application tested locally (`php artisan serve`)
- [ ] Admin login works (admin@havells.com)
- [ ] Employee login works (employee@havells.com)

### ✅ Code Quality
- [ ] No PHP syntax errors
- [ ] All routes accessible
- [ ] All models have proper relationships
- [ ] CSRF protection enabled
- [ ] Input validation in place
- [ ] Error handling implemented

### ✅ Security Review
- [ ] .env file in .gitignore
- [ ] APP_DEBUG set to false for production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] HTTPS configured (production)
- [ ] CORS headers set (if needed)
- [ ] Rate limiting enabled
- [ ] SQL injection prevention (Eloquent)
- [ ] XSS protection (Blade escaping)

## Production Server Setup

### 1. Server Requirements
- [ ] PHP 8.2 or higher installed
- [ ] MySQL 5.7+ or MariaDB 10.3+ installed
- [ ] Composer installed
- [ ] Apache or Nginx configured
- [ ] SSL certificate installed (HTTPS)

### 2. PHP Extensions Required
- [ ] BCMath
- [ ] Ctype
- [ ] JSON
- [ ] Mbstring
- [ ] OpenSSL
- [ ] PDO
- [ ] PDO_MySQL
- [ ] Tokenizer
- [ ] XML
- [ ] GD (for PDF generation)
- [ ] ZIP (for Excel export)

### 3. File Upload & Permissions
```bash
# Set ownership
sudo chown -R www-data:www-data /path/to/project

# Set directory permissions
sudo find /path/to/project -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /path/to/project -type f -exec chmod 644 {} \;

# Storage and cache writable
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Environment Configuration (.env)

**Critical Settings**:
```env
APP_NAME="Havells ERP+CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=havells_erp
DB_USERNAME=secure_user
DB_PASSWORD=strong_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 5. Database Setup
```bash
# Create production database
mysql -u root -p
CREATE DATABASE havells_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'havells_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON havells_erp.* TO 'havells_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force
```

### 6. Optimization for Production
```bash
# Install dependencies (production only)
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### 7. Web Server Configuration

#### Apache (.htaccess in public/ - already exists)
Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Virtual Host Configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/project/public

    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/havells-error.log
    CustomLog ${APACHE_LOG_DIR}/havells-access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/project/public;

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

### 8. SSL/HTTPS Setup (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
# OR
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already set up by certbot)
sudo certbot renew --dry-run
```

### 9. Queue Worker Setup (Optional)
If using queues for WhatsApp or notifications:

**Create Supervisor Config** (`/etc/supervisor/conf.d/havells-worker.conf`):
```ini
[program:havells-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start havells-worker:*
```

### 10. Scheduled Tasks (Cron)
Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add this line:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Backup Strategy

**Database Backup Script** (`/usr/local/bin/backup-havells.sh`):
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/havells"
PROJECT_DIR="/path/to/project"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u havells_user -p'password' havells_erp | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# File backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $PROJECT_DIR/storage/app

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable and add to cron:
```bash
chmod +x /usr/local/bin/backup-havells.sh

# Add to crontab (daily at 2 AM)
0 2 * * * /usr/local/bin/backup-havells.sh >> /var/log/havells-backup.log 2>&1
```

## Post-Deployment Verification

### 1. Application Testing
- [ ] Homepage loads (https://yourdomain.com)
- [ ] Login page accessible
- [ ] Admin login works
- [ ] Employee login works
- [ ] Dashboard displays correctly
- [ ] All menu items accessible
- [ ] HTTPS enabled (no mixed content warnings)
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] PDF generation works
- [ ] Excel export works

### 2. Database Verification
- [ ] All tables created
- [ ] Indexes present
- [ ] Foreign keys working
- [ ] Sample data loaded (if seeded)
- [ ] Migrations table updated

### 3. Security Testing
- [ ] .env file not publicly accessible
- [ ] storage/ not publicly accessible
- [ ] vendor/ not publicly accessible
- [ ] CSRF protection working
- [ ] XSS protection working
- [ ] SQL injection attempts blocked

### 4. Performance Testing
- [ ] Page load times acceptable (<2s)
- [ ] Database queries optimized
- [ ] Caching enabled
- [ ] Asset compression enabled
- [ ] CDN links loading

### 5. Monitoring Setup
- [ ] Error logging configured
- [ ] Storage logs rotating
- [ ] Server monitoring (CPU, RAM, Disk)
- [ ] Database monitoring
- [ ] Uptime monitoring

## Production Maintenance

### Daily Tasks
- [ ] Check error logs: `tail -f storage/logs/laravel.log`
- [ ] Monitor disk space: `df -h`
- [ ] Check database size
- [ ] Verify backups completed

### Weekly Tasks
- [ ] Review activity logs
- [ ] Check for security updates
- [ ] Analyze performance metrics
- [ ] Test backup restoration

### Monthly Tasks
- [ ] Update dependencies: `composer update`
- [ ] Optimize database: `php artisan optimize:clear`
- [ ] Review user access
- [ ] Security audit
- [ ] Performance tuning

## Rollback Plan

If deployment fails:

1. **Revert Code**:
```bash
git reset --hard HEAD~1
composer install --no-dev
```

2. **Rollback Database**:
```bash
php artisan migrate:rollback --step=1
```

3. **Restore from Backup**:
```bash
# Restore database
gunzip < backup_YYYYMMDD.sql.gz | mysql -u user -p database_name

# Restore files
tar -xzf backup_YYYYMMDD.tar.gz -C /path/to/restore
```

4. **Clear Cache**:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## Emergency Contacts

- **System Administrator**: [Contact Info]
- **Database Administrator**: [Contact Info]
- **Laravel Developer**: [Contact Info]
- **Server Provider**: [Contact Info]

## Important URLs

- **Production**: https://yourdomain.com
- **Admin Panel**: https://yourdomain.com/admin
- **Employee Panel**: https://yourdomain.com/employee
- **Server cPanel**: https://cpanel.yourdomain.com

## Credentials Storage

Store securely (password manager):
- [ ] Database credentials
- [ ] Admin user credentials
- [ ] Server SSH keys
- [ ] SSL certificates
- [ ] Email SMTP credentials
- [ ] API keys (WhatsApp, etc.)

---

## Sign-Off

- [ ] Development Team: _________________ Date: _______
- [ ] QA Team: _________________ Date: _______
- [ ] System Admin: _________________ Date: _______
- [ ] Project Manager: _________________ Date: _______

**Deployment Date**: _________________
**Version**: 1.0.0
**Status**: ☐ Pending  ☐ In Progress  ☐ **Completed**
