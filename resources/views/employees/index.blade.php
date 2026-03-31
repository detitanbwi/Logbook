<x-layouts.app :title="'Data Karyawan'">
    <div class="animate-in fade-in slide-in-from-bottom-8 duration-700">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12">
            <div class="w-full max-w-2xl text-center md:text-left">
                <p class="text-[0.65rem] font-bold text-primary/60 uppercase tracking-[0.2em] mb-2">Human Resources</p>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-primary leading-tight">Data Karyawan</h1>
                <p class="text-base-content/50 font-medium leading-relaxed text-sm mt-2">Kelola aset sumber daya manusia perusahaan dengan presisi dan manajemen terpadu dalam sistem HRIS.</p>
            </div>
            
            @if(auth()->user()->role !== 'staff')
            <a href="{{ route('employees.create') }}" class="btn btn-primary rounded-2xl gap-3 px-8 shadow-lg shadow-primary/20 hover:scale-[1.03] transition-all w-full md:w-auto">
                <i data-lucide="user-plus" class="h-5 w-5"></i>
                <span class="text-xs font-black uppercase tracking-widest">Tambah Karyawan</span>
            </a>
            @endif
        </div>

        <!-- Table Section -->
        <div class="card bg-base-100 rounded-3xl shadow-sm border border-base-300 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="table table-zebra w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-base-200/50 border-b border-base-300">
                            <th class="px-8 py-6 text-[0.65rem] font-black text-base-content/40 tracking-[0.2em] uppercase border-none">Personel</th>
                            <th class="px-8 py-6 text-[0.65rem] font-black text-base-content/40 tracking-[0.2em] uppercase border-none">NPP</th>
                            <th class="px-8 py-6 text-[0.65rem] font-black text-base-content/40 tracking-[0.2em] uppercase border-none">Role Sistem</th>
                            <th class="px-8 py-6 text-[0.65rem] font-black text-base-content/40 tracking-[0.2em] uppercase border-none">Atasan</th>
                            <th class="px-8 py-6 text-[0.65rem] font-black text-base-content/40 tracking-[0.2em] uppercase border-none text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($employees as $employee)
                        <tr class="hover:bg-base-200/30 transition-colors group">
                            <td class="px-8 py-5 border-none">
                                <div class="flex items-center gap-4">
                                    <div class="avatar border-2 border-primary/10 rounded-xl overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
                                        <div class="w-12 h-12 bg-base-200 flex items-center justify-center">
                                            @if($employee->foto)
                                                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" />
                                            @else
                                                <i data-lucide="user" class="h-6 w-6 text-base-content/20"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-primary leading-tight lowercase first-letter:uppercase">{{ $employee->nama }}</p>
                                        <p class="text-[0.6rem] font-bold text-base-content/30 tracking-widest uppercase mt-1">ID_{{ $employee->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 border-none">
                                <span class="text-sm font-black text-base-content/60 font-mono tracking-tight">{{ $employee->npp }}</span>
                            </td>
                            <td class="px-8 py-5 border-none">
                                @php
                                    $roleBadges = [
                                        'super_admin' => 'badge-primary text-white border-none',
                                        'admin' => 'bg-primary/10 text-primary border-none',
                                        'staff' => 'bg-base-300 text-base-content/40 border-none'
                                    ];
                                @endphp
                                <span class="badge {{ $roleBadges[$employee->role] ?? 'badge-ghost' }} badge-sm font-black text-[0.55rem] tracking-[0.15em] uppercase py-3 px-4">
                                    {{ str_replace('_', ' ', $employee->role) }}
                                </span>
                            </td>
                            <td class="px-8 py-5 border-none">
                                @if($employee->supervisor)
                                    <p class="text-sm font-black text-base-content/60 leading-tight">{{ $employee->supervisor->nama }}</p>
                                    <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest mt-0.5">Direct Supervisor</p>
                                @else
                                    <span class="text-xs font-black text-base-content/20 uppercase tracking-[0.3em]">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 border-none">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('employees.show', $employee->id) }}" 
                                       class="btn btn-ghost btn-square btn-sm rounded-xl text-base-content/30 hover:text-primary hover:bg-primary/10 transition-all">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                    </a>
                                    @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                                    <a href="{{ route('employees.edit', $employee->id) }}" 
                                       class="btn btn-ghost btn-square btn-sm rounded-xl text-base-content/30 hover:text-primary hover:bg-primary/10 transition-all">
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-32 text-center">
                                <div class="flex flex-col items-center gap-6 opacity-20">
                                    <i data-lucide="users" class="h-16 w-16"></i>
                                    <p class="text-xs font-black tracking-[0.4em] uppercase">Data Personel Tidak Ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($employees->hasPages())
        <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 px-4">
            <div class="text-[0.65rem] font-black text-base-content/30 tracking-widest uppercase">
                Showing {{ $employees->firstItem() }}-{{ $employees->lastItem() }} of {{ $employees->total() }} Employees
            </div>
            
            <div class="join">
                {{ $employees->links('pagination::simple-tailwind') }}
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
