#!/bin/bash

# Smart Village Complete Deployment Script
# This script runs all deployment scripts in the correct order

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

# Function to check if script exists and is executable
check_script() {
    local script=$1
    if [ ! -f "$script" ]; then
        error "Script not found: $script"
        exit 1
    fi
    if [ ! -x "$script" ]; then
        log "Making script executable: $script"
        chmod +x "$script"
    fi
}

# Function to prompt user for input with default
prompt_with_default() {
    local prompt="$1"
    local default="$2"
    local var_name="$3"
    
    read -p "$prompt [$default]: " input
    if [ -z "$input" ]; then
        eval "$var_name=\"$default\""
    else
        eval "$var_name=\"$input\""
    fi
}

# Function to validate domain format
validate_domain() {
    local domain=$1
    if [[ ! $domain =~ ^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$ ]]; then
        error "Invalid domain format: $domain"
        return 1
    fi
    return 0
}

# Function to validate email format
validate_email() {
    local email=$1
    if [[ ! $email =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
        error "Invalid email format: $email"
        return 1
    fi
    return 0
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

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

log "🚀 Smart Village Complete Deployment"
info "======================================"
info "This script will deploy the complete Smart Village Laravel application"
info "on a fresh Ubuntu/Debian VPS with the following components:"
info ""
info "✓ System packages and dependencies"
info "✓ PHP 8.3, Node.js 20, Composer, Redis"
info "✓ Nginx web server with SSL"
info "✓ Laravel application with database"
info "✓ SSL certificates with auto-renewal"
info "✓ Queue workers and schedulers"
info "✓ Monitoring and backup systems"
info ""

# Check if all scripts exist
check_script "./01-system-setup.sh"
check_script "./02-nginx-setup.sh"
check_script "./03-laravel-setup.sh"
check_script "./04-ssl-setup.sh"

# Collect deployment information
echo ""
log "Please provide the following information:"
echo ""

# Domain name
while true; do
    prompt_with_default "Enter your main domain name" "example.com" MAIN_DOMAIN
    if validate_domain "$MAIN_DOMAIN"; then
        break
    fi
done

# SSL Email
while true; do
    prompt_with_default "Enter email for SSL certificates" "admin@$MAIN_DOMAIN" SSL_EMAIL
    if validate_email "$SSL_EMAIL"; then
        break
    fi
done

# Git repository
prompt_with_default "Enter Git repository URL" "https://github.com/username/bayan-smart-village.git" REPO_URL

# Git branch
prompt_with_default "Enter Git branch" "main" BRANCH

# Confirmation
echo ""
info "Deployment Configuration:"
info "------------------------"
info "Domain: $MAIN_DOMAIN"
info "SSL Email: $SSL_EMAIL"
info "Repository: $REPO_URL"
info "Branch: $BRANCH"
echo ""

read -p "Do you want to proceed with this configuration? (y/N): " confirm
if [[ ! $confirm =~ ^[Yy]$ ]]; then
    log "Deployment cancelled by user"
    exit 0
fi

# Check system requirements
log "Checking system requirements..."

# Check OS
if ! grep -q "Ubuntu\|Debian" /etc/os-release; then
    warn "This script is designed for Ubuntu/Debian. Proceeding anyway..."
fi

# Check available disk space (minimum 10GB)
available_space=$(df / | awk 'NR==2 {print int($4/1024/1024)}')
if [ "$available_space" -lt 10 ]; then
    warn "Available disk space is ${available_space}GB. Recommended minimum is 10GB."
    read -p "Continue anyway? (y/N): " continue_anyway
    if [[ ! $continue_anyway =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check available memory (minimum 1GB)
available_memory=$(free -m | awk 'NR==2{print $2}')
if [ "$available_memory" -lt 1000 ]; then
    warn "Available memory is ${available_memory}MB. Recommended minimum is 1000MB."
    read -p "Continue anyway? (y/N): " continue_anyway
    if [[ ! $continue_anyway =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Test domain DNS resolution
log "Testing DNS resolution for $MAIN_DOMAIN..."
if ! nslookup "$MAIN_DOMAIN" >/dev/null 2>&1; then
    warn "DNS resolution failed for $MAIN_DOMAIN"
    warn "Make sure your domain points to this server's IP address"
    read -p "Continue anyway? (y/N): " continue_anyway
    if [[ ! $continue_anyway =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Start deployment
echo ""
log "Starting Smart Village deployment..."
echo ""

# Step 1: System Setup
log "STEP 1/4: System Setup"
info "Installing system packages, PHP 8.3, Node.js, and configuring security..."
if ./01-system-setup.sh; then
    log "✓ System setup completed successfully"
else
    error "✗ System setup failed"
    exit 1
fi

# Check if reboot is needed
if [ -f /var/run/reboot-required ]; then
    warn "System reboot is required to complete the setup"
    warn "Please run the following commands after reboot:"
    echo ""
    echo "cd $SCRIPT_DIR"
    echo "./02-nginx-setup.sh \"$MAIN_DOMAIN\" \"$SSL_EMAIL\""
    echo "./03-laravel-setup.sh \"$REPO_URL\" \"$BRANCH\""
    echo "./04-ssl-setup.sh \"$MAIN_DOMAIN\" \"$SSL_EMAIL\""
    echo ""
    read -p "Reboot now? (Y/n): " reboot_now
    if [[ ! $reboot_now =~ ^[Nn]$ ]]; then
        log "Rebooting system..."
        sudo reboot
    else
        log "Please reboot manually and continue with the remaining steps"
        exit 0
    fi
fi

# Step 2: Nginx Setup
log "STEP 2/4: Nginx Configuration"
info "Configuring Nginx web server with multi-domain support..."
if ./02-nginx-setup.sh "$MAIN_DOMAIN" "$SSL_EMAIL"; then
    log "✓ Nginx configuration completed successfully"
else
    error "✗ Nginx configuration failed"
    exit 1
fi

# Step 3: Laravel Setup
log "STEP 3/4: Laravel Application Setup"
info "Deploying Laravel application, installing dependencies, and configuring database..."
if ./03-laravel-setup.sh "$REPO_URL" "$BRANCH"; then
    log "✓ Laravel application setup completed successfully"
else
    error "✗ Laravel application setup failed"
    exit 1
fi

# Step 4: SSL Setup
log "STEP 4/4: SSL Certificate Configuration"
info "Obtaining SSL certificates and configuring HTTPS..."
if ./04-ssl-setup.sh "$MAIN_DOMAIN" "$SSL_EMAIL"; then
    log "✓ SSL certificate configuration completed successfully"
else
    error "✗ SSL certificate configuration failed"
    exit 1
fi

# Final system check
log "Performing final system checks..."

# Check if all services are running
services=("nginx" "php8.3-fpm" "redis-server" "supervisor")
all_services_ok=true

for service in "${services[@]}"; do
    if sudo systemctl is-active --quiet "$service"; then
        info "✓ $service is running"
    else
        error "✗ $service is not running"
        all_services_ok=false
    fi
done

# Check queue workers
if sudo supervisorctl status smartvillage-worker:* | grep -q "RUNNING"; then
    info "✓ Queue workers are running"
else
    error "✗ Queue workers are not running"
    all_services_ok=false
fi

# Test website accessibility
log "Testing website accessibility..."
if curl -sSf "https://$MAIN_DOMAIN" >/dev/null 2>&1; then
    info "✓ Website is accessible via HTTPS"
elif curl -sSf "http://$MAIN_DOMAIN" >/dev/null 2>&1; then
    warn "✓ Website is accessible via HTTP (SSL may still be configuring)"
else
    error "✗ Website is not accessible"
    all_services_ok=false
fi

# Display final results
echo ""
if [ "$all_services_ok" = true ]; then
    log "🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!"
else
    warn "⚠️  DEPLOYMENT COMPLETED WITH WARNINGS"
fi

info "========================================"
info "DEPLOYMENT SUMMARY"
info "========================================"
info "Domain: $MAIN_DOMAIN"
info "Application URL: https://$MAIN_DOMAIN"
info "Admin Panel: https://$MAIN_DOMAIN/admin"
info "Repository: $REPO_URL"
info "Branch: $BRANCH"
info ""
info "Application Structure:"
info "- App Directory: /var/www/smartvillage"
info "- Database: /var/www/smartvillage/database/database.sqlite"
info "- Logs: /var/log/smartvillage/"
info "- Backups: /var/backups/smartvillage/"
info ""
info "Management Commands:"
info "- smartvillage-maintenance.sh: Application maintenance"
info "- smartvillage-deploy.sh: Deploy updates"
info "- smartvillage-backup.sh: Manual backup"
info "- smartvillage-ssl-renew.sh: Renew SSL certificates"
info ""
info "Next Steps:"
info "1. Visit https://$MAIN_DOMAIN/admin to access the admin panel"
info "2. Create your first admin user account"
info "3. Configure villages and content through the admin interface"
info "4. Test subdomain functionality (e.g., village-name.$MAIN_DOMAIN)"
info ""

if [ "$all_services_ok" != true ]; then
    warn "Please check the logs and resolve any issues:"
    warn "- Application logs: tail -f /var/www/smartvillage/storage/logs/laravel.log"
    warn "- System health: smartvillage-health"
    warn "- Service status: sudo systemctl status nginx php8.3-fpm redis-server"
fi

log "Deployment process completed!"
echo ""