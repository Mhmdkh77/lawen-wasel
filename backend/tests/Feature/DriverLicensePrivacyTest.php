<?php

use App\Models\Admin;
use App\Models\Driver;
use App\Models\User;
use App\Services\DemoDriverLicenseFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('uploaded driver licenses are private and require driver or admin authentication', function () {
    Storage::fake('local');
    Storage::fake('public');
    DemoDriverLicenseFixtures::install();

    $user = User::create([
        'name' => 'License Tester',
        'email' => 'license@example.test',
        'password' => bcrypt('password'),
        'phone' => '70000000',
        'role' => 'driver',
    ]);
    $sharedDemoPath = DemoDriverLicenseFixtures::paths()[0];
    $driver = Driver::create(['user_id' => $user->id, 'is_verified' => false, 'driver_license' => $sharedDemoPath]);
    $otherUser = User::create([
        'name' => 'Other Demo Driver',
        'email' => 'other-license@example.test',
        'password' => bcrypt('password'),
        'phone' => '70000001',
        'role' => 'driver',
    ]);
    Driver::create(['user_id' => $otherUser->id, 'is_verified' => false, 'driver_license' => $sharedDemoPath]);

    $this->getJson('/api/user/driver-license')->assertUnauthorized();
    $this->get(route('admin.drivers.license', $user))->assertRedirect('/login');

    Sanctum::actingAs($user);
    $this->call('PUT', '/api/user', [], [], [], [
        'CONTENT_TYPE' => 'multipart/form-data; boundary=ignored',
        'HTTP_ACCEPT' => 'application/json',
    ], '--ignored--')->assertStatus(415);
    $this->post('/api/user', [
        '_method' => 'PUT',
        'driver_license' => UploadedFile::fake()->create('license.pdf', 10, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertOk();

    $path = $driver->fresh()->driver_license;
    expect($path)->toStartWith('driver_licenses/');
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
    Storage::disk('local')->assertExists($sharedDemoPath);

    Sanctum::actingAs($otherUser);
    $this->get('/api/user/driver-license')->assertOk();
    Sanctum::actingAs($user);

    $this->postJson('/api/user')
        ->assertOk()
        ->assertJsonPath('driver_license_url', route('api.user.driver-license'));
    $this->get('/api/user/driver-license')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $this->post('/api/user', [
        '_method' => 'PUT',
        'driver_license' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertOk();
    $replacementPath = $driver->fresh()->driver_license;
    expect($replacementPath)->not->toBe($path);
    Storage::disk('local')->assertMissing($path);
    Storage::disk('local')->assertExists($replacementPath);
    Storage::disk('local')->assertExists($sharedDemoPath);

    $driver->update(['is_verified' => true]);
    $this->post('/api/user', [
        '_method' => 'PUT',
        'driver_license' => UploadedFile::fake()->create('another.pdf', 10, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertForbidden();
    expect($driver->fresh()->driver_license)->toBe($replacementPath);

    $admin = Admin::create([
        'name' => 'License Admin',
        'email' => 'license-admin@example.test',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($admin, 'admin')
        ->get(route('admin.drivers.license', $user))
        ->assertOk();
    $this->get(route('admin.drivers.show', $user))
        ->assertOk()
        ->assertSee('data-license-type="pdf"', false);
});

test('the license migrations move older uploads and demo illustrations out of public storage', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('public')->put('driver_licenses/old-upload.jpg', 'private document');
    Storage::disk('public')->put('driver_licenses/dl1.jpg', file_get_contents(database_path('seeders/assets/driver_licenses/dl1.jpg')));

    $migration = require database_path('migrations/2026_10_17_000000_move_uploaded_driver_licenses_to_private_storage.php');
    $migration->up();

    Storage::disk('local')->assertExists('driver_licenses/old-upload.jpg');
    Storage::disk('public')->assertMissing('driver_licenses/old-upload.jpg');
    Storage::disk('public')->assertExists('driver_licenses/dl1.jpg');

    $demoMigration = require database_path('migrations/2026_10_17_000001_move_demo_driver_licenses_to_private_storage.php');
    $demoMigration->up();

    foreach (DemoDriverLicenseFixtures::paths() as $path) {
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }
});

test('the driver factory chooses only bundled private demo licenses', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('local')->put('driver_licenses/user-upload.pdf', 'private document');

    $path = Driver::factory()->definition()['driver_license'];

    expect($path)->toBeIn(DemoDriverLicenseFixtures::paths());
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
});
