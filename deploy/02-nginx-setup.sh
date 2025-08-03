#!/bin/bash

# Smart Village Laravel Application - Nginx Setup Script
# This script configures Nginx for the Smart Village multi-tenant application

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

# Get domain from user input
if [ -z "$1" ]; then
    echo "Usage: $0 <main-domain> [email-for-ssl]"
    echo "Example: $0 kecamatanbayan.id admin@kecamatanbayan.id"
    exit 1
fi

MAIN_DOMAIN="$1"
SSL_EMAIL="${2:-admin@${MAIN_DOMAIN}}"

log "Configuring Nginx for Smart Village..."
info "Main domain: $MAIN_DOMAIN"
info "SSL email: $SSL_EMAIL"

# Install Nginx if not already installed
if ! command -v nginx &> /dev/null; then
    log "Installing Nginx..."
    sudo apt update
    sudo apt install -y nginx
else
    log "Nginx is already installed"
fi

# Backup existing nginx configuration
log "Backing up existing Nginx configuration..."
sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup

# Create optimized nginx.conf
log "Creating optimized Nginx configuration..."
sudo tee /etc/nginx/nginx.conf > /dev/null << 'EOF'
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    # Basic Settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # File upload settings
    client_max_body_size 100M;
    client_body_buffer_size 128k;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    
    # Timeout settings
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;
    
    # MIME types
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # Logging
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log warn;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json
        image/svg+xml;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;
    
    # Include site configurations
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
EOF

# Remove default site
log "Removing default Nginx site..."
sudo rm -f /etc/nginx/sites-enabled/default

# Create Smart Village main site configuration
log "Creating Smart Village main site configuration..."
sudo tee "/etc/nginx/sites-available/smartvillage-main" > /dev/null << EOF
# Smart Village Main Domain Configuration
server {
    listen 80;
    server_name ${MAIN_DOMAIN};
    
    # Redirect to HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ${MAIN_DOMAIN};
    
    root /var/www/smartvillage/public;
    index index.php;
    
    # SSL Configuration (will be updated by certbot)
    # ssl_certificate /path/to/cert;
    # ssl_certificate_key /path/to/key;
    
    # SSL Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Laravel public path
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Static file optimization
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";
        access_log off;
    }
    
    # Security: Block access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(storage|bootstrap/cache|config|database|resources|routes|tests|vendor) {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Rate limiting for admin routes
    location ~* ^/(admin|filament) {
        limit_req zone=api burst=20 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # API rate limiting
    location ~* ^/api {
        limit_req zone=api burst=20 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # Error pages
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    
    location = /50x.html {
        root /var/www/html;
    }
}
EOF

# Create Smart Village subdomain (wildcard) configuration
log "Creating Smart Village subdomain configuration..."
sudo tee "/etc/nginx/sites-available/smartvillage-subdomains" > /dev/null << EOF
# Smart Village Subdomain Configuration (Wildcard)
server {
    listen 80;
    server_name *.${MAIN_DOMAIN};
    
    # Redirect to HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name *.${MAIN_DOMAIN};
    
    root /var/www/smartvillage/public;
    index index.php;
    
    # SSL Configuration (will be updated by certbot)
    # ssl_certificate /path/to/cert;
    # ssl_certificate_key /path/to/key;
    
    # SSL Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Laravel public path
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Pass subdomain to Laravel
        fastcgi_param HTTP_HOST \$host;
        fastcgi_param HTTP_X_FORWARDED_HOST \$host;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Static file optimization
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";
        access_log off;
    }
    
    # Security: Block access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(storage|bootstrap/cache|config|database|resources|routes|tests|vendor) {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # API rate limiting
    location ~* ^/api {
        limit_req zone=api burst=20 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # Error pages
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    
    location = /50x.html {
        root /var/www/html;
    }
}
EOF

# Enable sites
log "Enabling Smart Village sites..."
sudo ln -sf /etc/nginx/sites-available/smartvillage-main /etc/nginx/sites-enabled/
sudo ln -sf /etc/nginx/sites-available/smartvillage-subdomains /etc/nginx/sites-enabled/

# Test Nginx configuration
log "Testing Nginx configuration..."
if sudo nginx -t; then
    log "Nginx configuration is valid"
else
    error "Nginx configuration test failed"
    exit 1
fi

# Create PHP-FPM pool configuration for smartvillage
log "Creating PHP-FPM pool configuration..."
sudo tee /etc/php/8.3/fpm/pool.d/smartvillage.conf > /dev/null << 'EOF'
[smartvillage]
user = smartvillage
group = www-data
listen = /run/php/php8.3-fpm-smartvillage.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

php_admin_value[error_log] = /var/log/smartvillage/php-fpm.log
php_admin_flag[log_errors] = on

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
EOF

# Update Nginx configuration to use the custom PHP-FPM pool
log "Updating Nginx configuration to use custom PHP-FPM pool..."
sudo sed -i 's|fastcgi_pass unix:/run/php/php8.3-fpm.sock;|fastcgi_pass unix:/run/php/php8.3-fpm-smartvillage.sock;|g' /etc/nginx/sites-available/smartvillage-main
sudo sed -i 's|fastcgi_pass unix:/run/php/php8.3-fpm.sock;|fastcgi_pass unix:/run/php/php8.3-fpm-smartvillage.sock;|g' /etc/nginx/sites-available/smartvillage-subdomains

# Enable and start services
log "Starting and enabling services..."
sudo systemctl enable nginx
sudo systemctl enable php8.3-fpm
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

# Test services
log "Testing services..."
if sudo systemctl is-active --quiet nginx; then
    log "Nginx is running"
else
    error "Nginx failed to start"
    exit 1
fi

if sudo systemctl is-active --quiet php8.3-fpm; then
    log "PHP-FPM is running"
else
    error "PHP-FPM failed to start"
    exit 1
fi

log "Nginx configuration completed successfully!"
info "==============================================="
info "NGINX SETUP SUMMARY"
info "==============================================="
info "✓ Nginx installed and configured"
info "✓ PHP-FPM 8.3 pool configured"
info "✓ SSL-ready configurations created"
info "✓ Security headers configured"
info "✓ Rate limiting configured"
info "✓ Static file optimization enabled"
info ""
info "Domain configurations created:"
info "- Main domain: ${MAIN_DOMAIN}"
info "- Subdomains: *.${MAIN_DOMAIN}"
info ""
info "Next steps:"
info "1. Run 03-laravel-setup.sh to deploy the application"
info "2. Run 04-ssl-setup.sh to configure SSL certificates"
info ""
warn "SSL certificates not yet configured. The site will be accessible via HTTP only until SSL is set up."

# Store domain for other scripts
echo "MAIN_DOMAIN=${MAIN_DOMAIN}" | sudo tee /etc/smartvillage/domain.conf > /dev/null
echo "SSL_EMAIL=${SSL_EMAIL}" | sudo tee -a /etc/smartvillage/domain.conf > /dev/null

info "Domain configuration saved to /etc/smartvillage/domain.conf"