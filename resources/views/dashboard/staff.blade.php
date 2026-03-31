<x-layouts.app :title="auth()->user()->nama" active="dashboard">
    <div class="space-y-8">
        <!-- Dashboard Greeting -->
        <div class="px-2">
            <p class="text-xs font-bold tracking-wider text-primary/60 uppercase mb-1">Operational Hub</p>
            <h2 class="text-3xl font-bold text-base-content tracking-tight">{{ auth()->user()->nama }}</h2>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('dashboard') }}" class="relative group">
            <div class="bg-base-100 p-5 rounded-2xl flex items-center shadow-sm border border-base-300 cursor-pointer transition-all hover:border-primary/30 hover:shadow-md">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
                        <i data-lucide="calendar" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-0.5">Filter Tanggal</p>
                        <p class="text-sm font-bold text-base-content uppercase tracking-tight">
                            {{ $filterDate ? $filterDate->isoFormat('D MMM Y') : 'Semua Aktivitas' }}
                        </p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="h-5 w-5 text-base-content/20 group-hover:text-primary transition-colors"></i>
            </div>
            <input type="date" name="date" value="{{ $filterDate?->format('Y-m-d') }}" onchange="this.form.submit()" class="absolute inset-0 opacity-0 cursor-pointer z-10 w-full">
        </form>

        @if($isDateFiltered ?? false)
        <div class="px-1 mt-[-1rem]">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-ghost text-primary text-[0.65rem] font-black uppercase tracking-widest gap-2">
                <i data-lucide="filter-x" class="h-3 w-3"></i>
                Hapus Filter
            </a>
        </div>
        @endif

        <!-- Summary Stats Section -->
        <div class="grid grid-cols-2 gap-4">
            <div class="card bg-primary text-primary-content p-6 rounded-3xl shadow-xl shadow-primary/20 relative overflow-hidden group flex flex-col justify-center">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                <p class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-2 z-10">Total Jam</p>
                <div class="flex items-baseline gap-2 z-10">
                    <span class="text-4xl font-bold tracking-tighter">{{ number_format($totalHours, 1) }}</span>
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-70">Hrs</span>
                </div>
            </div>
            
            <div class="card bg-base-100 p-6 rounded-3xl shadow-sm border border-base-300 flex flex-col justify-center">
                <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-2">Total Laporan</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-base-content tracking-tighter">{{ $logbooks->count() }}</span>
                    <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Entries</span>
                </div>
            </div>
        </div>

        <!-- Activity List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2 pt-2">
                <h3 class="text-xs font-bold text-base-content/50 uppercase tracking-wider">RECENT ACTIVITIES</h3>
            </div>
            
            <div class="grid gap-4">
                @forelse($logbooks as $logbook)
                <a href="{{ route('logbooks.show', $logbook->id) }}" class="group bg-base-100 p-5 rounded-2xl border border-base-300 shadow-sm transition-all hover:shadow-md hover:border-primary/20 active:scale-[0.99] flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl {{ $logbook->status === 'approved' ? 'bg-success/10 text-success' : ($logbook->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-warning/10 text-warning') }} flex items-center justify-center shadow-inner">
                                <i data-lucide="{{ $logbook->status === 'approved' ? 'check-circle-2' : ($logbook->status === 'rejected' ? 'x-circle' : 'clock') }}" class="h-6 w-6"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-base-content uppercase tracking-tight leading-none mb-1">{{ optional($logbook->start_time)->isoFormat('dddd, D MMM') }}</span>
                                <span class="text-xs font-semibold text-base-content/50 uppercase tracking-wide">{{ $logbook->start_time?->format('H:i') }} - {{ $logbook->end_time?->format('H:i') }}</span>
                            </div>
                        </div>
                        
                        @php
                            $badgeStyles = [
                                'approved' => 'badge-success bg-success/10 text-success',
                                'rejected' => 'badge-error bg-error/10 text-error',
                                'pending' => 'badge-warning bg-warning/20 text-warning-content',
                            ];
                        @endphp
                        <span class="badge {{ $badgeStyles[$logbook->status] ?? 'badge-ghost' }} badge-sm font-bold uppercase tracking-wide py-3 px-4 border-none">
                            {{ $logbook->status }}
                        </span>
                    </div>
                    
                    <div class="px-1">
                        <p class="text-sm text-base-content/70 font-medium leading-relaxed line-clamp-2 italic italic opacity-80 border-l-2 border-base-300 pl-4 py-1">
                            "{{ $logbook->daily_report }}"
                        </p>
                    </div>

                    @if($logbook->main_photo_path)
                    <div class="pt-4 border-t border-base-200 flex items-center gap-2">
                        <i data-lucide="image" class="h-3.5 w-3.5 text-primary/40"></i>
                        <span class="text-[0.65rem] font-semibold text-base-content/40 uppercase tracking-wide">Evidence Attached</span>
                    </div>
                    @endif
                </a>
                @empty
                <div class="text-center py-20 bg-base-100 rounded-3xl border-2 border-dashed border-base-300">
                    <div class="w-16 h-16 mx-auto bg-base-200 rounded-full flex items-center justify-center text-base-content/20 mb-4">
                        <i data-lucide="archive-x" class="h-8 w-8"></i>
                    </div>
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wider">Tidak ada aktivitas ditemukan</p>
                </div>
                @endforelse
            </div>

            @if($logbooks->count() > 0)
            <div class="flex justify-center pt-4">
                <a href="{{ route('logbooks.index') }}" class="btn btn-ghost btn-sm text-primary/60 hover:text-primary gap-2 text-xs font-bold uppercase tracking-wider">
                    LIHAT SEMUA RIWAYAT
                    <i data-lucide="arrow-down" class="h-3 w-3"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="{{ route('logbooks.create') }}" class="fixed bottom-10 right-6 md:right-10 btn btn-primary btn-lg rounded-2xl shadow-2xl shadow-primary/40 gap-3 px-8 z-50 hover:scale-110 active:scale-95 transition-all group">
        <i data-lucide="plus" class="h-6 w-6 text-white group-hover:rotate-90 transition-transform"></i>
        <span class="text-xs font-black uppercase tracking-widest text-white">Tambah Log</span>
    </a>
</x-layouts.app>
