<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MasterKpiResource;
use App\Models\KpiMaster;
use Illuminate\Http\Request;

/**
 * @group Master KPIs
 */
class MasterKpiController extends Controller
{
    public function index(Request $request)
    {
        if (! in_array($request->user()->role, ['ADMIN', 'MANAGER'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = KpiMaster::query();

        // Search by nama
        if ($search = $request->input('search')) {
            $query->where('nama', 'LIKE', "%{$search}%");
        }

        // Filter by status_aktif
        if ($request->has('status_aktif')) {
            $query->where('status_aktif', $request->boolean('status_aktif'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['nama', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return MasterKpiResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'status_aktif' => 'boolean',
        ]);

        $kpi = KpiMaster::create($validated);

        return response()->json($kpi, 201);
    }

    public function show(Request $request, KpiMaster $kpi)
    {
        if (! in_array($request->user()->role, ['ADMIN', 'MANAGER'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($kpi);
    }

    public function update(Request $request, KpiMaster $kpi)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'status_aktif' => 'boolean',
        ]);

        $kpi->update($validated);

        return response()->json($kpi);
    }

    public function destroy(Request $request, KpiMaster $kpi)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $kpi->delete();

        return response()->json(['message' => 'KPI deleted successfully']);
    }
}
