# Smart Village Deployment Scripts

This directory contains automated deployment scripts for the Smart Village Laravel application. These scripts will set up a complete production environment on a fresh Ubuntu/Debian VPS.

## Prerequisites

- Fresh Ubuntu 20.04 LTS or Ubuntu 22.04 LTS VPS
- Domain name pointing to your VPS IP address
- Non-root user with sudo privileges
- At least 2GB RAM and 20GB disk space

## Quick Start

1. **Clone the repository to your VPS:**
   ```bash
   git clone <your-repo-url> /tmp/smartvillage-setup
   cd /tmp/smartvillage-setup/deploy
   ```

2. **Make scripts executable:**
   ```bash
   chmod +x *.sh
   ```

3. **Run deployment scripts in order:**
   ```bash
   # Step 1: System setup
   ./01-system-setup.sh
   
   # Reboot system (required)
   sudo reboot
   
   # Step 2: Nginx configuration
   ./02-nginx-setup.sh your-domain.com admin@your-domain.com
   
   # Step 3: Laravel application setup
   ./03-laravel-setup.sh https://github.com/username/bayan-smart-village.git main
   
   # Step 4: SSL certificates
   ./04-ssl-setup.sh your-domain.com admin@your-domain.com
   ```

## Deployment Scripts Overview

### 1. System Setup (`01-system-setup.sh`)

Sets up the basic system requirements and installs all necessary software.

**What it installs:**
- PHP 8.3 and all required extensions
- Node.js 20 LTS
- Composer
- Redis
- Supervisor
- Essential system packages

**What it configures:**
- UFW firewall (SSH, HTTP, HTTPS)
- Fail2ban security
- PHP-FPM optimization
- System monitoring
- Log rotation
- Automatic health checks

**Usage:**
```bash
./01-system-setup.sh
```

**Important:** Reboot the system after this step before proceeding.

### 2. Nginx Configuration (`02-nginx-setup.sh`)

Configures Nginx web server with optimized settings for Laravel and multi-tenant architecture.

**Features:**
- SSL-ready configuration
- Multi-domain support (main domain + subdomains)
- Security headers
- Rate limiting
- Static file optimization
- PHP-FPM integration

**Usage:**
```bash
./02-nginx-setup.sh <domain> [ssl-email]
```

**Example:**
```bash
./02-nginx-setup.sh kecamatanbayan.id admin@kecamatanbayan.id
```

### 3. Laravel Application Setup (`03-laravel-setup.sh`)

Clones and configures the Laravel application with all production optimizations.

**What it does:**
- Clones repository from Git
- Installs Composer dependencies
- Builds frontend assets
- Sets up database (SQLite)
- Configures environment variables
- Sets up Laravel scheduler
- Configures queue workers
- Creates backup system
- Runs migrations and seeders

**Usage:**
```bash
./03-laravel-setup.sh <git-repo-url> [branch]
```

**Example:**
```bash
./03-laravel-setup.sh https://github.com/username/bayan-smart-village.git main
```

### 4. SSL Certificate Setup (`04-ssl-setup.sh`)

Configures SSL certificates using Let's Encrypt Certbot with automatic renewal.

**Features:**
- Automatic SSL certificate generation
- HTTPS redirect configuration
- Certificate auto-renewal
- SSL security headers
- Certificate monitoring

**Usage:**
```bash
./04-ssl-setup.sh <domain> <ssl-email>
```

**Example:**
```bash
./04-ssl-setup.sh kecamatanbayan.id admin@kecamatanbayan.id
```

## Post-Deployment

After running all scripts, your Smart Village application will be live at:
- Main domain: `https://your-domain.com`
- Admin panel: `https://your-domain.com/admin`
- Village subdomains: `https://village-name.your-domain.com`

### Management Commands

The deployment scripts create several management commands:

#### Application Maintenance
```bash
# Put application in maintenance mode
smartvillage-maintenance.sh down

# Bring application back online
smartvillage-maintenance.sh up

# Clear application cache
smartvillage-maintenance.sh cache-clear

# Optimize application
smartvillage-maintenance.sh optimize

# Restart queue workers
smartvillage-maintenance.sh queue-restart

# View application logs
smartvillage-maintenance.sh logs
```

#### Application Updates
```bash
# Deploy updates from Git repository
smartvillage-deploy.sh
```

#### Backup System
```bash
# Manual backup
smartvillage-backup.sh
```
*Automatic backups run daily at 2 AM*

#### SSL Management
```bash
# Manual SSL renewal
smartvillage-ssl-renew.sh

# Check SSL certificate expiry
smartvillage-ssl-check.sh
```

#### System Health
```bash
# Check system health
smartvillage-health
```
*Automatic health checks run every 15 minutes*

## Directory Structure

After deployment, your application will be organized as follows:

