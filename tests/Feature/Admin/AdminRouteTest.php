<?php

use Illuminate\Support\Facades\Route;

it('has named admin layanan routes', function () {
    expect(Route::has('admin.layanan.index'))->toBeTrue();
    expect(Route::has('admin.layanan.store'))->toBeTrue();
    expect(Route::has('admin.layanan.update'))->toBeTrue();
    expect(Route::has('admin.layanan.destroy'))->toBeTrue();
});

it('has named admin loket routes', function () {
    expect(Route::has('admin.loket.index'))->toBeTrue();
    expect(Route::has('admin.loket.update'))->toBeTrue();
    expect(Route::has('admin.loket.destroy'))->toBeTrue();
});

it('has named admin users routes', function () {
    expect(Route::has('admin.users.index'))->toBeTrue();
    expect(Route::has('admin.users.store'))->toBeTrue();
    expect(Route::has('admin.users.update'))->toBeTrue();
    expect(Route::has('admin.users.destroy'))->toBeTrue();
});

it('has named kiosk routes', function () {
    expect(Route::has('kiosk.index'))->toBeTrue();
    expect(Route::has('kiosk.login'))->toBeTrue();
    expect(Route::has('kiosk.authenticate'))->toBeTrue();
    expect(Route::has('kiosk.logout'))->toBeTrue();
});

it('has named tv-display routes', function () {
    expect(Route::has('tv-display.index'))->toBeTrue();
    expect(Route::has('tv-display.login'))->toBeTrue();
    expect(Route::has('tv-display.authenticate'))->toBeTrue();
    expect(Route::has('tv-display.logout'))->toBeTrue();
});

it('admin layanan index route requires admin role middleware', function () {
    $response = $this->get(route('admin.layanan.index'));
    $response->assertRedirect('/login');
});

it('admin layanan store route requires admin role middleware', function () {
    $response = $this->post(route('admin.layanan.store'), []);
    $response->assertRedirect('/login');
});

it('admin layanan update route requires admin role middleware', function () {
    $response = $this->put(route('admin.layanan.update', 1), []);
    $response->assertRedirect('/login');
});

it('admin layanan destroy route requires admin role middleware', function () {
    $response = $this->delete(route('admin.layanan.destroy', 1));
    $response->assertRedirect('/login');
});

it('admin loket index route requires admin role middleware', function () {
    $response = $this->get(route('admin.loket.index'));
    $response->assertRedirect('/login');
});

it('admin loket update route requires admin role middleware', function () {
    $response = $this->put(route('admin.loket.update', 1), []);
    $response->assertRedirect('/login');
});

it('admin loket destroy route requires admin role middleware', function () {
    $response = $this->delete(route('admin.loket.destroy', 1));
    $response->assertRedirect('/login');
});

it('admin users index route requires admin role middleware', function () {
    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect('/login');
});

it('admin users store route requires admin role middleware', function () {
    $response = $this->post(route('admin.users.store'), []);
    $response->assertRedirect('/login');
});

it('admin users update route requires admin role middleware', function () {
    $response = $this->put(route('admin.users.update', 1), []);
    $response->assertRedirect('/login');
});

it('admin users destroy route requires admin role middleware', function () {
    $response = $this->delete(route('admin.users.destroy', 1));
    $response->assertRedirect('/login');
});
