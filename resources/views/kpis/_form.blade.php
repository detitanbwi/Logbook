<div class="space-y-12 animate-in fade-in slide-in-from-bottom duration-1000">
    <div class="max-w-4xl mx-auto space-y-12 lg:space-y-16">
        <section class="p-8 lg:p-16 bg-white rounded-xl editorial-shadow border border-outline-variant/10 space-y-10 lg:space-y-12">
            <div class="flex items-center gap-6">
                <span class="w-10 h-0.5 bg-primary/20 group-focus-within:bg-primary transition-all duration-700"></span>
                <h3 class="text-xs font-bold tracking-[0.4em] text-primary/60 uppercase">Metrik Standardisasi</h3>
            </div>

            <div class="space-y-8 lg:space-y-10">
                <div class="space-y-4">
                    <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Deskripsi KPI *</label>
                    <textarea name="description" rows="4" placeholder="Contoh: Menyelesaikan arsitektur sistem tepat waktu" required
                              class="w-full p-6 lg:p-8 rounded-xl font-medium text-primary tracking-tight leading-relaxed text-sm">{{ old('description', $kpi?->description) }}</textarea>
                    @error('description') <p class="text-[0.6rem] font-bold text-error mt-2 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 lg:gap-10">
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Target Angka *</label>
                        <input type="number" name="target" value="{{ old('target', $kpi?->target) }}" placeholder="Contoh: 100" required
                               class="w-full h-14 lg:h-16 px-6 lg:px-8 rounded-xl font-medium text-primary tracking-tight tabular-nums text-sm">
                        @error('target') <p class="text-[0.6rem] font-bold text-error mt-2 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Satuan Ukur *</label>
                        <input type="text" name="unit" value="{{ old('unit', $kpi?->unit) }}" placeholder="Contoh: Persen, Jam, Proyek" required
                               class="w-full h-14 lg:h-16 px-6 lg:px-8 rounded-xl font-medium text-primary tracking-tight text-sm">
                        @error('unit') <p class="text-[0.6rem] font-bold text-error mt-2 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row gap-4 lg:gap-6">
                <button type="submit" class="inline-flex items-center justify-center gap-4 px-10 lg:px-12 py-5 primary-gradient text-white rounded-xl text-[0.65rem] lg:text-[0.7rem] font-bold tracking-[0.2em] uppercase shadow-2xl shadow-primary/30 active:scale-95 transition-all duration-300 w-full sm:w-auto">
                    <span class="material-symbols-outlined font-bold">save_as</span>
                    <span>Simpan Standardisasi KPI</span>
                </button>
                <a href="{{ route('kpis.index') }}" class="inline-flex items-center justify-center gap-4 px-10 lg:px-12 py-5 bg-surface text-on-surface/40 hover:text-on-surface/60 rounded-xl text-[0.65rem] lg:text-[0.7rem] font-bold tracking-[0.2em] uppercase transition-all duration-300 border border-outline-variant/10 w-full sm:w-auto">
                    <span>Batal</span>
                </a>
            </div>
        </section>
    </div>
</div>