```
/var/www/smartvillage/          # Laravel application root
├── app/                        # Application code
├── public/                     # Web server document root
├── storage/                    # Application storage
│   ├── app/public/            # Public uploaded files
│   └── logs/                  # Application logs
├── database/
│   └── database.sqlite        # SQLite database
└── .env                       # Environment configuration

/var/log/smartvillage/          # Application logs
├── health.log                 # System health checks
├── ssl-renewal.log           # SSL certificate renewal
├── ssl-check.log             # SSL certificate monitoring
└── backup.log                # Backup operations

/etc/smartvillage/             # Configuration files
├── domain.conf               # Domain configuration
└── env.template             # Environment template

/var/backups/smartvillage/     # Backup storage
├── database_*.sqlite         # Database backups
├── storage_*.tar.gz         # File backups
└── env_*.txt               # Environment backups
```

## Configuration

### Environment Variables

Key environment variables in `/var/www/smartvillage/.env`:

```env
APP_NAME="Smart Village"
APP_ENV=production
APP_URL=https://your-domain.com
APP_DEBUG=false

DB_CONNECTION=sqlite

SMARTVILLAGE_MAIN_DOMAIN=your-domain.com
SMARTVILLAGE_ALLOW_REGISTRATION=false
SMARTVILLAGE_DEFAULT_TIMEZONE=Asia/Jakarta

CACHE_STORE=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### Nginx Configuration

Main configuration files:
- `/etc/nginx/sites-available/smartvillage-main` - Main domain
- `/etc/nginx/sites-available/smartvillage-subdomains` - Subdomains

### PHP-FPM

Dedicated PHP-FPM pool: `/etc/php/8.3/fpm/pool.d/smartvillage.conf`

### Supervisor

Queue worker configuration: `/etc/supervisor/conf.d/smartvillage-worker.conf`

## Monitoring and Logs

### Log Files
```bash
# Application logs
tail -f /var/www/smartvillage/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/smartvillage/php-fpm.log

# System health logs
tail -f /var/log/smartvillage/health.log
```

### Service Status
```bash
# Check all services
sudo systemctl status nginx php8.3-fpm redis-server supervisor

# Check queue workers
sudo supervisorctl status smartvillage-worker:*
```

## Troubleshooting

### Common Issues

1. **Permission errors:**
   ```bash
   sudo chown -R smartvillage:www-data /var/www/smartvillage/storage
   sudo chmod -R 775 /var/www/smartvillage/storage
   ```

2. **SSL certificate issues:**
   ```bash
   # Check certificate status
   sudo certbot certificates
   
   # Manual renewal
   sudo smartvillage-ssl-renew.sh
   ```

3. **Queue not processing:**
   ```bash
   # Restart queue workers
   sudo supervisorctl restart smartvillage-worker:*
   ```

4. **Application not loading:**
   ```bash
   # Check Nginx configuration
   sudo nginx -t
   
   # Check PHP-FPM status
   sudo systemctl status php8.3-fpm
   
   # Clear application cache
   smartvillage-maintenance.sh cache-clear
   ```

### Getting Help

1. Check application logs: `smartvillage-maintenance.sh logs`
2. Check system health: `smartvillage-health`
3. Review service status: `sudo systemctl status nginx php8.3-fpm`
4. Check disk space: `df -h`
5. Check memory usage: `free -h`

## Security Considerations

The deployment scripts implement several security measures:

- **Firewall:** UFW configured to allow only SSH, HTTP, and HTTPS
- **Fail2ban:** Automatic IP blocking for suspicious activity
- **SSL:** HTTPS-only with security headers
- **PHP Security:** Hidden PHP version, secure session handling
- **File Permissions:** Proper ownership and permissions
- **User Isolation:** Dedicated system user for the application

## Maintenance

### Regular Tasks

1. **Monitor SSL certificates:** Automatic checks every Monday
2. **Update system packages:** Monthly `sudo apt update && sudo apt upgrade`
3. **Review logs:** Weekly log review for errors or issues
4. **Test backups:** Monthly backup restoration test
5. **Security updates:** Apply security patches promptly

### Update Process

To update the application:

1. Test changes in development environment
2. Backup current application: `smartvillage-backup.sh`
3. Deploy updates: `smartvillage-deploy.sh`
4. Monitor logs for any issues

## Support

For additional support:
1. Check the application documentation in `/var/www/smartvillage/CLAUDE.md`
2. Review Laravel documentation at https://laravel.com/docs
3. Check system logs for detailed error information

---

## Script Execution Log

When running the deployment scripts, they will create detailed logs. Here's what to expect:

### Expected Timeline
- **System Setup:** 10-15 minutes
- **Nginx Configuration:** 2-3 minutes
- **Laravel Setup:** 5-10 minutes (depending on internet speed)
- **SSL Setup:** 3-5 minutes
- **Total:** 20-35 minutes

### Success Indicators
✅ All services running and enabled  
✅ Application accessible via HTTPS  
✅ SSL certificates valid  
✅ Database migrations completed  
✅ Queue workers active  
✅ Scheduled tasks configured  

Your Smart Village application is now ready for production use!