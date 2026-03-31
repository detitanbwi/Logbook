<x-layouts.app title="Detail Logbook" active="logbooks" flat="true" hideNav="true" :backUrl="route('logbooks.index')">
    <main class="space-y-12 pb-12">
        <!-- Header & Status Section -->
        <section class="space-y-6 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-primary leading-tight">Detail Logbook</h2>
                    <p class="text-[0.6rem] font-bold text-base-content/30 uppercase tracking-[0.2em] mt-1">Status Aktivitas Terkini</p>
                </div>
                @php
                    $statusStyles = [
                        'approved' => 'bg-success/10 text-success border border-success/20',
                        'rejected' => 'bg-error/10 text-error border border-error/20',
                        'pending' => 'bg-warning/10 text-warning border border-warning/20',
                    ];
                @endphp
                <span class="px-5 py-2.5 rounded-full {{ $statusStyles[$logbook->status] ?? 'bg-base-200' }} text-[0.6rem] font-black uppercase tracking-widest shadow-sm">
                    {{ $logbook->status }}
                </span>
            </div>

            <!-- Details: Flat row of info -->
            <div class="grid grid-cols-1 divide-y divide-base-200">
                <div class="py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shrink-0">
                            <i data-lucide="clock" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[0.6rem] uppercase font-black text-base-content/30 tracking-widest mb-0.5">Waktu Aktivitas</p>
                            <p class="text-xs font-black text-base-content leading-none">
                                {{ $logbook->start_time->isoFormat('D MMMM Y') }} • {{ $logbook->start_time->format('H:i') }} - {{ $logbook->end_time->format('H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                @if($logbook->latitude && $logbook->latitude !== '-')
                <div class="py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shrink-0">
                            <i data-lucide="map-pin" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[0.6rem] uppercase font-black text-base-content/30 tracking-widest mb-0.5">Koordinat Lokasi</p>
                            <p class="text-xs font-black text-base-content leading-none font-mono tracking-tight">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                        </div>
                    </div>
                    <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" 
                            class="btn btn-primary btn-sm rounded-xl gap-2 font-black text-[0.6rem] uppercase tracking-widest px-4">
                        MAP
                    </button>
                </div>
                @endif
            </div>
        </section>

        <!-- Documentation Photo -->
        <section class="space-y-4" x-data="{ modalOpen: false }">
            <h3 class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.25em]">Foto Dokumentasi</h3>
            <div class="relative aspect-video rounded-3xl overflow-hidden bg-base-300 shadow-lg border border-base-200" id="photo-container">
                @if($logbook->main_photo_path)
                    @php $photoUrl = asset('storage/' . $logbook->main_photo_path); @endphp
                    <img src="{{ $photoUrl }}" alt="Documentation" class="w-full h-full object-cover">
                    <div class="absolute top-4 right-4 z-20">
                        <button type="button" @click="modalOpen = true"
                            class="btn btn-circle btn-sm btn-primary shadow-lg border-none">
                            <i data-lucide="maximize-2" class="h-4 w-4"></i>
                        </button>
                    </div>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-base-content/20 bg-base-200">
                        <i data-lucide="camera" class="h-10 w-10 mb-2"></i>
                        <p class="text-[0.6rem] font-black uppercase tracking-widest">Tidak ada foto</p>
                    </div>
                @endif
            </div>

            <!-- Fullscreen Photo Modal -->
            @if($logbook->main_photo_path)
            <dialog class="modal modal-middle" :class="modalOpen ? 'modal-open' : ''">
                <div class="modal-box p-0 bg-transparent shadow-none max-w-4xl w-full">
                    <div class="relative bg-base-100 rounded-3xl overflow-hidden shadow-2xl">
                        <div class="p-4 flex justify-between items-center border-b border-base-200">
                            <h3 class="font-black text-xs tracking-wider uppercase ml-2 text-base-content/70">Pratinjau Foto</h3>
                            <button type="button" @click="modalOpen = false" class="btn btn-sm btn-circle btn-ghost">
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <div class="p-4 flex items-center justify-center min-h-[50vh]">
                            <img src="{{ $photoUrl }}" class="w-full h-auto object-contain max-h-[75vh] rounded-2xl">
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop bg-black/60 backdrop-blur-md" @click="modalOpen = false"></div>
            </dialog>
            @endif
        </section>

        <!-- KPI items: flat list -->
        <section class="space-y-5">
            <h3 class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.25em]">Detail Pekerjaan (KPI)</h3>
            <div class="space-y-4">
                @foreach($logbook->items as $item)
                <div class="bg-base-200/40 p-5 rounded-2xl border border-base-300 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-6">
                            <p class="text-xs font-black text-primary uppercase leading-tight">{{ $item->kpi->description }}</p>
                            <p class="text-[0.5rem] font-bold text-base-content/20 uppercase tracking-widest mt-1">KPI Item #{{ $loop->iteration }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xl font-black text-primary leading-none">{{ $item->score ?? '-' }}</p>
                            <p class="text-[0.45rem] font-bold text-base-content/30 uppercase">Skor</p>
                        </div>
                    </div>
                    <div class="bg-base-100 p-4 rounded-xl border border-base-200 italic">
                        <p class="text-[0.7rem] font-bold text-base-content/70 leading-relaxed">"{{ $item->work_description }}"</p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Daily Report: Flat -->
        <section class="space-y-4">
            <h3 class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.25em]">Laporan Harian</h3>
            <div class="bg-base-200/40 p-5 rounded-2xl border border-base-300 text-[0.75rem] font-bold italic leading-relaxed text-base-content/70">
                "{{ $logbook->daily_report }}"
            </div>
        </section>

        <!-- Supervisor Feedback: Pure circle avatar, no card -->
        <section class="space-y-5">
            <h3 class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.25em]">Masukan Atasan</h3>
            
            @if($logbook->latestReview)
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-primary/10 shrink-0 shadow-sm">
                            @if($logbook->latestReview->reviewer?->foto)
                                <img src="{{ asset('storage/' . $logbook->latestReview->reviewer->foto) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-primary text-white font-black text-xl">
                                    {{ substr($logbook->latestReview->reviewer?->nama ?? 'S', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-black text-sm text-primary">{{ $logbook->latestReview->reviewer?->nama ?? 'Supervisor' }}</p>
                                <div class="flex text-amber-500 gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i data-lucide="star" class="h-3.5 w-3.5 {{ $i <= ($logbook->latestReview->rating ?? 0) ? 'fill-current' : 'opacity-20' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-[0.55rem] font-bold text-base-content/40 uppercase tracking-widest mt-0.5">{{ $logbook->latestReview->reviewer?->role ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-primary/5 p-5 rounded-2xl border border-primary/10 italic">
                        <p class="text-sm font-bold text-primary/80 leading-relaxed italic">
                            "{{ $logbook->latestReview->comment ?? 'Luar biasa!' }}"
                        </p>
                    </div>
                </div>
            @else
                <div class="p-10 rounded-3xl border-2 border-dashed border-base-300 flex flex-col items-center justify-center text-center opacity-40">
                    <i data-lucide="clock" class="h-8 w-8 mb-3 text-base-content/30"></i>
                    <p class="text-[0.65rem] font-black uppercase tracking-[0.2em]">Menunggu Review Atasan</p>
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
