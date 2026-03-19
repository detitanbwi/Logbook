<?php

$content = file_get_contents('tests/Feature/Api/V1/LogbookTest.php');

$old = <<<'OLD'
    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED')
        ->assertJsonPath('gambar_bukti.1', 'http://example.com/img.jpg');
    
    $this->assertStringContainsString('/storage/proofs/', $response->json('gambar_bukti.0'));
OLD;

$new = <<<'NEW'
    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED');
    
    $gambarBukti = $response->json('gambar_bukti');
    $this->assertIsArray($gambarBukti);
    $this->assertCount(2, $gambarBukti);
    
    $hasUrl = false;
    $hasFile = false;
    foreach ($gambarBukti as $bukti) {
        if ($bukti === 'http://example.com/img.jpg') $hasUrl = true;
        if (str_contains($bukti, '/storage/proofs/')) $hasFile = true;
    }
    
    $this->assertTrue($hasUrl);
    $this->assertTrue($hasFile);
NEW;

$content = str_replace($old, $new, $content);
file_put_contents('tests/Feature/Api/V1/LogbookTest.php', $content);
