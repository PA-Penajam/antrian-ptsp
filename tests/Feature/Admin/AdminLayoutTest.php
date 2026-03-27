<?php

use App\Enums\UserRole;
use App\Models\User;

test('admin dashboard uses constrained content width wrapper', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Shortcut Manajemen')
        ->assertSee('max-w-6xl', false);
});

test('admin navigation template highlights every admin subpage', function () {
    $header = file_get_contents(resource_path('views/layouts/app/header.blade.php'));
    $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

    expect($header)->toContain("request()->is('admin/*')");
    expect($sidebar)->toContain("request()->is('admin/layanan')");
});

test('admin user management view avoids native select controls in forms', function () {
    $view = file_get_contents(resource_path('views/pages/admin/users/index.blade.php'));

    expect($view)->not->toContain('<select');
    expect($view)->toContain('<flux:select name="role"');
    expect($view)->toContain('variant="listbox"');
    expect($view)->toContain('multiple');
});

test('admin counter management view uses compact flux select controls', function () {
    $view = file_get_contents(resource_path('views/pages/admin/loket/index.blade.php'));

    expect($view)->not->toContain('<select');
    expect($view)->toContain('<flux:select name="queue_pool_id"');
    expect($view)->toContain('size="sm"');
});

test('remaining admin summary pages rely on the shared app content wrapper', function () {
    $serviceView = file_get_contents(resource_path('views/pages/admin/layanan/index.blade.php'));
    $rolesView = file_get_contents(resource_path('views/pages/admin/roles/index.blade.php'));
    $permissionsView = file_get_contents(resource_path('views/pages/admin/roles/permissions.blade.php'));

    expect($serviceView)->not->toContain('<flux:main container>');
    expect($rolesView)->not->toContain('<flux:main container>');
    expect($permissionsView)->not->toContain('<flux:main container>');
});

test('service management view avoids native select options and checkboxes', function () {
    $view = file_get_contents(resource_path('views/pages/admin/layanan/index.blade.php'));

    expect($view)->not->toContain('<option');
    expect($view)->not->toContain('type="checkbox"');
    expect($view)->toContain('<flux:select.option value="{{ $pool->id }}">');
    expect($view)->toContain('<flux:checkbox');
});
