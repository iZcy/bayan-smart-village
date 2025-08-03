#!/bin/bash

# Smart Village Laravel Application - SSL Certificate Setup Script
# This script configures SSL certificates using Certbot with automatic renewal

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

# Load domain configuration
if [ -f /tmp/smartvillage-domain.conf ]; then
    source /tmp/smartvillage-domain.conf
else
    if [ -z "$1" ] || [ -z "$2" ]; then
        echo "Usage: $0 <main-domain> <email-for-ssl>"
        echo "Example: $0 kecamatanbayan.id admin@kecamatanbayan.id"
        exit 1
    fi
    MAIN_DOMAIN="$1"
    SSL_EMAIL="$2"
fi

echo "🔒 Setting up SSL certificates for Smart Village..."
echo "Main domain: $MAIN_DOMAIN"
echo "SSL email: $SSL_EMAIL"

# Check if Nginx is running and accessible
if ! systemctl is-active --quiet nginx; then
    log_error "Nginx is not running. Please start Nginx first."
    exit 1
fi

# Create temporary Nginx configuration without SSL for verification
log_info "Creating temporary configuration for domain verification..."

# Backup current configurations
cp /etc/nginx/sites-available/smartvillage-main /etc/nginx/sites-available/smartvillage-main.backup
cp /etc/nginx/sites-available/smartvillage-subdomains /etc/nginx/sites-available/smartvillage-subdomains.backup

# Create temporary HTTP-only configuration for main domain
cat > "/etc/nginx/sites-available/smartvillage-main-temp" << EOF
server {
    listen 80;
    server_name ${MAIN_DOMAIN};
    
    root /var/www/smartvillage/public;
    index index.php;
    
    # Certbot challenge location
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }
    
    # Laravel routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm-smartvillage.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

# Enable temporary configuration
ln -sf /etc/nginx/sites-available/smartvillage-main-temp /etc/nginx/sites-enabled/smartvillage-main
rm -f /etc/nginx/sites-enabled/smartvillage-subdomains

# Test and reload Nginx
nginx -t && systemctl reload nginx

# Create webroot directory for challenges
mkdir -p /var/www/html/.well-known/acme-challenge/
chown -R www-data:www-data /var/www/html/.well-known/

# Obtain SSL certificate for main domain
log_info "Obtaining SSL certificate for main domain: $MAIN_DOMAIN"
if certbot certonly \
    --webroot \
    --webroot-path=/var/www/html \
    --email "$SSL_EMAIL" \
    --agree-tos \
    --no-eff-email \
    --domains "$MAIN_DOMAIN" \
    --non-interactive; then
    log_success "SSL certificate obtained for $MAIN_DOMAIN"
else
    log_error "Failed to obtain SSL certificate for $MAIN_DOMAIN"
    exit 1
fi

# Obtain wildcard SSL certificate for subdomains
log_info "Obtaining wildcard SSL certificate for subdomains: *.$MAIN_DOMAIN"
log_warning "This requires DNS-01 challenge. You'll need to add a DNS TXT record."

# For wildcard certificates, we need to use DNS challenge
if certbot certonly \
    --manual \
    --preferred-challenges dns \
    --email "$SSL_EMAIL" \
    --agree-tos \
    --no-eff-email \
    --domains "*.$MAIN_DOMAIN" \
    --non-interactive; then
    log_success "Wildcard SSL certificate obtained for *.$MAIN_DOMAIN"
    WILDCARD_SUCCESS=true
else
    log_warning "Wildcard SSL certificate failed. Will create individual certificates as needed."
    WILDCARD_SUCCESS=false
fi

# Create final SSL-enabled Nginx configurations
log_info "Creating final SSL-enabled Nginx configurations..."

