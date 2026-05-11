# Getting Started

<cite>
**Referenced Files in This Document**
- [composer.json](file://composer.json)
- [package.json](file://package.json)
- [config/app.php](file://config/app.php)
- [config/database.php](file://config/database.php)
- [config/kiosk.php](file://config/kiosk.php)
- [config/institution.php](file://config/institution.php)
- [bootstrap/app.php](file://bootstrap/app.php)
- [routes/web.php](file://routes/web.php)
- [vendor/laravel/sail/bin/sail](file://vendor/laravel/sail/bin/sail)
- [database/seeders/DatabaseSeeder.php](file://database/seeders/DatabaseSeeder.php)
- [database/seeders/QueueMvpSeeder.php](file://database/seeders/QueueMvpSeeder.php)
- [database/seeders/WilayahSeeder.php](file://database/seeders/WilayahSeeder.php)
- [vite.config.js](file://vite.config.js)
</cite>

## Update Summary
**Changes Made**
- Enhanced installation instructions with comprehensive step-by-step setup procedure
- Added detailed environment configuration guidelines with database and module password setup
- Expanded database migration and seeding process documentation
- Improved asset compilation and development server setup instructions
- Added comprehensive troubleshooting section for common installation issues
- Updated first-run verification procedures with module-specific access checks

## Table of Contents
1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Installation Steps](#installation-steps)
4. [Environment Configuration](#environment-configuration)
5. [Development Environment Setup](#development-environment-setup)
6. [Database Setup](#database-setup)
7. [Asset Compilation](#asset-compilation)
8. [First Run Verification](#first-run-verification)
9. [Troubleshooting Common Issues](#troubleshooting-common-issues)
10. [Module Access Guide](#module-access-guide)

## Introduction
This comprehensive guide helps you install, configure, and run the PTSP Queue Management System locally. The system is built with Laravel 12, featuring Livewire UI components, Vite-powered frontend assets, and modular routing for Admin, Frontdesk, Officer, Kiosk, and TV Display modules. Laravel Sail provides a Docker-based local development environment with support for multiple database backends.

## Prerequisites
Before installing the PTSP Queue Management System, ensure you have the following prerequisites:

### System Requirements
- **PHP**: Version 8.2 or higher (required by Composer)
- **Node.js**: Required for asset compilation and development server
- **Docker**: Essential for Laravel Sail containerized environment
- **Operating System**: macOS, Linux, or Windows with WSL2 support

### Required Dependencies
- Composer 2.x+ for PHP dependency management
- NPM (Node Package Manager) for JavaScript dependencies
- Git for version control and project initialization

**Section sources**
- [composer.json:12](file://composer.json#L12)
- [vendor/laravel/sail/bin/sail:13](file://vendor/laravel/sail/bin/sail#L13)

## Installation Steps

### Step 1: Clone and Initialize the Project
```bash
# Clone the repository
git clone <repository-url>
cd antrian-ptsp

# Copy environment configuration
cp .env.example .env
```

### Step 2: Install PHP Dependencies
```bash
# Install PHP dependencies via Composer
composer install
```

### Step 3: Generate Application Key
```bash
# Generate encryption key
php artisan key:generate
```

### Step 4: Configure Environment Variables
Edit your `.env` file with the following essential configurations:

```env
# Application Configuration
APP_NAME="PTSP Queue System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database Configuration (SQLite default)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Kiosk and TV Display Module Passwords
KIOSK_PASSWORD=password
TV_DISPLAY_PASSWORD=password

# Institution Configuration
INSTITUTION_NAME="PTSP Office"
INSTITUTION_ADDRESS="Jl. PTSP No. 1"
INSTITUTION_PHONE="(021) 1234567"
OPERATING_HOURS="Monday - Friday, 08:00 - 16:00 WIB"
```

### Step 5: Run Database Migrations
```bash
# Run database migrations
php artisan migrate --force
```

### Step 6: Install Frontend Dependencies
```bash
# Install Node.js dependencies
npm install
```

### Step 7: Compile Assets
```bash
# Compile frontend assets
npm run build
```

### Step 8: Start Development Server
```bash
# Start development server with concurrent processes
composer dev
```

**Section sources**
- [composer.json:54-65](file://composer.json#L54-L65)
- [composer.json:53-61](file://composer.json#L53-L61)

## Environment Configuration

### Database Configuration
The system supports multiple database backends. By default, SQLite is configured for simplicity:

```env
# SQLite (Default)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# MySQL Alternative
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ptsp_queue
DB_USERNAME=your_username
DB_PASSWORD=your_password

# PostgreSQL Alternative
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ptsp_queue
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Module Password Configuration
Configure authentication passwords for specialized modules:

```env
# Module passwords (can be overridden individually)
MODULE_PASSWORD=password
KIOSK_PASSWORD=password
TV_DISPLAY_PASSWORD=password

# Session lifetime in minutes
MODULE_SESSION_LIFETIME=1440
```

### Institution Configuration
Customize institutional branding:

```env
INSTITUTION_NAME="PTSP Office"
INSTITUTION_ADDRESS="Jl. PTSP No. 1"
INSTITUTION_PHONE="(021) 1234567"
OPERATING_HOURS="Monday - Friday, 08:00 - 16:00 WIB"
INSTITUTION_LOGO_PATH=""
```

**Section sources**
- [config/database.php:20-116](file://config/database.php#L20-L116)
- [config/kiosk.php:4-6](file://config/kiosk.php#L4-L6)
- [config/institution.php:4-8](file://config/institution.php#L4-L8)

## Development Environment Setup

### Using Laravel Sail (Recommended)
Laravel Sail provides a Docker-based development environment with pre-configured services:

```bash
# Start all services
./vendor/bin/sail up -d

# Stop all services
./vendor/bin/sail stop

# Execute commands within the Sail environment
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail php artisan db:seed
```

### Sail Command Reference
Common Sail operations for development:

- **Start/Stop Services**: `./vendor/bin/sail up -d`, `./vendor/bin/sail stop`
- **Run Artisan Commands**: `./vendor/bin/sail artisan [command]`
- **Database Operations**: `./vendor/bin/sail mysql`, `./vendor/bin/sail psql`
- **Testing**: `./vendor/bin/sail test`, `./vendor/bin/sail pest`
- **Open Browser**: `./vendor/bin/sail open`

### Alternative: Direct Local Setup
If you prefer not to use Docker:

```bash
# Install dependencies
composer install
npm install

# Start services manually
php artisan serve
php artisan queue:listen
npm run dev
```

**Section sources**
- [vendor/laravel/sail/bin/sail:41-122](file://vendor/laravel/sail/bin/sail#L41-L122)
- [vendor/laravel/sail/bin/sail:264-286](file://vendor/laravel/sail/bin/sail#L264-L286)

## Database Setup

### Migration Process
The system uses Laravel's migration system to create and update database schemas:

```bash
# Run all migrations
php artisan migrate

# Run migrations with force (non-interactive)
php artisan migrate --force

# Rollback last batch of migrations
php artisan migrate:rollback

# Fresh database (drops all tables and re-runs all migrations)
php artisan migrate:fresh
```

### Data Seeding
The system includes comprehensive seeders for initial data:

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=QueueMvpSeeder
php artisan db:seed --class=WilayahSeeder
```

### Seeders Breakdown

#### QueueMvpSeeder
Creates essential queue infrastructure:
- **Queue Pools**: UMUM (General Services), BAYAR (Payment), POSBAKUM (Legal Aid)
- **Services**: General Registration, Payment, Legal Aid services
- **Counters**: Physical service counters (Registration, Information, Product Retrieval, eCourt, Payment, Legal Aid)

#### WilayahSeeder
Populates regional administrative data (only runs outside unit tests):
- Province, city, district, and village data
- Regional boundaries and hierarchies

#### DatabaseSeeder
Main seeder orchestrator that coordinates other seeders

**Section sources**
- [database/seeders/QueueMvpSeeder.php:15-128](file://database/seeders/QueueMvpSeeder.php#L15-L128)
- [database/seeders/WilayahSeeder.php:14-31](file://database/seeders/WilayahSeeder.php#L14-L31)
- [database/seeders/DatabaseSeeder.php:15-45](file://database/seeders/DatabaseSeeder.php#L15-L45)

## Asset Compilation

### Vite Configuration
The system uses Vite for modern asset compilation with Laravel integration:

```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/tv-display.css',
                'resources/js/tv-display.js',
                'resources/css/kiosk.css',
                'resources/js/kiosk.js',
                'resources/js/thermal-printer.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        cors: true,
        hmr: { host: '127.0.0.1' },
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
    build: { emptyOutDir: false },
});
```

### Available Assets
- **Main Application**: `resources/css/app.css`, `resources/js/app.js`
- **TV Display Interface**: `resources/css/tv-display.css`, `resources/js/tv-display.js`
- **Kiosk Interface**: `resources/css/kiosk.css`, `resources/js/kiosk.js`
- **Thermal Printer**: `resources/js/thermal-printer.js`

### Build Commands
```bash
# Development build with hot module replacement
npm run dev

# Production build
npm run build

# Watch mode for development
npm run watch
```

**Section sources**
- [vite.config.js:1-37](file://vite.config.js#L1-L37)

## First Run Verification

### Default Credentials
Use these demo accounts for initial system access:

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Administrator | admin@example.com | password | Full system access |
| Frontdesk Demo | frontdesk@example.com | password | Front desk operations |
| Officer Demo | officer@example.com | password | Service counter operations |
| Monitor Demo | monitor@example.com | password | Reporting and monitoring |

### Initial Data Verification
After setup completion, verify the following:

1. **Database Tables**: Check that all migration tables exist
2. **Queue Infrastructure**: Verify queue pools, services, and counters are created
3. **Demo Users**: Confirm all four demo user accounts exist
4. **Regional Data**: Validate Wilayah (regional) data if applicable

### Module Access Verification
Test access to different system modules:

#### Public Queue Interface
- **URL**: `/`
- **Functionality**: Public ticket booking, status checking

#### Admin Module
- **URL**: `/admin/` (requires Admin role)
- **Features**: Service management, counter management, user administration

#### Frontdesk Module
- **URL**: `/frontdesk/antrian` (requires Frontdesk/Admin role)
- **Features**: Walk-in registration, queue management

#### Officer Module
- **URL**: `/petugas/loket/{counter}` (requires Officer role)
- **Features**: Counter operations, ticket calling

#### Kiosk Module
- **Login**: `/kiosk/login` (uses module password)
- **Interface**: `/kiosk` (ticket booking interface)

#### TV Display Module
- **Login**: `/tv-display/login` (uses module password)
- **Interface**: `/tv-display` (display interface)

**Section sources**
- [database/seeders/DatabaseSeeder.php:27-44](file://database/seeders/DatabaseSeeder.php#L27-L44)
- [routes/web.php:18-124](file://routes/web.php#L18-L124)

## Troubleshooting Common Issues

### Docker and Sail Issues

#### Docker Not Running
**Problem**: Sail commands fail with Docker errors
**Solution**:
```bash
# Start Docker daemon
sudo systemctl start docker
# or on macOS, start Docker Desktop

# Verify Docker is running
docker --version
docker info
```

#### Port Conflicts
**Problem**: Application fails to start due to port conflicts
**Solution**:
```bash
# Check for port usage
netstat -tulpn | grep :80
lsof -i :80

# Update .env with different ports
APP_PORT=8080
```

#### Permission Issues
**Problem**: File permission errors during setup
**Solution**:
```bash
# Fix directory permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 666 database/database.sqlite

# Or use Sail to fix permissions
./vendor/bin/sail shell
chown -R sail:sail /var/www/html
```

### Database Issues

#### SQLite File Permissions
**Problem**: SQLite database write errors
**Solution**:
```bash
# Ensure database file is writable
touch database/database.sqlite
chmod 666 database/database.sqlite

# Or use absolute path in .env
DB_DATABASE=/var/www/html/database/database.sqlite
```

#### Migration Failures
**Problem**: Migration errors during setup
**Solution**:
```bash
# Clear cached migrations
php artisan cache:clear
php artisan config:clear

# Re-run migrations
php artisan migrate:fresh --seed
```

### Asset Compilation Issues

#### Node.js Version Compatibility
**Problem**: Vite build fails with Node.js version errors
**Solution**:
```bash
# Check Node.js version
node --version

# Use LTS version recommended for Laravel projects
# Install via nvm if needed
nvm install --lts
nvm use --lts
```

#### Missing Dependencies
**Problem**: Vite compilation errors
**Solution**:
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear npm cache
npm cache clean --force
```

### Authentication and Authorization Issues

#### Role-Based Access Control
**Problem**: Cannot access certain modules despite login
**Solution**:
```bash
# Verify user roles in database
./vendor/bin/sail php artisan tinker
>>> App\Models\User::all()->pluck('name', 'email', 'role')

# Manually assign roles if needed
>>> $user = App\Models\User::where('email', 'admin@example.com')->first()
>>> $user->role = 'admin'
>>> $user->save()
```

#### Module Password Issues
**Problem**: Kiosk/TV Display login failures
**Solution**:
```bash
# Check module passwords in configuration
./vendor/bin/sail php artisan tinker
>>> config('kiosk.kiosk_password')
>>> config('kiosk.tv_display_password')

# Update passwords in .env if needed
KIOSK_PASSWORD=new_password
TV_DISPLAY_PASSWORD=new_password
```

### Development Server Issues

#### Concurrent Processes
**Problem**: Development server not starting properly
**Solution**:
```bash
# Use Composer script instead
composer dev

# Or start manually with proper ordering
# Terminal 1: php artisan serve
# Terminal 2: php artisan queue:listen
# Terminal 3: npm run dev
```

#### Hot Module Replacement (HMR)
**Problem**: Frontend changes not reflecting
**Solution**:
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Restart development server
npm run dev
```

**Section sources**
- [vendor/laravel/sail/bin/sail:157-164](file://vendor/laravel/sail/bin/sail#L157-L164)
- [vendor/laravel/sail/bin/sail:190-208](file://vendor/laravel/sail/bin/sail#L190-L208)
- [config/database.php:20-116](file://config/database.php#L20-L116)

## Module Access Guide

### Role-Based Navigation
The system implements role-based access control with different interfaces for each user type:

#### Administrator (Admin)
- **Dashboard**: Full system overview
- **Services Management**: CRUD operations for services
- **Counter Management**: Counter configuration and assignment
- **User Management**: User account administration
- **Regional Settings**: Administrative region configuration

#### Frontdesk (Frontdesk/Admin)
- **Walk-in Registration**: Quick registration for walk-in customers
- **Queue Management**: View and manage current queue
- **Check-in Operations**: Customer check-in procedures

#### Officer (Officer)
- **Counter Interface**: Service counter operations
- **Ticket Management**: Call, recall, skip, complete, cancel tickets
- **Real-time Updates**: Live queue status monitoring

#### Monitor (Monitor)
- **Reporting Interface**: Comprehensive reporting dashboard
- **Audit Trail**: System activity monitoring
- **Performance Analytics**: Queue performance metrics

#### Public Users
- **Online Booking**: Web-based ticket reservation
- **Status Checking**: Real-time queue status
- **Confirmation**: Booking confirmation and instructions

### Module-Specific Features
Each module has dedicated functionality:

#### Kiosk Module
- **Touchscreen Interface**: Optimized for kiosk devices
- **Barcode Printing**: Ticket barcode generation
- **Offline Capability**: Minimal connectivity requirements

#### TV Display Module
- **Large Screen Display**: Optimized for TV screens
- **Audio Announcements**: Text-to-speech integration
- **Multi-area Support**: Multiple display areas

**Section sources**
- [routes/web.php:28-90](file://routes/web.php#L28-L90)