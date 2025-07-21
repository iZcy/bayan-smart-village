# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Frontend (Vite + React)
- `npm run dev` - Start Vite development server
- `npm run build` - Build for production

### Backend (Laravel)
- `php artisan serve` - Start Laravel development server
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed database with test data
- `php artisan tinker` - Interactive PHP shell
- `php artisan test` - Run PHPUnit tests

### Combined Development
- `composer run dev` - Start all services (server, queue, logs, vite) using concurrently
- `composer run test` - Clear config and run tests

### Code Quality
- `vendor/bin/pint` - Laravel Pint for PHP formatting
- `php artisan config:clear` - Clear configuration cache

### Database Commands
- `php artisan migrate:fresh --seed` - Fresh migration with seeding
- `php artisan db:wipe` - Drop all tables

## Architecture Overview

### Core Concept
This is a **Smart Village** multi-tenant Laravel application with React frontend using Inertia.js. Each village operates as a subdomain (e.g., `village-name.kecamatanbayan.id`) with its own content while sharing the same codebase.

### Key Technologies
- **Backend**: Laravel 12 with Filament admin panel
- **Frontend**: React 19 + Inertia.js + Tailwind CSS 4
- **Database**: SQLite (development)
- **Media**: Intervention Image for processing
- **Build**: Vite with HMR

### Domain Structure
- **Main domain**: `kecamatanbayan.id` - Global admin, stunting calculator, products API
- **Village subdomains**: `{village}.kecamatanbayan.id` - Village-specific content
- **Custom domains**: Villages can have custom domains mapped to their content

### Core Models & Relationships

#### Village (Central Entity)
- Has many: Communities, Places, Categories, Articles, ExternalLinks, Images, Media
- Defines village boundaries and settings
- Manages media contexts (homepage, gallery, etc.)

#### Multi-level Content Structure
```
Village
├── Communities (village organizations)
│   └── SMEs (small businesses)
│       └── Offers (products/services)
├── Places (locations within village)
├── Articles (news/blog content)
├── Images & Media (context-aware)
└── External Links (social media, etc.)
```

### Key Features

#### 1. Multi-tenant Architecture
- Village resolution via `ResolveVillageSubdomain` middleware
- Scoped data access using `HasVillageScope` trait
- Dynamic routing for custom domains

#### 2. Media Management System
- Context-aware media (homepage, gallery, global)
- Support for video/audio backgrounds
- Automatic thumbnail generation
- Media statistics and organization

#### 3. Product/Offer System
- SME-owned products with multiple images
- E-commerce platform integration (Tokopedia, Shopee, TikTok Shop)
- Tag-based categorization
- Click tracking for external links

#### 4. Admin Interface
- Filament-based admin panel at main domain
- Resource-based CRUD for all entities
- File upload handling with optimization

### Important Directories

#### Backend Structure
- `app/Models/` - Eloquent models with relationships
- `app/Http/Controllers/` - API and page controllers
- `app/Http/Middleware/` - Village resolution and CORS
- `app/Filament/Resources/` - Admin panel configuration
- `app/Services/` - Business logic (VillageService, ImageUploadService)
- `database/migrations/` - Schema with `svnv_` prefix for village tables

#### Frontend Structure
- `resources/js/Components/` - Reusable React components
- `resources/js/Pages/Village/` - Village-specific page components
- `resources/js/Layouts/` - Layout components
- `vite.config.js` - Build configuration with React and Tailwind

### Configuration Files
- `config/smartvillage.php` - Village-specific settings and environment handling
- `routes/web.php` - Complex routing for domains and subdomains
- `composer.json` - Contains useful development scripts

### Testing
- PHPUnit configuration in `phpunit.xml`
- Feature and Unit tests in `tests/` directory
- Use `composer run test` which clears config before running tests

### Database Seeding
- Comprehensive seeders for all entities
- `CompleteSeeder` for full demo data
- `QuickTestSeeder` for minimal test setup
- Faker integration for realistic test data

### Media & File Handling
- Images stored in `storage/app/public/`
- Automatic thumbnail generation
- Support for various media contexts
- Image optimization and serving via dedicated controllers