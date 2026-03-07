<?php

use App\Enums\UserRole;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

dataset('admin breadcrumb pages', [
    'layanan' => ['admin.layanan.index', 'Layanan'],
    'loket' => ['admin.loket.index', 'Loket'],
    'users' => ['admin.users.index', 'Users'],
]);

test('admin pages render breadcrumb container and items', function (string $routeName, string $pageName) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    actingAs($admin);

    $response = get(route($routeName));

    $response->assertSuccessful()
        ->assertSeeInOrder([
            'data-flux-breadcrumbs',
            'data-flux-breadcrumbs-item',
            'href="'.route('dashboard').'"',
            'data-flux-breadcrumbs-item',
            $pageName,
        ], false);
})->with('admin breadcrumb pages');

test('admin breadcrumb views include dashboard home item', function (string $routeName) {
    $viewPath = match ($routeName) {
        'admin.layanan.index' => 'views/pages/admin/layanan/index.blade.php',
        'admin.loket.index' => 'views/pages/admin/loket/index.blade.php',
        'admin.users.index' => 'views/pages/admin/users/index.blade.php',
    };

    $view = file_get_contents(resource_path($viewPath));

    expect($view)->toContain("<flux:breadcrumbs.item :href=\"route('dashboard')\" icon=\"home\" />");
})->with([
    'admin.layanan.index',
    'admin.loket.index',
    'admin.users.index',
]);
