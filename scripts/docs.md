# Bayan Smart Village - CI/CD Deployment Guide

This guide provides complete setup instructions for deploying the Bayan Smart Village application with automated CI/CD pipelines.

## 📋 Prerequisites

### Local Development

-   PHP 8.2+
-   Composer
-   Node.js 18+
-   Git

### VPS Requirements

-   Ubuntu 20.04+ or similar Linux distribution
-   Minimum 2GB RAM, 2 CPU cores
-   20GB+ storage space
-   Root or sudo access

## 🚀 Quick Setup

### 1. Local Development Setup

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/bayan-smart-village.git
cd bayan-smart-village

# Make setup script executable
chmod +x scripts/setup-local.sh

# Run setup
./scripts/setup-local.sh
```

### 2. VPS Initial Setup

```bash
# Upload deployment script to your VPS
scp scripts/deploy-vps.sh user@your-vps-ip:/tmp/

# SSH to your VPS and run deployment
ssh user@your-vps-ip
sudo chmod +x /tmp/deploy-vps.sh
sudo /tmp/deploy-vps.sh
```

### 3. SSL Certificate Setup

```bash
# On your VPS, run SSL setup
sudo chmod +x /usr/local/bin/ssl-setup.sh
sudo /usr/local/bin/ssl-setup.sh
```

## ⚙️ GitHub Actions CI/CD Setup

### 1. Repository Secrets

Add these secrets to your GitHub repository (`Settings > Secrets and variables > Actions`):

```
VPS_HOST=your-vps-ip-address
VPS_USERNAME=root
VPS_SSH_KEY=your-private-ssh-key
VPS_PORT=22
```

### 2. Generate SSH Key for Deployment

```bash
# On your local machine
ssh-keygen -t rsa -b 4096 -f ~/.ssh/bayan_deploy_key

# Copy public key to VPS
ssh-copy-id -i ~/.ssh/bayan_deploy_key.pub user@your-vps-ip

# Add private key content to GitHub secrets as VPS_SSH_KEY
cat ~/.ssh/bayan_deploy_key
```

### 3. Workflow Files

The GitHub Actions workflow (`.github/workflows/ci-cd.yml`) automatically:

-   ✅ Runs tests on every push/PR
-   🔒 Performs security scans
-   📊 Checks code quality
-   🚀 Deploys to VPS on main branch pushes

## 📁 Project Structure

```
bayan-smart-village/
├── .github/workflows/
│ └── ci-cd.yml  # GitHub Actions workflow
├── scripts/
│ ├── setup-local.sh # Local development setup
│ ├── deploy-vps.sh  # VPS deployment script
│ ├── ssl-setup.sh   # SSL certificate setup
│ ├── backup.sh  # Automated backup script
│ ├── restore.sh   # Restore from backup
│ └── health-check.sh  # System monitoring
├── app/   # Laravel application
├── database/  # Database files and migrations
└── public/    # Web server document root
```

## 🔧 Configuration

### Environment Variables

Update these variables in your VPS `.env` file:

```env
APP_NAME="Bayan Smart Village"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/bayan-smart-village/database/database.sqlite

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

### Domain Configuration

The application supports subdomain routing for external links:

-   Main domain: `your-domain.com`
-   Subdomain links: `{place-slug}.kecamatanbayan.id/l/{link-slug}`

## 🔄 Automated Processes

### Backups

Automated daily backups are configured via cron:

```bash
# View backup schedule
sudo crontab -l

# Manual backup
sudo /usr/local/bin/backup_bayan.sh

# Restore from backup
sudo /usr/local/bin/restore_bayan.sh backup_name
```

### Health Monitoring

System health checks run every 5 minutes:

```bash
# View monitoring logs
sudo tail -f /var/log/bayan-smart-village_health.log

# Manual health check
sudo /usr/local/bin/health_check_bayan.sh
```

### Queue Workers

Background job processing via Supervisor:

```bash
# Check queue worker status
sudo supervisorctl status bayan-smart-village-worker:*

# Restart queue workers
sudo supervisorctl restart bayan-smart-village-worker:*
```

## 🔍 Troubleshooting

### Common Issues

1. **Permission Errors**

```bash
sudo chown -R bayan-smart-village:bayan-smart-village /var/www/bayan-smart-village
sudo chmod -R 755 /var/www/bayan-smart-village/storage
```

2. **Database Connection Issues**

```bash
# Check database file permissions
ls -la /var/www/bayan-smart-village/database/

# Create database if missing
sudo -u bayan-smart-village touch /var/www/bayan-smart-village/database/database.sqlite
```

3. **SSL Certificate Issues**

```bash
# Check certificate status
sudo certbot certificates

# Renew certificates manually
sudo certbot renew --dry-run
```

4. **Queue Worker Not Processing Jobs**

```bash
# Check worker logs
sudo tail -f /var/www/bayan-smart-village/storage/logs/worker.log

# Restart workers
sudo supervisorctl restart bayan-smart-village-worker:*
```

5. **High Memory Usage**

```bash
# Clear application cache
cd /var/www/bayan-smart-village
sudo -u bayan-smart-village php artisan cache:clear
sudo -u bayan-smart-village php artisan config:cache
```

### Log Files

Important log locations:

-   Application logs: `/var/www/bayan-smart-village/storage/logs/laravel.log`
-   Nginx logs: `/var/log/nginx/access.log` & `/var/log/nginx/error.log`
-   PHP-FPM logs: `/var/log/php8.2-fpm.log`
-   System logs: `/var/log/syslog`
-   Health monitoring: `/var/log/bayan-smart-village_health.log`

## 🔐 Security Considerations

### Firewall Configuration

