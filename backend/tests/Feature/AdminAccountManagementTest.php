<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admins can update their own details and password with the current password', function () {
    $this->withoutVite();
    $admin = Admin::create(['name' => 'First Admin', 'email' => 'first@example.test', 'password' => 'old-password']);
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.account.show'))->assertOk()->assertSee('Change password');
    $this->patch(route('admin.account.update'), [
        'name' => 'Updated Admin', 'email' => 'updated@example.test',
    ])->assertSessionHasErrors('current_password');
    expect($admin->fresh()->email)->toBe('first@example.test');

    $this->patch(route('admin.account.update'), [
        'name' => 'Updated Admin', 'email' => 'UPDATED@example.test', 'current_password' => 'old-password',
    ])->assertRedirect();
    expect($admin->fresh()->email)->toBe('updated@example.test')
        ->and($admin->fresh()->name)->toBe('Updated Admin');

    $this->patch(route('admin.account.password'), [
        'password_current' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors('password_current');
    $this->patch(route('admin.account.password'), [
        'password_current' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
    ])->assertRedirect(route('admin.login'));
    expect(Hash::check('new-password', $admin->fresh()->password))->toBeTrue();
    $this->assertGuest('admin');
});

test('only super admins can create and manage admin accounts', function () {
    $this->withoutVite();
    $regular = Admin::create(['name' => 'Regular', 'email' => 'regular@example.test', 'password' => 'password']);
    $super = Admin::create(['name' => 'Manager', 'email' => 'manager@example.test', 'password' => 'password', 'is_super_admin' => true]);

    $this->actingAs($regular, 'admin');
    $this->get(route('admin.admins.index'))->assertForbidden();
    $this->get(route('admin.admins.create'))->assertForbidden();
    $this->patch(route('admin.admins.status', $super))->assertForbidden();
    $this->patch(route('admin.admins.role', $super))->assertForbidden();
    $this->post(route('admin.admins.store'), [
        'name' => 'New Admin', 'email' => 'new@example.test', 'password' => 'new-password', 'password_confirmation' => 'new-password',
    ])->assertForbidden();

    $this->actingAs($super, 'admin');
    $this->get(route('admin.admins.index'))->assertOk()->assertSee('Add admin');
    $this->get(route('admin.admins.create'))->assertOk();
    $this->post(route('admin.admins.store'), [
        'name' => 'New Admin', 'email' => 'new@example.test', 'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
    $this->post(route('admin.admins.store'), [
        'name' => 'New Admin', 'email' => 'NEW@example.test', 'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect();
    $new = Admin::where('email', 'new@example.test')->firstOrFail();
    expect($new->is_super_admin)->toBeFalse()
        ->and($new->is_active)->toBeTrue()
        ->and(Hash::check('new-password', $new->password))->toBeTrue();

    $this->patch(route('admin.admins.role', $new))->assertRedirect();
    expect($new->fresh()->is_super_admin)->toBeTrue();
    $this->patch(route('admin.admins.status', $new))->assertRedirect();
    expect($new->fresh()->is_active)->toBeFalse();
    $this->patch(route('admin.admins.status', $new))->assertRedirect();
    expect($new->fresh()->is_active)->toBeTrue();

    $this->patch(route('admin.admins.status', $super))->assertForbidden();
    $this->patch(route('admin.admins.role', $super))->assertForbidden();
});

test('disabled admins cannot sign in or continue an open session', function () {
    $this->withoutVite();
    $admin = Admin::create(['name' => 'Disabled', 'email' => 'disabled@example.test', 'password' => 'password123']);
    $admin->update(['is_active' => false]);

    $this->post(route('admin.login.attempt'), [
        'email' => 'disabled@example.test', 'password' => 'password123',
    ])->assertSessionHasErrors('email');
    $this->assertGuest('admin');

    $admin->update(['is_active' => true]);
    $this->actingAs($admin, 'admin');
    $this->get(route('admin.dashboard'))->assertOk();
    $admin->update(['is_active' => false]);
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    $this->assertGuest('admin');
});
