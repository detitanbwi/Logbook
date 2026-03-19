<?php

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'STAFF']);
    $this->staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $this->manager->id]);
});

test('staff can update end_kerja on DRAFT logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => null,
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'end_kerja' => '17:00',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil diperbarui');

    $logbook->refresh();
    expect($logbook->end_kerja)->toBe('17:00');
});

test('staff can update start_kerja on DRAFT logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'start_kerja' => '09:00',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil diperbarui');

    $logbook->refresh();
    expect($logbook->start_kerja)->toBe('09:00');
});

test('staff can update tanggal on DRAFT logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'tanggal' => '2026-03-21',
    ]);

    $response->assertStatus(200);

    $logbook->refresh();
    expect($logbook->tanggal->format('Y-m-d'))->toBe('2026-03-21');
});

test('staff cannot update logbook with start_kerja before 07:00', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'start_kerja' => '06:00',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Jam mulai minimal 07:00');
});

test('staff cannot update logbook with end_kerja before start_kerja', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'end_kerja' => '07:30',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Jam selesai harus lebih besar dari jam mulai');
});

test('staff cannot update SUBMITTED logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'end_kerja' => '18:00',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Hanya logbook DRAFT yang dapat diupdate');
});

test('staff cannot update ACCEPTED logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'ACCEPTED',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'end_kerja' => '18:00',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Hanya logbook DRAFT yang dapat diupdate');
});

test('staff cannot update another users logbook', function () {
    $otherStaff = User::factory()->create(['role' => 'STAFF']);
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $otherStaff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'end_kerja' => '17:00',
    ]);

    $response->assertStatus(403);
});

test('staff can update multiple fields at once', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'lokasi' => 'Office A',
        'status' => 'DRAFT',
    ]);

    $response = $this->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'tanggal' => '2026-03-21',
        'start_kerja' => '09:00',
        'end_kerja' => '18:00',
        'lokasi' => 'Office B',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil diperbarui');

    $logbook->refresh();
    expect($logbook->tanggal->format('Y-m-d'))->toBe('2026-03-21');
    expect($logbook->start_kerja)->toBe('09:00');
    expect($logbook->end_kerja)->toBe('18:00');
    expect($logbook->lokasi)->toBe('Office B');
});
