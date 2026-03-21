<?php

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'MANAGER']);
    $this->staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $this->manager->id]);
    $kpi = KpiMaster::factory()->create();
    UserKpiAssignment::create([
        'user_id' => $this->staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $this->manager->id,
    ]);
});

test('staff can start logbook with gps coordinates', function () {
    $response = $this->actingAs($this->staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-21',
        'start_kerja' => '08:00',
        'lokasi' => 'Jakarta Selatan',
        'lokasi_lat' => -6.21462882,
        'lokasi_lng' => 106.84513327,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.lokasi', 'Jakarta Selatan')
        ->assertJsonPath('data.lokasi_lat', -6.21462882)
        ->assertJsonPath('data.lokasi_lng', 106.84513327);

    $this->assertDatabaseHas('logbooks', [
        'user_id' => $this->staff->id,
        'lokasi_lat' => -6.21462882,
        'lokasi_lng' => 106.84513327,
    ]);
});

test('staff can start logbook without gps coordinates', function () {
    $response = $this->actingAs($this->staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-21',
        'start_kerja' => '08:00',
        'lokasi' => 'Jakarta Selatan',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.lokasi_lat', null)
        ->assertJsonPath('data.lokasi_lng', null);
});

test('start logbook rejects invalid latitude', function () {
    $response = $this->actingAs($this->staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-21',
        'start_kerja' => '08:00',
        'lokasi' => 'Jakarta Selatan',
        'lokasi_lat' => 91.0,
        'lokasi_lng' => 106.0,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lokasi_lat']);
});

test('start logbook rejects invalid longitude', function () {
    $response = $this->actingAs($this->staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-21',
        'start_kerja' => '08:00',
        'lokasi' => 'Jakarta Selatan',
        'lokasi_lat' => -6.2,
        'lokasi_lng' => 181.0,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lokasi_lng']);
});

test('staff can update logbook coordinates', function () {
    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'status' => 'SUBMITTED',
        'lokasi_lat' => null,
        'lokasi_lng' => null,
    ]);

    $response = $this->actingAs($this->staff)->patchJson("/api/v1/logbooks/{$logbook->id}", [
        'lokasi_lat' => -6.17511000,
        'lokasi_lng' => 106.82715000,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.lokasi_lat', -6.17511)
        ->assertJsonPath('data.lokasi_lng', 106.82715);

    $logbook->refresh();
    expect((float) $logbook->lokasi_lat)->toBe(-6.17511000)
        ->and((float) $logbook->lokasi_lng)->toBe(106.82715000);
});

test('logbook show returns coordinates', function () {
    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'lokasi_lat' => -6.21462882,
        'lokasi_lng' => 106.84513327,
    ]);

    $response = $this->actingAs($this->staff)->getJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.lokasi_lat', -6.21462882)
        ->assertJsonPath('data.lokasi_lng', 106.84513327);
});
