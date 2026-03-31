<x-layouts.app :title="$title" :active="$active" flat="true" hideNav="true" :backUrl="route('reviews.index')">
    <div class="w-full pb-24"
         x-data="reviewForm({{ $logbook->items->map(fn($i) => ['id' => $i->id, 'score' => $i->score ?? 0, 'target' => $i->kpi->target])->toJson() }})"
         @click="if(event.target.classList.contains('modal-trigger')) showImageModal(event.target.dataset.src, event.target.dataset.title)">

        <!-- Staff Identity - flat, no card -->
        <div class="flex items-center gap-4 pb-6 border-b border-base-200">
            <div class="w-14 h-14 rounded-full bg-base-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm border border-base-200">
                @if($logbook->employee->foto)
                    <img src="{{ asset('storage/' . $logbook->employee->foto) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-primary flex items-center justify-center text-white text-xl font-black">{{ substr($logbook->employee->nama, 0, 1) }}</div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-0.5">Pelapor</p>
                <p class="font-black text-primary text-base leading-tight truncate">{{ $logbook->employee->nama }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[0.55rem] font-black text-primary bg-primary/10 px-2 py-0.5 rounded-full uppercase tracking-widest">ID_{{ $logbook->employee->npp }}</span>
                    <span class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest">{{ strtoupper($logbook->employee->role) }}</span>
                </div>
            </div>
            @php
                $statusColors = ['approved' => 'bg-success/10 text-success', 'rejected' => 'bg-error/10 text-error', 'pending' => 'bg-warning/10 text-warning'];
            @endphp
            <span class="text-[0.55rem] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shrink-0 {{ $statusColors[$logbook->status] ?? 'bg-base-200' }}">
                {{ $logbook->status }}
            </span>
        </div>

        <!-- Alert if already reviewed -->
        @if($logbook->status !== 'pending')
        <div class="py-4 border-b border-base-200">
            <div class="flex items-center gap-3 p-4 rounded-2xl {{ $logbook->status === 'approved' ? 'bg-success/5 border border-success/20' : 'bg-error/5 border border-error/20' }}">
                <i data-lucide="{{ $logbook->status === 'approved' ? 'check-circle' : 'x-circle' }}" class="h-5 w-5 {{ $logbook->status === 'approved' ? 'text-success' : 'text-error' }} shrink-0"></i>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest {{ $logbook->status === 'approved' ? 'text-success' : 'text-error' }}">
                        Logbook {{ $logbook->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                    </p>
                    @if($logbook->latestReview)
                    <p class="text-[0.55rem] font-bold text-base-content/40 mt-0.5 uppercase tracking-widest">
                        Rating: {{ $logbook->latestReview->rating }}/5 • Skor: {{ $logbook->latestReview->final_score }}%
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="divide-y divide-base-200">

            <!-- TIME METADATA -->
            <div class="py-5 space-y-3">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Waktu Aktivitas</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-base-200/50 rounded-2xl p-4 text-center">
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Mulai</p>
                        <p class="text-lg font-black text-primary">{{ $logbook->start_time->format('H:i') }}</p>
                        <p class="text-[0.55rem] font-bold text-base-content/30 uppercase mt-0.5">{{ $logbook->start_time->format('d M Y') }}</p>
                    </div>
                    <div class="bg-base-200/50 rounded-2xl p-4 text-center">
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Selesai</p>
                        <p class="text-lg font-black text-primary">{{ $logbook->end_time->format('H:i') }}</p>
                        <p class="text-[0.55rem] font-bold text-base-content/30 uppercase mt-0.5">{{ $logbook->end_time->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- GPS COORDINATE -->
            @if($logbook->latitude && $logbook->latitude !== '-')
            <div class="py-5 space-y-3">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Koordinat Lokasi</p>
                <div class="flex items-center justify-between bg-base-200/50 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <i data-lucide="crosshair" class="h-4 w-4"></i>
                        </div>
                        <p class="text-xs font-black text-primary font-mono">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                    </div>
                    <button type="button" onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})"
                            class="btn btn-primary btn-sm rounded-xl gap-1.5 font-black text-[0.6rem] uppercase tracking-widest">
                        <i data-lucide="map" class="h-3 w-3"></i> MAP
                    </button>
                </div>
            </div>
            @endif

            <!-- LAPORAN HARIAN -->
            <div class="py-5 space-y-3">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Laporan Harian</p>
                <p class="text-sm font-bold text-base-content/60 italic leading-relaxed bg-base-200/40 rounded-2xl p-4">
                    "{{ $logbook->daily_report }}"
                </p>
            </div>

            <!-- FOTO DOKUMENTASI (expand only, no download) -->
            @if($logbook->main_photo_path)
            <div class="py-5 space-y-3" x-data="{ photoOpen: false }">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Foto Dokumentasi</p>
                <div class="relative aspect-[4/3] rounded-2xl overflow-hidden border border-base-300 cursor-pointer group"
                     @click="showImageModal('{{ asset('storage/' . $logbook->main_photo_path) }}', 'Foto Dokumentasi')">
                    <img src="{{ asset('storage/' . $logbook->main_photo_path) }}"
                         alt="Foto Dokumentasi" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <i data-lucide="maximize-2" class="h-8 w-8 text-white"></i>
                    </div>
                    <div class="absolute bottom-3 left-3 flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-md rounded-xl">
                        <span class="w-1.5 h-1.5 rounded-full bg-error animate-pulse"></span>
                        <span class="text-[0.55rem] font-black text-white uppercase tracking-widest">Live Captured</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- LAMPIRAN FILE (with download) -->
            @php
                $fileAttachments = $logbook->attachments->filter(fn($a) => $a->file_type === 'file');
            @endphp
            @if($fileAttachments->count() > 0)
            <div class="py-5 space-y-3">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Lampiran File</p>
                <div class="space-y-2">
                    @foreach($fileAttachments as $attachment)
                    @php $ext = strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION)); @endphp
                    <div class="flex items-center gap-3 p-4 bg-base-200/50 rounded-2xl border border-base-300">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <i data-lucide="file-text" class="h-5 w-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black text-primary truncate">{{ basename($attachment->file_path) }}</p>
                            <p class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mt-0.5">{{ strtoupper($ext) }} FILE</p>
                        </div>
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download
                           class="btn btn-ghost btn-sm btn-square text-primary/40 hover:text-primary hover:bg-primary/10 rounded-xl transition-colors">
                            <i data-lucide="download" class="h-4 w-4"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- KPI EVALUATION -->
            <form action="{{ route('reviews.update', $logbook->id) }}" method="POST" class="space-y-0" @submit="isSubmitting = true">
                @csrf
                @method('PUT')

                <div class="py-5 space-y-4">
                    <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Evaluasi KPI</p>
                    <div class="space-y-4">
                        @foreach($logbook->items as $index => $item)
                        <div class="bg-base-200/30 rounded-2xl p-4 border border-base-300 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <span class="text-[0.5rem] font-black text-primary bg-primary/10 px-2 py-0.5 rounded-full uppercase tracking-widest">KPI #{{ $index + 1 }}</span>
                                    <p class="text-xs font-black text-primary uppercase tracking-tight mt-2 leading-tight">{{ $item->kpi->description }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-2xl font-black text-primary tabular-nums leading-none" x-text="items[{{ $index }}].score">0</p>
                                    <p class="text-[0.5rem] font-black text-base-content/30 uppercase">/ {{ $item->kpi->target }}</p>
                                </div>
                            </div>
                            <p class="text-[0.65rem] font-bold text-base-content/50 italic leading-relaxed bg-base-200/50 rounded-xl p-3">
                                "{{ $item->work_description }}"
                            </p>

                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

                            @if($logbook->status === 'pending')
                            <div class="space-y-1">
                                <input type="range" name="items[{{ $index }}][score]"
                                       x-model.number="items[{{ $index }}].score"
                                       min="0" :max="items[{{ $index }}].target" step="1"
                                       class="range range-primary h-2 rounded-full">
                                <div class="flex justify-between text-[0.5rem] font-black text-base-content/20 uppercase tracking-widest px-1">
                                    <span>0</span>
                                    <span>{{ $item->kpi->target }}</span>
                                </div>
                            </div>
                            @else
                            <div class="w-full h-2 bg-base-300 rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full" :style="'width: ' + ((items[{{ $index }}].score / items[{{ $index }}].target) * 100) + '%'"></div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- STAR RATING (sempre visible, prominent) -->
                <div class="py-5 space-y-4 border-t border-base-200">
                    <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Rating Keseluruhan</p>

                    <div class="flex items-center justify-center gap-3 py-2">
                        @if($logbook->status === 'pending')
                            <template x-for="star in 5">
                                <button type="button" @click="setStar(star)"
                                        class="transition-all duration-200 hover:scale-125 active:scale-95 outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 transition-all duration-300" 
                                         :fill="star <= starRating ? '#FFC107' : 'none'"
                                         :stroke="star <= starRating ? '#FFC107' : '#E2E8F0'"
                                         viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </button>
                            </template>
                        @else
                            <template x-for="star in 5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" 
                                     :fill="star <= starRating ? '#FFC107' : 'none'"
                                     :stroke="star <= starRating ? '#FFC107' : '#E2E8F0'"
                                     viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </template>
                        @endif
                    </div>
                    <p class="text-center text-[0.6rem] font-black text-base-content/30 uppercase tracking-widest" x-text="starRating > 0 ? starRating + ' dari 5 bintang' : 'Belum diberi rating'"></p>

                    <input type="hidden" name="final_score" x-model.number="finalScore">
                    <input type="hidden" name="rating" x-model.number="starRating">
                </div>

                <!-- SUPERVISOR COMMENT -->
                <div class="py-5 space-y-3 border-t border-base-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="message-square" class="h-4 w-4 text-primary/30"></i>
                        <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Komentar Supervisor</p>
                    </div>

                    @if($logbook->status === 'pending')
                    <textarea name="review_comment" :required="submitAction === 'approved'" rows="5"
                              placeholder="Tuliskan umpan balik atau alasan penolakan..."
                              class="textarea textarea-bordered w-full rounded-2xl px-4 py-3 text-sm font-bold text-base-content/60 leading-relaxed bg-base-200/50 border-base-300 focus:border-primary/30 italic resize-none"></textarea>
                    @else
                    <div class="bg-base-200/40 border border-base-300 rounded-2xl p-4 text-sm font-bold text-base-content/50 italic leading-relaxed">
                        "{{ $logbook->latestReview->comment ?? 'Tidak ada komentar.' }}"
                    </div>
                    @endif
                </div>

                <!-- ACTION BUTTONS -->
                @if($logbook->status === 'pending')
                <div class="pt-4 space-y-3 border-t border-base-200">
                    <button type="submit" name="status" value="approved" @click="submitAction = 'approved'"
                            class="btn btn-primary w-full h-14 rounded-2xl gap-3 font-black text-sm uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all">
                        <template x-if="!(isSubmitting && submitAction === 'approved')">
                            <div class="flex items-center gap-3">
                                <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                                <span>Sahkan Penilaian</span>
                            </div>
                        </template>
                        <template x-if="isSubmitting && submitAction === 'approved'">
                            <div class="flex items-center gap-3">
                                <span class="loading loading-spinner loading-sm"></span>
                                <span>Menyimpan...</span>
                            </div>
                        </template>
                    </button>

                    <button type="submit" name="status" value="rejected" @click="submitAction = 'rejected'"
                            class="btn btn-ghost w-full h-12 rounded-2xl gap-3 font-black text-xs uppercase tracking-widest text-error hover:bg-error/10 border border-error/20 transition-all">
                        <template x-if="!(isSubmitting && submitAction === 'rejected')">
                            <div class="flex items-center gap-3">
                                <i data-lucide="x-circle" class="h-4 w-4"></i>
                                <span>Tolak Laporan</span>
                            </div>
                        </template>
                        <template x-if="isSubmitting && submitAction === 'rejected'">
                            <div class="flex items-center gap-3">
                                <span class="loading loading-spinner loading-xs"></span>
                                <span>Memproses...</span>
                            </div>
                        </template>
                    </button>
                </div>
                @endif

            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <dialog id="img_modal" class="modal modal-bottom sm:modal-middle bg-black/40 backdrop-blur-xl">
        <div class="modal-box p-0 bg-transparent shadow-none w-full max-w-4xl overflow-hidden">
            <div class="relative">
                <img id="modal_img_src" src="" class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl mx-auto">
                <div class="absolute top-4 right-4">
                    <form method="dialog">
                        <button class="btn btn-circle btn-sm btn-primary shadow-xl border-none">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script>
        function reviewForm(initialItems) {
            return {
                items: initialItems,
                starRating: {{ $logbook->latestReview?->rating ?? 0 }},
                finalScore: {{ $logbook->latestReview?->final_score ?? 0 }},
                isSubmitting: false,
                submitAction: '',

                init() {
                    this.$nextTick(() => { lucide.createIcons(); });
                },

                setStar(star) {
                    this.starRating = star;
                    this.finalScore = star * 20;
                    // Re-render lucide icons after Alpine update
                    this.$nextTick(() => { lucide.createIcons(); });
                },

                showImageModal(src, title) {
                    const modal = document.getElementById('img_modal');
                    document.getElementById('modal_img_src').src = src;
                    modal.showModal();
                    this.$nextTick(() => { lucide.createIcons(); });
                }
            }
        }
    </script>
</x-layouts.app>
