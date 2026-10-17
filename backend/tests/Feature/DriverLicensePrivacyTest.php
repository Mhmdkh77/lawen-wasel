<?php

use App\Models\Admin;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('uploaded driver licenses are private and require driver or admin authentication', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::create([
        'name' => 'License Tester',
        'email' => 'license@example.test',
        'password' => bcrypt('password'),
        'phone' => '70000000',
        'role' => 'driver',
    ]);
    $driver = Driver::create(['user_id' => $user->id, 'is_verified' => false]);

    $this->getJson('/api/user/driver-license')->assertUnauthorized();
    $this->get(route('admin.drivers.license', $user))->assertRedirect('/login');

    Sanctum::actingAs($user);
    $this->put('/api/user', [
        'driver_license' => UploadedFile::fake()->create('license.pdf', 10, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertOk();

    $path = $driver->fresh()->driver_license;
    expect($path)->toStartWith('driver_licenses/');
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);

    $this->postJson('/api/user')
        ->assertOk()
        ->assertJsonPath('driver_license_url', route('api.user.driver-license'));
    $this->get('/api/user/driver-license')->assertOk();

    $admin = Admin::create([
        'name' => 'License Admin',
        'email' => 'license-admin@example.test',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($admin, 'admin')
        ->get(route('admin.drivers.license', $user))
        ->assertOk();
});

test('the license migration moves older uploads but keeps public demo illustrations', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('public')->put('driver_licenses/old-upload.jpg', 'private document');
    Storage::disk('public')->put('driver_licenses/dl1.jpg', 'demo illustration');

    $migration = require database_path('migrations/2026_10_17_000000_move_uploaded_driver_licenses_to_private_storage.php');
    $migration->up();

    Storage::disk('local')->assertExists('driver_licenses/old-upload.jpg');
    Storage::disk('public')->assertMissing('driver_licenses/old-upload.jpg');
    Storage::disk('public')->assertExists('driver_licenses/dl1.jpg');
});
