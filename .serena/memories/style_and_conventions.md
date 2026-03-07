# Code Style & Conventions

## PHP / Laravel
- **Pint preset**: `laravel` (pint.json: `{"preset": "laravel"}`)
- **PHP version**: 8.4 — use modern features (constructor promotion, enums, match, named args)
- **Return types**: Always declare explicit return types on methods
- **Type hints**: Always use type hints for parameters
- **Casts**: Use `casts()` method on models (not `$casts` property)
- **Fillable**: Models use `$fillable` array
- **Relationships**: Always typed with return type hints (e.g. `BelongsTo`, `HasMany`)
- **PHPDoc**: Use `@use HasFactory<\Database\Factories\ModelFactory>` on models
- **Enums**: Backed string enums (e.g. `UserRole: string { case Admin = 'admin'; }`)
- **No `DB::`** — use `Model::query()` or Eloquent
- **No `env()`** outside config files

## Architecture
- **Actions pattern**: Business logic in `app/Actions/{Domain}/` (single-responsibility classes)
- **Form Requests**: Always use FormRequest for validation (never inline in controllers)
- **Concerns/Traits**: Shared behavior in `app/Concerns/`
- **Support classes**: Helpers/builders in `app/Support/`
- **Middleware**: Registered via alias in `bootstrap/app.php`
- **Providers**: Listed in `bootstrap/providers.php`

## Frontend / Views
- **Flux UI Pro**: Use `<flux:*>` components for UI elements
- **Livewire Volt**: Single-file components with ⚡ prefix (e.g. `⚡dashboard-stats.blade.php`)
- **Blade views**: Organized in `resources/views/pages/{section}/{page}.blade.php`
- **Layouts**: `layouts/app.blade.php` (main), `layouts/auth.blade.php` (auth pages)
- **Components**: Reusable in `resources/views/components/`
- **Tailwind CSS v4**: Utility-first, check project patterns before adding new ones
- **Alpine.js**: For client-side interactions within Livewire

## Naming
- **Routes**: Indonesian naming convention (e.g. `/antrian`, `/petugas/loket`, `/laporan`, `/admin/layanan`)
- **Variables/methods**: Descriptive English names (e.g. `isAccessible`, not `check()`)
- **Controllers**: Resource-style naming (index, store, show, update, destroy)
- **Tests**: Descriptive test names organized by domain folder

## Testing
- **Framework**: Pest v4 (not PHPUnit directly)
- **Test structure**: `tests/Feature/{Domain}/` and `tests/Unit/{Domain}/`
- **Factories**: Every model has a factory — use factories in tests
- **Command**: `php artisan test --compact` with filter for targeted runs
