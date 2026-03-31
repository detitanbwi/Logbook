@props(['logbook' => null])
<x-layouts.app
    :title="$logbook ? 'Edit Logbook' : 'Tambah Logbook'"
    active="create"
    hideNav="true"
    :backUrl="route('dashboard')"
>
    <form action="{{ $logbook ? route('logbooks.update', $logbook->id) : route('logbooks.store') }}"
          method="POST"
          enctype="multipart/form-data"
    <form action="{{ $logbook ? route('logbooks.update', $logbook->id) : route('logbooks.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-8 pb-10"
          x-data="{
              isSubmitting: false,
              items: @js($logbook ? $logbook->kpis->map(fn($k) => ['kpi_id' => $k->kpi_id, 'details' => $k->details]) : [['kpi_id' => '', 'details' => '']]),
              addItem() { this.items.push({kpi_id: '', details: ''}) },
              removeItem(index) { this.items.splice(index, 1) }
          }"
          @submit="isSubmitting = true">
        @csrf
        @if($logbook) @method('PUT') @endif

        <!-- SECTION 1: LOKASI -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em] ml-1">LOKASI GPS</h3>
            <div class="bg-base-100 p-4 rounded-2xl border border-base-200">
                <!-- Location Check -->
                <div class="flex items-center justify-between gap-4 mb-4">
                    <button type="button" onclick="getLocation()" class="btn btn-primary btn-sm h-11 rounded-xl gap-2 px-5 shadow-lg shadow-primary/10 hover:scale-[1.03] transition-all">
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                        <span class="text-[0.6rem] font-black uppercase tracking-wider">Deteksi Lokasi</span>
                    </button>
                    <div class="text-right">
                        <p class="text-[0.5rem] font-bold text-base-content/20 uppercase tracking-[0.2em] mb-0.5">STATUS GPS</p>
                        <p id="gps-status" class="text-[0.65rem] font-black text-primary uppercase tracking-wide">Ready</p>
                    </div>
                </div>

                <!-- Coordinates -->
                <div class="flex items-center gap-6 pt-4 border-t border-base-200/60">
                    <div>
                        <label class="block text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mb-1">LATITUDE</label>
                        <p id="lat-display" class="text-xs font-black text-base-content font-mono">-</p>
                    </div>
                    <div>
                        <label class="block text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mb-1">LONGITUDE</label>
                        <p id="lng-display" class="text-xs font-black text-base-content font-mono">-</p>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude" value="-">
                <input type="hidden" name="longitude" id="longitude" value="-">
            </div>
        </div>

        <!-- SECTION 2: WAKTU AKTIVITAS -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em] ml-1">WAKTU AKTIVITAS</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="form-control w-full space-y-2">
                    <label class="label py-0 ml-1">
                        <span class="label-text text-[0.55rem] font-black tracking-widest text-base-content/30 uppercase">JAM MULAI</span>
                    </label>
                    <input type="time" name="start_time" value="{{ old('start_time', $logbook?->start_time ?? '08:00') }}"
                           class="input input-bordered w-full h-12 rounded-xl font-black text-sm text-base-content bg-base-200 border-transparent focus:border-primary/20">
                </div>
                <div class="form-control w-full space-y-2">
                    <label class="label py-0 ml-1">
                        <span class="label-text text-[0.55rem] font-black tracking-widest text-base-content/30 uppercase">JAM SELESAI</span>
                    </label>
                    <input type="time" name="end_time" value="{{ old('end_time', $logbook?->end_time ?? '17:00') }}"
                           class="input input-bordered w-full h-12 rounded-xl font-black text-sm text-base-content bg-base-200 border-transparent focus:border-primary/20">
                </div>
            </div>
        </div>

        <!-- SECTION 3: FOTO DOKUMENTASI -->
        <div class="space-y-3" x-data="{ hasImage: {{ $logbook?->main_photo ? 'true' : 'false' }}, imageSrc: '{{ $logbook?->main_photo ? asset('storage/' . $logbook->main_photo) : '' }}', modalOpen: false }">
            <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em] ml-1">DOKUMENTASI FOTO</h3>
            <div class="bg-base-100 p-3 rounded-2xl border border-base-200">
                <div class="relative aspect-[16/9] rounded-xl bg-base-200 overflow-hidden group border-2 border-dashed border-base-300 transition-all" :class="hasImage ? 'border-primary/50' : 'hover:border-primary/30'">
                    <input type="file" name="main_photo" id="main_photo" class="hidden" accept="image/*" capture="environment" 
                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { imageSrc = e.target.result; hasImage = true; }; reader.readAsDataURL(file); }" 
                           {{ $logbook?->main_photo ? '' : 'required' }}>
                    
                    <label for="main_photo" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer transition-all z-10"
                           :class="hasImage ? 'opacity-0 hover:opacity-100 bg-base-300/60 backdrop-blur-sm' : 'hover:bg-base-300/30'">
                        <div class="w-12 h-12 bg-base-100 rounded-xl shadow-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="camera" class="h-6 w-6 text-primary/80"></i>
                        </div>
                        <span class="text-[0.55rem] font-black text-base-content uppercase tracking-[0.15em] bg-base-100/90 py-1.5 px-3 rounded-lg shadow-sm" x-text="hasImage ? 'GANTI FOTO' : 'AMBIL FOTO'"></span>
                    </label>

                    <img id="image-preview" :src="imageSrc" alt="Preview" class="absolute inset-0 w-full h-full object-cover z-0" x-show="hasImage" x-transition>
                    
                    <!-- Watermark -->
                    <div x-show="hasImage" class="absolute bottom-3 left-3 z-20 pointer-events-none">
                        <span class="text-[0.5rem] font-black text-white bg-black/60 shadow-lg border border-white/20 px-2 py-1 rounded-lg tracking-[0.2em] uppercase backdrop-blur-md">WIRODEV DEMO</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: TARGET KPI -->
        <div class="space-y-3">
            <div class="flex items-center justify-between mx-1">
                <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em]">DETAIL PEKERJAAN (KPI)</h3>
                <button type="button" @click="addItem" class="btn btn-ghost btn-xs text-primary h-8 px-3 rounded-lg font-black uppercase tracking-widest gap-2 bg-primary/5">
                    <i data-lucide="plus-circle" class="h-3 w-3"></i> TAMBAH
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="bg-base-100 p-4 rounded-2xl border border-base-200 border-l-4 !border-l-primary relative animate-in fade-in slide-in-from-top-4 duration-300">
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="absolute top-4 right-4 btn btn-ghost btn-circle btn-sm text-error/30 hover:text-error hover:bg-error/10">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>

                        <div class="space-y-4">
                            <div class="form-control w-full space-y-2">
                                <label class="label py-0 ml-1">
                                    <span class="label-text text-[0.55rem] font-black tracking-widest text-base-content/30 uppercase">JENIS PEKERJAAN</span>
                                </label>
                                <div class="relative">
                                    <select :name="`items[${index}][kpi_id]`" x-model="item.kpi_id" required
                                            class="select select-bordered w-full h-12 rounded-xl font-black text-xs text-base-content bg-base-200 border-transparent focus:border-primary/20 appearance-none">
                                        <option value="">-- Pilih Jenis Pekerjaan --</option>
                                        @foreach($kpis as $kpi)
                                            <option value="{{ $kpi->id }}">{{ $kpi->description }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-primary/30">
                                        <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-control w-full space-y-2">
                                <label class="label py-0 ml-1">
                                    <span class="label-text text-[0.55rem] font-black tracking-widest text-base-content/30 uppercase">PENJELASAN DETAIL</span>
                                </label>
                                <textarea :name="`items[${index}][details]`" x-model="item.details" required rows="3"
                                          placeholder="Jelaskan detail pekerjaan..."
                                          class="textarea textarea-bordered w-full rounded-xl p-4 text-xs font-bold leading-relaxed bg-base-200 border-transparent focus:border-primary/20"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- SECTION 5: LAPORAN NARASI -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em] ml-1">CATATAN KEGIATAN</h3>
            <div class="bg-base-100 p-3 rounded-2xl border border-base-200">
                <textarea name="daily_report" rows="4" required
                          placeholder="Ceritakan apa saja yang Anda kerjakan hari ini..."
                          class="textarea textarea-bordered w-full rounded-xl p-4 text-sm font-bold leading-relaxed bg-base-200 border-transparent focus:border-primary/20">{{ old('daily_report', $logbook?->daily_report) }}</textarea>
            </div>
        </div>

        <!-- SUBMIT BUTTON -->
        <div class="pt-6">
            <div class="flex justify-center">
                <button type="submit" 
                        class="btn btn-primary rounded-xl w-full max-w-xs h-14 px-10 shadow-xl shadow-primary/20 text-white font-black tracking-widest uppercase transition-all active:scale-95 disabled:bg-base-300 disabled:text-base-content/30"
                        :disabled="isSubmitting">
                    <span x-show="!isSubmitting">Kirim Logbook</span>
                    <span x-show="isSubmitting" class="flex items-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span>
                        MEMPROSES...
                    </span>
                </button>
            </div>
        </div>
    </form>
    </form>

    <script>
        // Use Lucide for newly added items
        $watch('items', () => { setTimeout(() => lucide.createIcons(), 50); });

        async function getLocation() {
            const status = document.getElementById('gps-status');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const latDisplay = document.getElementById('lat-display');
            const lngDisplay = document.getElementById('lng-display');

            if (!window.HRISNative || typeof window.HRISNative.getCurrentPosition !== 'function') {
                status.innerText = 'GPS TIDAK TERSEDIA';
                status.classList.add('text-error');
                return;
            }

            status.innerText = 'MENCARI...';
            status.classList.remove('text-error');

            try {
                const position = await window.HRISNative.getCurrentPosition({
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0,
                });

                const latitude = Number(position.coords.latitude);
                const longitude = Number(position.coords.longitude);

                status.innerText = 'AKURASI TINGGI';
                latInput.value = latitude;
                lngInput.value = longitude;

                latDisplay.innerText = `${latitude.toFixed(6)}°`;
                lngDisplay.innerText = `${longitude.toFixed(6)}°`;
            } catch (error) {
                status.innerText = 'IZIN DITOLAK';
                status.classList.add('text-error');
                alert('Izin lokasi belum diberikan. Aktifkan izin lokasi untuk melanjutkan.');
            }
        }

        function previewImage(event) {
            const preview = document.getElementById('image-preview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    preview.src = reader.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-layouts.app>
