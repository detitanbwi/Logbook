<?php

namespace App\Modules\Kpi;

use App\Models\Kpi;

class KpiRepository
{
    public function getAll()
    {
        return Kpi::latest()->get();
    }

    public function findById($id)
    {
        return Kpi::findOrFail($id);
    }

    public function create(array $data)
    {
        return Kpi::create($data);
    }

    public function update($id, array $data)
    {
        $kpi = $this->findById($id);
        $kpi->update($data);
        return $kpi;
    }

    public function delete($id)
    {
        return $this->findById($id)->delete();
    }
}
