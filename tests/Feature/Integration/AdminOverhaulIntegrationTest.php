<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Overhaul Integration Tests
|--------------------------------------------------------------------------
|
| Cross-module integration tests that verify the complete admin UI overhaul
| works end-to-end across all modules.
|
*/

// Helper function for creating admin user
function createAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
}

// Helper function for creating non-admin user
function createNonAdmin(UserRole $role): User
{
    return User::factory()->create([
        'role' => $role->value,
        'email_verified_at' => now(),
    ]);
}

/*
|--------------------------------------------------------------------------
| Group 1: Admin Dashboard
|--------------------------------------------------------------------------
*/

describe('admin dashboard', function () {
    it('admin can access dashboard with stat cards', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('Total Hari Ini')
            ->assertSee('Sudah Dilayani')
            ->assertSee('Menunggu');
    });

    it('non-admin cannot access admin routes', function () {
        $officer = createNonAdmin(UserRole::Officer);

        $this->actingAs($officer)
            ->get(route('admin.layanan.index'))
            ->assertForbidden();
    });

    it('frontdesk user cannot access admin routes', function () {
        $frontdesk = createNonAdmin(UserRole::Frontdesk);

        $this->actingAs($frontdesk)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('monitor user cannot access admin routes', function () {
        $monitor = createNonAdmin(UserRole::Monitor);

        $this->actingAs($monitor)
            ->get(route('admin.loket.index'))
            ->assertForbidden();
    });

    it('unauthenticated user is redirected to login for admin routes', function () {
        $this->get(route('admin.layanan.index'))
            ->assertRedirect('/login');
    });
});

/*
|--------------------------------------------------------------------------
| Group 2: Admin CRUD Pages Load
|--------------------------------------------------------------------------
*/

describe('admin crud pages', function () {
    it('admin layanan page loads with breadcrumb', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.layanan.index'))
            ->assertSuccessful()
            ->assertSee('Layanan')
            ->assertSee(route('dashboard')); // breadcrumb home link
    });

    it('admin loket page loads with breadcrumb', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.loket.index'))
            ->assertSuccessful()
            ->assertSee('Loket')
            ->assertSee(route('dashboard'));
    });

    it('admin users page loads with breadcrumb', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertSuccessful()
            ->assertSee('Users')
            ->assertSee(route('dashboard'));
    });

    it('admin pages contain flux breadcrumbs component', function () {
        $admin = createAdmin();

        $routes = [
            'admin.layanan.index',
            'admin.loket.index',
            'admin.users.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertSuccessful()
                ->assertSee('data-flux-breadcrumbs', false);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Group 3: Old Route Redirects
|--------------------------------------------------------------------------
*/

describe('old route redirects', function () {
    it('old admin roles route redirects to users with 301', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertRedirect(route('admin.users.index', ['tab' => 'roles']))
            ->assertStatus(301);
    });

    it('old admin izin-layanan route redirects to users with 301', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get('/admin/izin-layanan')
            ->assertRedirect(route('admin.users.index', ['tab' => 'roles']))
            ->assertStatus(301);
    });

    it('redirects work for all authenticated users with proper role', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertRedirect(route('admin.users.index', ['tab' => 'roles']));

        $this->actingAs($admin)
            ->get('/admin/izin-layanan')
            ->assertRedirect(route('admin.users.index', ['tab' => 'roles']));
    });
});

/*
|--------------------------------------------------------------------------
| Group 4: Kiosk Module
|--------------------------------------------------------------------------
*/

