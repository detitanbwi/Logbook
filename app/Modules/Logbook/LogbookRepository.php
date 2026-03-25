<?php

namespace App\Modules\Logbook;

use App\Models\Logbook;
use App\Models\LogbookItem;
use App\Models\LogbookReview;
use App\Models\LogbookAttachment;

class LogbookRepository
{
    public function create(array $data)
    {
        return Logbook::create($data);
    }

    public function createItem(array $data)
    {
        return LogbookItem::create($data);
    }

    public function createAttachment(array $data)
    {
        return LogbookAttachment::create($data);
    }

    public function createReview(array $data)
    {
        return LogbookReview::create($data);
    }

    public function getMyLogbooks($employeeId)
    {
        return Logbook::where('employee_id', $employeeId)
            ->with(['items.kpi', 'latestReview'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getPendingLogbooks($supervisorId)
    {
        return Logbook::where('supervisor_id', $supervisorId)
            ->where('status', 'pending')
            ->with(['employee', 'items.kpi'])
            ->orderBy('created_at', 'asc')
            ->paginate(10);
    }

    public function find($id)
    {
        return Logbook::with(['employee', 'supervisor', 'items.kpi', 'reviews.reviewer', 'attachments'])
            ->findOrFail($id);
    }

    public function findItem($id)
    {
        return LogbookItem::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $logbook = Logbook::findOrFail($id);
        $logbook->update($data);
        return $logbook;
    }

    public function updateItem($id, array $data)
    {
        $item = LogbookItem::findOrFail($id);
        $item->update($data);
        return $item;
    }
}
