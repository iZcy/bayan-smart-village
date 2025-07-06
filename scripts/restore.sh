#!/bin/bash

# Restore Script for Bayan Smart Village
# This script restores the application from a backup

set -e  # Exit on any error

# Configuration
APP_NAME="bayan-smart-village"
APP_DIR="/var/www/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if backup name is provided
if [ -z "$1" ]; then
    print_error "Usage: $0 <backup_name>"
    echo ""
    echo "Available backups:"
    ls -1 $BACKUP_DIR/${APP_NAME}_backup_* 2>/dev/null | head -10 || echo "No backups found"
    exit 1
fi

BACKUP_NAME="$1"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "This script must be run as root or with sudo"
    exit 1
fi

print_warning "🔄 Starting restore process for $APP_NAME..."
print_warning "This will OVERWRITE the current application!"
echo ""
read -p "Are you sure you want to continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_error "Restore cancelled"
    exit 1
fi

cd $BACKUP_DIR

# Check if backup files exist
if [ ! -f "${BACKUP_NAME}_files.tar.gz" ]; then
    print_error "Backup files not found: ${BACKUP_NAME}_files.tar.gz"
    exit 1
fi

print_status "Creating pre-restore backup..."
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
PRE_RESTORE_BACKUP="${APP_NAME}_pre_restore_${TIMESTAMP}"

# Backup current state
if [ -d "$APP_DIR" ]; then
    tar -czf "${PRE_RESTORE_BACKUP}_current.tar.gz" -C /var/www $APP_NAME
    print_success "Pre-restore backup created: ${PRE_RESTORE_BACKUP}_current.tar.gz"
fi

# Stop services
print_status "Stopping services..."
systemctl stop nginx
supervisorctl stop ${APP_NAME}-worker:* || true

# Create temporary restoration directory
TEMP_DIR="/tmp/restore_${BACKUP_NAME}"
rm -rf $TEMP_DIR
mkdir -p $TEMP_DIR

# Extract application files
print_status "Extracting application files..."
tar -xzf "${BACKUP_NAME}_files.tar.gz" -C $TEMP_DIR

# Backup current .env and storage
if [ -d "$APP_DIR" ]; then
    if [ -f "$APP_DIR/.env" ]; then
        cp "$APP_DIR/.env" "${TEMP_DIR}/${APP_NAME}/.env.current" || true
    fi
    if [ -d "$APP_DIR/storage/app/public" ]; then
        mkdir -p "${TEMP_DIR}/${APP_NAME}/storage/app"
        cp -r "$APP_DIR/storage/app/public" "${TEMP_DIR}/${APP_NAME}/storage/app/" || true
    fi
fi

# Remove old application directory
if [ -d "$APP_DIR" ]; then
    rm -rf "${APP_DIR}.old" || true
    mv "$APP_DIR" "${APP_DIR}.old"
fi

# Move restored files to application directory
mv "${TEMP_DIR}/${APP_NAME}" "$APP_DIR"

# Restore environment file if backup exists
if [ -f "${BACKUP_NAME}_env" ]; then
    print_status "Restoring environment file..."
    cp "${BACKUP_NAME}_env" "$APP_DIR/.env"
    print_success "Environment file restored"
else
    print_warning "No environment backup found, using current .env"
    if [ -f "$APP_DIR/.env.current" ]; then
        mv "$APP_DIR/.env.current" "$APP_DIR/.env"
    fi
fi

# Restore database
if [ -f "${BACKUP_NAME}_database.sqlite.gz" ]; then
    print_status "Restoring SQLite database..."
    gunzip -c "${BACKUP_NAME}_database.sqlite.gz" > "$APP_DIR/database/database.sqlite"
    print_success "SQLite database restored"
elif [ -f "${BACKUP_NAME}_mysql.sql.gz" ]; then
    print_status "Restoring MySQL database..."
    print_warning "Please ensure database credentials are correct in .env"
    gunzip -c "${BACKUP_NAME}_mysql.sql.gz" | mysql -u root -p
    print_success "MySQL database restored"
fi

# Restore storage files
if [ -f "${BACKUP_NAME}_storage.tar.gz" ]; then
    print_status "Restoring storage files..."
    tar -xzf "${BACKUP_NAME}_storage.tar.gz" -C "$APP_DIR"
    print_success "Storage files restored"
fi

# Set proper ownership and permissions
print_status "Setting proper permissions..."
chown -R $APP_NAME:$APP_NAME $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache
chmod 664 $APP_DIR/database/database.sqlite 2>/dev/null || true

# Install dependencies (in case of version differences)
print_status "Installing/updating dependencies..."
cd $APP_DIR
sudo -u $APP_NAME composer install --no-dev --optimize-autoloader --no-interaction
sudo -u $APP_NAME npm ci --production
sudo -u $APP_NAME npm run build

# Run migrations (in case of schema changes)
print_status "Running migrations..."
sudo -u $APP_NAME php artisan migrate --force

# Clear and optimize
print_status "Optimizing application..."
sudo -u $APP_NAME php artisan storage:link
sudo -u $APP_NAME php artisan config:cache
sudo -u $APP_NAME php artisan route:cache
sudo -u $APP_NAME php artisan view:cache

# Start services
print_status "Starting services..."
systemctl start nginx
supervisorctl start ${APP_NAME}-worker:*

# Test application
print_status "Testing application..."
sleep 5
if curl -f -s http://localhost > /dev/null; then
    print_success "Application is responding"
else
    print_warning "Application may not be responding correctly"
fi

# Cleanup
rm -rf $TEMP_DIR
rm -rf "${APP_DIR}.old"

print_success "🎉 Restore completed successfully!"
echo ""
print_success "✅ Application restored from backup: $BACKUP_NAME"
echo "🔗 Application URL: http://your-domain.com"
echo "📖 Admin panel: http://your-domain.com/admin"
echo ""
print_warning "📝 Post-restore checklist:"
echo "1. Verify application functionality"
echo "2. Check environment settings in .env"
echo "3. Test all features and links"
echo "4. Monitor logs for any issues"
echo ""
echo "📊 Restore details:"
echo "   Backup used: $BACKUP_NAME"
echo "   Restore time: $(date)"
echo "   Pre-restore backup: ${PRE_RESTORE_BACKUP}_current.tar.gz"