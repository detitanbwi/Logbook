<x-layouts.app title="Assessment" active="reviews">
    <!-- Back Button -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('reviews.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-surface-container hover:bg-surface-container/70 rounded-2xl transition-colors">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span class="text-[0.75rem] font-black uppercase tracking-widest">Back</span>
        </a>
    </div>

    <form action="{{ route('reviews.store', $logbook->id) }}" method="POST" class="space-y-12 pb-40">
        @csrf

        <!-- STAFF INFO SECTION -->
        <section class="space-y-4">
            <h3 class="text-[0.6rem] font-black text-outline uppercase tracking-[0.2em] ml-2 font-label">Entry Summary</h3>
            <div class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-sm border border-outline-variant/10 flex flex-col md:flex-row gap-8 items-center md:items-start text-center md:text-left">
                <div class="w-24 h-24 rounded-3xl overflow-hidden border-2 border-white shadow-xl shadow-primary/10 bg-surface-container shrink-0">
                    @if($logbook->user->foto)
                        <img src="{{ asset('storage/' . $logbook->user->foto) }}" alt="Photo" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center primary-gradient text-white text-3xl font-black">{{ substr($logbook->user->nama, 0, 1) }}</div>
                    @endif
                </div>
                <div class="flex-1 space-y-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-headline font-black text-primary uppercase tracking-tight">{{ $logbook->user->nama }}</h2>
                        <p class="text-[0.6rem] font-black text-outline uppercase tracking-widest mt-1">{{ $logbook->user->jabatan ?? 'Associate Staff' }}</p>
                    </div>
                    <div class="flex flex-wrap justify-center md:justify-start gap-4">
                        <div class="flex items-center gap-2 px-4 py-2 bg-primary/5 rounded-full border border-primary/5">
                            <span class="material-symbols-outlined text-[0.9rem] text-primary">calendar_today</span>
                            <span class="text-[0.6rem] font-black text-primary uppercase tracking-widest">{{ \Carbon\Carbon::parse($logbook->date)->isoFormat('D MMM Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-secondary/5 rounded-full border border-secondary/5">
                            <span class="material-symbols-outlined text-[0.9rem] text-secondary">schedule</span>
                            <span class="text-[0.6rem] font-black text-secondary uppercase tracking-widest">{{ $logbook->start_time }} - {{ $logbook->end_time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ACTIVITY REPORT SECTION -->
        <section class="space-y-4">
            <h3 class="text-[0.6rem] font-black text-outline uppercase tracking-[0.2em] ml-2 font-label">Activity Report</h3>
            <div class="bg-white p-10 rounded-[3rem] border border-outline-variant/5 shadow-sm space-y-8">
                <p class="text-[0.85rem] font-medium text-on-surface-variant leading-[2] tracking-wide italic underline underline-offset-[12px] decoration-primary/5 decoration-dashed">
                    {{ $logbook->daily_report }}
                </p>
                @if($logbook->main_photo)
                <div class="relative aspect-video rounded-[2rem] overflow-hidden group shadow-lg">
                    <img src="{{ asset('storage/' . $logbook->main_photo) }}" alt="Evidence" class="w-full h-full object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-8">
                        <p class="text-white text-[0.6rem] font-black uppercase tracking-widest flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg">verified</span>
                            Visual Documentation Verified
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <!-- KPI ASSESSMENT SECTION -->
        <section class="space-y-6">
            <h3 class="text-[0.6rem] font-black text-outline uppercase tracking-[0.2em] ml-2 font-label">KPI Assessment</h3>
            <div class="space-y-6">
                @foreach($logbook->kpis as $item)
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-outline-variant/5 space-y-8" x-data="{ score: 80 }">
                    <div class="flex justify-between items-start">
                        <div class="pr-6 space-y-2">
                            <h4 class="text-sm font-black text-primary uppercase tracking-tight">{{ $item->kpi->name }}</h4>
                            <p class="text-[0.55rem] font-bold text-outline leading-relaxed uppercase tracking-widest italic opacity-60">"{{ $item->details }}"</p>
                        </div>
                        <div class="bg-primary/5 px-6 py-3 rounded-2xl flex items-baseline gap-1 border border-primary/5">
                            <span class="text-2xl font-headline font-black text-primary" x-text="score"></span>
                            <span class="text-[0.65rem] font-black text-outline uppercase opacity-40">/ 100</span>
                        </div>
                    </div>

                    <!-- Modern Slider -->
                    <div class="space-y-4">
                        <input type="range" name="scores[{{ $item->id }}]" min="0" max="100" x-model="score" required
                               class="w-full h-2 bg-surface-container rounded-full appearance-none cursor-pointer accent-primary">
                        <div class="flex justify-between text-[0.55rem] font-black text-outline uppercase tracking-widest opacity-40 px-1">
                            <span>Poor</span>
                            <span>Target ({{ $item->kpi->target_score }})</span>
                            <span>Excellent</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- OVERALL RATING & FEEDBACK -->
        <section class="space-y-4">
            <h3 class="text-[0.6rem] font-black text-outline uppercase tracking-[0.2em] ml-2 font-label">Overall Feedback</h3>
            <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-outline-variant/5 space-y-10">
                <!-- Rating -->
                <div class="space-y-4" x-data="{ rating: 0 }">
                    <label class="block text-[0.6rem] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Overall Rating</label>
                    <div class="flex gap-4">
                        <template x-for="i in 5">
                            <button type="button" @click="rating = i" class="group">
                                <span class="material-symbols-outlined text-4xl transition-all duration-300"
                                      :class="i <= rating ? 'text-[#FFB691] fill-1 scale-110' : 'text-outline/20'">star</span>
                            </button>
                        </template>
                        <input type="hidden" name="rating" x-model="rating" required>
                    </div>
                </div>

                <!-- Feedback -->
                <div class="space-y-4">
                    <label class="block text-[0.6rem] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Reviewer Comments</label>
                    <textarea name="comments" rows="6" required
                              placeholder="Describe why you gave this score..."
                              class="w-full bg-surface-container border-none rounded-[2.5rem] p-8 text-[0.75rem] font-medium text-primary placeholder:text-outline/20 focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                </div>
            </div>
        </section>

        <!-- STICKY ACTION FOOTER -->
        <div class="fixed bottom-0 left-0 w-full z-[100] px-6 pb-12 pt-6 bg-white/50 backdrop-blur-2xl border-t border-outline-variant/5">
            <div class="max-w-lg mx-auto flex gap-4">
                <button type="submit" name="status" value="rejected" class="flex-1 py-6 bg-white border border-error/20 text-error rounded-3xl font-black text-[0.7rem] uppercase tracking-widest shadow-xl shadow-error/5 active:scale-95 transition-all">Tolak</button>
                <button type="submit" name="status" value="approved" class="flex-[3] py-6 primary-gradient text-white rounded-3xl font-black text-[0.75rem] uppercase tracking-widest shadow-2xl shadow-primary/40 active:scale-95 transition-all">Konfirmasi Review</button>
            </div>
        </div>
    </form>
</x-layouts.app>
