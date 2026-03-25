<x-layouts.app :title="auth()->user()->nama" active="dashboard">
    <div class="space-y-6 pb-28">
        <!-- Dashboard Greeting -->
        <div class="px-2">
            <p class="text-[0.65rem] font-black text-primary/60 uppercase tracking-[0.2em] mb-1 opacity-80 font-sans leading-none">Operational Hub</p>
            <h2 class="text-3xl font-headline font-black text-primary uppercase tracking-tighter">{{ auth()->user()->nama }}</h2>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('dashboard') }}" class="relative bg-white p-4 rounded-[1.8rem] flex items-center shadow-sm border border-primary/5 cursor-pointer hover:border-primary/20 transition-all">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary shadow-inner">
                    <span class="material-symbols-outlined text-[1.2rem]">calendar_today</span>
                </div>
                <div>
                    <p class="text-[0.5rem] font-black text-primary/40 uppercase tracking-[0.2em] mb-0.5 leading-none">Filter Tanggal</p>
                    <p class="text-[0.8rem] font-extrabold text-primary uppercase tracking-tight">
                        {{ $filterDate ? $filterDate->isoFormat('D MMM Y') : 'Semua' }}
                    </p>
                </div>
            </div>
            <input type="date" name="date" value="{{ $filterDate?->format('Y-m-d') }}" onchange="this.form.submit()" class="absolute inset-0 opacity-0 cursor-pointer z-10">
        </form>

        @if($isDateFiltered ?? false)
        <div class="px-1">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary/5 text-primary rounded-xl border border-primary/10 text-[0.6rem] font-black uppercase tracking-[0.2em] active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[1rem]">filter_alt_off</span>
                Hapus Filter
            </a>
        </div>
        @endif

        <!-- Summary Stats Section -->
        <div class="px-2">
            <div class="bg-white p-7 rounded-[2.5rem] flex flex-col justify-between min-h-[140px] shadow-sm border border-primary/5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-primary/2 rounded-full -mr-16 -mt-16"></div>
                <p class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.2em] leading-none mb-2 z-10">Total Jam (Bulan Ini)</p>
                <div class="flex items-baseline gap-2 z-10">
                    <span class="text-5xl font-headline font-black text-primary tracking-tighter">{{ number_format($totalHours, 1) }}</span>
                    <span class="text-[0.8rem] font-black text-primary/20 uppercase tracking-widest leading-none">Hours</span>
                </div>
            </div>
        </div>

        <!-- Activity List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between mx-1 pt-1">
                <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em]">RECENT ACTIVITIES</h3>
                <span></span>
            </div>
            @forelse($logbooks as $logbook)
            <a href="{{ route('logbooks.show', $logbook->id) }}" class="block bg-white p-5 rounded-[1.8rem] border-l-[6px] {{ $logbook->status === 'approved' ? 'border-primary' : ($logbook->status === 'rejected' ? 'border-red-500' : 'border-[#FFB691]') }} shadow-[0_10px_40px_rgba(0,0,0,0.012)] border-r border-t border-b border-gray-100 transition-all active:scale-[0.98]">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex flex-col">
                        <span class="text-[0.75rem] font-black text-primary uppercase tracking-tight leading-none mb-1">{{ optional($logbook->start_time)->isoFormat('dddd, D MMM') }}</span>
                        <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-widest">{{ $logbook->start_time?->format('H:i') }} - {{ $logbook->end_time?->format('H:i') }}</span>
                    </div>
                    @php
                        $statusStyles = [
                            'approved' => 'bg-primary/5 text-primary',
                            'rejected' => 'bg-red-50 text-red-600',
                            'pending' => 'bg-[#FFDBCB] text-[#341100]',
                        ];
                    @endphp
                    <span class="px-3 py-1.5 text-[0.45rem] font-black tracking-[0.2em] uppercase rounded-full {{ $statusStyles[$logbook->status] ?? 'bg-gray-100' }}">
                        {{ $logbook->status }}
                    </span>
                </div>
                <p class="text-[0.8rem] text-primary/70 font-medium leading-relaxed line-clamp-2 italic opacity-80">
                    "{{ $logbook->daily_report }}"
                </p>
                @if($logbook->main_photo_path)
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-primary/5 flex items-center justify-center text-primary/30 border border-primary/5">
                        <span class="material-symbols-outlined text-[0.8rem]">image</span>
                    </div>
                    <span class="text-[0.55rem] font-bold text-primary/40 uppercase tracking-widest">Evidence Attached</span>
                </div>
                @endif
            </a>
            @empty
            <div class="text-center py-16 bg-surface-container/30 rounded-[2.5rem] border-2 border-dashed border-primary/10">
                <div class="w-16 h-16 mx-auto primary-gradient rounded-full flex items-center justify-center text-white/20 mb-6 shadow-xl relative">
                    <span class="material-symbols-outlined text-3xl">event_busy</span>
                </div>
                <p class="text-[0.6rem] font-black text-primary/60 uppercase tracking-[0.3em]">Logbook Kosong</p>
            </div>
            @endforelse

            @if($logbooks->count() > 0)
            <a href="{{ route('logbooks.index') }}" class="w-full py-6 text-primary font-black text-[0.6rem] tracking-[0.3em] uppercase flex items-center justify-center gap-2 opacity-30 hover:opacity-100 transition-opacity active:scale-95 group">
                Lihat Semua <span class="material-symbols-outlined text-lg group-hover:translate-y-1 transition-transform">keyboard_arrow_down</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="{{ route('logbooks.create') }}" class="fixed bottom-28 right-6 bg-primary text-white flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl shadow-primary/40 z-[100] transition-all hover:scale-110 active:scale-90 group overflow-hidden">
        <div class="absolute inset-0 primary-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <span class="material-symbols-outlined text-xl font-black relative z-10">add</span>
        <span class="text-[0.6rem] font-black tracking-widest uppercase relative z-10">Tambah</span>
    </a>
</x-layouts.app>
