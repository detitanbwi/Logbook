<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kpi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role === 'staff') {
            abort(403, 'Akses ditolak.');
        }

        $employees = User::with('supervisor')
            ->orderBy('nama')
            ->paginate(10);

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()->role === 'staff') {
            abort(403, 'Anda tidak memiliki akses ini.');
        }

        $supervisors = User::orderBy('nama')->get();
        $kpis = Kpi::all();
        return view('employees.create', compact('supervisors', 'kpis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role === 'staff') abort(403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'npp' => ['required', 'numeric', 'unique:users,npp'],
            'password' => ['required', 'min:8'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nik' => ['nullable', 'numeric', 'digits_between:1,16', 'unique:users,nik'],
            'npwp' => ['nullable', 'string', 'regex:/^[0-9.-]*$/'], // Only digits, dots, dashes
            'alamat' => ['nullable', 'string'],
            'status_perkawinan' => ['required', Rule::in(['belum_menikah', 'menikah'])],
            'riwayat_pendidikan' => ['nullable', 'string'],
            'riwayat_karir' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'staff'])],
            'kpi_ids' => ['required', 'array', 'min:1'],
            'kpi_ids.*' => ['exists:kpis,id'],
        ]);

        // Constraint: Admin can only create Staff
        if (auth()->user()->role === 'admin') {
            $validated['role'] = 'staff';
        }

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('photos', 'public');
            $validated['foto'] = $path;
        }

        $validated['password'] = Hash::make($validated['password']);

        $employee = User::create($validated);
        $employee->kpis()->sync($request->kpi_ids);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil didaftarkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = User::with(['supervisor', 'kpis'])->findOrFail($id);

        if (auth()->user()->role === 'staff' && auth()->id() !== $employee->id) {
            abort(403, 'Akses terbatas pada profil sendiri.');
        }

        $supervisors = $employee->supervisor ? [$employee->supervisor] : [];
        $kpis = Kpi::all();

        return view('employees.show', compact('employee', 'supervisors', 'kpis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        if ($user->role === 'staff') abort(403);

        $employee = User::findOrFail($id);

        // Constraint: Admin cannot edit other admins or superadmins
        if ($user->role === 'admin' && $employee->role !== 'staff') {
            abort(403, 'Admin hanya dapat mengelola Staff.');
        }

        $supervisors = User::where('id', '!=', $employee->id)
            ->orderBy('nama')
            ->get();
        $kpis = Kpi::all();

        return view('employees.edit', compact('employee', 'supervisors', 'kpis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if ($user->role === 'staff') abort(403);

        $employee = User::findOrFail($id);

        if ($user->role === 'admin' && $employee->role !== 'staff') abort(403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'npp' => ['required', 'numeric', Rule::unique('users')->ignore($employee->id)],
            'password' => ['nullable', 'min:8'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nik' => ['nullable', 'numeric', 'digits_between:1,16', Rule::unique('users')->ignore($employee->id)],
            'npwp' => ['nullable', 'string', 'regex:/^[0-9.-]*$/'],
            'alamat' => ['nullable', 'string'],
            'status_perkawinan' => ['required', Rule::in(['belum_menikah', 'menikah'])],
            'riwayat_pendidikan' => ['nullable', 'string'],
            'riwayat_karir' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id', 'different:id'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'staff'])],
            'kpi_ids' => ['required', 'array', 'min:1'],
            'kpi_ids.*' => ['exists:kpis,id'],
        ]);

        if ($user->role === 'admin') {
            $validated['role'] = 'staff';
        }

        if ($request->hasFile('foto')) {
            if ($employee->foto) Storage::disk('public')->delete($employee->foto);
            $path = $request->file('foto')->store('photos', 'public');
            $validated['foto'] = $path;
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);
        $employee->kpis()->sync($request->kpi_ids);

        return redirect()->route('employees.index')->with('success', 'Data karyawan diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat menghapus karyawan.');
        }

        $employee = User::findOrFail($id);
        
        if ($employee->foto) Storage::disk('public')->delete($employee->foto);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Karyawan telah dihapus.');
    }
}
