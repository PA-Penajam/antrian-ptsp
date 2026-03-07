# Suggested Commands

## Development
```bash
# Start all dev services (server + queue + vite)
composer run dev

# Laravel dev server only
php artisan serve

# Vite dev server only
npm run dev

# Build frontend assets
npm run build
```

## Testing
```bash
# Run all tests (compact output)
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/Queue/CreateQueueTicketTest.php

# Run test by filter name
php artisan test --compact --filter=testName

# Create new feature test (Pest)
php artisan make:test --pest FeatureNameTest

# Create new unit test (Pest)
php artisan make:test --pest --unit UnitNameTest
```

## Code Formatting
```bash
# Format changed files (use before committing)
vendor/bin/pint --dirty --format agent

# Format all files
vendor/bin/pint --format agent

# Check formatting only (CI)
vendor/bin/pint --test --format agent
```

## Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seed
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name_table

# Seed database
php artisan db:seed
php artisan db:seed --class=QueueMvpSeeder
```

## Artisan Generators
```bash
# Model with factory, migration, seeder
php artisan make:model ModelName -fms --no-interaction

# Controller
php artisan make:controller ControllerName --no-interaction

# Form Request
php artisan make:request RequestName --no-interaction

# Generic class
php artisan make:class ClassName --no-interaction

# List all available artisan commands
php artisan list
```

## Git
```bash
git status
git add .
git commit -m "message"
git log --oneline -10
```

## CI Pipeline
```bash
# Full CI check (clear config + lint check + all tests)
composer run ci:check
```

## Debugging
```bash
# Laravel Pail (real-time log viewer)
php artisan pail

# Tinker
php artisan tinker
```
