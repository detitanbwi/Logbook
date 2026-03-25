<?php

namespace App\Modules\Kpi;

class KpiService
{
    protected $repository;

    public function __construct(KpiRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listKpis()
    {
        return $this->repository->getAll();
    }

    public function getKpi($id)
    {
        return $this->repository->findById($id);
    }

    public function createKpi(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateKpi($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteKpi($id)
    {
        return $this->repository->delete($id);
    }
}
