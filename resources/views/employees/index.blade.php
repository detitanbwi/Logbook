<x-layouts.app :title="'Data Karyawan'" :breadcrumb="'Data Karyawan'">
    <div class="animate-in fade-in slide-in-from-bottom duration-1000">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 md:mb-20">
            <div class="w-full max-w-2xl text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-primary mb-3 md:mb-4 leading-tight">Data Karyawan</h1>
                <p class="text-on-surface/40 font-medium leading-relaxed text-sm">Kelola aset sumber daya manusia perusahaan dengan presisi dan manajemen terpadu.</p>
            </div>
            
            @if(auth()->user()->role !== 'staff')
            <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center gap-4 px-6 py-4 md:px-10 md:py-5 primary-gradient text-white rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase shadow-2xl shadow-primary/30 active:scale-95 transition-all duration-300 w-full md:w-auto">
                <span class="material-symbols-outlined font-bold text-sm">person_add</span>
                <span>Tambah Karyawan</span>
            </a>
            @endif
        </div>

        <div class="bg-white rounded-xl editorial-shadow overflow-hidden border border-outline-variant/10">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[1000px] lg:min-w-0">
                    <thead>
                    <tr class="bg-white border-b border-outline-variant/5">
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">FOTO</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">NAMA</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">NPP</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">ROLE</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">ATASAN</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/5">
                    @forelse($employees as $employee)
                    <tr class="group hover:bg-surface-container-low transition-colors duration-300">
                        <td class="px-10 py-6">
                            <div class="w-12 h-12 rounded-xl bg-surface-container-highest flex items-center justify-center overflow-hidden border border-outline-variant/10 shadow-sm group-hover:scale-110 transition-transform">
                                @if($employee->foto)
                                    <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-on-surface/20">person</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-10 py-6">
                            <p class="text-sm font-bold text-primary tracking-tight leading-tight mb-1">{{ $employee->nama }}</p>
                            <p class="text-[0.6rem] font-bold text-on-surface/30 tracking-widest uppercase">EMP_{{ $employee->id }}</p>
                        </td>
                        <td class="px-10 py-6 text-sm font-bold text-primary tabular-nums tracking-wider opacity-60">
                            {{ $employee->npp }}
                        </td>
                        <td class="px-10 py-6">
                            @php
                                $roleColors = [
                                    'super_admin' => 'bg-primary text-white shadow-primary/20',
                                    'admin' => 'bg-primary-container/10 text-primary-container border border-primary-container/20',
                                    'staff' => 'bg-surface-container-highest text-on-surface/50 border border-outline-variant/20'
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1.5 {{ $roleColors[$employee->role] }} rounded-lg text-[0.55rem] font-extrabold tracking-[0.15em] uppercase">
                                {{ str_replace('_', ' ', $employee->role) }}
                            </span>
                        </td>
                        <td class="px-10 py-6">
                            @if($employee->supervisor)
                                <p class="text-sm font-bold text-on-surface/60 group-hover:text-primary transition-colors leading-tight">{{ $employee->supervisor->nama }}</p>
                                <p class="text-[0.6rem] font-bold text-on-surface/20 uppercase tracking-widest">Supervisor</p>
                            @else
                                <span class="text-[0.6rem] font-bold text-on-surface/10 uppercase tracking-[0.3em]">Manajemen Pusat</span>
                            @endif
                        </td>
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('employees.show', $employee->id) }}" class="w-10 h-10 rounded-xl bg-white border border-outline-variant/10 flex items-center justify-center text-on-surface/30 hover:text-primary hover:border-primary/30 transition-all duration-300 editorial-shadow">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </a>
                                @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                                <a href="{{ route('employees.edit', $employee->id) }}" class="w-10 h-10 rounded-xl bg-white border border-outline-variant/10 flex items-center justify-center text-on-surface/30 hover:text-primary hover:border-primary/30 transition-all duration-300 editorial-shadow">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-10 py-32 text-center">
                            <div class="flex flex-col items-center gap-6 opacity-20">
                                <span class="material-symbols-outlined text-8xl">inbox</span>
                                <p class="text-[0.65rem] font-bold tracking-[0.4em] uppercase">Data Personel Tidak Ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

        @if($employees->hasPages())
        <div class="mt-12 px-10 py-10 glass-morphism rounded-xl flex items-center justify-between border border-outline-variant/10">
            <div class="text-[0.65rem] font-bold text-on-surface/30 tracking-widest uppercase">
                Showing {{ $employees->firstItem() }}-{{ $employees->lastItem() }} of {{ $employees->total() }} Personel
            </div>
            
            <div class="flex items-center gap-4">
                {{ $employees->links('pagination::simple-bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
