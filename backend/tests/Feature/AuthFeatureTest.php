<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('user can login', function () {
    User::factory()->create([
        'npp' => '12345678',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'npp' => '12345678',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access_token', 'token_type', 'user']);
});

test('user cannot login with nip payload alias', function () {
    User::factory()->create([
        'npp' => '12345678',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'nip' => '12345678',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'npp' => '12345678',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'npp' => '12345678',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('user can get me', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJson(['user' => ['id' => $user->id]]);
});

test('user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);
});

test('user can change password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/change-password', [
        'old_password' => 'oldpassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200);
    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('staff can update own profile', function () {
    $staff = User::factory()->create([
        'role' => 'Staff',
        'alamat' => 'Alamat Lama',
        'tempat_lahir' => 'Jakarta',
    ]);

    Sanctum::actingAs($staff);

    $response = $this->putJson('/api/v1/auth/profile', [
        'alamat' => 'Alamat Baru Staff',
        'tempat_lahir' => 'Bandung',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Profil berhasil diperbarui')
        ->assertJsonPath('data.alamat', 'Alamat Baru Staff')
        ->assertJsonPath('data.tempat_lahir', 'Bandung');
});

test('manager can update own profile', function () {
    $manager = User::factory()->create([
        'role' => 'Staff',
        'alamat' => 'Alamat Lama Manager',
    ]);
    User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    Sanctum::actingAs($manager);

    $response = $this->putJson('/api/v1/auth/profile', [
        'alamat' => 'Alamat Baru Manager',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.alamat', 'Alamat Baru Manager');
});

test('admin can update own profile', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'alamat' => 'Alamat Lama Admin',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/v1/auth/profile', [
        'alamat' => 'Alamat Baru Admin',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.alamat', 'Alamat Baru Admin');
});

test('superadmin can update own profile', function () {
    $superAdmin = User::factory()->create([
        'role' => 'SuperAdmin',
        'alamat' => 'Alamat Lama SuperAdmin',
    ]);

    Sanctum::actingAs($superAdmin);

    $response = $this->putJson('/api/v1/auth/profile', [
        'alamat' => 'Alamat Baru SuperAdmin',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.alamat', 'Alamat Baru SuperAdmin');
});

test('user can upload profile photo', function () {
    Storage::fake('public');
    $user = User::factory()->create(['role' => 'Staff']);
    Sanctum::actingAs($user);

    $response = $this->put('/api/v1/auth/profile', [
        'foto' => UploadedFile::fake()->image('profile.jpg', 200, 200),
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Profil berhasil diperbarui');

    $updatedUser = $user->fresh();
    expect($updatedUser?->foto)->not->toBeNull();
    Storage::disk('public')->assertExists($updatedUser->foto);
});

test('user cannot update protected fields npp and role via profile endpoint', function () {
    $user = User::factory()->create([
        'role' => 'Staff',
        'npp' => '199003032010012003',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/auth/profile', [
        'npp' => '999999999999999999',
        'role' => 'SuperAdmin',
        'alamat' => 'Alamat Aman',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.alamat', 'Alamat Aman');

    $updatedUser = $user->fresh();
    expect($updatedUser?->npp)->toBe('199003032010012003');
    expect($updatedUser?->role)->toBe('Staff');
});

test('profile photo validation rejects non-image files', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->withHeaders(['Accept' => 'application/json'])->put('/api/v1/auth/profile', [
        'foto' => UploadedFile::fake()->create('not-image.pdf', 100, 'application/pdf'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('foto');
});

test('profile photo validation rejects oversized image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->withHeaders(['Accept' => 'application/json'])->put('/api/v1/auth/profile', [
        'foto' => UploadedFile::fake()->image('large.jpg')->size(6000),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('foto');
});
