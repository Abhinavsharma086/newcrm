@echo off
echo ======================================
echo HisabMittra ERP+CRM Setup Script
echo ======================================
echo.

REM Check if composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: Composer is not installed. Please install Composer first.
    pause
    exit /b 1
)

REM Check if PHP is installed
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: PHP is not installed. Please install PHP 8.2 or higher.
    pause
    exit /b 1
)

echo Step 1: Installing Composer dependencies...
call composer install

echo.
echo Step 2: Setting up environment file...
if not exist .env (
    copy .env.example .env
    echo .env file created from .env.example
) else (
    echo .env file already exists
)

echo.
echo Step 3: Generating application key...
php artisan key:generate

echo.
echo Step 4: Database Configuration
echo Please edit .env file and configure your database settings:
echo - DB_DATABASE (default: hisabmittra_erp)
echo - DB_USERNAME (default: root)  
echo - DB_PASSWORD
echo.
set /p continue="Press Enter after configuring database in .env file..."

echo.
echo Step 5: Running database migrations...
php artisan migrate

echo.
echo Step 6: Publishing vendor packages...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

echo.
echo Step 7: Seeding database with sample data...
set /p seed="Do you want to seed the database? (y/n): "
if /i "%seed%"=="y" (
    php artisan db:seed
)

echo.
echo Step 8: Creating storage link...
php artisan storage:link

echo.
echo ======================================
echo Setup Complete!
echo ======================================
echo.
echo Default Admin Credentials:
echo Email: admin@hisabmittra.com
echo Password: password
echo.
echo Default Employee Credentials:
echo Email: employee@hisabmittra.com
echo Password: password
echo.
echo To start the development server, run:
echo php artisan serve
echo.
echo Then visit: http://localhost:8000
echo ======================================
pause
