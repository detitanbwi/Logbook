<?php

namespace Tests\Feature\Api\V1;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('staff can start logbook and it copies active kpis', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    UserKpiAssignment::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $manager->id,
    ]);

    $response = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'gps_location_start' => '-6.200000,106.816666',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'DRAFT')
        ->assertJsonPath('lokasi_start', '-6.200000,106.816666')
        ->assertJsonCount(1, 'kpi_details');

    $this->assertDatabaseHas('logbooks', [
        'user_id' => $staff->id,
        'status' => 'DRAFT',
    ]);

    $logbookId = $response->json('id');

    $this->assertDatabaseHas('logbook_kpi_details', [
        'logbook_id' => $logbookId,
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'is_finished' => false,
    ]);
});

it('staff can toggle kpi in draft logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'DRAFT',
        'start_kerja' => now(),
        'lokasi_start' => 'loc',
    ]);

    $kpi = KpiMaster::factory()->create();
    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'is_finished' => false,
    ]);

    $response = $this->actingAs($staff)->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/toggle", [
        'is_finished' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('is_finished', true);

    $this->assertDatabaseHas('logbook_kpi_details', [
        'id' => $detail->id,
        'is_finished' => true,
    ]);
});

it('staff can submit logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'DRAFT',
        'start_kerja' => now(),
        'lokasi_start' => 'loc',
    ]);

    Storage::fake('public');
    $file = UploadedFile::fake()->image('bukti.jpg');

    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/submit", [
        'gps_location_end' => 'loc2',
        'gambar_bukti' => [$file],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED');

    $gambarBukti = $response->json('gambar_bukti');
    $this->assertIsArray($gambarBukti);
    $this->assertCount(1, $gambarBukti);

    $hasFile = false;
    foreach ($gambarBukti as $bukti) {
        if (str_contains($bukti, '/storage/proofs/')) {
            $hasFile = true;
        }
    }
    $this->assertTrue($hasFile);

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'SUBMITTED',
        'lokasi_end' => 'loc2',
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $manager->id,
        'title' => 'Logbook Menunggu Review',
    ]);
});

it('manager can rate submitted logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'start_kerja' => now(),
        'end_kerja' => now(),
        'lokasi_start' => 'loc',
        'lokasi_end' => 'loc',
    ]);

    $response = $this->actingAs($manager)->putJson("/api/v1/logbooks/{$logbook->id}/rate", [
        'rating' => 4,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'REVIEWED')
        ->assertJsonPath('rating', 4);

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'REVIEWED',
        'rating' => 4,
        'reviewed_by' => $manager->id,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'title' => 'Logbook Reviewed',
    ]);
});
