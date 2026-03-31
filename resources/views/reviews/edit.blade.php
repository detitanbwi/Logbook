<x-layouts.app :title="$title" :active="$active">
    <div class="w-full pb-24 space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700" 
         x-data="reviewForm({{ $logbook->items->map(fn($i) => ['id' => $i->id, 'score' => $i->score ?? 0, 'target' => $i->kpi->target])->toJson() }})"
         @click="if(event.target.classList.contains('modal-trigger')) showImageModal(event.target.dataset.src, event.target.dataset.title)">
        
        <!-- Header & Staff Profile Section -->
        <div class="card bg-base-100 rounded-[2.5rem] p-10 border border-base-300 shadow-sm overflow-hidden relative">
            <div class="flex flex-col lg:flex-row items-center lg:items-start gap-10 relative z-10">
                <div class="avatar border-4 border-primary/10 rounded-[2rem] overflow-hidden bg-base-200 shrink-0 shadow-xl">
                    <div class="w-32 h-32 lg:w-40 lg:h-40">
                        @if($logbook->employee->foto)
                            <img src="{{ asset('storage/' . $logbook->employee->foto) }}" class="object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-primary text-white text-3xl font-black">
                                {{ substr($logbook->employee->nama, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex-1 text-center lg:text-left space-y-6">
                    <div>
                        <p class="text-[0.7rem] font-bold text-primary/40 uppercase tracking-[0.4em] mb-3 leading-none">Profil Pelapor</p>
                        <h2 class="text-3xl lg:text-5xl font-black text-primary uppercase tracking-tight leading-none">
                            {{ $logbook->employee->nama }}
                        </h2>
                        <div class="mt-5 flex flex-wrap justify-center lg:justify-start gap-3">
                            <span class="badge badge-primary badge-lg py-4 px-6 text-[0.65rem] font-black uppercase tracking-widest border-none">
                                ID_{{ $logbook->employee->npp }}
                            </span>
                            <span class="badge bg-base-200 border-base-300 text-base-content/40 badge-lg py-4 px-6 text-[0.65rem] font-black uppercase tracking-widest">
                                {{ strtoupper($logbook->employee->role) }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-8 rounded-3xl border border-base-300 relative group">
                        <p class="text-[0.6rem] font-black text-base-content/20 uppercase tracking-widest mb-4 leading-none">Daily Report Summary</p>
                        <p class="text-sm lg:text-base font-bold text-base-content/60 italic leading-relaxed">
                            "{{ $logbook->daily_report }}"
                        </p>
                        <i data-lucide="quote" class="absolute top-6 right-8 h-8 w-8 text-base-content/5 opacity-40"></i>
                    </div>
                </div>
            </div>

            <!-- Decorative light blobs -->
            <div class="absolute -top-12 -right-12 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 w-48 h-48 bg-primary/5 rounded-full blur-2xl"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            <!-- Details Column (Left/Top) -->
            <div class="lg:col-span-4 space-y-12">
                <!-- Time Metadata Section -->
                <section class="space-y-6">
                    <div class="flex items-center gap-4 px-2">
                        <i data-lucide="clock" class="h-4 w-4 text-primary/40"></i>
                        <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em]">Log Metadata</h3>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="card bg-base-100 p-6 rounded-3xl border border-base-300 shadow-sm text-center">
                            <p class="text-[0.6rem] font-black text-base-content/30 uppercase tracking-widest mb-2 leading-none">Mulai</p>
                            <p class="text-lg font-black text-primary leading-tight">{{ $logbook->start_time->format('H:i') }}</p>
                            <p class="text-[0.6rem] font-black text-base-content/20 mt-1 uppercase tracking-tight">{{ $logbook->start_time->format('d M Y') }}</p>
                        </div>
                        <div class="card bg-base-100 p-6 rounded-3xl border border-base-300 shadow-sm text-center">
                            <p class="text-[0.6rem] font-black text-base-content/30 uppercase tracking-widest mb-2 leading-none">Selesai</p>
                            <p class="text-lg font-black text-primary leading-tight">{{ $logbook->end_time->format('H:i') }}</p>
                            <p class="text-[0.6rem] font-black text-base-content/20 mt-1 uppercase tracking-tight">{{ $logbook->end_time->format('d M Y') }}</p>
                        </div>
                    </div>
                </section>

                <!-- Location Track Section -->
                @if($logbook->latitude && $logbook->latitude !== '-' && $logbook->longitude && $logbook->longitude !== '-')
                <section class="space-y-6">
                    <div class="flex items-center gap-4 px-2">
                        <i data-lucide="map-pin" class="h-4 w-4 text-primary/40"></i>
                        <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em]">Location Data</h3>
                    </div>
                    
                    <div class="card bg-base-100 p-8 rounded-3xl border border-base-300 shadow-sm flex flex-row items-center justify-between hover:border-primary/20 transition-all group overflow-hidden">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
                                <i data-lucide="crosshair" class="h-6 w-6"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[0.6rem] font-black text-base-content/20 uppercase tracking-widest leading-none">Koordinat GPS</p>
                                <p class="text-xs font-black text-primary font-mono tabular-nums">{{ $logbook->latitude }}, {{ $logbook->longitude }}</p>
                            </div>
                        </div>
                        <button onclick="window.HRISNative.viewLocation({{ $logbook->latitude }}, {{ $logbook->longitude }})" 
                                class="btn btn-square btn-primary rounded-xl shadow-lg shadow-primary/20 hover:scale-110 active:scale-90 transition-all">
                            <i data-lucide="external-link" class="h-5 w-5"></i>
                        </button>
                    </div>
                </section>
                @endif

                <!-- Visual Evidence Section -->
                @if($logbook->main_photo_path)
                <section class="space-y-6">
                    <div class="flex items-center gap-4 px-2">
                        <i data-lucide="camera" class="h-4 w-4 text-primary/40"></i>
                        <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em]">Evidence Proof</h3>
                    </div>
                    
                    <div class="relative aspect-[4/3] rounded-[2.5rem] overflow-hidden border-4 border-base-100 shadow-xl cursor-pointer group" 
                         @click="showImageModal('{{ asset('storage/' . $logbook->main_photo_path) }}', 'Main Evidence')">
                        <img src="{{ asset('storage/' . $logbook->main_photo_path) }}"
                             alt="Main Evidence" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                            <i data-lucide="maximize" class="h-10 w-10 text-white"></i>
                        </div>
                        <div class="absolute bottom-6 left-6 inline-flex items-center gap-3 px-5 py-2.5 bg-black/70 backdrop-blur-md rounded-2xl border border-white/10">
                            <span class="w-2 h-2 rounded-full bg-error animate-pulse"></span>
                            <span class="text-[0.6rem] font-black text-white uppercase tracking-widest leading-none">Live Captured</span>
                        </div>
                    </div>
                </section>
                @endif

                <!-- File Repository Section -->
                @if($logbook->attachments->count() > 0)
                <section class="space-y-6">
                    <div class="flex items-center gap-4 px-2">
                        <i data-lucide="paperclip" class="h-4 w-4 text-primary/40"></i>
                        <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em]">Repository Files</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($logbook->attachments as $attachment)
                        @php
                            $ext = strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        <div class="card bg-base-100 p-5 rounded-3xl border border-base-300 flex flex-row items-center justify-between hover:bg-base-200/50 transition-all group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-base-200 flex items-center justify-center text-primary/40 group-hover:scale-105 transition-transform cursor-pointer {{ $isImage ? 'modal-trigger' : '' }}" 
                                     {{ $isImage ? 'data-src=' . asset('storage/' . $attachment->file_path) . ' data-title=' . basename($attachment->file_path) : 'onclick=\'window.location.href="' . asset('storage/' . $attachment->file_path) . '"\'' }}>
                                    <i data-lucide="{{ $isImage ? 'image' : 'file-text' }}" class="h-5 w-5"></i>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[0.75rem] font-black text-primary uppercase tracking-tight truncate leading-none">{{ basename($attachment->file_path) }}</span>
                                    <span class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mt-1.5 leading-none">{{ strtoupper($ext) }} FILE</span>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $attachment->file_path) }}" download 
                               class="btn btn-ghost btn-sm btn-square text-base-content/20 hover:text-primary transition-colors">
                                <i data-lucide="download" class="h-4 w-4"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <!-- Assessment Column (Right/Bottom) -->
            <div class="lg:col-span-8">
                <form action="{{ route('reviews.update', $logbook->id) }}" method="POST" class="space-y-12" @submit="isSubmitting = true">
                    @csrf
                    @method('PUT')

                    @if($logbook->status !== 'pending')
                    <div class="alert {{ $logbook->status === 'approved' ? 'bg-success/5 border-success/20 text-success' : 'bg-error/5 border-error/20 text-error' }} rounded-3xl p-8 border">
                        <i data-lucide="{{ $logbook->status === 'approved' ? 'check-circle' : 'x-circle' }}" class="h-8 w-8"></i>
                        <div>
                            <p class="text-sm font-black uppercase tracking-widest">
                                Status: Logbook {{ $logbook->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                            </p>
                            @if($logbook->latestReview)
                            <p class="text-xs font-bold mt-1 opacity-70">
                                Rating Akhir: {{ $logbook->latestReview->rating }}/5 &bull; Skor: {{ $logbook->latestReview->final_score }}%
                            </p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- KPI Breakdown Assessment -->
                    <div class="space-y-8">
                        <div class="flex items-center gap-4 px-2">
                            <i data-lucide="list-checks" class="h-4 w-4 text-primary/40"></i>
                            <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em]">KPI Rubrics Evaluation</h3>
                        </div>

                        <div class="space-y-6">
                            @foreach($logbook->items as $index => $item)
                            <div class="card bg-base-100 p-8 lg:p-10 rounded-[2.5rem] border border-base-300 shadow-sm relative overflow-hidden group">
                                <div class="relative z-10 mb-8 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                                    <div class="flex-1">
                                        <div class="badge badge-primary border-none text-[0.55rem] font-black uppercase tracking-widest mb-4 py-3 px-4">
                                            Indicator #{{ $index + 1 }}
                                        </div>
                                        <h4 class="text-lg lg:text-xl font-black text-primary leading-tight tracking-tight uppercase">
                                            {{ $item->kpi->description }}
                                        </h4>
                                    </div>
                                    <div class="flex items-center gap-4 bg-base-200/50 p-4 rounded-2xl border border-base-300 self-start">
                                        <i data-lucide="target" class="h-5 w-5 text-primary/20"></i>
                                        <div class="text-right">
                                            <p class="text-[0.55rem] font-black text-base-content/20 uppercase tracking-widest leading-none mb-1.5">Target</p>
                                            <p class="text-sm font-black text-primary leading-none">{{ $item->kpi->target }} {{ $item->kpi->unit ?? 'Satuan' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-base-200/40 p-8 rounded-3xl border border-base-300 mb-10 relative">
                                    <p class="text-[0.6rem] font-black text-base-content/20 uppercase tracking-widest mb-3 leading-none">Evidence Description</p>
                                    <p class="text-[0.85rem] font-bold text-base-content/60 italic leading-relaxed">
                                        "{{ $item->work_description }}"
                                    </p>
                                </div>

                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

                                <div class="space-y-6 relative z-10">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <i data-lucide="award" class="h-4 w-4 text-primary/40"></i>
                                            <label class="text-[0.65rem] font-black text-primary/30 uppercase tracking-[0.2em]">Item Proficiency Score</label>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-4xl font-black text-primary leading-none tabular-nums" x-text="items[{{ $index }}].score"></span>
                                            <span class="text-xs font-black text-primary/10 tracking-widest uppercase">/ <span x-text="items[{{ $index }}].target"></span></span>
                                        </div>
                                    </div>
                                    
                                    @if($logbook->status === 'pending')
                                    <div class="px-2">
                                        <input type="range" name="items[{{ $index }}][score]" 
                                               x-model.number="items[{{ $index }}].score"
                                               min="0" :max="items[{{ $index }}].target" step="1"
                                               class="range range-primary h-3 rounded-full bg-base-300">
                                        <div class="w-full flex justify-between text-[0.5rem] font-black text-base-content/20 px-2 mt-4 uppercase tracking-widest">
                                            <span>Insufficient</span>
                                            <span>Exceeds Expectation</span>
                                        </div>
                                    </div>
                                    @else
                                    <div class="w-full h-3 bg-base-300 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary" :style="'width: ' + ((items[{{ $index }}].score / items[{{ $index }}].target) * 100) + '%'"></div>
                                    </div>
                                    @endif
                                </div>
                                
                                <i data-lucide="award" class="absolute -bottom-10 -right-10 h-32 w-32 text-primary/5 -rotate-12"></i>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Final Professional Verdict -->
                    <div class="card bg-base-100 rounded-[3rem] p-12 border border-base-300 shadow-sm relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-12 relative z-10">
                            <div>
                                <h3 class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.4em] mb-4 leading-none">Professional Performance Rating</h3>
                                <p class="text-xs font-black text-base-content/30 uppercase tracking-widest">Assign a visual star rating for the overall session</p>
                            </div>

                            @if($logbook->status === 'pending')
                            <div class="flex items-center gap-4">
                                <template x-for="star in 5">
                                    <button type="button" @click="setStar(star)" class="transition-all duration-300 transform hover:scale-125 active:scale-95 flex items-center justify-center outline-none">
                                        <i data-lucide="star" class="h-14 w-14 transition-all duration-500"
                                           :class="star <= starRating ? 'text-warning fill-warning drop-shadow-lg' : 'text-base-content/5 opacity-40' "></i>
                                    </button>
                                </template>
                            </div>
                            @else
                            <div class="flex items-center gap-4">
                                <template x-for="star in 5">
                                    <i data-lucide="star" class="h-12 w-12"
                                       :class="star <= starRating ? 'text-warning fill-warning' : 'text-base-content/5 opacity-40' "></i>
                                </template>
                            </div>
                            @endif

                            <input type="hidden" name="final_score" x-model.number="finalScore">
                            <input type="hidden" name="rating" x-model.number="starRating">
                        </div>

                        <div class="mt-16 space-y-6 relative z-10">
                            <div class="flex items-center gap-4">
                                <i data-lucide="message-square" class="h-4 w-4 text-primary/40"></i>
                                <label class="text-[0.7rem] font-black text-primary/40 uppercase tracking-[0.2em] leading-none">Supervisor Deliberation</label>
                            </div>
                            
                            @if($logbook->status === 'pending')
                            <textarea name="review_comment" :required="submitAction === 'approved'" rows="6"
                                      placeholder="Elaborate on constructive feedback or reasons for rejection..."
                                      class="textarea textarea-bordered w-full rounded-3xl p-8 text-sm lg:text-base font-bold text-base-content/60 leading-relaxed bg-base-200/50 border-base-300 focus:border-primary/20 italic"></textarea>
                            @else
                            <div class="w-full bg-base-200/50 border border-base-300 rounded-3xl p-8 text-sm lg:text-base font-bold text-base-content/40 leading-relaxed italic">
                                {{ $logbook->latestReview->comment ?? 'No additional comments provided.' }}
                            </div>
                            @endif
                        </div>
                        
                        <!-- Watermark -->
                        <i data-lucide="shield-check" class="absolute -top-12 -left-12 h-48 w-48 text-primary/5 rotate-12"></i>
                    </div>

                    <!-- Submission Interface -->
                    @if($logbook->status === 'pending')
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-12">
                        <button type="submit" name="status" value="rejected" @click="submitAction = 'rejected'"
                                class="btn btn-ghost btn-lg h-20 bg-error/10 hover:bg-error/20 text-error rounded-[1.5rem] px-10 gap-4 flex-1 w-full order-2 sm:order-1 transition-all">
                            <template x-if="!(isSubmitting && submitAction === 'rejected')">
                                <div class="flex items-center gap-4">
                                    <i data-lucide="x-circle" class="h-5 w-5"></i>
                                    <span class="text-xs font-black uppercase tracking-widest">Tolak Laporan</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting && submitAction === 'rejected'">
                                <div class="flex items-center gap-4">
                                    <span class="loading loading-spinner loading-sm"></span>
                                    <span class="text-xs font-black uppercase tracking-widest">Memproses...</span>
                                </div>
                            </template>
                        </button>

                        <button type="submit" name="status" value="approved" @click="submitAction = 'approved'"
                                class="btn btn-primary btn-lg h-20 rounded-[1.5rem] px-12 gap-4 flex-[2.5] w-full shadow-2xl shadow-primary/30 order-1 sm:order-2 transition-all hover:scale-[1.02]">
                            <template x-if="!(isSubmitting && submitAction === 'approved')">
                                <div class="flex items-center gap-4">
                                    <i data-lucide="check-circle-2" class="h-6 w-6"></i>
                                    <span class="text-sm font-black uppercase tracking-[0.1em]">Sahkan Penilaian</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting && submitAction === 'approved'">
                                <div class="flex items-center gap-4">
                                    <span class="loading loading-spinner loading-md"></span>
                                    <span class="text-sm font-black uppercase tracking-[0.1em]">Menyimpan Data...</span>
                                </div>
                            </template>
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Image Modal (Vanilla DaisyUI Style) -->
    <dialog id="img_modal" class="modal modal-bottom sm:modal-middle bg-primary/20 backdrop-blur-xl transition-all duration-500">
        <div class="modal-box p-0 bg-transparent shadow-none w-full max-w-5xl overflow-hidden">
            <div class="relative group">
                <img id="modal_img_src" src="" class="w-full h-auto max-h-[85vh] object-contain rounded-3xl shadow-2xl border-4 border-white/10 mx-auto">
                <div class="absolute top-6 right-6">
                    <form method="dialog">
                        <button class="btn btn-circle btn-primary shadow-xl border-none"><i data-lucide="x" class="h-5 w-5"></i></button>
                    </form>
                </div>
                <div class="absolute bottom-10 left-1/2 -translate-x-1/2">
                    <div class="bg-black/80 backdrop-blur-md px-8 py-3 rounded-2xl border border-white/10 text-center">
                        <p id="modal_img_title" class="text-xs font-black text-white uppercase tracking-widest"></p>
                    </div>
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
                    // Update lucide icons after init
                    this.$nextTick(() => { lucide.createIcons(); });
                },

                setStar(star) {
                    this.starRating = star;
                    this.finalScore = star * 20; // 5 stars = 100%
                },

                showImageModal(src, title) {
                    const modal = document.getElementById('img_modal');
                    const img = document.getElementById('modal_img_src');
                    const titleEl = document.getElementById('modal_img_title');
                    
                    img.src = src;
                    titleEl.innerText = title;
                    modal.showModal();
                    
                    this.$nextTick(() => { lucide.createIcons(); });
                }
            }
        }
    </script>
</x-layouts.app>
