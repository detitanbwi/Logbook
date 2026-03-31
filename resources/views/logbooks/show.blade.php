<x-layouts.app title="Detail Logbook" active="logbooks" flat="true" hideNav="true" :backUrl="route('logbooks.index')">
    <div class="space-y-4 pb-12 px-1">
        <!-- Compact Status Banner -->
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $logbook->status === 'approved' ? 'bg-success/10 text-success' : ($logbook->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-warning/10 text-warning') }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $logbook->status === 'approved' ? 'check-circle' : ($logbook->status === 'rejected' ? 'x-circle' : 'clock') }}" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-primary uppercase tracking-wider">Logbook</h2>
                    <p class="text-[0.6rem] font-bold text-base-content/30 uppercase tracking-widest mt-0.5">ID Aktivitas #{{ $logbook->id }}</p>
                </div>
            </div>
            @php
                $statusColors = [
                    'approved' => 'bg-success/10 text-success border-success/20',
                    'rejected' => 'bg-error/10 text-error border-error/20',
                    'pending' => 'bg-warning/10 text-warning border-warning/20',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full {{ $statusColors[$logbook->status] ?? 'bg-base-200' }} border text-[0.55rem] font-black uppercase tracking-widest">
                {{ $logbook->status }}
            </span>
        </div>

        <!-- Details Card: Time & Location -->
        <div class="bg-base-100 rounded-xl border border-base-200 shadow-sm overflow-hidden divide-y divide-base-100">
            <div class="p-4 flex items-center gap-4">
                <div class="w-8 h-8 rounded-lg bg-base-50 flex items-center justify-center text-base-content/30 shrink-0 border border-base-200/50">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                </div>
                <div>
                    <p class="text-[0.6rem] uppercase font-black text-base-content/20 tracking-widest mb-0.5">Waktu Aktivitas</p>
                    <p class="text-[0.7rem] font-bold text-base-content leading-none">
                        {{ $logbook->start_time->isoFormat('D MMM Y') }} • {{ $logbook->start_time->format('H:i') }} - {{ $logbook->end_time->format('H:i') }}
                    </p>
                </div>
            </div>

            @if($logbook->latitude && $logbook->latitude !== '-')
            <div class="p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-base-50 flex items-center justify-center text-base-content/30 shrink-0 border border-base-200/50">
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <p class="text-[0.6rem] uppercase font-black text-base-content/20 tracking-widest mb-0.5">Lokasi Presensi</p>
                        <p class="text-[0.65rem] font-bold text-base-content font-mono">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                    </div>
                </div>
                <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" 
                        class="btn btn-ghost btn-xs text-primary border border-primary/10 rounded-lg font-black tracking-widest">
                    MAP
                </button>
            </div>
            @endif
        </div>

        <!-- Documentation Card -->
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm space-y-3" x-data="{ modalOpen: false }">
            <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest">Dokumentasi Foto</p>
            <div class="relative aspect-video rounded-lg overflow-hidden bg-base-50 border border-base-200/50 group">
                @if($logbook->main_photo_path)
                    @php $photoUrl = asset('storage/' . $logbook->main_photo_path); @endphp
                    <img src="{{ $photoUrl }}" alt="Documentation" class="w-full h-full object-cover">
                    <button type="button" @click="modalOpen = true"
                        class="absolute top-2 right-2 btn btn-circle btn-xs bg-white/80 backdrop-blur-sm border-none shadow-lg text-primary">
                        <i data-lucide="maximize-2" class="h-3 w-3"></i>
                    </button>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-base-content/10">
                        <i data-lucide="camera" class="h-8 w-8 mb-1"></i>
                        <p class="text-[0.55rem] font-black uppercase">No Photo</p>
                    </div>
                @endif
            </div>

            <!-- Light Modal -->
            @if($logbook->main_photo_path)
            <dialog class="modal" :class="modalOpen ? 'modal-open' : ''">
                <div class="modal-box p-0 bg-transparent shadow-none max-w-4xl">
                    <div class="relative bg-base-100 rounded-2xl overflow-hidden shadow-2xl">
                        <div class="p-3 flex justify-between items-center border-b border-base-100">
                            <span class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest ml-2">Preview Foto</span>
                            <button @click="modalOpen = false" class="btn btn-xs btn-circle btn-ghost">
                                <i data-lucide="x" class="h-3 w-3"></i>
                            </button>
                        </div>
                        <div class="p-3"><img src="{{ $photoUrl }}" class="w-full h-auto rounded-lg"></div>
                    </div>
                </div>
                <div class="modal-backdrop bg-black/70 backdrop-blur-sm" @click="modalOpen = false"></div>
            </dialog>
            @endif
        </div>

        <!-- KPI Items Card -->
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm space-y-4">
             <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest">Detail Laporan KPI</p>
             <div class="space-y-4">
                @foreach($logbook->items as $item)
                    <div class="space-y-2 last:mb-0">
                        <div class="flex items-start justify-between">
                            <p class="text-[0.65rem] font-black text-primary uppercase leading-tight pr-4">{{ $item->kpi->description }}</p>
                            <span class="px-2 py-0.5 rounded-lg bg-base-50 border border-base-200 text-primary font-black text-[0.65rem] shrink-0">
                                {{ $item->score ?? 0 }}
                            </span>
                        </div>
                        <div class="bg-base-50 p-3 rounded-lg border border-base-200/50 italic text-[0.7rem] font-bold text-base-content/60 leading-relaxed">
                            "{{ $item->work_description }}"
                        </div>
                    </div>
                    @if(!$loop->last) <div class="h-[1px] bg-base-100"></div> @endif
                @endforeach
             </div>
        </div>

        <!-- Daily Report Card -->
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm space-y-3">
            <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest">Laporan Harian (Summary)</p>
            <div class="p-4 rounded-lg bg-base-50/50 border border-dashed border-base-300 italic text-[0.75rem] font-bold text-base-content/80 leading-relaxed">
                "{{ $logbook->daily_report }}"
            </div>
        </div>

        <!-- Supervisor Review Card -->
        @if($logbook->latestReview)
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm space-y-4">
            <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest">Penilaian Atasan</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-base-200 ring-2 ring-base-50 shadow-sm">
                    @if($logbook->latestReview->reviewer?->foto)
                        <img src="{{ asset('storage/' . $logbook->latestReview->reviewer->foto) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-primary text-white flex items-center justify-center font-black text-xs">{{ substr($logbook->latestReview->reviewer?->nama ?? 'S', 0, 1) }}</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-black text-[0.75rem] text-primary truncate leading-tight">{{ $logbook->latestReview->reviewer?->nama }}</p>
                        <div class="flex text-amber-500 gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="h-2.5 w-2.5 {{ $i <= ($logbook->latestReview->rating ?? 0) ? 'fill-current' : 'opacity-20' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-[0.55rem] font-bold text-base-content/40 uppercase tracking-widest mt-0.5 truncate">{{ $logbook->latestReview->reviewer?->role }}</p>
                </div>
            </div>
            <div class="p-4 rounded-xl bg-primary/[0.03] border border-primary/10 italic text-[0.75rem] font-bold text-primary/80">
                "{{ $logbook->latestReview->comment }}"
            </div>
        </div>
        @else
        <div class="p-8 rounded-xl border border-dashed border-base-300 text-center">
            <i data-lucide="clock" class="h-6 w-6 mx-auto mb-2 text-base-content/20"></i>
            <p class="text-[0.6rem] font-black uppercase text-base-content/30 tracking-widest">Waiting for Supervisor Review</p>
        </div>
        @endif
    </div>
</x-layouts.app>
