<?php

use App\Enums\UserRole;
use App\Livewire\AdminRoleSwitcher;
use App\Models\User;
use Livewire\Livewire;

// --- activeRole() tests ---

it('mengembalikan role asli untuk user non-admin', function () {
    $user = User::factory()->create(['role' => UserRole::Officer]);

    $this->actingAs($user);

    expect($user->activeRole())->toBe(UserRole::Officer);
});

it('mengembalikan admin jika session belum diset', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user);

    expect($user->activeRole())->toBe(UserRole::Admin);
});

it('mengembalikan role dari session untuk admin', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user);
    session(['admin_active_role' => 'officer']);

    expect($user->activeRole())->toBe(UserRole::Officer);
});

it('mengabaikan session role yang tidak valid untuk admin', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user);
    session(['admin_active_role' => 'invalid_role']);

    expect($user->activeRole())->toBe(UserRole::Admin);
});

// --- AdminRoleSwitcher Livewire component tests ---

it('merender component AdminRoleSwitcher untuk admin', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($user)
        ->test(AdminRoleSwitcher::class)
        ->assertSee('Admin')
        ->assertSee('Frontdesk')
        ->assertSee('Officer')
        ->assertSee('Monitor');
});

it('menyimpan role ke session saat switchRole dipanggil', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($user)
        ->test(AdminRoleSwitcher::class)
        ->call('switchRole', 'officer')
        ->assertRedirect('/workstation');

    expect(session('admin_active_role'))->toBe('officer');
});

it('redirect ke halaman default sesuai role yang dipilih', function (string $role, string $expectedUrl) {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($user)
        ->test(AdminRoleSwitcher::class)
        ->call('switchRole', $role)
        ->assertRedirect($expectedUrl);
})->with([
    ['admin', '/admin/layanan'],
    ['frontdesk', '/frontdesk/antrian'],
    ['officer', '/workstation'],
    ['monitor', '/laporan/antrian'],
]);

it('tidak mengizinkan non-admin untuk switch role', function () {
    $user = User::factory()->create(['role' => UserRole::Officer]);

    Livewire::actingAs($user)
        ->test(AdminRoleSwitcher::class)
        ->call('switchRole', 'frontdesk')
        ->assertForbidden();
});

it('menolak role yang tidak valid', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($user)
        ->test(AdminRoleSwitcher::class)
        ->call('switchRole', 'invalid_role')
        ->assertHasErrors(['role']);
});

// --- Sidebar navigation tests ---

it('menampilkan dropdown switcher hanya untuk admin', function () {
    $officer = User::factory()->create(['role' => UserRole::Officer]);

    $this->actingAs($officer)
        ->get(route('workstation'))
        ->assertDontSee('wire:model="activeRole"');
});
