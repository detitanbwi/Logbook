<?php

namespace App\Modules\Kpi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    protected $service;

    public function __construct(KpiService $service)
    {
        $this->service = $service;
    }

    protected function checkAccess()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized access to KPI management.');
        }
    }

    public function index()
    {
        $this->checkAccess();
        $kpis = $this->service->listKpis();
        return view('kpis.index', compact('kpis'));
    }

    public function create()
    {
        $this->checkAccess();
        return view('kpis.create');
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'description' => 'required|string',
            'target' => 'required|numeric',
            'unit' => 'required|string',
        ]);

        $this->service->createKpi($validated);
        return redirect()->route('kpis.index')->with('success', 'KPI berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->checkAccess();
        $kpi = $this->service->getKpi($id);
        return view('kpis.edit', compact('kpi'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'description' => 'required|string',
            'target' => 'required|numeric',
            'unit' => 'required|string',
        ]);

        $this->service->updateKpi($id, $validated);
        return redirect()->route('kpis.index')->with('success', 'KPI berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->checkAccess();
        $this->service->deleteKpi($id);
        return redirect()->route('kpis.index')->with('success', 'KPI berhasil dihapus.');
    }
}
