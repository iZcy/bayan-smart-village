#!/bin/bash

# Smart Village Laravel Application - Application Setup Script
# This script clones, configures, and deploys the Laravel application

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
    error "This script should NOT be run as root. Please run as a regular user with sudo privileges."
    exit 1
fi

# Check if user has sudo privileges
if ! sudo -n true 2>/dev/null; then
    error "This script requires sudo privileges. Please run: sudo visudo and add your user."
    exit 1
fi

# Get repository URL from user input
if [ -z "$1" ]; then
    echo "Usage: $0 <git-repository-url> [branch]"
    echo "Example: $0 https://github.com/username/bayan-smart-village.git main"
    exit 1
fi

REPO_URL="$1"
BRANCH="${2:-main}"
APP_PATH="/var/www/smartvillage"

log "Setting up Laravel Smart Village application..."
info "Repository: $REPO_URL"
info "Branch: $BRANCH"
info "Path: $APP_PATH"

# All operations will use sudo as needed
RUN_AS_USER="sudo -u smartvillage"

# Clone or update repository
if [ -d "$APP_PATH/.git" ]; then
    log "Updating existing repository..."
    cd "$APP_PATH"
    $RUN_AS_USER git fetch origin
    $RUN_AS_USER git reset --hard origin/"$BRANCH"
    $RUN_AS_USER git pull origin "$BRANCH"
else
    log "Cloning repository..."
    sudo rm -rf "$APP_PATH"
    $RUN_AS_USER git clone -b "$BRANCH" "$REPO_URL" "$APP_PATH"
    cd "$APP_PATH"
fi

# Ensure correct ownership
sudo chown -R smartvillage:www-data "$APP_PATH"

# Install Composer dependencies
log "Installing Composer dependencies..."
cd "$APP_PATH"
$RUN_AS_USER composer install --no-dev --optimize-autoloader --no-interaction

# Create environment file if it doesn't exist
if [ ! -f "$APP_PATH/.env" ]; then
    log "Creating environment file..."
    $RUN_AS_USER cp .env.example .env
    
    # Load domain configuration if available
    if [ -f "/etc/smartvillage/domain.conf" ]; then
        source /etc/smartvillage/domain.conf
        log "Configuring environment with domain: $MAIN_DOMAIN"
        
        # Update environment file with domain configuration
        $RUN_AS_USER sed -i "s|APP_URL=.*|APP_URL=https://$MAIN_DOMAIN|g" .env
        $RUN_AS_USER sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
        $RUN_AS_USER sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env
        $RUN_AS_USER sed -i "s|SMARTVILLAGE_MAIN_DOMAIN=.*|SMARTVILLAGE_MAIN_DOMAIN=$MAIN_DOMAIN|g" .env
    fi
    
    # Generate application key
    log "Generating application key..."
    $RUN_AS_USER php artisan key:generate --no-interaction
fi

# Create storage directories and set permissions
log "Setting up storage directories..."
$RUN_AS_USER php artisan storage:link --force

# Create required directories
sudo mkdir -p "$APP_PATH/storage/framework/"{cache,sessions,views}
sudo mkdir -p "$APP_PATH/storage/app/public"
sudo mkdir -p "$APP_PATH/storage/logs"
sudo mkdir -p "$APP_PATH/bootstrap/cache"

# Set correct permissions
sudo chown -R smartvillage:www-data "$APP_PATH/storage"
sudo chown -R smartvillage:www-data "$APP_PATH/bootstrap/cache"
sudo chmod -R 775 "$APP_PATH/storage"
sudo chmod -R 775 "$APP_PATH/bootstrap/cache"

# Create SQLite database if needed
if [ ! -f "$APP_PATH/database/database.sqlite" ]; then
    log "Creating SQLite database..."
    $RUN_AS_USER touch "$APP_PATH/database/database.sqlite"
    sudo chmod 664 "$APP_PATH/database/database.sqlite"
    sudo chown smartvillage:www-data "$APP_PATH/database/database.sqlite"
fi

# Install Node.js dependencies and build assets
log "Installing Node.js dependencies..."
$RUN_AS_USER npm ci

log "Building frontend assets..."
$RUN_AS_USER npm run build

# Create supervisor configuration for Laravel queues
log "Setting up Laravel queue worker..."
sudo tee /etc/supervisor/conf.d/smartvillage-worker.conf > /dev/null << EOF
[program:smartvillage-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=smartvillage
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Create Laravel scheduler cron job
log "Setting up Laravel scheduler..."
(sudo crontab -u smartvillage -l 2>/dev/null; echo "* * * * * cd $APP_PATH && php artisan schedule:run >> /dev/null 2>&1") | sudo crontab -u smartvillage -

# Create backup script
log "Creating backup script..."
sudo tee /usr/local/bin/smartvillage-backup.sh > /dev/null << EOF
#!/bin/bash
# Smart Village Backup Script

BACKUP_DIR="/var/backups/smartvillage"
DATE=\$(date +%Y%m%d_%H%M%S)
APP_PATH="$APP_PATH"

# Create backup directory
mkdir -p "\$BACKUP_DIR"

# Backup database
echo "Backing up database..."
cp "\$APP_PATH/database/database.sqlite" "\$BACKUP_DIR/database_\$DATE.sqlite"

# Backup uploaded files
echo "Backing up storage..."
tar -czf "\$BACKUP_DIR/storage_\$DATE.tar.gz" -C "\$APP_PATH" storage/app/public

# Backup environment file
echo "Backing up environment..."
cp "\$APP_PATH/.env" "\$BACKUP_DIR/env_\$DATE.txt"

# Clean old backups (keep 30 days)
find "\$BACKUP_DIR" -name "*.sqlite" -mtime +30 -delete
find "\$BACKUP_DIR" -name "*.tar.gz" -mtime +30 -delete
find "\$BACKUP_DIR" -name "*.txt" -mtime +30 -delete

echo "Backup completed: \$DATE"
EOF

sudo chmod +x /usr/local/bin/smartvillage-backup.sh

# Setup daily backup cron
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/smartvillage-backup.sh >> /var/log/smartvillage/backup.log 2>&1") | crontab -

# Create log rotation for application logs
sudo tee /etc/logrotate.d/smartvillage-app > /dev/null << EOF
$APP_PATH/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 smartvillage www-data
    copytruncate
}
EOF

