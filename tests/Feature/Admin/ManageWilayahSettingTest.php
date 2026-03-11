<?php

use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('admin can open wilayah setting page', function () {
    DB::table('wilayah')->insert([
        ['kode' => '64.09', 'nama' => 'Kabupaten Penajam Paser Utara'],
        ['kode' => '64.09.01.1001', 'nama' => 'Penajam'],
    ]);

    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.wilayah.index'));

    $response->assertOk()
        ->assertSee('Setting Wilayah')
        ->assertSee('Kabupaten Penajam Paser Utara');
});

it('admin can update selected wilayah kabupaten scope', function () {
    DB::table('wilayah')->insert([
        ['kode' => '64.09', 'nama' => 'Kabupaten Penajam Paser Utara'],
        ['kode' => '64.09.01.1001', 'nama' => 'Penajam'],
    ]);

    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->put(route('admin.wilayah.update'), [
        'kabupaten_kode' => '64.09',
    ]);

    $response->assertRedirect(route('admin.wilayah.index'))
        ->assertSessionHas('status');

    expect(AppSetting::getValue('wilayah.scope.kabupaten_kode'))->toBe('64.09');
});

it('non admin cannot access wilayah setting routes', function () {
    $user = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->get(route('admin.wilayah.index'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.wilayah.update'), [
        'kabupaten_kode' => '64.09',
    ])->assertForbidden();
});
