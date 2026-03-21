<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('admin can update staff profile fields', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $staff = User::factory()->create(['role' => 'Staff']);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/users/{$staff->id}", [
        'nik' => '1234567890123456',
        'npwp' => '12.345.678.9-012.345',
        'alamat' => 'Jl. Sudirman No. 1, Jakarta',
        'status_kawin' => 'Kawin',
        'tempat_lahir' => 'Surabaya',
        'tanggal_lahir' => '1990-05-15',
        'riwayat_pendidikan' => [
            ['jenjang' => 'S1', 'jurusan' => 'Teknik Informatika', 'institusi' => 'ITS', 'tahun' => 2012],
        ],
        'riwayat_karir' => [
            ['jabatan' => 'Software Engineer', 'perusahaan' => 'PT Maju Jaya', 'periode' => '2012-2020'],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.nik', '1234567890123456')
        ->assertJsonPath('data.npwp', '12.345.678.9-012.345')
        ->assertJsonPath('data.alamat', 'Jl. Sudirman No. 1, Jakarta')
        ->assertJsonPath('data.status_kawin', 'Kawin')
        ->assertJsonPath('data.tempat_lahir', 'Surabaya')
        ->assertJsonPath('data.tanggal_lahir', '1990-05-15');

    $updated = $staff->fresh();
    expect($updated->nik)->toBe('1234567890123456');
    expect($updated->npwp)->toBe('12.345.678.9-012.345');
    expect($updated->alamat)->toBe('Jl. Sudirman No. 1, Jakarta');
    expect($updated->status_kawin)->toBe('Kawin');
    expect($updated->tempat_lahir)->toBe('Surabaya');
});

test('admin can upload foto when updating staff profile', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $staff = User::factory()->create(['role' => 'Staff', 'foto' => null]);

    Sanctum::actingAs($admin);

    $response = $this->put("/api/v1/users/{$staff->id}", [
        'foto' => UploadedFile::fake()->create('staff-photo.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertOk();

    $updated = $staff->fresh();
    expect($updated->foto)->not->toBeNull();
    Storage::disk('public')->assertExists($updated->foto);
});

test('admin updating staff foto replaces existing foto', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $existingPath = 'profile-photos/old-photo.jpg';
    Storage::disk('public')->put($existingPath, 'fake-image-content');
    $staff = User::factory()->create(['role' => 'Staff', 'foto' => $existingPath]);

    Sanctum::actingAs($admin);

    $response = $this->put("/api/v1/users/{$staff->id}", [
        'foto' => UploadedFile::fake()->create('new-photo.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertOk();

    $updated = $staff->fresh();
    expect($updated->foto)->not->toBe($existingPath);
    Storage::disk('public')->assertMissing($existingPath);
    Storage::disk('public')->assertExists($updated->foto);
});

test('response includes foto_url as full url', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $staff = User::factory()->create(['role' => 'Staff', 'foto' => null]);

    Sanctum::actingAs($admin);

    $this->put("/api/v1/users/{$staff->id}", [
        'foto' => UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
    ])->assertOk();

    $updated = $staff->fresh();
    $response = $this->getJson("/api/v1/users/{$staff->id}");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['foto', 'foto_url']]);

    $data = $response->json('data');
    expect($data['foto'])->not->toBeNull();
    expect($data['foto_url'])->toContain($updated->foto);
});

test('non-privileged staff cannot update other users profile fields', function () {
    $staff = User::factory()->create(['role' => 'Staff']);
    $otherStaff = User::factory()->create(['role' => 'Staff']);

    Sanctum::actingAs($staff);

    $response = $this->putJson("/api/v1/users/{$otherStaff->id}", [
        'nik' => '9999999999999999',
        'alamat' => 'Alamat Tidak Sah',
    ]);

    $response->assertForbidden();
});

test('superadmin can update staff profile fields', function () {
    $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);
    $staff = User::factory()->create(['role' => 'Staff']);

    Sanctum::actingAs($superAdmin);

    $response = $this->putJson("/api/v1/users/{$staff->id}", [
        'nik' => '9876543210987654',
        'alamat' => 'Jl. Thamrin No. 10',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.nik', '9876543210987654')
        ->assertJsonPath('data.alamat', 'Jl. Thamrin No. 10');
});

test('user resource foto_url is null when foto is null', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $staff = User::factory()->create(['role' => 'Staff', 'foto' => null]);

    Sanctum::actingAs($admin);

    $response = $this->getJson("/api/v1/users/{$staff->id}");

    $response->assertOk()
        ->assertJsonPath('data.foto', null)
        ->assertJsonPath('data.foto_url', null);
});
