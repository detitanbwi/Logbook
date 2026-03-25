<x-layouts.app :title="'KPI Management'" :breadcrumb="'Manajemen KPI'">
    <div class="animate-in fade-in slide-in-from-bottom duration-1000">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 md:mb-20">
            <div class="w-full max-w-2xl text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-primary mb-3 md:mb-4 leading-tight">Key Performance Indicators</h1>
                <p class="text-on-surface/40 font-medium leading-relaxed text-sm">Menetapkan standar performa kerja untuk mencapai target perusahaan.</p>
            </div>
            
            <a href="{{ route('kpis.create') }}" class="inline-flex items-center justify-center gap-4 px-6 py-4 md:px-10 md:py-5 primary-gradient text-white rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase shadow-2xl shadow-primary/30 active:scale-95 transition-all duration-300 w-full md:w-auto">
                <span class="material-symbols-outlined font-bold text-sm">add_task</span>
                <span>Tambah KPI Baru</span>
            </a>
        </div>

        <div class="bg-white rounded-xl editorial-shadow overflow-hidden border border-outline-variant/10">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[800px] lg:min-w-0">
                    <thead>
                    <tr class="bg-white border-b border-outline-variant/5">
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">KPI DESKRIPSI</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">TARGET</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">SATUAN</th>
                        <th class="px-10 py-8 text-[0.6rem] font-bold text-on-surface/30 tracking-[0.4em] uppercase">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/5">
                    @forelse($kpis as $kpi)
                    <tr class="group hover:bg-surface-container-low transition-colors duration-300">
                        <td class="px-10 py-6">
                            <p class="text-sm font-bold text-primary tracking-tight leading-loose max-w-lg italic">"{{ $kpi->description }}"</p>
                            <p class="text-[0.6rem] font-bold text-on-surface/20 tracking-widest uppercase mt-1">Ref ID_{{ $kpi->id }}</p>
                        </td>
                        <td class="px-10 py-6 text-sm font-bold text-primary tabular-nums tracking-wider">
                            {{ number_format($kpi->target) }}
                        </td>
                        <td class="px-10 py-6">
                            <span class="inline-flex px-3 py-1.5 bg-surface-container-highest text-on-surface/50 border border-outline-variant/20 rounded-lg text-[0.55rem] font-extrabold tracking-[0.15em] uppercase">
                                {{ $kpi->unit }}
                            </span>
                        </td>
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('kpis.edit', $kpi->id) }}" class="w-10 h-10 rounded-xl bg-white border border-outline-variant/10 flex items-center justify-center text-on-surface/30 hover:text-primary hover:border-primary/30 transition-all duration-300 editorial-shadow">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </a>
                                <form action="{{ route('kpis.destroy', $kpi->id) }}" method="POST" onsubmit="return confirm('Hapus KPI ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 rounded-xl bg-white border border-outline-variant/10 flex items-center justify-center text-on-surface/30 hover:text-error hover:border-error/30 transition-all duration-300 editorial-shadow">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-32 text-center">
                            <div class="flex flex-col items-center gap-6 opacity-20">
                                <span class="material-symbols-outlined text-8xl">list_alt</span>
                                <p class="text-[0.65rem] font-bold tracking-[0.4em] uppercase">Belum ada KPI yang terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
