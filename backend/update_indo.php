<?php

$replacements = [
    "'message' => 'Only DRAFT logbooks can be submitted'" => "'message' => 'Hanya logbook DRAFT yang dapat disubmit'",
    "'message' => 'Data tidak ditemukan'" => "'message' => 'Data tidak ditemukan'", // already?
    "'message' => 'Successfully registered'" => "'message' => 'Berhasil mendaftar'",
    "'message' => 'Invalid credentials'" => "'message' => 'Kredensial tidak valid'",
    "'message' => 'Successfully logged out'" => "'message' => 'Berhasil logout'",
    "'message' => 'Successfully logged in'" => "'message' => 'Berhasil login'",
    "'message' => 'User not found'" => "'message' => 'Pengguna tidak ditemukan'",
    "'message' => 'User deleted successfully'" => "'message' => 'Pengguna berhasil dihapus'",
    "'message' => 'Invalid status transition'" => "'message' => 'Transisi status tidak valid'",
    "'title' => 'Logbook Submitted'" => "'title' => 'Logbook Menunggu Review'",
    "'message' => \"{\$user->name} has submitted their logbook.\"" => "'message' => \"{\$user->name} telah mensubmit logbook mereka.\"",
    "'title' => 'Logbook Approved'" => "'title' => 'Logbook Disetujui'",
    "'message' => 'Your logbook for ' . \$logbook->tanggal->toDateString() . ' has been approved.'" => "'message' => 'Logbook Anda untuk tanggal ' . \$logbook->tanggal->toDateString() . ' telah disetujui.'",
    "'title' => 'Logbook Rejected'" => "'title' => 'Logbook Ditolak'",
    "'message' => 'Your logbook for ' . \$logbook->tanggal->toDateString() . ' has been rejected.'" => "'message' => 'Logbook Anda untuk tanggal ' . \$logbook->tanggal->toDateString() . ' telah ditolak.'",
    "'title' => 'New KPI Assigned'" => "'title' => 'KPI Baru Ditugaskan'",
    "'message' => \"You have been assigned a new KPI: {\$assignment->masterKpi->name}.\"" => "'message' => \"Anda telah ditugaskan KPI baru: {\$assignment->masterKpi->name}.\"",
    "'message' => 'Invalid token'" => "'message' => 'Token tidak valid'",
    "'message' => 'Unauthenticated.'" => "'message' => 'Tidak terautentikasi.'",
];

$testReplacements = [
    "'message' => 'Only DRAFT logbooks can be submitted'" => "'message' => 'Hanya logbook DRAFT yang dapat disubmit'",
    "'message' => 'Successfully registered'" => "'message' => 'Berhasil mendaftar'",
    "'message' => 'Invalid credentials'" => "'message' => 'Kredensial tidak valid'",
    "'message' => 'Successfully logged out'" => "'message' => 'Berhasil logout'",
    "'message' => 'Successfully logged in'" => "'message' => 'Berhasil login'",
    "'message' => 'User not found'" => "'message' => 'Pengguna tidak ditemukan'",
    "'message' => 'User deleted successfully'" => "'message' => 'Pengguna berhasil dihapus'",
    "'message' => 'Invalid status transition'" => "'message' => 'Transisi status tidak valid'",
    "'title' => 'Logbook Submitted'" => "'title' => 'Logbook Menunggu Review'",
    "'title' => 'Logbook Approved'" => "'title' => 'Logbook Disetujui'",
    "'title' => 'Logbook Rejected'" => "'title' => 'Logbook Ditolak'",
    "'title' => 'New KPI Assigned'" => "'title' => 'KPI Baru Ditugaskan'",
];

function processDir($dir, $replacements)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($content !== $newContent) {
                file_put_contents($file->getPathname(), $newContent);
                echo 'Updated '.$file->getPathname()."\n";
            }
        }
    }
}

processDir(__DIR__.'/app/Http/Controllers', $replacements);
processDir(__DIR__.'/tests/Feature', $testReplacements);

echo "Done\n";
