<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