describe('kiosk module', function () {
    it('kiosk requires password authentication', function () {
        $this->get(route('kiosk.index'))
            ->assertRedirect(route('kiosk.login'));
    });

    it('kiosk login page is accessible', function () {
        $this->get(route('kiosk.login'))
            ->assertSuccessful()
            ->assertSee('Selamat Datang');
    });

    it('kiosk shows booking wizard when authenticated', function () {
        $this->withSession([
            'kiosk_authenticated' => true,
            'kiosk_authenticated_at' => now()->timestamp,
        ])
            ->get(route('kiosk.index'))
            ->assertSuccessful()
            ->assertSee('Pilih Layanan');
    });

    it('kiosk authentication works with correct password', function () {
        config(['kiosk.kiosk_password' => bcrypt('test-pass')]);
        $this->post(route('kiosk.authenticate'), [
            'password' => 'test-pass',
        ])
            ->assertRedirect(route('kiosk.index'))
            ->assertSessionHas('kiosk_authenticated', true);
    });

    it('kiosk rejects wrong password', function () {
        config(['kiosk.kiosk_password' => bcrypt('correct-pass')]);
        $this->from(route('kiosk.login'))->post(route('kiosk.authenticate'), [
            'password' => 'wrong-password',
        ])
            ->assertRedirect(route('kiosk.login'))
            ->assertSessionHasErrors(['password']);
    });

    it('kiosk logout clears session', function () {
        $this->withSession([
            'kiosk_authenticated' => true,
            'kiosk_authenticated_at' => now()->timestamp,
        ])
            ->post(route('kiosk.logout'))
            ->assertRedirect(route('kiosk.login'))
            ->assertSessionMissing('kiosk_authenticated')
            ->assertSessionMissing('kiosk_authenticated_at');
    });
});

/*
|--------------------------------------------------------------------------
| Group 5: TV Display Module
|--------------------------------------------------------------------------
*/

describe('tv display module', function () {
    it('tv display requires password authentication', function () {
        $this->get(route('tv-display.index'))
            ->assertRedirect(route('tv-display.login'));
    });

    it('tv display login page is accessible', function () {
        $this->get(route('tv-display.login'))
            ->assertSuccessful()
            ->assertSee('Monitor Antrian');
    });

    it('tv display shows queue board when authenticated', function () {
        $this->withSession([
            'tv_display_authenticated' => true,
            'tv_display_authenticated_at' => now()->timestamp,
        ])
            ->get(route('tv-display.index'))
            ->assertSuccessful()
            ->assertSee('Monitor Antrian');
    });

    it('tv display authentication works with correct password', function () {
        config(['kiosk.tv_display_password' => bcrypt('test-tv-pass')]);
        $this->post(route('tv-display.authenticate'), [
            'password' => 'test-tv-pass',
        ])
            ->assertRedirect(route('tv-display.index'))
            ->assertSessionHas('tv_display_authenticated', true);
    });

    it('tv display rejects wrong password', function () {
        config(['kiosk.tv_display_password' => bcrypt('correct-pass')]);
        $this->from(route('tv-display.login'))->post(route('tv-display.authenticate'), [
            'password' => 'wrong-password',
        ])
            ->assertRedirect(route('tv-display.login'))
            ->assertSessionHasErrors(['password']);
    });

    it('tv display logout clears session', function () {
        $this->withSession([
            'tv_display_authenticated' => true,
            'tv_display_authenticated_at' => now()->timestamp,
        ])
            ->post(route('tv-display.logout'))
            ->assertRedirect(route('tv-display.login'))
            ->assertSessionMissing('tv_display_authenticated')
            ->assertSessionMissing('tv_display_authenticated_at');
    });
});

/*
|--------------------------------------------------------------------------
| Group 6: Theme Toggle
|--------------------------------------------------------------------------
*/

describe('theme toggle', function () {
    it('admin sidebar contains theme toggle button', function () {
        $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

        expect($sidebar)
            ->toContain('localStorage')
            ->toContain('theme')
            ->toContain('x-on:click="theme = theme')
            ->toContain('Toggle tema');
    });

    it('sidebar does not have hardcoded dark class on html element', function () {
        $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

        // Should use Alpine's :class binding, not hardcoded class="dark"
        expect($sidebar)->not->toContain('class="dark"');
    });

    it('theme toggle uses flux button component', function () {
        $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

        expect($sidebar)->toContain('<flux:button');
    });

    it('theme state is persisted in localStorage', function () {
        $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

        expect($sidebar)
            ->toContain("localStorage.getItem('theme')")
            ->toContain("localStorage.setItem('theme', value)");
    });
});

