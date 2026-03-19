<?php

$content = file_get_contents('tests/Feature/Api/V1/LogbookTest.php');

$oldSubmit = <<<'OLD'
    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/submit", [
        'gps_location_end' => 'loc2',
        'gambar_bukti' => ['http://example.com/img.jpg'],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED')
        ->assertJsonPath('gambar_bukti.0', 'http://example.com/img.jpg');
OLD;

$newSubmit = <<<'NEW'
    \Illuminate\Support\Facades\Storage::fake('public');
    $file = \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg');

    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/submit", [
        'gps_location_end' => 'loc2',
        'gambar_bukti' => [$file, 'http://example.com/img.jpg'],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED')
        ->assertJsonPath('gambar_bukti.1', 'http://example.com/img.jpg');
    
    $this->assertStringContainsString('/storage/proofs/', $response->json('gambar_bukti.0'));
NEW;

$content = str_replace($oldSubmit, $newSubmit, $content);

file_put_contents('tests/Feature/Api/V1/LogbookTest.php', $content);

echo "Updated LogbookTest\n";
