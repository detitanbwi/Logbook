<?php

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'ADMIN']);
    $this->manager = User::factory()->create(['role' => 'MANAGER']);
    $this->staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $this->manager->id]);

    $this->otherStaff = User::factory()->create(['role' => 'STAFF']); // No manager

    Carbon::setTestNow('2023-10-15 12:00:00'); // Mid month
});

it('allows admin to access admin dashboard', function () {
    // Create some data
    User::factory()->count(2)->create(); // total users

    Logbook::factory()->create(['start_kerja' => now(), 'status' => 'SUBMITTED', 'user_id' => $this->staff->id]);
    Logbook::factory()->create(['start_kerja' => now(), 'status' => 'DRAFT', 'user_id' => $this->staff->id]);
    Logbook::factory()->create(['start_kerja' => now()->subMonth(), 'created_at' => now()->subMonth(), 'status' => 'SUBMITTED', 'user_id' => $this->staff->id]); // old

    actingAs($this->admin)->getJson('/api/v1/dashboard/admin')
        ->assertOk()
        ->assertJson([
            'data' => [
                'total_logbooks_this_month' => 2,
                'pending_logbooks_count' => 2, // submitted across all time
            ],
        ]);
});

it('prevents non-admin from accessing admin dashboard', function () {
    actingAs($this->manager)->getJson('/api/v1/dashboard/admin')->assertForbidden();
    actingAs($this->staff)->getJson('/api/v1/dashboard/admin')->assertForbidden();
});

it('allows manager to access manager dashboard', function () {
    // Subordinate logbook
    $logbook1 = Logbook::factory()->create(['user_id' => $this->staff->id, 'tanggal' => now()->toDateString(), 'start_kerja' => now(), 'status' => 'SUBMITTED']);
    $logbook2 = Logbook::factory()->create(['user_id' => $this->staff->id, 'tanggal' => now()->toDateString(), 'start_kerja' => now(), 'status' => 'DRAFT']);

    // Other staff (not subordinate)
    Logbook::factory()->create(['user_id' => $this->otherStaff->id, 'tanggal' => now()->toDateString(), 'start_kerja' => now(), 'status' => 'SUBMITTED']);

    $kpi1 = KpiMaster::factory()->create();
    $kpi2 = KpiMaster::factory()->create();

    LogbookKpiDetail::factory()->create([
        'logbook_id' => $logbook1->id,
        'kpi_id' => $kpi1->id,
        'target_angka' => 10,
        'capaian_angka' => 10,
    ]);
    LogbookKpiDetail::factory()->create([
        'logbook_id' => $logbook1->id,
        'kpi_id' => $kpi2->id,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);
    LogbookKpiDetail::factory()->create([
        'logbook_id' => $logbook2->id,
        'kpi_id' => $kpi1->id,
        'target_angka' => 10,
        'capaian_angka' => 10,
    ]);

    $response = actingAs($this->manager)->getJson('/api/v1/dashboard/manager');

    $response->assertOk()
        ->assertJsonPath('data.pending_logbooks_count', 1)
        ->assertJsonPath('data.subordinates.0.id', $this->staff->id)
        ->assertJsonPath('data.subordinates.0.target_angka_total', 30)
        ->assertJsonPath('data.subordinates.0.capaian_angka_total', 20)
        ->assertJsonPath('data.subordinates.0.completion_rate', 66.67);
});

it('prevents non-manager from accessing manager dashboard', function () {
    actingAs($this->admin)->getJson('/api/v1/dashboard/manager')->assertForbidden();
    actingAs($this->staff)->getJson('/api/v1/dashboard/manager')->assertForbidden();
});

it('allows staff to access staff dashboard', function () {
    $logbook1 = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'start_kerja' => now()->format('H:i:s'),
        'status' => 'ACCEPTED',
        'rating' => 4,
        'tanggal' => now()->toDateString(),
    ]);
    $logbook2 = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'start_kerja' => now()->format('H:i:s'),
        'status' => 'ACCEPTED',
        'rating' => 5,
        'tanggal' => now()->toDateString(),
    ]);

    $kpi1 = KpiMaster::factory()->create();
    LogbookKpiDetail::factory()->create(['logbook_id' => $logbook1->id, 'kpi_id' => $kpi1->id, 'target_angka' => 10, 'capaian_angka' => 10]);
    LogbookKpiDetail::factory()->create(['logbook_id' => $logbook1->id, 'kpi_id' => $kpi1->id, 'target_angka' => 10, 'capaian_angka' => 0]);
    LogbookKpiDetail::factory()->create(['logbook_id' => $logbook2->id, 'kpi_id' => $kpi1->id, 'target_angka' => 10, 'capaian_angka' => 0]);

    // Workdays passed on 2023-10-15:
    // Oct 1 is Sunday. Oct 2-6 (5), Oct 9-13 (5) = 10 workdays.
    // Oct 14 is Sat, 15 is Sun. So 10 workdays total so far.
    // The staff has 2 logbooks. Missed should be 10 - 2 = 8.

    $response = actingAs($this->staff)->getJson('/api/v1/dashboard/staff');

    $response->assertOk()
        ->assertJsonPath('data.personal_kpi_completion_rate', 33.33)
        ->assertJsonPath('data.missed_logbooks_count', 8)
        ->assertJsonPath('data.average_rating', 4.5);
});

it('prevents non-staff from accessing staff dashboard', function () {
    actingAs($this->admin)->getJson('/api/v1/dashboard/staff')->assertForbidden();
    actingAs($this->manager)->getJson('/api/v1/dashboard/staff')->assertForbidden();
});

it('allows admin and manager to view user KPI achievements', function () {
    $logbook = Logbook::factory()->create(['user_id' => $this->staff->id, 'tanggal' => now()->toDateString(), 'start_kerja' => now()]);
    $kpi = KpiMaster::factory()->create();
    LogbookKpiDetail::factory()->create(['logbook_id' => $logbook->id, 'kpi_id' => $kpi->id, 'target_angka' => 5, 'capaian_angka' => 5]);

    actingAs($this->admin)->getJson("/api/v1/users/{$this->staff->id}/kpi-achievements")
        ->assertOk()
        ->assertJsonPath('data.completed_kpi_details', 1);

    actingAs($this->manager)->getJson("/api/v1/users/{$this->staff->id}/kpi-achievements")
        ->assertOk()
        ->assertJsonPath('data.completed_kpi_details', 1);
});

it('prevents unauthorized access to user KPI achievements', function () {
    // Another manager trying to view this manager's staff
    $otherManager = User::factory()->create(['role' => 'MANAGER']);

    actingAs($otherManager)->getJson("/api/v1/users/{$this->staff->id}/kpi-achievements")
        ->assertForbidden();

    actingAs($this->staff)->getJson("/api/v1/users/{$this->staff->id}/kpi-achievements")
        ->assertForbidden();
});

it('allows admin and manager to initiate export', function () {
    actingAs($this->admin)->getJson('/api/v1/reports/export')
        ->assertOk()
        ->assertJsonStructure(['message', 'download_url']);

    actingAs($this->manager)->getJson('/api/v1/reports/export')
        ->assertOk();
});

it('prevents staff from exporting', function () {
    actingAs($this->staff)->getJson('/api/v1/reports/export')->assertForbidden();
});
