#!/bin/bash

echo "======================================"
echo "HisabMittra ERP+CRM Setup Script"
echo "======================================"
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "Error: Composer is not installed. Please install Composer first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null
then
    echo "Error: PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

echo "Step 1: Installing Composer dependencies..."
composer install

echo ""
echo "Step 2: Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created from .env.example"
else
    echo ".env file already exists"
fi

echo ""
echo "Step 3: Generating application key..."
php artisan key:generate

echo ""
echo "Step 4: Please configure your database in .env file"
echo "Database settings:"
read -p "DB_DATABASE (default: hisabmittra_erp): " db_name
db_name=${db_name:-hisabmittra_erp}

read -p "DB_USERNAME (default: root): " db_user
db_user=${db_user:-root}

read -p "DB_PASSWORD: " db_pass

# Update .env file
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env

echo ""
echo "Step 5: Running database migrations..."
read -p "Do you want to run migrations now? (y/n): " run_migrations

if [ "$run_migrations" = "y" ]; then
    php artisan migrate
    
    echo ""
    echo "Step 6: Publishing vendor packages..."
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-migrations"
    php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
    
    echo ""
    echo "Step 7: Seeding database..."
    read -p "Do you want to seed the database with sample data? (y/n): " run_seed
    
    if [ "$run_seed" = "y" ]; then
        php artisan db:seed
    fi
fi

echo ""
echo "Step 8: Creating storage link..."
php artisan storage:link

echo ""
echo "======================================"
echo "Setup Complete!"
echo "======================================"
echo ""
echo "Default Admin Credentials:"
echo "Email: admin@hisabmittra.com"
echo "Password: password"
echo ""
echo "Default Employee Credentials:"
echo "Email: employee@hisabmittra.com"
echo "Password: password"
echo ""
echo "To start the development server, run:"
echo "php artisan serve"
echo ""
echo "Then visit: http://localhost:8000"
echo "======================================"
