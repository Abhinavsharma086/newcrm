# ==============================================================================
# HisabMittra Live Deployment Script (PowerShell Version)
# ==============================================================================

$SSH_PORT = "65002"
$SSH_USER = "u425316205"
$SSH_HOST = "46.202.183.28"
$REMOTE_PATH = "/home/u425316205/public_html" # Adjust this path to match your server's document root

Write-Host "🚀 Starting Deployment to $SSH_HOST..." -ForegroundColor Cyan

# Step 1: Zip the project files locally
Write-Host "📦 Packaging project files..." -ForegroundColor Yellow
$excludeList = @("vendor", "node_modules", ".git", "project.zip", "project.tar.gz")
Compress-Archive -Path (Get-ChildItem -Path . -Exclude $excludeList) -DestinationPath project.zip -Force

# Step 2: Upload the zip file to the server
Write-Host "📤 Uploading package to the remote server..." -ForegroundColor Yellow
scp -P $SSH_PORT project.zip "$($SSH_USER)@$($SSH_HOST):$REMOTE_PATH/"

# Step 3: Extract and configure on the server
Write-Host "⚙️ Configuring the remote server..." -ForegroundColor Yellow
$remoteCommands = @"
cd $REMOTE_PATH
echo "Extracting files..."
unzip -o project.zip
rm project.zip

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

if (! grep -q "APP_KEY=base" .env) {
    php artisan key:generate
}

echo "Running migrations..."
php artisan migrate --force

echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
"@

ssh -p $SSH_PORT "$($SSH_USER)@$($SSH_HOST)" $remoteCommands

# Clean up local zip
Remove-Item project.zip -ErrorAction SilentlyContinue

Write-Host "🎉 Deployment complete!" -ForegroundColor Green
