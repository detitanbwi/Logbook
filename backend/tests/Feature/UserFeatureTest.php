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
        'nama' => 'New User',
        'email' => 'new@user.com',
        'npp' => '99999999',
        'role' => 'STAFF',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['nama' => 'New User']);
});

test('admin cannot create admin or superadmin user', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $createAdmin = $this->postJson('/api/v1/users', [
        'nama' => 'Blocked Admin',
        'email' => 'blocked-admin@user.com',
        'npp' => '11112222',
        'role' => 'Admin',
        'password' => 'password123',
    ]);

    $createAdmin->assertForbidden();

    $createSuper = $this->postJson('/api/v1/users', [
        'nama' => 'Blocked Super',
        'email' => 'blocked-super@user.com',
        'npp' => '11113333',
        'role' => 'SuperAdmin',
        'password' => 'password123',
    ]);

    $createSuper->assertForbidden();
});

test('admin can update user', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $user = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/users/{$user->id}", [
        'nama' => 'Updated User',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['nama' => 'Updated User']);
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

test('manager can list their own subordinates', function () {
    $manager = User::factory()->create(['role' => 'Staff']);
    $subordinateA = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);
    $subordinateB = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);
    User::factory()->create(['role' => 'Staff']);

    Sanctum::actingAs($manager);

    $response = $this->getJson("/api/v1/users/{$manager->id}/subordinates");

    $response->assertOk()
        ->assertJsonFragment(['id' => $subordinateA->id])
        ->assertJsonFragment(['id' => $subordinateB->id]);
});

test('staff without subordinates cannot access subordinates endpoint', function () {
    $staff = User::factory()->create(['role' => 'Staff']);
    Sanctum::actingAs($staff);

    $this->getJson("/api/v1/users/{$staff->id}/subordinates")
        ->assertForbidden();
});

test('manager cannot access another users subordinates endpoint', function () {
    $manager = User::factory()->create(['role' => 'Staff']);
    User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $otherManager = User::factory()->create(['role' => 'Staff']);
    User::factory()->create(['role' => 'Staff', 'manager_id' => $otherManager->id]);

    Sanctum::actingAs($manager);

    $this->getJson("/api/v1/users/{$otherManager->id}/subordinates")
        ->assertForbidden();
});

test('admin can view any users subordinates', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $subordinate = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    Sanctum::actingAs($admin);

    $this->getJson("/api/v1/users/{$manager->id}/subordinates")
        ->assertOk()
        ->assertJsonFragment(['id' => $subordinate->id]);
});

test('superadmin can view any users subordinates', function () {
    $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $subordinate = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    Sanctum::actingAs($superAdmin);

    $this->getJson("/api/v1/users/{$manager->id}/subordinates")
        ->assertOk()
        ->assertJsonFragment(['id' => $subordinate->id]);
});
