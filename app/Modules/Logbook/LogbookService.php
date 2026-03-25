<?php

namespace App\Modules\Logbook;

use App\Models\User;
use App\Models\Kpi;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LogbookService
{
    protected $repository;

    public function __construct(LogbookRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeLogbook(User $employee, array $data)
    {
        // 1. Validasi KPI ownership
        foreach ($data['kpi_items'] as $item) {
            $hasKpi = $employee->kpis()->where('kpi_id', $item['kpi_id'])->exists();
            if (!$hasKpi) {
                throw new \Exception("KPI ID {$item['kpi_id']} tidak dimiliki oleh karyawan.", 422);
            }
        }

        // 2. Process Main Photo
        $mainPhotoPath = $this->processAndStoreImage($data['main_photo'], 'logbooks/photos');

        // 3. Create Logbook
        $logbookData = [
            'employee_id' => $employee->id,
            'supervisor_id' => $employee->supervisor_id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'main_photo_path' => $mainPhotoPath,
            'daily_report' => $data['daily_report'],
            'status' => 'pending',
        ];

        $logbook = $this->repository->create($logbookData);

        // 4. Create Items
        foreach ($data['kpi_items'] as $item) {
            $this->repository->createItem([
                'logbook_id' => $logbook->id,
                'kpi_id' => $item['kpi_id'],
                'work_description' => $item['work_description'],
            ]);
        }

        // 5. Process Attachments
        if (isset($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $path = $file->store('logbooks/attachments', 'public');
                $this->repository->createAttachment([
                    'logbook_id' => $logbook->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        return $logbook;
    }

    public function reviewLogbook(User $supervisor, $id, array $data)
    {
        $logbook = $this->repository->find($id);

        if ($logbook->supervisor_id !== $supervisor->id) {
            throw new \Exception("Anda tidak memiliki akses untuk mereview logbook ini.", 403);
        }

        if ($logbook->status !== 'pending') {
            throw new \Exception("Logbook sudah diproses.", 422);
        }

        // Update scores
        foreach ($data['kpi_scores'] as $scoreData) {
            $item = $this->repository->findItem($scoreData['logbook_item_id']);
            
            // Validasi score <= target KPI
            $kpi = Kpi::find($item->kpi_id);
            if ($scoreData['score'] > $kpi->target) {
                throw new \Exception("Score untuk KPI '{$kpi->description}' melebihi target ({$kpi->target}).", 422);
            }

            $this->repository->updateItem($item->id, ['score' => $scoreData['score']]);
        }

        // Create Review
        $this->repository->createReview([
            'logbook_id' => $logbook->id,
            'reviewer_id' => $supervisor->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'reviewed_at' => now(),
        ]);

        // Update Status
        return $this->repository->update($id, ['status' => 'approved']);
    }

    public function rejectLogbook(User $supervisor, $id, $comment = null)
    {
        $logbook = $this->repository->find($id);

        if ($logbook->supervisor_id !== $supervisor->id) {
            throw new \Exception("Anda tidak memiliki akses untuk mereview logbook ini.", 403);
        }

        // Status -> rejected
        return $this->repository->update($id, [
            'status' => 'rejected'
        ]);
        
        // Optional: create a review record with rejected status if needed
    }

    protected function processAndStoreImage($file, $folder)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        // Resize if too large
        $image->scale(width: 1024);

        // Compress and Convert to WebP
        $filename = uniqid() . '.webp';
        $path = $folder . '/' . $filename;
        
        // Save to public storage
        Storage::disk('public')->put($path, (string) $image->toWebp(70));

        return $path;
    }
}
