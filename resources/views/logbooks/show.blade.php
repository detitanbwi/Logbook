<x-layouts.app title="Detail Logbook" active="logbooks" hideNav="true" :backUrl="route('logbooks.index')">
    <main class="space-y-8 pb-12">
        <!-- Header & Status Section -->
        <section class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[0.6rem] font-black text-base-content/30 uppercase tracking-[0.2em] leading-none mb-2">ENTRY DETAILS</p>
                    <h2 class="text-3xl font-black text-primary leading-tight">Logbook</h2>
                </div>
                @php
                    $statusStyles = [
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'pending' => 'bg-[#FFDBCB] text-[#341100]',
                    ];
                @endphp
                <span class="px-5 py-2 rounded-full {{ $statusStyles[$logbook->status] ?? 'bg-base-300' }} text-[0.6rem] font-black uppercase tracking-widest shadow-sm">
                    {{ $logbook->status }}
                </span>
            </div>

            <!-- Details Card -->
            <div class="bg-base-100 p-6 rounded-3xl shadow-sm border border-base-300 space-y-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shrink-0">
                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-[0.6rem] uppercase font-black text-base-content/30 tracking-widest mb-1">Koordinat Lokasi</p>
                        <p class="text-base-content font-black text-xs leading-tight font-mono">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                    </div>
                    @if($logbook->latitude && $logbook->latitude !== '-')
                    <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" class="btn btn-primary btn-sm rounded-xl gap-2 font-black text-[0.6rem] uppercase tracking-widest shadow-lg shadow-primary/10">
                        <i data-lucide="map" class="h-3 w-3"></i>
                        LIHAT MAP
                    </button>
                    @endif
                </div>
                <div class="flex items-start gap-4 pt-6 border-t border-base-200">
                    <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shrink-0">
                        <i data-lucide="clock" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-[0.6rem] uppercase font-black text-base-content/30 tracking-widest mb-1">Waktu Aktivitas</p>
                        <p class="text-base-content font-black text-xs leading-tight">
                            {{ $logbook->start_time->isoFormat('D MMMM Y') }} • {{ $logbook->start_time->format('H:i') }} - {{ $logbook->end_time->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Documentation Photo -->
        <section class="space-y-4">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.2em] flex items-center gap-2">
                FOTO DOKUMENTASI <span class="h-[1px] flex-1 bg-primary/10"></span>
            </h3>
            <div class="relative aspect-video rounded-3xl overflow-hidden bg-base-300 shadow-xl border border-base-300" id="photo-container">
                @if($logbook->main_photo_path)
                    @php
                        $photoUrl = asset('storage/' . $logbook->main_photo_path);
                    @endphp
                    <img src="{{ $photoUrl }}" 
                         alt="Documentation" 
                         class="w-full h-full object-cover"
                         id="doc-photo"
                         onerror="this.style.display='none'; document.getElementById('photo-error').style.display='flex';">
                    <div id="photo-error" class="w-full h-full absolute inset-0 flex flex-col items-center justify-center bg-base-200 text-base-content/30" style="display:none;">
                        <i data-lucide="image-off" class="h-10 w-10 mb-3"></i>
                        <p class="text-[0.6rem] font-black uppercase tracking-widest mb-1">Foto tidak dapat dimuat</p>
                        <p class="text-[0.5rem] font-mono text-base-content/20 break-all px-4">{{ $photoUrl }}</p>
                    </div>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-base-content/20 bg-base-200">
                        <i data-lucide="camera" class="h-10 w-10 mb-2"></i>
                        <p class="text-[0.6rem] font-black uppercase tracking-widest">Tidak ada foto</p>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                <div class="absolute bottom-4 left-4 flex items-center gap-2 text-white/90">
                    <i data-lucide="camera" class="h-3 w-3"></i>
                    <span class="text-[0.55rem] font-black uppercase tracking-widest">Entry ID #{{ $logbook->id }}</span>
                </div>
                <!-- Watermark -->
                <div class="absolute bottom-4 right-4 z-20 pointer-events-none">
                    <span class="text-[0.5rem] font-black text-white bg-black/40 border border-white/10 px-2 py-1 rounded-lg tracking-[0.2em] uppercase backdrop-blur-md">WIRODEV DEMO</span>
                </div>
            </div>
        </section>


        <!-- KPI items -->
        <section class="space-y-4">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.2em] flex items-center gap-2">
                DETAIL PEKERJAAN (KPI) <span class="h-[1px] flex-1 bg-primary/10"></span>
            </h3>
            <div class="grid grid-cols-1 gap-4">
                @foreach($logbook->items as $item)
                <div class="bg-base-100 p-5 rounded-2xl border-l-4 {{ $loop->first ? 'border-primary' : 'border-base-300' }} border-t border-r border-b border-base-300 shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1 pr-4">
                            <p class="text-[0.65rem] font-black text-primary uppercase tracking-tight mb-0.5">{{ $item->kpi->description }}</p>
                            <p class="text-[0.5rem] font-bold text-base-content/40 uppercase tracking-widest">Item Pekerjaan #{{ $loop->iteration }}</p>
                        </div>
                        <div class="bg-primary/5 px-2.5 py-1.5 rounded-xl border border-primary/10 text-center min-w-[3.5rem]">
                            <p class="text-xs font-black text-primary leading-none mb-0.5">{{ $item->score ?? '-' }}</p>
                            <p class="text-[0.45rem] font-bold text-primary/40 uppercase">Skor</p>
                        </div>
                    </div>
                    <div class="bg-base-200/50 p-3 rounded-xl">
                        <p class="text-[0.7rem] font-bold text-base-content/70 italic leading-relaxed">
                            "{{ $item->work_description }}"
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Daily Report -->
        <section class="space-y-4">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.2em] flex items-center gap-2">
                LAPORAN HARIAN <span class="h-[1px] flex-1 bg-primary/10"></span>
            </h3>
            <div class="bg-base-100 p-6 rounded-3xl shadow-sm border border-base-300">
                <p class="text-base-content/80 leading-relaxed text-[0.75rem] font-bold italic">
                    "{{ $logbook->daily_report }}"
                </p>
                <div class="mt-6 flex items-center gap-3 pt-4 border-t border-base-200">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary/40">
                         <i data-lucide="check-circle" class="h-4 w-4"></i>
                    </div>
                    <span class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest">Sistem Verifikasi Otomatis</span>
                </div>
            </div>
        </section>

        <!-- Supervisor Feedback -->
        <section class="space-y-4 pb-10">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.2em] flex items-center gap-2">
                MASUKAN ATASAN <span class="h-[1px] flex-1 bg-primary/10"></span>
            </h3>
            
            @if($logbook->latestReview)
                <div class="bg-primary/5 p-6 rounded-3xl border border-primary/10 shadow-sm space-y-5">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-base-300 p-1 shadow-sm shrink-0">
                                @if($logbook->latestReview->reviewer?->foto)
                                    <img src="{{ asset('storage/' . $logbook->latestReview->reviewer->foto) }}" alt="Supervisor" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-primary text-white font-black text-lg rounded-xl">
                                        {{ substr($logbook->latestReview->reviewer?->nama ?? 'S', 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-black text-xs text-primary leading-tight mb-1">{{ $logbook->latestReview->reviewer?->nama ?? 'Supervisor' }}</p>
                                <p class="text-[0.55rem] font-bold text-base-content/40 uppercase tracking-widest">{{ $logbook->latestReview->reviewer?->role ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex text-amber-500 gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="h-3 w-3 {{ $i <= ($logbook->latestReview->rating ?? 0) ? 'fill-current' : 'opacity-20' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-white/60 p-4 rounded-2xl border border-primary/5">
                        <p class="text-[0.7rem] font-bold italic text-primary/80 leading-relaxed">
                            "{{ $logbook->latestReview->comment ?? 'Sangat detail dan rapi. Lanjutkan!' }}"
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-base-300/10 p-10 rounded-3xl border-2 border-dashed border-base-300 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center text-base-content/20 mb-3">
                        <i data-lucide="clock" class="h-6 w-6"></i>
                    </div>
                    <p class="text-[0.65rem] font-black text-base-content/40 uppercase tracking-[0.2em]">Menunggu Review Atasan</p>
                    <p class="text-[0.55rem] font-bold text-base-content/20 uppercase tracking-widest mt-1">Status: PENDING</p>
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
