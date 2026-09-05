# Quick Installation Guide

## Automatic Setup (Recommended)

### Windows
```bash
setup.bat
```

### Linux/Mac
```bash
chmod +x setup.sh
./setup.sh
```

## Manual Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env`:
```env
DB_DATABASE=hisabmittra_erp
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:
```sql
CREATE DATABASE hisabmittra_erp;
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Publish Packages
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
```

### 6. Seed Database
```bash
php artisan db:seed
```

### 7. Storage Link
```bash
php artisan storage:link
```

### 8. Start Server
```bash
php artisan serve
```

Visit: http://localhost:8000

## Default Login

**Admin:**
- Email: admin@hisabmittra.com
- Password: password

**Employee:**
- Email: employee@hisabmittra.com
- Password: password

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
```

### Composer Issues
```bash
composer update
composer dump-autoload
```

### Migration Errors
```bash
php artisan migrate:fresh
php artisan db:seed
```

## Production Deployment

1. Update `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

3. Set permissions:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Requirements

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Apache/Nginx
- PHP Extensions:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
