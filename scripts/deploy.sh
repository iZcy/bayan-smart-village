#!/bin/bash

# Bayan Smart Village - VPS Deployment Script
# This script deploys the application to your VPS server

set -e  # Exit on any error

# Configuration (modify these variables)
APP_NAME="bayan-smart-village"
APP_DIR="/var/www/$APP_NAME"
REPO_URL="https://github.com/YOUR_USERNAME/bayan-smart-village.git"
BRANCH="main"
PHP_VERSION="8.2"
DOMAIN="your-domain.com"

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

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    print_error "This script must be run as root or with sudo"
    exit 1
fi

print_status "🏘️  Deploying Bayan Smart Village to VPS..."

# Update system packages
print_status "Updating system packages..."
apt update && apt upgrade -y

# Install required packages
print_status "Installing required packages..."
apt install -y software-properties-common curl wget unzip git nginx mysql-server \
    supervisor redis-server fail2ban ufw

# Install PHP and extensions
print_status "Installing PHP $PHP_VERSION and extensions..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php$PHP_VERSION php$PHP_VERSION-fpm php$PHP_VERSION-cli php$PHP_VERSION-common \
    php$PHP_VERSION-mysql php$PHP_VERSION-zip php$PHP_VERSION-gd php$PHP_VERSION-mbstring \
    php$PHP_VERSION-curl php$PHP_VERSION-xml php$PHP_VERSION-bcmath php$PHP_VERSION-sqlite3 \
    php$PHP_VERSION-intl php$PHP_VERSION-redis

# Install Composer
if ! command -v composer &> /dev/null; then
    print_status "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    print_success "Composer installed"
fi

# Install Node.js and npm
if ! command -v node &> /dev/null; then
    print_status "Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install -y nodejs
    print_success "Node.js installed"
fi

# Create application user
if ! id "$APP_NAME" &>/dev/null; then
    print_status "Creating application user..."
    adduser --system --group --shell /bin/bash --home /home/$APP_NAME $APP_NAME
    print_success "User $APP_NAME created"
fi

# Create application directory
print_status "Setting up application directory..."
mkdir -p $APP_DIR
chown $APP_NAME:$APP_NAME $APP_DIR

# Clone or update repository
if [ -d "$APP_DIR/.git" ]; then
    print_status "Updating existing repository..."
    cd $APP_DIR
    sudo -u $APP_NAME git fetch origin
    sudo -u $APP_NAME git reset --hard origin/$BRANCH
else
    print_status "Cloning repository..."
    sudo -u $APP_NAME git clone $REPO_URL $APP_DIR
    cd $APP_DIR
    sudo -u $APP_NAME git checkout $BRANCH
fi

print_success "Repository ready"

# Install PHP dependencies
print_status "Installing PHP dependencies..."
sudo -u $APP_NAME composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies and build assets
print_status "Installing Node.js dependencies and building assets..."
sudo -u $APP_NAME npm ci
sudo -u $APP_NAME npm run build

# Set up environment file
if [ ! -f "$APP_DIR/.env" ]; then
    print_status "Creating .env file..."
    sudo -u $APP_NAME cp $APP_DIR/.env.example $APP_DIR/.env

    # Generate app key
    sudo -u $APP_NAME php artisan key:generate --force

    print_warning "Please edit $APP_DIR/.env with your production settings"
    print_warning "Don't forget to set proper database credentials and APP_URL"
fi

# Set proper permissions
print_status "Setting file permissions..."
chown -R $APP_NAME:$APP_NAME $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache

# Create database and run migrations
print_status "Setting up database..."
if [ ! -f "$APP_DIR/database/database.sqlite" ]; then
    sudo -u $APP_NAME touch $APP_DIR/database/database.sqlite
    chmod 664 $APP_DIR/database/database.sqlite
fi

sudo -u $APP_NAME php artisan migrate --force
sudo -u $APP_NAME php artisan storage:link

# Optimize application
print_status "Optimizing application..."
sudo -u $APP_NAME php artisan config:cache
sudo -u $APP_NAME php artisan route:cache
sudo -u $APP_NAME php artisan view:cache

# Configure Nginx
print_status "Configuring Nginx..."
cat > /etc/nginx/sites-available/$APP_NAME << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN *.kecamatanbayan.id;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php$PHP_VERSION-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable the site
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t
systemctl reload nginx

# Configure PHP-FPM
print_status "Configuring PHP-FPM..."
sed -i "s/user = www-data/user = $APP_NAME/" /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
sed -i "s/group = www-data/group = $APP_NAME/" /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
systemctl restart php$PHP_VERSION-fpm

# Configure queue worker (Supervisor)
print_status "Setting up queue worker..."
cat > /etc/supervisor/conf.d/$APP_NAME-worker.conf << EOF
[program:$APP_NAME-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=$APP_DIR
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$APP_NAME
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update
supervisorctl start $APP_NAME-worker:*

# Configure firewall
print_status "Configuring firewall..."
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable

# Set up log rotation
print_status "Setting up log rotation..."
cat > /etc/logrotate.d/$APP_NAME << EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 $APP_NAME $APP_NAME
    postrotate
        supervisorctl restart $APP_NAME-worker:*
    endscript
}
EOF

# Start all services
print_status "Starting services..."
systemctl enable nginx
systemctl enable php$PHP_VERSION-fpm
systemctl enable mysql
systemctl enable redis-server
systemctl enable supervisor

systemctl start nginx
systemctl start php$PHP_VERSION-fpm
systemctl start mysql
systemctl start redis-server
systemctl start supervisor

print_success "🎉 Deployment completed successfully!"
echo ""
print_warning "⚠️  Post-deployment checklist:"
echo "1. Edit $APP_DIR/.env with production settings"
echo "2. Set up SSL certificate (recommend using Certbot)"
echo "3. Configure your DNS to point to this server"
echo "4. Set up database backups"
echo "5. Configure monitoring (optional)"
echo ""
echo "🔗 Your application should be accessible at: http://$DOMAIN"
echo "📖 Admin panel: http://$DOMAIN/admin"