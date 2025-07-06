#!/bin/bash

# Bayan Smart Village - Local Development Setup Script
# This script sets up the project for local development

set -e  # Exit on any error

echo "🏘️  Setting up Bayan Smart Village for local development..."

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

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if [ "$(printf '%s\n' "8.2" "$PHP_VERSION" | sort -V | head -n1)" != "8.2" ]; then
    print_error "PHP 8.2 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi

print_success "PHP $PHP_VERSION detected"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi

print_success "Composer detected"

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18+ first."
    exit 1
fi

NODE_VERSION=$(node -v)
print_success "Node.js $NODE_VERSION detected"

# Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy environment file
if [ ! -f .env ]; then
    print_status "Creating .env file..."
    cp .env.example .env
    print_success ".env file created"
else
    print_warning ".env file already exists, skipping..."
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate --ansi

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    print_status "Creating SQLite database..."
    touch database/database.sqlite
    print_success "SQLite database created"
else
    print_warning "SQLite database already exists"
fi

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed the database
print_status "Seeding database with sample data..."
php artisan db:seed --force

# Create storage link
print_status "Creating storage symbolic link..."
php artisan storage:link

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install

# Build assets
print_status "Building frontend assets..."
npm run build

# Set proper permissions (Unix/Linux/macOS only)
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
    print_status "Setting proper file permissions..."
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    print_success "File permissions set"
fi

# Clear and cache configuration
print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "🎉 Setup completed successfully!"
echo ""
echo "🚀 To start the development server, run:"
echo "   php artisan serve"
echo ""
echo "🔗 Your application will be available at:"
echo "   http://localhost:8000"
echo ""
echo "👤 Admin panel login:"
echo "   Email: admin@bayansmart.com"
echo "   Password: password"
echo ""
echo "📖 Admin panel URL:"
echo "   http://localhost:8000/admin"