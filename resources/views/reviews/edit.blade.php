<x-layouts.app :title="$title" :active="$active">
    <div class="w-full pb-24 space-y-8 font-sans overflow-x-hidden" x-data="reviewForm({{ $logbook->items->map(fn($i) => ['id' => $i->id, 'score' => 0, 'target' => $i->kpi->target])->toJson() }}), @click="if(event.target.classList.contains('modal-trigger')) showImageModal(event.target.dataset.src, event.target.dataset.title)">
        <!-- Staff Info Section -->
        <div class="glass-morphism rounded-3xl p-8 border border-outline-variant/10">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-20 h-20 rounded-3xl border-4 border-white shadow-xl overflow-hidden bg-surface-container-high shrink-0 overflow-hidden">
                    @if($logbook->employee->foto)
                        <img src="{{ asset('storage/' . $logbook->employee->foto) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center primary-gradient text-white text-xl font-bold">{{ substr($logbook->employee->nama, 0, 2) }}</div>
                    @endif
                </div>
                <div class="flex flex-col">
                    <span class="text-[0.6rem] font-black text-on-surface/20 uppercase tracking-[0.4em] mb-1">Informasi Staff</span>
                    <h2 class="text-xl font-black text-primary uppercase tracking-tight">{{ $logbook->employee->nama }}</h2>
                    <p class="text-[0.6rem] font-bold text-on-surface/40 uppercase tracking-widest mt-1">{{ $logbook->employee->npp }} • {{ $logbook->employee->role }}</p>
                </div>
            </div>

            <div class="bg-surface-container-high/40 p-6 rounded-2xl border border-outline-variant/5">
                <p class="text-[0.55rem] font-black text-on-surface/30 uppercase tracking-widest mb-3">Laporan Aktivitas Harian</p>
                <p class="text-[0.75rem] font-medium text-on-surface/60 italic leading-relaxed">"{{ $logbook->daily_report }}"</p>
            </div>

            <!-- Time Details Section -->
            @if($logbook->start_time && $logbook->end_time)
            <div class="mt-6 space-y-4">
                <div class="flex items-center gap-3 px-2">
                    <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.2em]">Detail Waktu</span>
                    <div class="h-[1px] flex-1 bg-primary/5"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-[1.8rem] p-5 border border-primary/5 shadow-sm">
                        <p class="text-[0.6rem] font-bold text-primary/50 uppercase tracking-tight mb-2">Jam Mulai</p>
                        <p class="text-[0.9rem] font-black text-primary">{{ $logbook->start_time->format('H:i') }}</p>
                        <p class="text-[0.6rem] font-medium text-primary/40 mt-1">{{ $logbook->start_time->format('d M Y') }}</p>
                    </div>
                    <div class="bg-white rounded-[1.8rem] p-5 border border-primary/5 shadow-sm">
                        <p class="text-[0.6rem] font-bold text-primary/50 uppercase tracking-tight mb-2">Jam Akhir</p>
                        <p class="text-[0.9rem] font-black text-primary">{{ $logbook->end_time->format('H:i') }}</p>
                        <p class="text-[0.6rem] font-medium text-primary/40 mt-1">{{ $logbook->end_time->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Location Section -->
            @if($logbook->latitude && $logbook->latitude !== '-' && $logbook->longitude && $logbook->longitude !== '-')
            <div class="mt-6 space-y-4">
                <div class="flex items-center gap-3 px-2">
                    <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.2em]">Lokasi Pelaporan</span>
                    <div class="h-[1px] flex-1 bg-primary/5"></div>
                </div>
                <div class="bg-white rounded-[1.8rem] p-5 border border-primary/5 shadow-sm flex items-center justify-between hover:border-primary/20 transition-all">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[1.4rem]">location_on</span>
                        </div>
                        <div class="text-[0.75rem]">
                            <p class="font-bold text-primary/50 uppercase tracking-tight">Koordinat Lokasi</p>
                            <p class="font-black text-primary mt-1">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                        </div>
                    </div>
                    <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary hover:bg-primary/20 active:scale-90 transition-all">
                        <span class="material-symbols-outlined text-[1.2rem]">explore</span>
                    </button>
                </div>
            </div>
            @endif

            <!-- Evidence Section -->
            @if($logbook->main_photo_path)
            <div class="mt-8 space-y-4">
                <div class="flex items-center gap-3 px-2">
                    <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.2em]">Bukti Dokumentasi</span>
                    <div class="h-[1px] flex-1 bg-primary/5"></div>
                </div>
                <div class="relative aspect-[16/9] rounded-[2rem] overflow-hidden border border-primary/10 shadow-lg cursor-pointer group" @click="showImageModal('{{ asset('storage/' . $logbook->main_photo_path) }}', 'Main Evidence')">
                    <img src="{{ asset('storage/' . $logbook->main_photo_path) }}"
                         alt="Main Evidence" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute bottom-4 left-4 inline-flex items-center gap-2 px-4 py-2 bg-black/60 backdrop-blur-md rounded-xl">
                        <span class="material-symbols-outlined text-[1rem] text-white/80">photo_camera</span>
                        <span class="text-[0.55rem] font-black text-white/80 uppercase tracking-widest">CAPTURED PROOF</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Attachments Section -->
            @if($logbook->attachments->count() > 0)
            <div class="mt-8 space-y-4">
                <div class="flex items-center gap-3 px-2">
                    <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.2em]">File Pendukung</span>
                    <div class="h-[1px] flex-1 bg-primary/5"></div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($logbook->attachments as $attachment)
                    @php
                        $ext = strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    @endphp
                    <div class="flex items-center justify-between p-5 bg-white border border-outline-variant/5 rounded-2xl hover:bg-primary/2 transition-all group">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform cursor-pointer {{ $isImage ? 'modal-trigger' : '' }}" {{ $isImage ? 'data-src=' . asset('storage/' . $attachment->file_path) . ' data-title=' . basename($attachment->file_path) : 'onclick=\'window.location.href="' . asset('storage/' . $attachment->file_path) . '"\'' }}>
                                <span class="material-symbols-outlined text-[1.4rem]">{{ $isImage ? 'image' : 'description' }}</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[0.75rem] font-black text-primary uppercase tracking-tight truncate max-w-[160px]">{{ basename($attachment->file_path) }}</span>
                                <span class="text-[0.55rem] font-bold text-on-surface/40 uppercase tracking-widest">{{ strtoupper($ext) }}</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download class="material-symbols-outlined text-[1.2rem] text-primary/20 hover:text-primary transition-colors">download</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <form action="{{ route('reviews.update', $logbook->id) }}" method="POST" class="space-y-8" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            @if($logbook->status !== 'pending')
            {{-- Read-only banner for already reviewed logbooks --}}
            <div class="p-5 rounded-2xl border flex items-center gap-4
                {{ $logbook->status === 'approved' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <span class="material-symbols-outlined text-2xl {{ $logbook->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $logbook->status === 'approved' ? 'check_circle' : 'cancel' }}
                </span>
                <div>
                    <p class="text-[0.7rem] font-black uppercase tracking-widest {{ $logbook->status === 'approved' ? 'text-green-700' : 'text-red-700' }}">
                        Logbook {{ $logbook->status === 'approved' ? 'Telah Disetujui' : 'Telah Ditolak' }}
                    </p>
                    @if($logbook->latestReview)
                    <p class="text-[0.6rem] text-on-surface/50 mt-0.5">
                        Rating: {{ $logbook->latestReview->rating }}/5 ★
                        @if($logbook->latestReview->comment)
                            &bull; "{{ Str::limit($logbook->latestReview->comment, 60) }}"
                        @endif
                    </p>
                    @endif
                </div>
            </div>
            @endif

            @if($logbook->status !== 'pending')
            {{-- Prevent form submission when already reviewed --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('Logbook yang sudah direview tidak dapat diubah lagi.');
                            return false;
                        });
                    }
                });
            </script>
            @endif

            <!-- KPI Assessment -->
            <div class="space-y-4">
                <div class="px-2">
                    <h3 class="text-[0.65rem] font-black text-primary/30 uppercase tracking-[0.2em]">KPI Assessment</h3>
                </div>

                <div class="space-y-4">
                    @foreach($logbook->items as $index => $item)
                    <div class="bg-white p-7 rounded-[2rem] border border-primary/5 shadow-sm">
                        <div class="mb-4">
                            <h4 class="text-[0.9rem] font-black text-primary leading-tight tracking-tight uppercase">{{ $item->kpi->description }}</h4>
                        </div>

                        <p class="text-[0.8rem] text-primary/40 italic mb-8 leading-relaxed font-medium">
                            "{{ $item->work_description }}"
                        </p>

                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[0.6rem] font-black text-primary/30 uppercase tracking-widest">Penilaian Item</label>
                                <div class="flex items-end gap-1">
                                    <span class="text-2xl font-headline font-black text-primary leading-none">{{ $item->score ?? 0 }}</span>
                                    <span class="text-[0.65rem] font-bold text-primary/10 pb-0.5">/ {{ $item->kpi->target }}</span>
                                </div>
                            </div>
                            @if($logbook->status === 'pending')
                            <input type="range" name="items[{{ $index }}][score]" x-model.number="items[{{ $index }}].score"
                                   min="0" :max="items[{{ $index }}].target" step="1" @input="recalculateScores()"
                                   class="w-full h-2 bg-surface-container rounded-lg appearance-none cursor-pointer accent-primary">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Overall Rating -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-primary/5 shadow-sm">
                <div class="flex flex-col items-center text-center">
                    @if($logbook->status === 'pending')
                    <div class="flex items-center gap-3 mb-10">
                        <template x-for="star in 5">
                            <button type="button" @click="setStar(star)" class="transition-all duration-300 transform active:scale-90 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[3.2rem] transition-colors"
                                      :class="star <= starRating ? 'text-[#FFD700] fill-1 shadow-gold' : 'text-primary/5' ">star</span>
                            </button>
                        </template>
                    </div>
                    @else
                    @if($logbook->latestReview)
                    <p class="text-[0.7rem] font-black text-primary/40 uppercase tracking-widest mb-10">Rating: {{ $logbook->latestReview->rating }} / 5</p>
                    @endif
                    @endif

                    <input type="hidden" name="final_score" x-model.number="finalScore">
                </div>

                <div class="mt-4 space-y-4">
                    <label class="text-[0.65rem] font-black text-primary/30 uppercase tracking-widest px-1 block">Supervisor Comments</label>
                    @if($logbook->status === 'pending')
                    <textarea name="review_comment" :required="submitAction === 'approved'" rows="4"
                              placeholder="Provide detailed feedback on the performance..."
                              class="w-full bg-surface-container/30 border border-primary/5 rounded-2xl p-6 text-[0.85rem] font-medium text-primary/80 leading-relaxed placeholder:text-primary/20 focus:ring-2 focus:ring-primary/10 transition-all italic"></textarea>
                    @else
                    <div class="w-full bg-surface-container/30 border border-primary/5 rounded-2xl p-6 text-[0.85rem] font-medium text-primary/60 leading-relaxed italic">
                        {{ $logbook->latestReview->comment ?? '-' }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @if($logbook->status === 'pending')
            <div class="flex items-center gap-4 pb-12">
                <button type="submit" name="status" value="rejected" @click="submitAction = 'rejected'"
                    class="flex-1 py-5 bg-red-100 rounded-[1.2rem] text-center text-[0.9rem] font-bold text-red-900 active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    <span x-show="!(isSubmitting && submitAction === 'rejected')">Tolak</span>
                    <span x-show="isSubmitting && submitAction === 'rejected'" class="inline-flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border-2 border-red-800 border-t-transparent animate-spin"></span>
                        Memproses...
                    </span>
                </button>
                <button type="submit" name="status" value="approved" @click="submitAction = 'approved'"
                    class="flex-[3] py-5 bg-primary rounded-[1.2rem] text-white shadow-xl shadow-primary/20 text-[0.9rem] font-bold active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    <span x-show="!(isSubmitting && submitAction === 'approved')">Simpan Penilaian</span>
                    <span x-show="isSubmitting && submitAction === 'approved'" class="inline-flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                        Menyimpan...
                    </span>
                </button>
            </div>
            @endif
        </form>
    </div>

    <script>
        function reviewForm(initialItems) {
            return {
                items: initialItems,
                starRating: {{ $logbook->latestReview?->rating ?? 0 }},
                finalScore: {{ $logbook->latestReview?->rating ? $logbook->latestReview->rating * 20 : 0 }},
                isSubmitting: false,
                submitAction: '',

                init() {
                    // Initialize starRating from existing review if present
                    if ('{{ $logbook->status }}' !== 'pending' && {{ $logbook->latestReview?->rating ?? 0 }} > 0) {
                        this.starRating = {{ $logbook->latestReview->rating }};
                        this.finalScore = {{ $logbook->latestReview->rating * 20 }};
                    }
                },

                recalculateScores() {
                    // This function is called when sliders change
                    // but we don't update starRating here
                    // only update the individual item display
                },

                setStar(star) {
                    this.starRating = star;
                    this.finalScore = star * 20;
                },

                showImageModal(src, title) {
                    this.$nextTick(() => {
                        const modal = document.createElement('div');
                        modal.className = 'fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4';
                        modal.innerHTML = `<div class='max-w-2xl w-full max-h-[90vh] flex flex-col'><div class='flex items-center justify-between mb-4'><p class='text-white text-sm font-bold'>${title}</p><button onclick='this.closest(".fixed").remove()' class='text-white text-2xl'>&times;</button></div><img src='${src}' class='w-full h-auto max-h-[80vh] object-contain rounded-2xl'></div>`;
                        document.body.appendChild(modal);
                        modal.addEventListener('click', (e) => { if(e.target === modal) modal.remove(); });
                    });
                },

                prepareSubmit(action) {
                    this.submitAction = action;
                    this.isSubmitting = true;
                }
            }
        }
    </script>
</x-layouts.app>
