#!/bin/bash

# Automated Backup Script for Bayan Smart Village
# This script creates backups of the application and database

set -e  # Exit on any error

# Configuration
APP_NAME="bayan-smart-village"
APP_DIR="/var/www/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
DB_NAME="bayan_smart_village"
DB_USER="root"
DB_PASSWORD=""  # Set your database password
RETENTION_DAYS=7  # Keep backups for 7 days
S3_BUCKET=""  # Optional: S3 bucket for remote backups
AWS_REGION="us-east-1"

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

# Create timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="${APP_NAME}_backup_${TIMESTAMP}"

print_status "🗄️  Starting backup process for $APP_NAME..."

# Create backup directory
mkdir -p $BACKUP_DIR
cd $BACKUP_DIR

print_status "Creating application backup..."

# Create application files backup (excluding sensitive files)
tar -czf "${BACKUP_NAME}_files.tar.gz" \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='vendor' \
    -C /var/www \
    $APP_NAME

print_success "Application files backup created"

# Backup database (SQLite)
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    print_status "Creating SQLite database backup..."
    cp "$APP_DIR/database/database.sqlite" "${BACKUP_NAME}_database.sqlite"
    gzip "${BACKUP_NAME}_database.sqlite"
    print_success "SQLite database backup created"
fi

# Backup MySQL database (if using MySQL)
if [ ! -z "$DB_PASSWORD" ] && command -v mysql &> /dev/null; then
    print_status "Creating MySQL database backup..."
    mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > "${BACKUP_NAME}_mysql.sql.gz"
    print_success "MySQL database backup created"
fi

# Backup environment file
if [ -f "$APP_DIR/.env" ]; then
    print_status "Creating environment backup..."
    cp "$APP_DIR/.env" "${BACKUP_NAME}_env"
    print_success "Environment backup created"
fi

# Backup storage files (uploads, etc.)
if [ -d "$APP_DIR/storage/app/public" ]; then
    print_status "Creating storage backup..."
    tar -czf "${BACKUP_NAME}_storage.tar.gz" -C "$APP_DIR" storage/app/public
    print_success "Storage backup created"
fi

# Create backup manifest
print_status "Creating backup manifest..."
cat > "${BACKUP_NAME}_manifest.txt" << EOF
Bayan Smart Village Backup Manifest
===================================
Backup Date: $(date)
Backup Name: $BACKUP_NAME
Application Directory: $APP_DIR
Database Type: SQLite/MySQL

Files Included:
- Application files: ${BACKUP_NAME}_files.tar.gz
- Database: ${BACKUP_NAME}_database.sqlite.gz or ${BACKUP_NAME}_mysql.sql.gz
- Environment: ${BACKUP_NAME}_env
- Storage: ${BACKUP_NAME}_storage.tar.gz

Backup Size:
$(du -sh ${BACKUP_NAME}_* | awk '{print $1 "\t" $2}')

Total Backup Size: $(du -shc ${BACKUP_NAME}_* | tail -1 | awk '{print $1}')
EOF

print_success "Backup manifest created"

# Upload to S3 (if configured)
if [ ! -z "$S3_BUCKET" ] && command -v aws &> /dev/null; then
    print_status "Uploading backup to S3..."
    aws s3 cp ${BACKUP_NAME}_files.tar.gz s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION
    aws s3 cp ${BACKUP_NAME}_database.sqlite.gz s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION 2>/dev/null || true
    aws s3 cp ${BACKUP_NAME}_mysql.sql.gz s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION 2>/dev/null || true
    aws s3 cp ${BACKUP_NAME}_env s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION
    aws s3 cp ${BACKUP_NAME}_storage.tar.gz s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION
    aws s3 cp ${BACKUP_NAME}_manifest.txt s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION
    print_success "Backup uploaded to S3"
fi

# Clean up old backups
print_status "Cleaning up old backups (keeping last $RETENTION_DAYS days)..."
find $BACKUP_DIR -name "${APP_NAME}_backup_*" -type f -mtime +$RETENTION_DAYS -delete
print_success "Old backups cleaned up"

# Clean up S3 old backups (if configured)
if [ ! -z "$S3_BUCKET" ] && command -v aws &> /dev/null; then
    print_status "Cleaning up old S3 backups..."
    aws s3 ls s3://$S3_BUCKET/backups/$APP_NAME/ --region $AWS_REGION | while read -r line; do
        file_date=$(echo $line | awk '{print $1}')
        file_name=$(echo $line | awk '{print $4}')
        if [[ ! -z "$file_name" ]] && [[ $(date -d "$file_date" +%s) -lt $(date -d "$RETENTION_DAYS days ago" +%s) ]]; then
            aws s3 rm s3://$S3_BUCKET/backups/$APP_NAME/$file_name --region $AWS_REGION
        fi
    done 2>/dev/null || true
    print_success "Old S3 backups cleaned up"
fi

# Generate backup report
BACKUP_SIZE=$(du -shc ${BACKUP_NAME}_* | tail -1 | awk '{print $1}')
TOTAL_BACKUPS=$(ls -1 ${APP_NAME}_backup_* 2>/dev/null | wc -l)

print_success "🎉 Backup completed successfully!"
echo ""
echo "📊 Backup Summary:"
echo "   Backup Name: $BACKUP_NAME"
echo "   Backup Size: $BACKUP_SIZE"
echo "   Location: $BACKUP_DIR"
echo "   Total Backups: $TOTAL_BACKUPS"
echo ""

# Log to syslog
logger "Bayan Smart Village backup completed: $BACKUP_NAME ($BACKUP_SIZE)"

print_warning "💡 To restore from this backup, use:"
echo "   sudo bash /usr/local/bin/restore_bayan.sh $BACKUP_NAME"