# Main domain with SSL
cat > "/etc/nginx/sites-available/smartvillage-main" << EOF
# Smart Village Main Domain Configuration with SSL
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
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/${MAIN_DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${MAIN_DOMAIN}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
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
        fastcgi_pass unix:/run/php/php8.3-fpm-smartvillage.sock;
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

# Subdomain configuration with SSL
if [ "$WILDCARD_SUCCESS" = true ]; then
    SSL_CERT_PATH="/etc/letsencrypt/live/${MAIN_DOMAIN}"
    SSL_CERT_FILE="$SSL_CERT_PATH/fullchain.pem"
    SSL_KEY_FILE="$SSL_CERT_PATH/privkey.pem"
    
    # Check if wildcard certificate files exist
    if [ ! -f "$SSL_CERT_FILE" ]; then
        SSL_CERT_PATH="/etc/letsencrypt/live/${MAIN_DOMAIN}-0001"
        SSL_CERT_FILE="$SSL_CERT_PATH/fullchain.pem"
        SSL_KEY_FILE="$SSL_CERT_PATH/privkey.pem"
    fi
else
    SSL_CERT_PATH="/etc/letsencrypt/live/${MAIN_DOMAIN}"
    SSL_CERT_FILE="$SSL_CERT_PATH/fullchain.pem"
    SSL_KEY_FILE="$SSL_CERT_PATH/privkey.pem"
fi

cat > "/etc/nginx/sites-available/smartvillage-subdomains" << EOF
# Smart Village Subdomain Configuration with SSL
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
    
    # SSL Configuration
    ssl_certificate ${SSL_CERT_FILE};
    ssl_certificate_key ${SSL_KEY_FILE};
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
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
        fastcgi_pass unix:/run/php/php8.3-fpm-smartvillage.sock;
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

# Remove temporary configuration
rm -f /etc/nginx/sites-available/smartvillage-main-temp
rm -f /etc/nginx/sites-enabled/smartvillage-main-temp

# Enable final configurations
ln -sf /etc/nginx/sites-available/smartvillage-main /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/smartvillage-subdomains /etc/nginx/sites-enabled/

# Test Nginx configuration
log_info "Testing final Nginx configuration..."
if /usr/sbin/nginx -t; then
    log_success "Nginx configuration is valid"
    systemctl reload nginx
else
    log_error "Nginx configuration test failed"
    # Restore backup configurations
    cp /etc/nginx/sites-available/smartvillage-main.backup /etc/nginx/sites-available/smartvillage-main
    cp /etc/nginx/sites-available/smartvillage-subdomains.backup /etc/nginx/sites-available/smartvillage-subdomains
    systemctl reload nginx
    exit 1
fi

# Setup automatic renewal
log_info "Setting up automatic SSL certificate renewal..."

# Create renewal script
cat > /usr/local/bin/smartvillage-ssl-renew.sh << 'EOF'
#!/bin/bash
# Smart Village SSL Renewal Script

set -e

echo "$(date): Starting SSL certificate renewal process..."

# Renew certificates
if certbot renew --quiet --nginx; then
    echo "$(date): SSL certificates renewed successfully"
    
    # Test nginx configuration
    if /usr/sbin/nginx -t; then
        systemctl reload nginx
        echo "$(date): Nginx reloaded successfully"
    else
        echo "$(date): ERROR - Nginx configuration test failed after renewal"
        exit 1
    fi
else
    echo "$(date): SSL certificate renewal failed or not needed"
fi

echo "$(date): SSL renewal process completed"
EOF

chmod +x /usr/local/bin/smartvillage-ssl-renew.sh

# Add to crontab for automatic renewal (twice daily)
(crontab -l 2>/dev/null; echo "0 2,14 * * * /usr/local/bin/smartvillage-ssl-renew.sh >> /var/log/smartvillage/ssl-renewal.log 2>&1") | crontab -

# Create SSL monitoring script
cat > /usr/local/bin/smartvillage-ssl-check.sh << EOF
#!/bin/bash
# Smart Village SSL Certificate Monitoring Script

DOMAIN="$MAIN_DOMAIN"
ALERT_DAYS=30

check_ssl_expiry() {
    local domain=\$1
    local expiry_date=\$(echo | openssl s_client -servername \$domain -connect \$domain:443 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d= -f2)
    local expiry_epoch=\$(date -d "\$expiry_date" +%s)
    local current_epoch=\$(date +%s)
    local days_until_expiry=\$(( (expiry_epoch - current_epoch) / 86400 ))
    
    echo "SSL certificate for \$domain expires in \$days_until_expiry days (\$expiry_date)"
    
    if [ \$days_until_expiry -lt \$ALERT_DAYS ]; then
        echo "WARNING: SSL certificate for \$domain expires in \$days_until_expiry days!"
        return 1
    fi
    
    return 0
}

echo "Checking SSL certificates..."
check_ssl_expiry "$MAIN_DOMAIN"

# Check a few common subdomains if they exist
for subdomain in www admin api; do
    if nslookup "\$subdomain.$MAIN_DOMAIN" >/dev/null 2>&1; then
        check_ssl_expiry "\$subdomain.$MAIN_DOMAIN"
    fi
done

echo "SSL certificate check completed."
EOF

chmod +x /usr/local/bin/smartvillage-ssl-check.sh

# Add weekly SSL check to crontab
(crontab -l 2>/dev/null; echo "0 8 * * 1 /usr/local/bin/smartvillage-ssl-check.sh >> /var/log/smartvillage/ssl-check.log 2>&1") | crontab -

# Create directory for SSL logs
mkdir -p /var/log/smartvillage
touch /var/log/smartvillage/ssl-renewal.log
touch /var/log/smartvillage/ssl-check.log
chown smartvillage:www-data /var/log/smartvillage/*.log

log_success "SSL setup completed successfully!"
log_info "SSL certificates configured for:"
log_info "- Main domain: $MAIN_DOMAIN"
if [ "$WILDCARD_SUCCESS" = true ]; then
    log_info "- Wildcard: *.$MAIN_DOMAIN"
else
    log_warning "- Wildcard certificate failed. Individual certificates will be needed for subdomains."
fi

log_info ""
log_info "Automatic renewal configured:"
log_info "- Renewal check: Twice daily (2 AM, 2 PM)"
log_info "- Certificate monitoring: Weekly (Monday 8 AM)"
log_info "- Logs: /var/log/smartvillage/ssl-*.log"

log_info ""
log_info "Available commands:"
log_info "- smartvillage-ssl-renew.sh: Manual renewal"
log_info "- smartvillage-ssl-check.sh: Check certificate expiry"

echo ""
echo "🔒 SSL Configuration Summary:"
echo "- Main domain HTTPS: https://$MAIN_DOMAIN"
echo "- Certificate files: /etc/letsencrypt/live/$MAIN_DOMAIN/"
echo "- Nginx SSL config: /etc/nginx/sites-available/smartvillage-*"