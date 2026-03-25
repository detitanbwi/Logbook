<x-layouts.app title="Logbook Saya" active="logbooks">
    <div class="space-y-6 pb-32">
        <!-- Activity List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between mx-1 pt-1">
                <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em]">AKTIVITAS LOGBOOK</h3>
                <span class="text-[0.55rem] font-bold text-primary/30 uppercase tracking-wider">Page {{ $logbooks->currentPage() }}</span>
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

            <div class="pt-4 px-1">
                {{ $logbooks->links() }}
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="{{ route('logbooks.create') }}" class="fixed bottom-28 right-6 bg-primary text-white flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl shadow-primary/40 z-[100] transition-all active:scale-90 group overflow-hidden">
        <div class="absolute inset-0 primary-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <span class="material-symbols-outlined text-xl font-black relative z-10">add</span>
        <span class="text-[0.6rem] font-black tracking-widest uppercase relative z-10">Tambah</span>
    </a>
</x-layouts.app>
