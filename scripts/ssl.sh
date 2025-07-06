#!/bin/bash

# SSL Certificate Setup Script for Bayan Smart Village
# This script installs and configures SSL certificates using Let's Encrypt

set -e  # Exit on any error

# Configuration
DOMAIN="your-domain.com"
EMAIL="admin@your-domain.com"
APP_NAME="bayan-smart-village"

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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "This script must be run as root or with sudo"
    exit 1
fi

print_status "🔒 Setting up SSL certificate for $DOMAIN..."

# Install Certbot
print_status "Installing Certbot..."
apt update
apt install -y snapd
snap install core; snap refresh core
snap install --classic certbot

# Create symlink
ln -sf /snap/bin/certbot /usr/bin/certbot

print_success "Certbot installed"

# Stop Nginx temporarily
print_status "Stopping Nginx temporarily..."
systemctl stop nginx

# Obtain SSL certificate
print_status "Obtaining SSL certificate..."
certbot certonly --standalone \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN \
    -d www.$DOMAIN \
    -d "*.kecamatanbayan.id"

# Update Nginx configuration with SSL
print_status "Updating Nginx configuration with SSL..."
cat > /etc/nginx/sites-available/$APP_NAME << EOF
# HTTP redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN *.kecamatanbayan.id;
    return 301 https://\$server_name\$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $DOMAIN www.$DOMAIN *.kecamatanbayan.id;
    root /var/www/$APP_NAME/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;

    # SSL Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    index index.php;
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        log_not_found off;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;

        # Security headers for PHP
        fastcgi_hide_header X-Powered-By;

        # Increase timeouts for large requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Additional security
    location ~ /\.(htaccess|htpasswd|ini|log|sh|sql|conf)$ {
        deny all;
    }
}
EOF

# Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t

# Start Nginx
print_status "Starting Nginx..."
systemctl start nginx
systemctl reload nginx

# Set up automatic renewal
print_status "Setting up automatic SSL renewal..."
cat > /etc/cron.d/certbot << EOF
# Renew SSL certificates twice daily
0 12 * * * root /usr/bin/certbot renew --quiet --post-hook "systemctl reload nginx"
0 0 * * * root /usr/bin/certbot renew --quiet --post-hook "systemctl reload nginx"
EOF

# Test renewal process
print_status "Testing SSL renewal process..."
certbot renew --dry-run

print_success "🎉 SSL certificate setup completed successfully!"
echo ""
print_success "✅ Your site is now secured with HTTPS"
echo "🔗 HTTPS URL: https://$DOMAIN"
echo "📖 Admin panel: https://$DOMAIN/admin"
echo ""
print_warning "📝 Important notes:"
echo "1. SSL certificate will auto-renew every 60 days"
echo "2. Make sure to update APP_URL in .env to use https://"
echo "3. Test all subdomain links (*.kecamatanbayan.id)"
echo "4. Consider setting up HSTS preload for maximum security"