```bash
# Check firewall status
sudo ufw status

# Allow specific IPs only (recommended)
sudo ufw allow from YOUR_IP_ADDRESS to any port 22
sudo ufw deny 22
```

### File Permissions

```bash
# Secure file permissions
sudo find /var/www/bayan-smart-village -type f -exec chmod 644 {} \;
sudo find /var/www/bayan-smart-village -type d -exec chmod 755 {} \;
sudo chmod -R 755 /var/www/bayan-smart-village/storage
sudo chmod -R 755 /var/www/bayan-smart-village/bootstrap/cache
```

### Environment File Security

```bash
# Secure .env file
sudo chmod 600 /var/www/bayan-smart-village/.env
sudo chown bayan-smart-village:bayan-smart-village /var/www/bayan-smart-village/.env
```

## 📊 Monitoring & Maintenance

### Performance Monitoring

1. **Application Performance**

-   Response time monitoring via health checks
-   Queue job processing monitoring
-   Database query optimization

2. **System Resources**

-   CPU and memory usage alerts
-   Disk space monitoring
-   Network traffic analysis

3. **Error Tracking**

-   Application error logging
-   Real-time error notifications
-   Error rate trend analysis

### Maintenance Tasks

#### Daily

-   ✅ Automated backups
-   ✅ Health checks every 5 minutes
-   ✅ Log rotation
-   ✅ SSL certificate monitoring

#### Weekly

-   🔄 Security updates
-   📊 Performance review
-   🧹 Database optimization
-   📈 Analytics review

#### Monthly

-   🔒 Security audit
-   💾 Backup verification
-   📋 Capacity planning
-   🔄 Dependency updates

## 🚀 Advanced Configuration

### Load Balancing (Multiple Servers)

For high-traffic scenarios, configure load balancing:

```nginx
upstream bayan_backend {
  server 10.0.1.10:80;
  server 10.0.1.11:80;
  server 10.0.1.12:80;
}

server {
  listen 443 ssl http2;
  server_name your-domain.com;

  location / {
  proxy_pass http://bayan_backend;
  proxy_set_header Host $host;
  proxy_set_header X-Real-IP $remote_addr;
  }
}
```

### Redis Caching

Enable Redis for better performance:

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Database Optimization

For MySQL in production:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bayan_smart_village
DB_USERNAME=bayan_user
DB_PASSWORD=secure_password
```

### CDN Integration

Configure AWS CloudFront or similar CDN:

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=bayan-smart-village-assets
AWS_URL=https://cdn.your-domain.com
```

## 📞 Support & Maintenance Contacts

### Emergency Procedures

1. **Application Down**

```bash
# Check all services
sudo systemctl status nginx php8.2-fpm
sudo supervisorctl status

# Restart services
sudo systemctl restart nginx php8.2-fpm
sudo supervisorctl restart all
```

2. **Database Issues**

```bash
# Restore from latest backup
sudo /usr/local/bin/restore_bayan.sh $(ls -t /var/backups/bayan-smart-village/bayan-smart-village_backup_* | head -1 | xargs basename | cut -d'_' -f1-4)
```

3. **SSL Certificate Expired**

```bash
# Force renewal
sudo certbot renew --force-renewal
sudo systemctl reload nginx
```

### Maintenance Windows

Recommended maintenance schedule:

-   **Security Updates**: Every Tuesday 2:00 AM UTC
-   **Application Updates**: First Sunday of month 3:00 AM UTC
-   **Database Maintenance**: Last Sunday of month 4:00 AM UTC

## 📈 Scaling Considerations

### Vertical Scaling (Single Server)

-   Upgrade to 4GB+ RAM
-   Add SSD storage
-   Optimize PHP-FPM workers

### Horizontal Scaling (Multiple Servers)

-   Load balancer setup
-   Shared storage (NFS/S3)
-   Database clustering
-   Redis cluster

### Performance Optimization

-   Enable OPcache
-   Configure Laravel caching
-   Optimize database queries
-   Implement CDN

## ✅ Deployment Checklist

Before going live:

-   [ ] Domain DNS configured
-   [ ] SSL certificates installed and auto-renewal setup
-   [ ] Firewall properly configured
-   [ ] Backups tested and verified
-   [ ] Monitoring alerts configured
-   [ ] Error tracking enabled
-   [ ] Performance baseline established
-   [ ] Security scan completed
-   [ ] Load testing performed
-   [ ] Documentation updated

## 📝 Change Log & Version Control

### Deployment Process

1. Development → Feature branch
2. Feature branch → Develop branch (staging)
3. Develop → Main branch (production)
4. Automated testing on all merges
5. Automated deployment to production from main

### Rollback Procedure

```bash
# Quick rollback using Git
cd /var/www/bayan-smart-village
sudo -u bayan-smart-village git log --oneline -10
sudo -u bayan-smart-village git reset --hard COMMIT_HASH

# Or restore from backup
sudo /usr/local/bin/restore_bayan.sh backup_name
```

## 📞 Getting Help

### Log Analysis

```bash
# Recent application errors
sudo tail -100 /var/www/bayan-smart-village/storage/logs/laravel.log | grep ERROR

# System performance
sudo htop
sudo iotop
sudo nethogs

# Disk usage
sudo du -sh /var/www/bayan-smart-village/*
sudo df -h
```

### Community Resources

-   Laravel Documentation: https://laravel.com/docs
-   Filament Documentation: https://filamentphp.com/docs
-   Server Management: https://forge.laravel.com

---

**Note**: This setup provides a production-ready deployment with automated CI/CD, monitoring, backups, and security configurations. Adjust the configuration based on your specific requirements and traffic expectations.
