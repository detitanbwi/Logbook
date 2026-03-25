<x-layouts.app :title="$title" :active="$active">
    <div class="space-y-8 pb-12 overflow-x-hidden">
        <!-- Dashboard Header -->
        <div
            class="glass-morphism rounded-3xl p-8 border border-outline-variant/10 relative overflow-hidden flex flex-col items-center text-center">
            <div
                class="w-20 h-20 primary-gradient rounded-3xl flex items-center justify-center text-white mb-6 shadow-2xl shadow-primary/30 relative z-10">
                <span class="material-symbols-outlined text-4xl font-black italic">rate_review</span>
            </div>
            <h1 class="text-3xl font-black text-primary uppercase tracking-tight relative z-10">Perlu Review</h1>
            <p class="text-[0.65rem] font-bold text-on-surface/30 uppercase tracking-[0.4em] mt-2 relative z-10">
                Manajemen Performa Staff</p>

            <div
                class="mt-8 flex items-center gap-2 px-6 py-3 bg-primary/5 rounded-2xl border border-primary/10 relative z-10">
                <p class="text-[1.2rem] font-black text-primary leading-none">{{ $logbooks->total() }}</p>
                <p class="text-[0.6rem] font-black text-primary/60 uppercase tracking-widest pt-1">Total Logs</p>
            </div>

            <div class="absolute -top-12 -left-12 w-48 h-48 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>
        </div>

        <!-- Subordinate Logs List -->
        <div class="space-y-6">
            <div class="flex items-center gap-3 px-2">
                <div class="h-[1px] flex-1 bg-outline-variant/10"></div>
                <h3 class="text-[0.6rem] font-black text-on-surface/30 uppercase tracking-[0.4em]">Submitted Logs</h3>
                <div class="h-[1px] flex-1 bg-outline-variant/10"></div>
            </div>

            <div class="grid gap-4">
                @forelse($logbooks as $log)
                    <div
                        class="glass-morphism rounded-3xl p-6 border border-outline-variant/10 hover:border-primary/20 transition-all group relative overflow-hidden">
                        <div class="flex items-start justify-between mb-6 relative z-10">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-14 h-14 rounded-2xl border-2 border-white shadow-md overflow-hidden bg-surface-container-high shrink-0">
                                    @if($log->employee->foto)
                                        <img src="{{ asset('storage/' . $log->employee->foto) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div
                                            class="w-full h-full flex items-center justify-center primary-gradient text-white text-xs font-bold">
                                            {{ substr($log->employee->nama, 0, 2) }}</div>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-1">
                                    <h4 class="text-[0.75rem] font-black text-primary uppercase tracking-tight">
                                        {{ $log->employee->nama }}</h4>
                                    <p class="text-[0.55rem] font-bold text-on-surface/30 uppercase tracking-widest">
                                        {{ $log->employee->npp }}</p>
                                    {{-- Status badge --}}
                                    @if($log->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            <span class="text-[0.5rem] font-black text-amber-600 uppercase tracking-widest">Menunggu Review</span>
                                        </span>
                                    @elseif($log->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 border border-green-200 w-fit">
                                            <span class="material-symbols-outlined text-[0.65rem] text-green-600">check_circle</span>
                                            <span class="text-[0.5rem] font-black text-green-600 uppercase tracking-widest">Disetujui
                                                @if($log->latestReview)
                                                    &bull; {{ $log->latestReview->rating }}/5 ★
                                                @endif
                                            </span>
                                        </span>
                                    @elseif($log->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 border border-red-200 w-fit">
                                            <span class="material-symbols-outlined text-[0.65rem] text-red-600">cancel</span>
                                            <span class="text-[0.5rem] font-black text-red-600 uppercase tracking-widest">Ditolak</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-surface-container-high/40 p-5 rounded-2xl border border-outline-variant/5 mb-6 relative z-10">
                            <p class="text-[0.7rem] font-medium text-on-surface/60 italic leading-relaxed line-clamp-2">
                                "{{ $log->daily_report }}"</p>
                        </div>

                        <div class="flex items-center justify-between pt-2 relative z-10">
                            <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                                <div class="flex flex-col border-l-2 border-primary/10 pl-3">
                                    <span
                                        class="text-[0.5rem] font-black text-on-surface/20 uppercase tracking-widest mb-1">Durasi
                                        Kerja</span>
                                    <span
                                        class="text-[0.65rem] font-black text-primary uppercase leading-none">{{ $log->start_time->format('H:i') }}
                                        - {{ $log->end_time->format('H:i') }}</span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/10 pl-3">
                                    <span
                                        class="text-[0.5rem] font-black text-on-surface/20 uppercase tracking-widest mb-1">Total
                                        Jam</span>
                                    <span class="text-[0.65rem] font-black text-primary uppercase leading-none">
                                        {{ number_format($log->start_time->diffInMinutes($log->end_time) / 60, 1) }}h
                                    </span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/10 pl-3">
                                    <span
                                        class="text-[0.5rem] font-black text-on-surface/20 uppercase tracking-widest mb-1">Entri
                                        Tanggal</span>
                                    <span
                                        class="text-[0.65rem] font-black text-primary uppercase leading-none">{{ $log->start_time->isoFormat('D MMM Y') }}</span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/10 pl-3">
                                    <span
                                        class="text-[0.5rem] font-black text-on-surface/20 uppercase tracking-widest mb-1">Total
                                        KPI</span>
                                    <span
                                        class="text-[0.65rem] font-black text-primary uppercase leading-none">{{ $log->items->count() }}
                                        Items</span>
                                </div>
                            </div>

                            @if($log->status === 'pending')
                                <a href="{{ route('reviews.edit', $log->id) }}"
                                    class="primary-gradient px-5 py-3 rounded-xl text-white shadow-xl shadow-primary/20 flex items-center justify-center gap-2 active:scale-95 transition-all outline-none shrink-0">
                                    <span class="material-symbols-outlined text-[1rem]">rate_review</span>
                                    <span class="text-[0.75rem] font-black uppercase tracking-wide">Review</span>
                                </a>
                            @else
                                <a href="{{ route('reviews.edit', $log->id) }}"
                                    class="px-5 py-3 bg-surface-container-high border border-outline-variant/10 rounded-xl text-primary/50 hover:text-primary flex items-center justify-center gap-2 active:scale-95 transition-all shrink-0">
                                    <span class="material-symbols-outlined text-[1rem]">visibility</span>
                                    <span class="text-[0.75rem] font-black uppercase tracking-wide">Lihat</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="glass-morphism p-12 rounded-3xl border border-dashed border-outline-variant/30 text-center">
                        <div
                            class="w-16 h-16 bg-surface-container-high rounded-full flex items-center justify-center mx-auto mb-4 grayscale opacity-40">
                            <span class="material-symbols-outlined text-3xl">done_all</span>
                        </div>
                        <p class="text-[0.65rem] font-black text-on-surface/30 uppercase tracking-widest leading-relaxed">
                            Semua logbook telah ditinjau.<br>Kerja bagus, Supervisor!</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-6">
                {{ $logbooks->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
