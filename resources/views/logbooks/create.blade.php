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
          class="space-y-12 pb-24"
          x-data="{
              items: @js($logbook ? $logbook->kpis->map(fn($k) => ['kpi_id' => $k->kpi_id, 'details' => $k->details]) : [['kpi_id' => '', 'details' => '']]),
              addItem() { this.items.push({kpi_id: '', details: ''}) },
              removeItem(index) { this.items.splice(index, 1) }
          }">
        @csrf
        @if($logbook) @method('PUT') @endif

        <!-- SECTION 1: LOKASI -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-primary/70 uppercase tracking-wider ml-1">LOKASI GPS</h3>
            <div class="bg-base-100 p-4 rounded-2xl border border-base-200 space-y-4">
                <!-- Location Check -->
                <div class="flex items-center justify-between gap-4">
                    <button type="button" onclick="getLocation()" class="btn btn-primary rounded-2xl gap-3 px-6 shadow-lg shadow-primary/20 hover:scale-[1.03] transition-all">
                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Deteksi Lokasi</span>
                    </button>
                    <div class="text-right">
                        <p class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mb-0.5">STATUS GPS</p>
                        <p id="gps-status" class="text-xs font-black text-primary uppercase tracking-wide">Akurasi Tinggi</p>
                    </div>
                </div>

                <!-- Coordinates -->
                <div class="flex items-center gap-6 pt-4 border-t border-base-200">
                    <div>
                        <label class="block text-[0.6rem] font-bold text-base-content/40 uppercase tracking-wider mb-1">LATITUDE</label>
                        <p id="lat-display" class="text-sm font-bold text-base-content font-mono">-</p>
                    </div>
                    <div>
                        <label class="block text-[0.6rem] font-bold text-base-content/40 uppercase tracking-wider mb-1">LONGITUDE</label>
                        <p id="lng-display" class="text-sm font-bold text-base-content font-mono">-</p>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude" value="-">
                <input type="hidden" name="longitude" id="longitude" value="-">
            </div>
        </div>

        <!-- SECTION 2: WAKTU AKTIVITAS -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-primary/70 uppercase tracking-wider ml-1">WAKTU AKTIVITAS</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control w-full space-y-2">
                    <label class="label py-0 ml-1">
                        <span class="label-text text-xs font-semibold tracking-wide text-base-content/40 uppercase">JAM MULAI</span>
                    </label>
                    <input type="time" name="start_time" value="{{ old('start_time', $logbook?->start_time ?? '08:00') }}"
                           class="input input-bordered w-full h-14 rounded-2xl font-medium text-base-content bg-base-200 border-transparent focus:border-primary/20">
                </div>
                <div class="form-control w-full space-y-2">
                    <label class="label py-0 ml-1">
                        <span class="label-text text-xs font-semibold tracking-wide text-base-content/40 uppercase">JAM SELESAI</span>
                    </label>
                    <input type="time" name="end_time" value="{{ old('end_time', $logbook?->end_time ?? '17:00') }}"
                           class="input input-bordered w-full h-14 rounded-2xl font-medium text-base-content bg-base-200 border-transparent focus:border-primary/20">
                </div>
            </div>
        </div>

        <!-- SECTION 3: FOTO DOKUMENTASI -->
        <div class="space-y-4" x-data="{ hasImage: {{ $logbook?->main_photo ? 'true' : 'false' }}, imageSrc: '{{ $logbook?->main_photo ? asset('storage/' . $logbook->main_photo) : '' }}', modalOpen: false }">
            <h3 class="text-xs font-bold text-primary/70 uppercase tracking-wider ml-1">DOKUMENTASI (WAJIB)</h3>
            <div class="bg-base-100 p-4 rounded-2xl border border-base-200">
                <div class="relative aspect-[16/9] rounded-[1.5rem] bg-base-200 overflow-hidden group border-2 border-dashed border-base-300 transition-all" :class="hasImage ? 'border-primary/50' : 'hover:border-primary/30'">
                    <input type="file" name="main_photo" id="main_photo" class="hidden" accept="image/*" capture="environment" 
                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { imageSrc = e.target.result; hasImage = true; }; reader.readAsDataURL(file); }" 
                           {{ $logbook?->main_photo ? '' : 'required' }}>
                    
                    <label for="main_photo" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer transition-all z-10"
                           :class="hasImage ? 'opacity-0 hover:opacity-100 bg-base-300/60 backdrop-blur-sm' : 'hover:bg-base-300/30'">
                        <div class="w-16 h-16 bg-base-100 rounded-2xl shadow-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i data-lucide="camera" class="h-8 w-8 text-primary/80"></i>
                        </div>
                        <span class="text-[0.65rem] font-bold text-base-content uppercase tracking-wider bg-base-100/90 py-1.5 px-3 rounded-lg shadow-sm" x-text="hasImage ? 'KETUK UNTUK GANTI FOTO' : 'AMBIL FOTO DARI KAMERA'"></span>
                    </label>

                    <img id="image-preview" :src="imageSrc" alt="Preview" class="absolute inset-0 w-full h-full object-cover z-0" x-show="hasImage" x-transition>
                    
                    <!-- Watermark -->
                    <div x-show="hasImage" class="absolute bottom-4 left-4 z-20 pointer-events-none">
                        <span class="text-[0.6rem] font-black text-white bg-black/60 shadow-lg border border-white/20 px-3 py-1.5 rounded-xl tracking-[0.2em] uppercase backdrop-blur-md">WIRODEV DEMO</span>
                    </div>
                    
                    <!-- View Full Size Button -->
                    <div class="absolute top-3 right-3 z-30" x-show="hasImage">
                        <button type="button" @click="modalOpen = true" class="btn btn-circle btn-sm btn-primary shadow-lg hover:scale-110 transition-transform bg-primary/95 backdrop-blur-sm border-none ring-4 ring-base-100/30">
                            <i data-lucide="maximize-2" class="h-4 w-4 text-white"></i>
                        </button>
                    </div>
                </div>
                @error('main_photo') <p class="text-[0.65rem] font-bold text-error uppercase tracking-widest mt-3 ml-1">{{ $message }}</p> @enderror
            </div>

            <!-- Image Modal -->
            <dialog class="modal modal-middle" :class="modalOpen ? 'modal-open' : ''">
                <div class="modal-box p-0 bg-transparent shadow-none max-w-4xl w-full flex flex-col items-center justify-center">
                    <div class="relative bg-base-100 backdrop-blur-md rounded-3xl overflow-hidden shadow-2xl w-full">
                        <div class="p-4 flex justify-between items-center bg-base-100/80 backdrop-blur-md absolute top-0 left-0 right-0 z-30 border-b border-base-200">
                            <h3 class="font-bold text-xs tracking-wider uppercase ml-2 text-base-content/70">Pratinjau Foto</h3>
                            <button type="button" @click="modalOpen = false" class="btn btn-sm btn-circle btn-ghost bg-base-200 hover:bg-error/20 hover:text-error transition-colors">
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <div class="pt-16 pb-4 px-4 bg-black/5 flex items-center justify-center min-h-[50vh] relative">
                            <img :src="imageSrc" alt="Full Preview" class="w-full h-auto object-contain max-h-[75vh] rounded-2xl shadow-sm z-10">
                            <!-- Floating Watermark in Modal -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden z-20">
                                <span class="text-4xl sm:text-6xl font-black text-white/20 -rotate-12 select-none tracking-[0.3em] whitespace-nowrap drop-shadow-lg uppercase">WIRODEV DEMO</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop overflow-hidden" @click="modalOpen = false">
                    <button type="button" class="cursor-default bg-black/60 backdrop-blur-sm w-full h-full">close</button>
                </div>
            </dialog>
        </div>

        <!-- SECTION 4: TARGET KPI -->
        <div class="space-y-4">
            <div class="flex items-center justify-between mx-1">
                <h3 class="text-xs font-bold text-primary/70 uppercase tracking-wider">DETAIL PEKERJAAN (KPI)</h3>
                <button type="button" @click="addItem" class="btn btn-ghost btn-xs text-primary text-sm font-bold uppercase tracking-wider gap-2">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i> TAMBAH TARGET
                </button>
            </div>

            <div class="space-y-6">
                <template x-for="(item, index) in items" :key="index">
                    <div class="bg-base-100 p-6 rounded-2xl border border-base-200 border-l-[6px] !border-l-primary relative animate-in fade-in slide-in-from-top-4 duration-300">
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="absolute top-6 right-6 btn btn-ghost btn-circle btn-sm text-error/30 hover:text-error hover:bg-error/10">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>

                        <div class="grid gap-8">
                            <div class="form-control w-full space-y-3">
                                <label class="label py-0 ml-1">
                                    <span class="label-text text-[0.65rem] font-semibold tracking-wide text-base-content/60 uppercase">JENIS PEKERJAAN (BERBASIS KPI)</span>
                                </label>
                                <div class="relative">
                                    <select :name="`items[${index}][kpi_id]`" x-model="item.kpi_id" required
                                            class="select select-bordered w-full h-14 rounded-2xl font-medium text-base-content bg-base-200 border-transparent focus:border-primary/20 appearance-none">
                                        <option value="">-- Pilih Jenis Pekerjaan --</option>
                                        @foreach($kpis as $kpi)
                                            <option value="{{ $kpi->id }}">{{ $kpi->description }} (Target: {{ $kpi->target }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none">
                                        <i data-lucide="chevron-down" class="h-5 w-5 text-primary/30"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-control w-full space-y-3">
                                <label class="label py-0 ml-1">
                                    <span class="label-text text-[0.65rem] font-semibold tracking-wide text-base-content/60 uppercase">PENJELASAN DETAIL PEKERJAAN</span>
                                </label>
                                <textarea :name="`items[${index}][details]`" x-model="item.details" required rows="4"
                                          placeholder="Jelaskan detail pekerjaan yang dilakukan..."
                                          class="textarea textarea-bordered w-full rounded-2xl p-5 text-sm font-medium leading-relaxed bg-base-200 border-transparent focus:border-primary/20"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- SECTION 5: LAPORAN NARASI -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-primary/70 uppercase tracking-wider ml-1">CATATAN / LAPORAN KEGIATAN</h3>
            <div class="bg-base-100 p-4 rounded-2xl border border-base-200">
                <textarea name="daily_report" rows="6" required
                          placeholder="Ceritakan apa saja yang Anda kerjakan hari ini secara lengkap..."
                          class="textarea textarea-bordered w-full rounded-2xl p-6 text-base font-medium leading-relaxed bg-base-200 border-transparent focus:border-primary/20">{{ old('daily_report', $logbook?->daily_report) }}</textarea>
            </div>
        </div>

        <!-- SUBMIT BUTTON -->
        <div class="pt-8">
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary btn-lg rounded-2xl w-full sm:w-auto h-16 px-12 shadow-xl shadow-primary/20 text-white font-bold tracking-wider uppercase transition-all hover:-translate-y-1 hover:shadow-2xl hover:shadow-primary/30 active:scale-95">
                    Kirim Logbook
                </button>
            </div>
        </div>
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
