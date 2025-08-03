#!/bin/bash

# Smart Village Laravel Application - System Setup Script
# This script sets up a clean Ubuntu/Debian VPS for Laravel deployment

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

log "Starting Smart Village system setup..."

# Update system packages
log "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install essential packages
log "Installing essential packages..."
sudo apt install -y \
    curl \
    wget \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    git \
    htop \
    nano \
    vim \
    fail2ban \
    ufw \
    supervisor \
    redis-server \
    sqlite3

# Install Node.js 20 LTS
log "Installing Node.js 20 LTS..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify Node.js installation
node_version=$(node --version)
npm_version=$(npm --version)
log "Node.js installed: ${node_version}"
log "NPM installed: ${npm_version}"

# Install PHP 8.3 and extensions
log "Adding PHP repository..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

log "Installing PHP 8.3 and required extensions..."
sudo apt install -y \
    php8.3 \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-zip \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-sqlite3 \
    php8.3-intl \
    php8.3-redis \
    php8.3-imagick \
    php8.3-fileinfo \
    php8.3-tokenizer \
    php8.3-ctype \
    php8.3-json \
    php8.3-pdo

# Verify PHP installation
php_version=$(php --version | head -n 1)
log "PHP installed: ${php_version}"

# Install Composer
log "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify Composer installation
composer_version=$(composer --version)
log "Composer installed: ${composer_version}"

# Configure PHP for production
log "Configuring PHP for production..."
sudo cp /etc/php/8.3/fpm/php.ini /etc/php/8.3/fpm/php.ini.backup
sudo cp /etc/php/8.3/cli/php.ini /etc/php/8.3/cli/php.ini.backup

# Update PHP-FPM configuration
sudo tee /etc/php/8.3/fpm/conf.d/99-smartvillage.ini > /dev/null <<EOF
; Smart Village PHP Configuration
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20
session.gc_maxlifetime = 7200
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.save_comments = 1
opcache.enable_cli = 1
date.timezone = Asia/Jakarta
expose_php = Off
EOF

# Update PHP CLI configuration
sudo cp /etc/php/8.3/fpm/conf.d/99-smartvillage.ini /etc/php/8.3/cli/conf.d/99-smartvillage.ini

# Configure Redis
log "Configuring Redis..."
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Test Redis connection
if redis-cli ping | grep -q "PONG"; then
    log "Redis is running and accessible"
else
    warn "Redis may not be running properly"
fi

# Configure Supervisor
log "Configuring Supervisor..."
sudo systemctl enable supervisor
sudo systemctl start supervisor

# Basic firewall setup
log "Configuring UFW firewall..."
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Configure fail2ban
log "Configuring fail2ban..."
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

sudo tee /etc/fail2ban/jail.d/smartvillage.conf > /dev/null <<EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/error.log
EOF

sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Create deployment directory structure
log "Creating deployment directory structure..."
sudo mkdir -p /var/www
sudo mkdir -p /var/log/smartvillage
sudo mkdir -p /etc/smartvillage

# Create smartvillage user for application
log "Creating smartvillage system user..."
sudo useradd -r -d /var/www/smartvillage -s /bin/bash smartvillage || true
sudo mkdir -p /var/www/smartvillage
sudo chown smartvillage:smartvillage /var/www/smartvillage

# Add current user to smartvillage group
sudo usermod -a -G smartvillage $USER

# Set up log rotation
log "Setting up log rotation..."
sudo tee /etc/logrotate.d/smartvillage > /dev/null <<EOF
/var/log/smartvillage/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 0640 smartvillage smartvillage
}
EOF

# Install additional tools
log "Installing additional development tools..."
sudo apt install -y \
    tree \
    ncdu \
    iotop \
    iftop \
    nethogs \
    screen \
    tmux

# Set up swap file if not exists (for low memory VPS)
if [ ! -f /swapfile ]; then
    log "Setting up swap file (2GB)..."
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    
    # Configure swap usage
    echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf
fi

# Create system service monitoring script
log "Creating system monitoring script..."
sudo tee /usr/local/bin/smartvillage-health > /dev/null <<'EOF'
#!/bin/bash
# Smart Village Health Check Script

LOG_FILE="/var/log/smartvillage/health.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# Function to log messages
log_message() {
    echo "[$DATE] $1" >> $LOG_FILE
}

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    log_message "WARNING: Disk usage is ${DISK_USAGE}%"
fi

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEM_USAGE -gt 90 ]; then
    log_message "WARNING: Memory usage is ${MEM_USAGE}%"
fi

# Check if services are running
SERVICES=("nginx" "php8.3-fpm" "redis-server" "supervisor")
for service in "${SERVICES[@]}"; do
    if ! systemctl is-active --quiet $service; then
        log_message "ERROR: Service $service is not running"
    fi
done

log_message "Health check completed"
EOF

sudo chmod +x /usr/local/bin/smartvillage-health

# Set up cron job for health monitoring
log "Setting up health monitoring cron job..."
(crontab -l 2>/dev/null; echo "*/15 * * * * /usr/local/bin/smartvillage-health") | crontab -

# Create environment configuration template
log "Creating environment configuration template..."
sudo tee /etc/smartvillage/env.template > /dev/null <<EOF
# Smart Village Environment Configuration Template
# Copy this file to your project root as .env and customize the values

APP_NAME="Smart Village"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single,daily
LOG_LEVEL=info

DB_CONNECTION=sqlite
# For MySQL/MariaDB, use:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=smartvillage
# DB_USERNAME=smartvillage
# DB_PASSWORD=secure_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="\${APP_NAME}"

# Filament Admin Configuration
FILAMENT_DOMAIN=your-domain.com

# Smart Village Specific Configuration
SMARTVILLAGE_MAIN_DOMAIN=your-domain.com
SMARTVILLAGE_ALLOW_REGISTRATION=false
SMARTVILLAGE_DEFAULT_TIMEZONE=Asia/Jakarta

# Production Security
SECURE_HEADERS=true
FORCE_HTTPS=true
EOF

# Display summary
log "System setup completed successfully!"
info "==============================================="
info "SYSTEM SETUP SUMMARY"
info "==============================================="
info "✓ System packages updated"
info "✓ Node.js $(node --version) installed"
info "✓ PHP 8.3 installed and configured"
info "✓ Composer installed"
info "✓ Redis server configured"
info "✓ Supervisor configured"
info "✓ UFW firewall configured (SSH, HTTP, HTTPS allowed)"
info "✓ Fail2ban configured"
info "✓ System user 'smartvillage' created"
info "✓ Log rotation configured"
info "✓ Health monitoring script installed"
info "✓ Swap file configured (2GB)"
info ""
info "Next steps:"
info "1. Run 02-nginx-setup.sh to configure Nginx"
info "2. Run 03-laravel-setup.sh to deploy the application"
info "3. Run 04-ssl-setup.sh to configure SSL certificates"
info ""
info "Configuration files:"
info "- Environment template: /etc/smartvillage/env.template"
info "- Application directory: /var/www/smartvillage"
info "- Logs directory: /var/log/smartvillage"
info ""
warn "IMPORTANT: Please reboot the system before proceeding with the next scripts."
info "Run: sudo reboot"