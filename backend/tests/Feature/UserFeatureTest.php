<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin can see all users', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    User::factory()->count(3)->create();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonCount(4, 'data'); // Admin + 3 users
});

test('manager can only see their subordinates', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $subordinate = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $otherUser = User::factory()->create(['role' => 'STAFF']);

    Sanctum::actingAs($manager);

    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $subordinate->id])
        ->assertJsonMissing(['id' => $otherUser->id])
        ->assertJsonMissing(['id' => $manager->id]); // Depending on how query is built, if manager is manager_id of themselves or not. Wait, the code says where('manager_id', $user->id), so manager is missing.
});

test('admin can create user', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'New User',
        'email' => 'new@user.com',
        'nip' => '99999999',
        'role' => 'STAFF',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['name' => 'New User']);
});

test('admin can update user', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $user = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/users/{$user->id}", [
        'name' => 'Updated User',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated User']);
});

test('admin can reset user password', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $user = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/users/{$user->id}/reset-password", [
        'new_password' => 'newpassword123',
    ]);

    $response->assertStatus(200);
});

test('admin can delete user', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $user = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($user);
});