# Update supervisor and start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smartvillage-worker:*

# Create deployment script for future updates
log "Creating deployment script..."
sudo tee /usr/local/bin/smartvillage-deploy.sh > /dev/null << EOF
#!/bin/bash
# Smart Village Deployment Script for Updates

set -e

APP_PATH="$APP_PATH"
BRANCH="$BRANCH"

echo "🚀 Deploying Smart Village updates..."

# Put application in maintenance mode
cd "\$APP_PATH"
sudo -u smartvillage php artisan down --retry=60

# Pull latest changes
sudo -u smartvillage git fetch origin
sudo -u smartvillage git reset --hard origin/"\$BRANCH"
sudo -u smartvillage git pull origin "\$BRANCH"

# Install/update dependencies
sudo -u smartvillage composer install --no-dev --optimize-autoloader --no-interaction
sudo -u smartvillage npm ci
sudo -u smartvillage npm run build

# Run database migrations
sudo -u smartvillage php artisan migrate --force

# Clear and cache optimizations
sudo -u smartvillage php artisan config:clear
sudo -u smartvillage php artisan cache:clear
sudo -u smartvillage php artisan route:cache
sudo -u smartvillage php artisan view:cache
sudo -u smartvillage php artisan config:cache

# Set permissions
chown -R smartvillage:www-data "\$APP_PATH/storage"
chown -R smartvillage:www-data "\$APP_PATH/bootstrap/cache"
chmod -R 775 "\$APP_PATH/storage"
chmod -R 775 "\$APP_PATH/bootstrap/cache"

# Restart services
supervisorctl restart smartvillage-worker:*
systemctl reload nginx
systemctl reload php8.3-fpm

# Bring application back online
sudo -u smartvillage php artisan up

echo "✅ Deployment completed successfully!"
EOF

sudo chmod +x /usr/local/bin/smartvillage-deploy.sh

# Create maintenance commands
sudo tee /usr/local/bin/smartvillage-maintenance.sh > /dev/null << EOF
#!/bin/bash
# Smart Village Maintenance Commands

case "\$1" in
    "down")
        cd "$APP_PATH" && sudo -u smartvillage php artisan down --retry=60
        echo "Application is now in maintenance mode"
        ;;
    "up")
        cd "$APP_PATH" && sudo -u smartvillage php artisan up
        echo "Application is now live"
        ;;
    "cache-clear")
        cd "$APP_PATH" && sudo -u smartvillage php artisan cache:clear
        cd "$APP_PATH" && sudo -u smartvillage php artisan config:clear
        cd "$APP_PATH" && sudo -u smartvillage php artisan view:clear
        cd "$APP_PATH" && sudo -u smartvillage php artisan route:clear
        echo "Cache cleared"
        ;;
    "optimize")
        cd "$APP_PATH" && sudo -u smartvillage php artisan config:cache
        cd "$APP_PATH" && sudo -u smartvillage php artisan route:cache
        cd "$APP_PATH" && sudo -u smartvillage php artisan view:cache
        echo "Application optimized"
        ;;
    "queue-restart")
        supervisorctl restart smartvillage-worker:*
        echo "Queue workers restarted"
        ;;
    "logs")
        tail -f "$APP_PATH/storage/logs/laravel.log"
        ;;
    *)
        echo "Usage: \$0 {down|up|cache-clear|optimize|queue-restart|logs}"
        exit 1
        ;;
esac
EOF

sudo chmod +x /usr/local/bin/smartvillage-maintenance.sh

# Run database migrations and seeding
log "Running database migrations..."
$RUN_AS_USER php artisan migrate --force

log "Seeding database with sample data..."
$RUN_AS_USER php artisan db:seed --class=CompleteSeeder --force

# Optimize application for production
log "Optimizing application for production..."
$RUN_AS_USER php artisan config:cache
$RUN_AS_USER php artisan route:cache
$RUN_AS_USER php artisan view:cache

log "Laravel application setup completed successfully!"
info "==============================================="
info "LARAVEL SETUP SUMMARY"
info "==============================================="
info "✓ Repository cloned and configured"
info "✓ Composer dependencies installed"
info "✓ Environment file configured"
info "✓ Database created and migrated"
info "✓ Frontend assets built"
info "✓ Queue workers configured"
info "✓ Scheduler configured"
info "✓ Backup system configured"
info "✓ Application optimized for production"
info ""
info "Available commands:"
info "- smartvillage-deploy.sh: Deploy application updates"
info "- smartvillage-maintenance.sh: Maintenance commands"
info "- smartvillage-backup.sh: Backup application data"
info ""
info "Application structure:"
info "- App path: $APP_PATH"
info "- Logs: $APP_PATH/storage/logs/"
info "- Database: $APP_PATH/database/database.sqlite"
info "- Public files: $APP_PATH/storage/app/public/"
info ""
info "Next step:"
info "Run 04-ssl-setup.sh to configure SSL certificates"