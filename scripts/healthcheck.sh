#!/bin/bash

# Health Monitoring Script for Bayan Smart Village
# This script monitors application health and sends alerts

set -e  # Exit on any error

# Configuration
APP_NAME="bayan-smart-village"
APP_DIR="/var/www/$APP_NAME"
DOMAIN="your-domain.com"
NOTIFICATION_EMAIL="admin@your-domain.com"
SLACK_WEBHOOK=""  # Optional: Slack webhook URL
LOG_FILE="/var/log/${APP_NAME}_health.log"

# Thresholds
CPU_THRESHOLD=80
MEMORY_THRESHOLD=80
DISK_THRESHOLD=85
RESPONSE_TIME_THRESHOLD=5  # seconds

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

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

# Function to send notifications
send_notification() {
    local subject="$1"
    local message="$2"

    # Email notification
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" $NOTIFICATION_EMAIL
    fi

    # Slack notification
    if [ ! -z "$SLACK_WEBHOOK" ]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"$subject\n$message\"}" \
            $SLACK_WEBHOOK 2>/dev/null || true
    fi

    log_message "ALERT: $subject - $message"
}

print_status "🔍 Starting health check for $APP_NAME..."

# Initialize health status
HEALTH_STATUS="HEALTHY"
ISSUES=()

# Check if application is running
print_status "Checking application availability..."
if ! curl -f -s -m $RESPONSE_TIME_THRESHOLD http://localhost > /dev/null; then
    print_error "Application is not responding"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("Application not responding")
else
    print_success "Application is responding"
fi

# Check response time
print_status "Checking response time..."
RESPONSE_TIME=$(curl -o /dev/null -s -w "%{time_total}" http://localhost)
RESPONSE_TIME_MS=$(echo "$RESPONSE_TIME * 1000" | bc)
if (( $(echo "$RESPONSE_TIME > $RESPONSE_TIME_THRESHOLD" | bc -l) )); then
    print_warning "Slow response time: ${RESPONSE_TIME_MS}ms"
    HEALTH_STATUS="WARNING"
    ISSUES+=("Slow response time: ${RESPONSE_TIME_MS}ms")
else
    print_success "Response time: ${RESPONSE_TIME_MS}ms"
fi

# Check admin panel
print_status "Checking admin panel..."
if ! curl -f -s -m $RESPONSE_TIME_THRESHOLD http://localhost/admin > /dev/null; then
    print_error "Admin panel is not accessible"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("Admin panel not accessible")
else
    print_success "Admin panel is accessible"
fi

# Check database
print_status "Checking database..."
cd $APP_DIR
if ! sudo -u $APP_NAME php artisan migrate:status > /dev/null 2>&1; then
    print_error "Database connection failed"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("Database connection failed")
else
    print_success "Database is accessible"
fi

# Check queue worker
print_status "Checking queue worker..."
if ! supervisorctl status ${APP_NAME}-worker:* | grep RUNNING > /dev/null; then
    print_error "Queue worker is not running"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("Queue worker not running")
else
    print_success "Queue worker is running"
fi

# Check Nginx
print_status "Checking Nginx status..."
if ! systemctl is-active --quiet nginx; then
    print_error "Nginx is not running"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("Nginx not running")
else
    print_success "Nginx is running"
fi

# Check PHP-FPM
print_status "Checking PHP-FPM status..."
if ! systemctl is-active --quiet php8.2-fpm; then
    print_error "PHP-FPM is not running"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("PHP-FPM not running")
else
    print_success "PHP-FPM is running"
fi

# Check SSL certificate (if HTTPS is configured)
print_status "Checking SSL certificate..."
if openssl s_client -connect $DOMAIN:443 -servername $DOMAIN </dev/null 2>/dev/null | openssl x509 -noout -dates | grep "notAfter" | grep -q "$(date -d '+30 days' '+%b %d')"; then
    print_warning "SSL certificate expires within 30 days"
    HEALTH_STATUS="WARNING"
    ISSUES+=("SSL certificate expires soon")
else
    print_success "SSL certificate is valid"
fi

# Check disk usage
print_status "Checking disk usage..."
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | cut -d'%' -f1)
if [ $DISK_USAGE -gt $DISK_THRESHOLD ]; then
    print_error "High disk usage: ${DISK_USAGE}%"
    HEALTH_STATUS="CRITICAL"
    ISSUES+=("High disk usage: ${DISK_USAGE}%")
else
    print_success "Disk usage: ${DISK_USAGE}%"
fi

