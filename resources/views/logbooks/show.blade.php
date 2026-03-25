<x-layouts.app title="Rincian Logbook" :active="'dashboard'">
    <main class="space-y-8 pb-12">
        <!-- Header & Status Section -->
        <section class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[0.6rem] font-black text-outline uppercase tracking-widest leading-none mb-2 font-label">Entry Details</p>
                    <h2 class="text-3xl font-headline font-black text-primary leading-tight">Logbook</h2>
                </div>
                @php
                    $statusStyles = [
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'pending' => 'bg-[#FFDBCB] text-[#341100]',
                    ];
                @endphp
                <span class="px-5 py-2 rounded-full {{ $statusStyles[$logbook->status] ?? 'bg-surface-container-high' }} text-[0.6rem] font-black uppercase tracking-widest shadow-sm">
                    {{ $logbook->status }}
                </span>
            </div>

            <!-- Tonal Layering: Details Card -->
            <div class="bg-surface-container-lowest p-8 rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-outline-variant/5 space-y-6">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-[1.5rem]">location_on</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-[0.55rem] uppercase font-black text-outline tracking-widest mb-1.5">Location</p>
                        <p class="text-on-surface font-bold text-sm leading-tight italic">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                    </div>
                    @if($logbook->latitude !== '-' && $logbook->longitude !== '-')
                    <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" class="px-4 py-3 rounded-xl bg-primary text-white text-[0.7rem] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/20 shrink-0 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[1.1rem]">map</span>
                        <span class="hidden sm:inline">Lihat</span>
                    </button>
                    @endif
                </div>
                <div class="flex items-start gap-5 pt-6 border-t border-outline-variant/10">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/5 flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[1.5rem]">schedule</span>
                    </div>
                    <div>
                        <p class="text-[0.55rem] uppercase font-black text-outline tracking-widest mb-1.5">Time Period</p>
                        <p class="text-on-surface font-bold text-sm leading-tight">
                            {{ $logbook->start_time->isoFormat('MMM D, Y') }} • {{ $logbook->start_time->format('H:i') }} - {{ $logbook->end_time->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Documentation Photo (Hero Element) -->
        <section class="space-y-4">
            <h3 class="text-[0.65rem] font-black font-headline text-primary uppercase tracking-widest flex items-center gap-2">
                Documentation Photo <span class="h-[1px] flex-1 primary-gradient opacity-10"></span>
            </h3>
            <div class="relative aspect-video rounded-3xl overflow-hidden bg-surface-container-highest shadow-xl">
                @if($logbook->main_photo_path)
                    <img src="{{ asset('storage/' . $logbook->main_photo_path) }}" alt="Documentation" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-on-surface/20">
                        <span class="material-symbols-outlined text-4xl mb-2">add_a_photo</span>
                        <p class="text-[0.6rem] font-black uppercase tracking-widest">No visual documentation</p>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-6 left-6 flex items-center gap-3 text-white">
                    <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <span class="material-symbols-outlined text-[1rem]">photo_camera</span>
                    </div>
                    <span class="text-[0.65rem] font-black uppercase tracking-widest">Captured • Entry ID #{{ $logbook->id }}</span>
                </div>
            </div>
        </section>

        <!-- KPI Performance Section -->
        <section class="space-y-5">
            <h3 class="text-[0.65rem] font-black font-headline text-primary uppercase tracking-widest flex items-center gap-2">
                KPI Performance <span class="h-[1px] flex-1 primary-gradient opacity-10"></span>
            </h3>
            <div class="grid grid-cols-1 gap-4">
                @foreach($logbook->items as $item)
                <div class="bg-white p-6 rounded-[2rem] border-l-4 {{ $loop->first ? 'border-primary' : 'border-outline-variant/30' }} shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div class="pr-4">
                            <h4 class="font-black text-primary text-[0.8rem] uppercase tracking-tight mb-1">KPI Item</h4>
                            <p class="text-[0.65rem] font-bold text-outline-700 uppercase tracking-widest">{{ $item->kpi->description }}</p>
                        </div>
                        <div class="flex items-baseline gap-1 bg-primary/5 px-3 py-1.5 rounded-xl">
                            <span class="text-xl font-headline font-black text-primary">{{ $item->score ?? '-' }}</span>
                            <span class="text-[0.55rem] font-black text-outline uppercase">/ 100</span>
                        </div>
                    </div>
                    <p class="text-[0.7rem] font-medium text-on-surface-variant leading-relaxed bg-surface-container/50 p-4 rounded-xl italic">
                        "{{ $item->work_description }}"
                    </p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Daily Report -->
        <section class="space-y-4">
            <h3 class="text-[0.65rem] font-black font-headline text-primary uppercase tracking-widest flex items-center gap-2">
                Daily Report <span class="h-[1px] flex-1 primary-gradient opacity-10"></span>
            </h3>
            <div class="bg-surface-container-lowest p-10 rounded-[2.5rem] shadow-[0_15px_60px_rgba(0,0,0,0.02)] border border-outline-variant/10">
                <p class="text-on-surface leading-[2] text-[0.85rem] font-medium font-body italic opacity-80 decoration-primary/20 decoration-dashed underline-offset-8">
                    {{ $logbook->daily_report }}
                </p>
                <div class="mt-8 flex items-center gap-4 py-4 border-t border-outline-variant/5">
                    <div class="w-10 h-10 rounded-full primary-gradient flex items-center justify-center text-white/40">
                         <span class="material-symbols-outlined text-[1rem]">verified</span>
                    </div>
                    <span class="text-[0.55rem] font-black text-outline uppercase tracking-[0.2em]">Authenticity Verified System</span>
                </div>
            </div>
        </section>

        <!-- Supervisor Feedback -->
        @if($logbook->latestReview)
        <section class="space-y-5 pb-12">
            <h3 class="text-[0.65rem] font-black font-headline text-primary uppercase tracking-widest flex items-center gap-2">
                Supervisor Feedback <span class="h-[1px] flex-1 primary-gradient opacity-10"></span>
            </h3>
            <div class="bg-primary/5 p-8 rounded-[2.5rem] border border-primary/10 shadow-lg shadow-primary/5 space-y-6">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-[1.25rem] bg-white p-1 shadow-md shrink-0">
                            @if($logbook->latestReview->reviewer?->foto)
                                <img src="{{ asset('storage/' . $logbook->latestReview->reviewer->foto) }}" alt="Supervisor" class="w-full h-full object-cover rounded-xl">
                            @else
                                <div class="w-full h-full flex items-center justify-center primary-gradient text-white font-black text-xl rounded-xl">
                                    {{ substr($logbook->latestReview->reviewer?->nama ?? 'S', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-headline font-black text-lg text-primary leading-none mb-1.5">{{ $logbook->latestReview->reviewer?->nama ?? 'Supervisor' }}</p>
                            <p class="text-[0.55rem] font-black text-outline uppercase tracking-widest">{{ $logbook->latestReview->reviewer?->role ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <p class="text-[0.5rem] uppercase font-black text-outline tracking-widest mb-2">Overall Rating</p>
                        <div class="flex text-[#FFB691] gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="material-symbols-outlined text-[1.2rem]" style="font-variation-settings: 'FILL' {{ $i <= ($logbook->latestReview->rating ?? 0) ? '1' : '0' }};">star</span>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="bg-white/40 p-6 rounded-2xl">
                    <p class="text-[0.75rem] font-bold italic text-primary/80 leading-relaxed">
                        "{{ $logbook->latestReview->comment ?? 'Excellent attention to detail. No feedback provided.' }}"
                    </p>
                </div>
            </div>
        </section>
        @endif


    </main>
</x-layouts.app>
