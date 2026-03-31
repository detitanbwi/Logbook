<x-layouts.app :title="'KPI Management'" :breadcrumb="'Manajemen KPI'">
    <div class="animate-in fade-in slide-in-from-bottom-8 duration-700">
        <!-- Action Section -->
        <div class="flex justify-start mb-6">
            <a href="{{ route('kpis.create') }}" class="btn btn-primary rounded-xl h-14 px-8 gap-3 shadow-lg shadow-primary/20 hover:scale-[1.03] transition-all w-full md:w-auto">
                <i data-lucide="plus-circle" class="h-5 w-5"></i>
                <span class="text-xs font-black uppercase tracking-[0.2em]">Buat KPI Baru</span>
            </a>
        </div>

        <!-- Table Section -->
        <div class="card bg-base-100 rounded-3xl shadow-sm border border-base-300 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="table table-zebra w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-base-200/50 border-b border-base-300">
                            <th class="px-8 py-6 text-[0.65rem] font-bold text-base-content/40 tracking-[0.2em] uppercase border-none">KPI DESKRIPSI</th>
                            <th class="px-8 py-6 text-[0.65rem] font-bold text-base-content/40 tracking-[0.2em] uppercase border-none">TARGET</th>
                            <th class="px-8 py-6 text-[0.65rem] font-bold text-base-content/40 tracking-[0.2em] uppercase border-none">SATUAN</th>
                            <th class="px-8 py-6 text-[0.65rem] font-bold text-base-content/40 tracking-[0.2em] uppercase border-none text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($kpis as $kpi)
                        <tr class="hover:bg-base-200/30 transition-colors group">
                            <td class="px-8 py-5 border-none">
                                <p class="text-sm font-bold text-primary tracking-tight leading-relaxed max-w-lg italic">"{{ $kpi->description }}"</p>
                                <p class="text-[0.6rem] font-semibold text-base-content/30 tracking-widest uppercase mt-2">Ref ID_{{ $kpi->id }}</p>
                            </td>
                            <td class="px-8 py-5 border-none">
                                <span class="text-lg font-bold text-primary font-mono tracking-tight">{{ number_format($kpi->target) }}</span>
                            </td>
                            <td class="px-8 py-5 border-none">
                                <span class="badge bg-base-300 text-base-content/50 border-none badge-sm font-bold text-[0.55rem] tracking-[0.15em] uppercase py-3 px-4">
                                    {{ $kpi->unit }}
                                </span>
                            </td>
                            <td class="px-8 py-5 border-none">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('kpis.edit', $kpi->id) }}" 
                                       class="btn btn-ghost btn-square btn-sm rounded-xl text-base-content/30 hover:text-primary hover:bg-primary/10 transition-all">
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </a>
                                    <form action="{{ route('kpis.destroy', $kpi->id) }}" method="POST" onsubmit="return confirm('Hapus KPI ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-square btn-sm rounded-xl text-base-content/30 hover:text-error hover:bg-error/10 transition-all">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-32 text-center border-none">
                                <div class="flex flex-col items-center gap-6 opacity-20 hover:opacity-40 transition-opacity">
                                    <i data-lucide="target" class="h-16 w-16 text-base-content"></i>
                                    <p class="text-xs font-bold tracking-[0.4em] uppercase text-base-content">Belum ada KPI yang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
