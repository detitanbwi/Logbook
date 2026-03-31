<x-layouts.app :title="$title" :active="$active">
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700">
        <!-- Dashboard Header -->
        <div class="card bg-base-100 rounded-[2.5rem] p-10 border border-base-300 relative overflow-hidden flex flex-col items-center text-center shadow-sm">
            <div class="w-24 h-24 bg-primary/10 rounded-3xl flex items-center justify-center text-primary mb-8 shadow-inner relative z-10">
                <i data-lucide="clipboard-check" class="h-10 w-10"></i>
            </div>
            <h1 class="text-3xl lg:text-4xl font-black text-primary uppercase tracking-tight relative z-10 leading-none">Review Logbook</h1>
            <p class="text-[0.7rem] font-bold text-base-content/30 uppercase tracking-[0.4em] mt-3 relative z-10">
                Manajemen Performa & Akuntabilitas Tim
            </p>

            <div class="mt-10 flex items-center gap-3 px-8 py-4 bg-primary/5 rounded-2xl border border-primary/10 relative z-10">
                <p class="text-2xl font-black text-primary leading-none">{{ $logbooks->total() }}</p>
                <p class="text-[0.65rem] font-bold text-primary/60 uppercase tracking-widest pt-1">Entri Logbook</p>
            </div>

            <!-- Decorative blobs -->
            <div class="absolute -top-12 -left-12 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -right-8 w-48 h-48 bg-primary/5 rounded-full blur-2xl"></div>
        </div>

        <!-- Subordinate Logs List -->
        <div class="space-y-8">
            <div class="flex items-center gap-4 px-2">
                <h3 class="text-[0.7rem] font-black text-base-content/40 uppercase tracking-[0.25em] whitespace-nowrap">Daftar Antrian</h3>
                <div class="h-[1px] w-full bg-base-300"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($logbooks as $log)
                    <div class="card bg-base-100 rounded-3xl p-8 border border-base-300 hover:border-primary/30 transition-all group relative overflow-hidden shadow-sm hover:shadow-md">
                        <div class="flex items-start justify-between mb-8 relative z-10">
                            <div class="flex items-center gap-5">
                                <div class="avatar border-2 border-primary/10 rounded-2xl overflow-hidden bg-base-200 shrink-0 shadow-sm">
                                    <div class="w-16 h-16">
                                        @if($log->employee->foto)
                                            <img src="{{ asset('storage/' . $log->employee->foto) }}" class="object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary text-sm font-black">
                                                {{ substr($log->employee->nama, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <h4 class="text-sm font-black text-primary uppercase tracking-tight leading-tight">
                                        {{ $log->employee->nama }}
                                    </h4>
                                    <p class="text-[0.6rem] font-bold text-base-content/40 uppercase tracking-widest leading-none">
                                        ID_{{ $log->employee->npp }}
                                    </p>
                                    
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg' => 'bg-warning/20', 'text' => 'text-warning-content', 'icon' => 'clock'],
                                            'approved' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'icon' => 'check-circle'],
                                            'rejected' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'icon' => 'x-circle'],
                                        ];
                                        $currentStatus = $statusConfig[$log->status] ?? $statusConfig['pending'];
                                    @endphp
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl {{ $currentStatus['bg'] }} {{ $currentStatus['text'] }} w-fit mt-1">
                                        <i data-lucide="{{ $currentStatus['icon'] }}" class="h-3 w-3"></i>
                                        <span class="text-[0.6rem] font-black uppercase tracking-widest whitespace-nowrap">
                                            {{ $log->status === 'pending' ? 'Butuh Review' : $log->status }}
                                            @if($log->latestReview) &bull; {{ $log->latestReview->rating }}/5 ★ @endif
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-base-200/60 p-6 rounded-2xl border border-base-300 mb-8 relative z-10 min-h-[5rem] flex items-center">
                            <p class="text-[0.8rem] font-bold text-base-content/60 italic leading-relaxed line-clamp-2">
                                "{{ $log->daily_report }}"
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-6 relative z-10 pt-4 border-t border-base-200">
                            <div class="grid grid-cols-2 gap-y-4 gap-x-8">
                                <div class="flex flex-col border-l-2 border-primary/20 pl-4">
                                    <span class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-[0.15em] mb-1">Durasi</span>
                                    <span class="text-xs font-black text-primary uppercase tracking-tight leading-none">
                                        {{ $log->start_time->format('H:i') }} - {{ $log->end_time->format('H:i') }}
                                    </span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/20 pl-4">
                                    <span class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-[0.15em] mb-1">Total Jam</span>
                                    <span class="text-xs font-black text-primary uppercase tracking-tight leading-none">
                                        {{ number_format($log->start_time->diffInMinutes($log->end_time) / 60, 1) }}h
                                    </span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/20 pl-4">
                                    <span class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-[0.15em] mb-1">Entri Log</span>
                                    <span class="text-xs font-black text-primary uppercase tracking-tight leading-none">
                                        {{ $log->start_time->isoFormat('D MMM Y') }}
                                    </span>
                                </div>
                                <div class="flex flex-col border-l-2 border-primary/20 pl-4">
                                    <span class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-[0.15em] mb-1">KPI Items</span>
                                    <span class="text-xs font-black text-primary uppercase tracking-tight leading-none">
                                        {{ $log->items->count() }} Poin
                                    </span>
                                </div>
                            </div>

                            @if($log->status === 'pending')
                                <a href="{{ route('reviews.edit', $log->id) }}"
                                    class="btn btn-primary rounded-2xl gap-3 px-8 shadow-lg shadow-primary/20 hover:scale-[1.03] active:scale-95 transition-all text-xs font-black uppercase tracking-widest h-14">
                                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                                    Update Review
                                </a>
                            @else
                                <a href="{{ route('reviews.edit', $log->id) }}"
                                    class="btn btn-ghost bg-base-200 hover:bg-base-300 rounded-2xl gap-3 px-8 text-xs font-black uppercase tracking-widest h-14 text-primary/60 border border-base-300">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full card p-16 rounded-[2.5rem] border-2 border-dashed border-base-300 text-center bg-base-100">
                        <div class="w-20 h-20 bg-base-200 rounded-full flex items-center justify-center mx-auto mb-6 text-base-content/20">
                            <i data-lucide="check-square" class="h-10 w-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-primary/40 uppercase tracking-widest mb-2">Semua logbook telah ditinjau</h4>
                        <p class="text-xs font-bold text-base-content/30 uppercase tracking-[0.3em]">Kerja bagus, Supervisor!</p>
                    </div>
                @endforelse
            </div>

            @if($logbooks->hasPages())
            <div class="pt-8 flex justify-center">
                {{ $logbooks->links('pagination::simple-tailwind') }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
