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
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-primary/5 flex items-center justify-center text-primary/40 border border-primary/5">
                        <i data-lucide="image" class="h-4 w-4"></i>
                    </div>
                    <span class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.15em]">Evidence Attached</span>
                </div>
                @endif
            </a>
            @empty
            <div class="text-center py-16 bg-base-300/10 rounded-[2.5rem] border-2 border-dashed border-primary/10">
                <div class="w-16 h-16 mx-auto bg-primary/10 rounded-full flex items-center justify-center text-primary/20 mb-6 shadow-sm">
                    <i data-lucide="calendar-x-2" class="h-8 w-8"></i>
                </div>
                <p class="text-[0.65rem] font-black text-primary/60 uppercase tracking-[0.3em]">Logbook Kosong</p>
            </div>
            @endforelse

            <div class="pt-4 px-1">
                {{ $logbooks->links() }}
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="{{ route('logbooks.create') }}" class="fixed bottom-28 right-6 bg-primary text-white flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl shadow-primary/40 z-[100] transition-all active:scale-95 group overflow-hidden border-none cursor-pointer">
        <i data-lucide="plus" class="h-5 w-5 font-black text-white relative z-10 transition-transform group-hover:rotate-90"></i>
        <span class="text-[0.65rem] font-black tracking-[0.2em] uppercase relative z-10">TAMBAH</span>
    </a>
</x-layouts.app>