# Check memory usage
print_status "Checking memory usage..."
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEMORY_USAGE -gt $MEMORY_THRESHOLD ]; then
    print_warning "High memory usage: ${MEMORY_USAGE}%"
    if [ "$HEALTH_STATUS" != "CRITICAL" ]; then
        HEALTH_STATUS="WARNING"
    fi
    ISSUES+=("High memory usage: ${MEMORY_USAGE}%")
else
    print_success "Memory usage: ${MEMORY_USAGE}%"
fi

# Check CPU usage
print_status "Checking CPU usage..."
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
if (( $(echo "$CPU_USAGE > $CPU_THRESHOLD" | bc -l) )); then
    print_warning "High CPU usage: ${CPU_USAGE}%"
    if [ "$HEALTH_STATUS" != "CRITICAL" ]; then
        HEALTH_STATUS="WARNING"
    fi
    ISSUES+=("High CPU usage: ${CPU_USAGE}%")
else
    print_success "CPU usage: ${CPU_USAGE}%"
fi

# Check log file sizes
print_status "Checking log file sizes..."
LOG_SIZE=$(du -sm $APP_DIR/storage/logs/ | awk '{print $1}')
if [ $LOG_SIZE -gt 100 ]; then  # More than 100MB
    print_warning "Large log files: ${LOG_SIZE}MB"
    if [ "$HEALTH_STATUS" != "CRITICAL" ]; then
        HEALTH_STATUS="WARNING"
    fi
    ISSUES+=("Large log files: ${LOG_SIZE}MB")
else
    print_success "Log files size: ${LOG_SIZE}MB"
fi

# Check for errors in Laravel logs
print_status "Checking for recent errors..."
ERROR_COUNT=$(tail -1000 $APP_DIR/storage/logs/laravel.log 2>/dev/null | grep -c "ERROR" || echo "0")
if [ $ERROR_COUNT -gt 10 ]; then
    print_warning "Recent errors found: $ERROR_COUNT"
    if [ "$HEALTH_STATUS" != "CRITICAL" ]; then
        HEALTH_STATUS="WARNING"
    fi
    ISSUES+=("Recent errors in logs: $ERROR_COUNT")
else
    print_success "Error count: $ERROR_COUNT"
fi

# Generate health report
print_status "Generating health report..."
REPORT="Health Check Report for $APP_NAME
======================================
Timestamp: $(date)
Overall Status: $HEALTH_STATUS

System Metrics:
- CPU Usage: ${CPU_USAGE}%
- Memory Usage: ${MEMORY_USAGE}%
- Disk Usage: ${DISK_USAGE}%
- Response Time: ${RESPONSE_TIME_MS}ms
- Log Size: ${LOG_SIZE}MB
- Recent Errors: $ERROR_COUNT

Service Status:
- Application: $(curl -f -s http://localhost > /dev/null && echo "UP" || echo "DOWN")
- Admin Panel: $(curl -f -s http://localhost/admin > /dev/null && echo "UP" || echo "DOWN")
- Database: $(cd $APP_DIR && sudo -u $APP_NAME php artisan migrate:status > /dev/null 2>&1 && echo "UP" || echo "DOWN")
- Queue Worker: $(supervisorctl status ${APP_NAME}-worker:* | grep RUNNING > /dev/null && echo "RUNNING" || echo "STOPPED")
- Nginx: $(systemctl is-active --quiet nginx && echo "RUNNING" || echo "STOPPED")
- PHP-FPM: $(systemctl is-active --quiet php8.2-fpm && echo "RUNNING" || echo "STOPPED")
"

if [ ${#ISSUES[@]} -gt 0 ]; then
    REPORT="$REPORT
Issues Found:
$(printf '%s\n' "${ISSUES[@]}" | sed 's/^/- /')
"
fi

# Log the report
log_message "Health check completed - Status: $HEALTH_STATUS"

# Display final status
case $HEALTH_STATUS in
    "HEALTHY")
        print_success "🟢 System is healthy"
        ;;
    "WARNING")
        print_warning "🟡 System has warnings"
        echo "$REPORT"
        send_notification "$APP_NAME Health Warning" "$REPORT"
        ;;
    "CRITICAL")
        print_error "🔴 System has critical issues"
        echo "$REPORT"
        send_notification "$APP_NAME Health Critical" "$REPORT"
        ;;
esac

# Cleanup old logs (keep last 30 days)
find /var/log -name "${APP_NAME}_health.log*" -mtime +30 -delete 2>/dev/null || true

echo ""
print_status "Health check completed. Status: $HEALTH_STATUS"