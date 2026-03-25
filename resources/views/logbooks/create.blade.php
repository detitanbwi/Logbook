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
          class="space-y-12 pb-48"
          x-data="{
              items: @js($logbook ? $logbook->kpis->map(fn($k) => ['kpi_id' => $k->kpi_id, 'details' => $k->details]) : [['kpi_id' => '', 'details' => '']]),
              addItem() { this.items.push({kpi_id: '', details: ''}) },
              removeItem(index) { this.items.splice(index, 1) }
          }">
        @csrf
        @if($logbook) @method('PUT') @endif

        <!-- SECTION 1: LOKASI -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em] ml-1">SECTION 1: LOKASI</h3>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-primary/5 space-y-6">
                <!-- Row 1: Button & Status -->
                <div class="flex items-center justify-between">
                    <button type="button" onclick="getLocation()" class="flex items-center gap-2 bg-primary text-white px-5 py-3 rounded-xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all text-[0.75rem] font-black uppercase tracking-widest group">
                        <span class="material-symbols-outlined text-[1.1rem] font-bold">my_location</span>
                        <span>Ambil Lokasi</span>
                    </button>
                    <div class="text-right">
                        <p class="text-[0.5rem] font-black text-primary/30 uppercase tracking-widest mb-0.5">STATUS GPS</p>
                        <p id="gps-status" class="text-[0.65rem] font-black text-primary uppercase leading-tight tracking-wide">Akurasi Tinggi</p>
                    </div>
                </div>

                <!-- Row 2: Lat/Long Display -->
                <div class="grid grid-cols-2 gap-8 border-t border-primary/5 pt-4">
                    <div>
                        <label class="block text-[0.55rem] font-black text-primary/30 uppercase tracking-widest mb-1.5">LATITUDE</label>
                        <p id="lat-display" class="text-[0.85rem] font-bold text-primary font-mono tracking-tight">-</p>
                    </div>
                    <div>
                        <label class="block text-[0.55rem] font-black text-primary/30 uppercase tracking-widest mb-1.5">LONGITUDE</label>
                        <p id="lng-display" class="text-[0.85rem] font-bold text-primary font-mono tracking-tight">-</p>
                    </div>
                </div>

                <!-- Hidden GPS Data -->
                <input type="hidden" name="latitude" id="latitude" value="-">
                <input type="hidden" name="longitude" id="longitude" value="-">
            </div>
        </div>

        <!-- SECTION 2: WAKTU -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em] ml-1">SECTION 2: WAKTU</h3>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                {{-- Start Time Card --}}
                <div class="bg-white p-4 sm:p-5 rounded-3xl shadow-sm border border-primary/5 space-y-3">
                    <label class="block text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.1em]">START TIME</label>
                    <div>
                        <input type="time" name="start_time" value="{{ old('start_time', $logbook?->start_time ?? '08:00') }}"
                               class="w-full bg-surface-container/80 border-none rounded-xl py-3 px-3 text-[0.9rem] sm:text-[0.95rem] font-black text-primary focus:ring-2 focus:ring-primary/10 transition-all">
                    </div>
                </div>
                {{-- End Time Card --}}
                <div class="bg-white p-4 sm:p-5 rounded-3xl shadow-sm border border-primary/5 space-y-3">
                    <label class="block text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.1em]">END TIME</label>
                    <div>
                        <input type="time" name="end_time" value="{{ old('end_time', $logbook?->end_time ?? '17:00') }}"
                               class="w-full bg-surface-container/80 border-none rounded-xl py-3 px-3 text-[0.9rem] sm:text-[0.95rem] font-black text-primary focus:ring-2 focus:ring-primary/10 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: FOTO DOKUMENTASI -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em] ml-1">SECTION 3: FOTO DOKUMENTASI</h3>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-primary/5">
                <div class="relative aspect-[16/9] rounded-2xl bg-surface-container/50 overflow-hidden group border border-primary/5">
                    <input type="file" name="main_photo" id="main_photo" class="hidden" accept="image/*" onchange="previewImage(event)">
                    <label for="main_photo" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer hover:bg-primary/5 transition-all">
                        <span class="material-symbols-outlined text-4xl text-primary/10 mb-2">add_a_photo</span>
                        <span class="text-[0.6rem] font-black text-primary/30 uppercase tracking-[0.1em]">Upload Proof</span>
                    </label>
                    <img id="image-preview" src="{{ $logbook?->main_photo ? asset('storage/' . $logbook->main_photo) : '' }}"
                         alt="Preview" class="absolute inset-0 w-full h-full object-cover {{ $logbook?->main_photo ? '' : 'hidden' }}">
                </div>
                @error('main_photo') <p class="text-[0.6rem] font-bold text-red-500 uppercase tracking-widest mt-2 ml-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- SECTION 4: KPI ITEMS -->
        <div class="space-y-3">
            <div class="flex items-center justify-between mx-1">
                <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em]">SECTION 4: KPI ITEMS</h3>
                <button type="button" @click="addItem" class="flex items-center gap-1.5 text-primary text-[0.65rem] font-black uppercase tracking-widest">
                    <span class="material-symbols-outlined text-sm">add_circle</span> TAMBAH KPI
                </button>
            </div>

            <template x-for="(item, index) in items" :key="index">
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-primary/5 border-l-[6px] !border-l-primary relative pt-10 group animate-fade-in">
                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="absolute top-4 right-4 text-red-500/20 hover:text-red-500 transition-all">
                        <span class="material-symbols-outlined text-[1.1rem]">delete</span>
                    </button>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="block text-[0.55rem] font-black text-primary/30 uppercase tracking-widest ml-1">DROPDOWN KPI</label>
                            <div class="relative">
                                <select :name="`items[${index}][kpi_id]`" x-model="item.kpi_id" required
                                        class="w-full bg-surface-container/80 border-none rounded-xl py-3.5 px-4 text-[0.9rem] font-bold text-primary appearance-none focus:ring-2 focus:ring-primary/10 transition-all">
                                    <option value="">Pilih Target KPI</option>
                                    @foreach($kpis as $kpi)
                                        <option value="{{ $kpi->id }}">{{ $kpi->description }} (Target: {{ $kpi->target }})</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary/30 pointer-events-none text-xl">expand_more</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[0.55rem] font-black text-primary/30 uppercase tracking-widest ml-1">JOB DESCRIPTION</label>
                            <textarea :name="`items[${index}][details]`" x-model="item.details" required rows="3"
                                      placeholder="Describe the task..."
                                      class="w-full bg-surface-container/80 border-none rounded-xl py-4 px-5 text-[0.9rem] font-medium text-primary/70 leading-relaxed placeholder:text-outline/40 focus:ring-2 focus:ring-primary/10 transition-all"></textarea>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- SECTION 5: LAPORAN HARIAN -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em] ml-1">SECTION 5: LAPORAN HARIAN</h3>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-primary/5">
                <textarea name="daily_report" rows="5" required
                          placeholder="Tuliskan laporan detail kegiatan..."
                          class="w-full bg-surface-container/80 border-none rounded-2xl py-5 px-6 text-[0.9rem] font-medium leading-relaxed text-primary/80 placeholder:text-outline/40 focus:ring-2 focus:ring-primary/10 transition-all">{{ old('daily_report', $logbook?->daily_report) }}</textarea>
            </div>
        </div>

        <!-- SECTION 6: FILE PENDUKUNG -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-black text-primary/40 uppercase tracking-[0.15em] ml-1">SECTION 6: FILE PENDUKUNG</h3>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-primary/5">
                <div class="border-2 border-dashed border-primary/10 rounded-2xl p-6 flex flex-col items-center justify-center gap-3 hover:bg-primary/5 transition-all cursor-pointer group">
                    <span class="material-symbols-outlined text-3xl text-primary/20 group-hover:scale-110 transition-transform">upload_file</span>
                    <p class="text-[0.55rem] font-black text-primary/30 uppercase tracking-widest">MULTI UPLOAD</p>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION FOOTER -->
        <div class="fixed bottom-0 left-0 w-full z-[100] px-6 pb-10 pt-4 bg-transparent pointer-events-none">
            <div class="max-w-lg mx-auto pointer-events-auto">
                <button type="submit" class="w-full h-14 bg-primary text-white rounded-2xl font-black text-[0.8rem] tracking-[0.3em] shadow-xl shadow-primary/30 active:scale-95 transition-all">
                    KIRIM
                </button>
            </div>
        </div>
    </form>

    <script>
        async function getLocation() {
            const status = document.getElementById('gps-status');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const latDisplay = document.getElementById('lat-display');
            const lngDisplay = document.getElementById('lng-display');

            if (!window.HRISNative || typeof window.HRISNative.getCurrentPosition !== 'function') {
                status.innerText = 'GPS TIDAK TERSEDIA';
                return;
            }

            status.innerText = 'MENCARI...';

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
                alert('Izin lokasi belum diberikan. Aktifkan izin lokasi untuk melanjutkan.');
            }
        }

        function previewImage(event) {
            const preview = document.getElementById('image-preview');
            const btn = document.getElementById('remove-img-btn');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    preview.src = reader.result;
                    preview.classList.remove('hidden');
                    btn.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            const input = document.getElementById('main_photo');
            const preview = document.getElementById('image-preview');
            const btn = document.getElementById('remove-img-btn');
            input.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            btn.classList.add('hidden');
        }
    </script>
</x-layouts.app>
