<x-layouts.app title="Review Logbook" active="reviews">
    <div class="space-y-8 pb-32">
        <!-- Analytics Card -->
        <div class="primary-gradient p-10 rounded-[2.5rem] text-white flex justify-between items-center shadow-2xl shadow-primary/20">
            <div>
                <p class="text-white/40 text-[0.6rem] font-black uppercase tracking-widest mb-2">Pending Verifikasi</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-headline font-black">{{ $logbooks->where('status', 'pending')->count() }}</span>
                    <span class="text-[0.65rem] font-black uppercase tracking-widest text-white/60">Logs</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-3xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                <span class="material-symbols-outlined text-white text-3xl">pending_actions</span>
            </div>
        </div>

        <!-- Filter Chips -->
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar pb-2">
            <button class="px-8 py-3 bg-primary text-white rounded-full text-[0.6rem] font-black tracking-widest uppercase">Semua</button>
            <button class="px-8 py-3 bg-white border border-outline-variant/10 text-outline rounded-full text-[0.6rem] font-black tracking-widest uppercase whitespace-nowrap">Belum Selesai</button>
            <div class="flex items-center gap-2 px-6 py-3 bg-white border border-outline-variant/10 rounded-full text-[0.6rem] font-black text-primary uppercase tracking-widest ml-auto">
                <span class="material-symbols-outlined text-[1rem]">calendar_month</span>
                <span>Minggu Ini</span>
            </div>
        </div>

        <!-- Logs List -->
        <div class="space-y-6">
            @forelse($logbooks as $logbook)
            <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_15px_50px_rgba(0,0,0,0.02)] border border-outline-variant/5 space-y-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-surface-container overflow-hidden shrink-0 border border-outline-variant/10 shadow-sm">
                            @if($logbook->user->foto)
                                <img src="{{ asset('storage/' . $logbook->user->foto) }}" alt="Photo" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center primary-gradient text-white font-black text-xl">
                                    {{ substr($logbook->user->nama, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-headline font-black text-primary text-[1rem] leading-none mb-1.5 uppercase tracking-tight">{{ $logbook->user->nama }}</h3>
                            <p class="text-[0.55rem] font-bold text-outline uppercase tracking-widest">{{ $logbook->user->jabatan ?? 'Associate Staff' }}</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 bg-tertiary-fixed text-on-tertiary-fixed text-[0.55rem] font-black rounded-full uppercase tracking-widest">
                        {{ $logbook->status }}
                    </span>
                </div>

                <div class="bg-surface-container/30 p-6 rounded-[2rem] border border-outline-variant/5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-primary text-[0.9rem]">calendar_today</span>
                        <p class="text-[0.6rem] font-black text-primary/60 tracking-widest uppercase">{{ \Carbon\Carbon::parse($logbook->date)->isoFormat('D MMM Y') }}</p>
                    </div>
                    <p class="text-[0.75rem] text-on-surface-variant font-medium leading-relaxed line-clamp-2 italic opacity-80 underline underline-offset-4 decoration-primary/5 decoration-dashed">
                       "{{ $logbook->daily_report }}"
                    </p>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('reviews.show', $logbook->id) }}" class="px-10 py-4 bg-primary text-white rounded-2xl text-[0.65rem] font-black uppercase tracking-widest shadow-xl shadow-primary/20 active:opacity-90 transition-all">
                        View Details
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-24 bg-surface-container-low/50 rounded-[3rem] border-2 border-dashed border-outline-variant/10">
                <span class="material-symbols-outlined text-4xl text-outline/20 mb-4 font-black">task_alt</span>
                <p class="text-[0.65rem] font-black text-outline uppercase tracking-[0.2em]">Semua laporan terverifikasi</p>
            </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
