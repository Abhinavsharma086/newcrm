#!/bin/bash

# ==============================================================================
# HisabMittra Live Deployment Script
# ==============================================================================
# Run this script from your local machine to deploy the project to your live server.
#
# Prerequisite: Make sure you have git bash or a bash terminal installed.
# ==============================================================================

# Server Configuration
SSH_PORT="65002"
SSH_USER="u425316205"
SSH_HOST="46.202.183.28"
REMOTE_PATH="/home/u425316205/public_html" # Adjust this path to match your server's document root

echo "🚀 Starting Deployment to $SSH_HOST..."

# Step 1: Zip the project files locally (excluding vendor, node_modules, etc.)
echo "📦 Packaging project files..."
tar --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.git' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    -czf project.tar.gz .

# Step 2: Upload the zip file to the server
echo "📤 Uploading package to the remote server..."
scp -P $SSH_PORT project.tar.gz $SSH_USER@$SSH_HOST:$REMOTE_PATH/

# Step 3: Extract and configure on the server
echo "⚙️ Configuring the remote server..."
ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << EOF
    cd $REMOTE_PATH
    
    # Extract
    echo "Extracting files..."
    tar -xzf project.tar.gz
    rm project.tar.gz
    
    # Set permissions
    echo "Setting file permissions..."
    chmod -R 775 storage bootstrap/cache
    
    # Install dependencies
    echo "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader
    
    # Generate App Key if not exists
    if ! grep -q "APP_KEY=base" .env; then
        echo "Generating APP_KEY..."
        php artisan key:generate
    fi
    
    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force
    
    # Cache Configuration for performance
    echo "Caching configurations..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "✅ Remote configuration completed!"
EOF

# Clean up local zip
rm project.tar.gz

echo "🎉 Deployment complete!"