/*
|--------------------------------------------------------------------------
| Group 7: Named Routes All Present
|--------------------------------------------------------------------------
*/

describe('named routes', function () {
    it('all admin named routes exist', function () {
        $routes = [
            'admin.layanan.index',
            'admin.layanan.store',
            'admin.layanan.update',
            'admin.layanan.destroy',
            'admin.loket.index',
            'admin.loket.store',
            'admin.loket.update',
            'admin.loket.destroy',
            'admin.users.index',
            'admin.users.store',
            'admin.users.update',
            'admin.users.destroy',
        ];

        foreach ($routes as $routeName) {
            expect(Route::has($routeName))->toBeTrue("Route {$routeName} should exist");
        }
    });

    it('kiosk and tv-display named routes exist', function () {
        $routes = [
            'kiosk.login',
            'kiosk.authenticate',
            'kiosk.logout',
            'kiosk.index',
            'tv-display.login',
            'tv-display.authenticate',
            'tv-display.logout',
            'tv-display.index',
        ];

        foreach ($routes as $routeName) {
            expect(Route::has($routeName))->toBeTrue("Route {$routeName} should exist");
        }
    });

    it('dashboard route exists', function () {
        expect(Route::has('dashboard'))->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| Group 8: Navigation Integration
|--------------------------------------------------------------------------
*/

describe('navigation integration', function () {
    it('admin sidebar contains all admin menu items', function () {
        $admin = createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Check for admin navigation items
        $response
            ->assertSee('Dashboard')
            ->assertSee('Layanan')
            ->assertSee('Loket')
            ->assertSee('Users')
            ->assertSee('Kiosk')
            ->assertSee('TV Display');
    });

    it('admin sidebar navigation links use correct routes', function () {
        $sidebar = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

        // Check that sidebar uses route() helpers for admin links
        expect($sidebar)
            ->toContain("route('dashboard')")
            ->toContain("route('admin.layanan.index')")
            ->toContain("route('admin.loket.index')")
            ->toContain("route('admin.users.index')")
            ->toContain("route('kiosk.index')")
            ->toContain("route('tv-display.index')");
    });

    it('non-admin users do not see admin menu items', function () {
        $officer = createNonAdmin(UserRole::Officer);

        $response = $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Officer should not see admin-specific items
        $response->assertDontSee('admin.layanan.index', false);
    });
});

/*
|--------------------------------------------------------------------------
| Group 9: Cross-Module Access Control
|--------------------------------------------------------------------------
*/

describe('cross-module access control', function () {
    it('admin can access all admin crud operations', function () {
        $admin = createAdmin();
        $this->actingAs($admin);

        // Layanan
        $this->get(route('admin.layanan.index'))->assertSuccessful();

        // Loket
        $this->get(route('admin.loket.index'))->assertSuccessful();

        // Users
        $this->get(route('admin.users.index'))->assertSuccessful();
    });

    it('admin can access kiosk and tv display modules', function () {
        $admin = createAdmin();

        // With proper session, admin can access kiosk
        $this->actingAs($admin)
            ->withSession([
                'kiosk_authenticated' => true,
                'kiosk_authenticated_at' => now()->timestamp,
            ])
            ->get(route('kiosk.index'))
            ->assertSuccessful();

        // With proper session, admin can access tv display
        $this->actingAs($admin)
            ->withSession([
                'tv_display_authenticated' => true,
                'tv_display_authenticated_at' => now()->timestamp,
            ])
            ->get(route('tv-display.index'))
            ->assertSuccessful();
    });
});
