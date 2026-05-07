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
          class="space-y-8 pb-10"
          x-data="{
              isSubmitting: false,
              items: @js(old('items', $logbook ? $logbook->items->map(fn($k) => ['kpi_id' => $k->kpi_id, 'details' => $k->work_description])->toArray() : [['kpi_id' => '', 'details' => '']])),
              init() {
                  this.$watch('items', () => { 
                      setTimeout(() => lucide.createIcons(), 50); 
                  });
              },
              addItem() { this.items.push({kpi_id: '', details: ''}) },
              removeItem(index) { this.items.splice(index, 1) },
              validateAndSubmit(e) {
                  const lat = document.getElementById('latitude').value;
                  const lng = document.getElementById('longitude').value;
                  
                  if (!lat || lat === '-' || isNaN(lat) || !lng || lng === '-' || isNaN(lng)) {
                      alert('LokASI GPS WAJIB DIDETEKSI! Silakan klik tombol [Deteksi Lokasi] terlebih dahulu agar koordinat Anda tercatat.');
                      e.preventDefault();
                      return false;
                  }
                  
                  setTimeout(() => { this.isSubmitting = true; }, 50);
              }
          }"
          @submit="validateAndSubmit($event)">
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
        <div class="space-y-3" x-data="{ 
            hasImage: {{ $logbook?->main_photo ? 'true' : 'false' }}, 
            imageSrc: '{{ $logbook?->main_photo ? asset('storage/' . $logbook->main_photo) : '' }}', 
            cameraActive: false,
            stream: null,
            handleNativePhoto(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imageSrc = e.target.result;
                    this.hasImage = true;
                    
                    // Kompres jika ukuran melebihi 1MB
                    if (file.size > 1024 * 1024) {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let w = img.width;
                            let h = img.height;
                            const maxW = 1280;
                            
                            if (w > maxW) {
                                h = Math.round(h * maxW / w);
                                w = maxW;
                            }
                            
                            canvas.width = w;
                            canvas.height = h;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);
                            
                            canvas.toBlob((blob) => {
                                const newFile = new File([blob], 'compressed_' + file.name, { type: 'image/jpeg' });
                                const dt = new DataTransfer();
                                dt.items.add(newFile);
                                document.getElementById('main_photo').files = dt.files;
                            }, 'image/jpeg', 0.8);
                        };
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            },
            async openCamera() {
                if (window.HRISNative && window.HRISNative.isNativePlatform) {
                    document.getElementById('main_photo').click();
                    return;
                }
                this.cameraActive = true;
                this.$nextTick(async () => {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { 
                                facingMode: 'environment',
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            }, 
                            audio: false 
                        });
                        this.$refs.video.srcObject = this.stream;
                        this.$refs.video.play();
                    } catch (err) {
                        console.error('Kamera error:', err);
                        alert('Gagal mengakses kamera. Pastikan izin kamera sudah diberikan dan Anda menggunakan HTTPS/Localhost.');
                        this.cameraActive = false;
                    }
                });
            },
            capture() {
                const video = this.$refs.video;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                
                // Draw current frame
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                this.imageSrc = dataUrl;
                this.hasImage = true;
                
                // Set to hidden file input
                canvas.toBlob((blob) => {
                    const file = new File([blob], 'camera_capture.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('main_photo').files = dt.files;
                }, 'image/jpeg', 0.8);

                this.closeCamera();
            },
            closeCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
                this.cameraActive = false;
            }
        }" @keydown.escape.window="closeCamera()">
            <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em] ml-1">DOKUMENTASI FOTO</h3>
            <div class="bg-base-100 p-3 rounded-2xl border border-base-200">
                <div class="relative aspect-[16/9] rounded-xl bg-base-200 overflow-hidden group border-2 border-dashed border-base-300 transition-all" :class="hasImage ? 'border-primary/50' : 'hover:border-primary/30'">
                    <!-- File input (Hidden, triggered via JS) -->
                    <input type="file" name="main_photo" id="main_photo" class="hidden" accept="image/*" capture="environment" 
                           @change="handleNativePhoto($event)" 
                           {{ $logbook?->main_photo ? '' : 'required' }}>
                    
                    <button type="button" @click="openCamera" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer transition-all z-10"
                           :class="hasImage ? 'opacity-0 hover:opacity-100 bg-base-300/60 backdrop-blur-sm' : 'hover:bg-base-300/30'">
                        <div class="w-12 h-12 bg-base-100 rounded-xl shadow-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i data-lucide="camera" class="h-6 w-6 text-primary/80"></i>
                        </div>
                        <span class="text-[0.55rem] font-black text-base-content uppercase tracking-[0.15em] bg-base-100/90 py-1.5 px-3 rounded-lg shadow-sm" x-text="hasImage ? 'GANTI FOTO' : 'AMBIL FOTO'"></span>
                    </button>

                    <img id="image-preview" :src="imageSrc" alt="Preview" class="absolute inset-0 w-full h-full object-cover z-0" x-show="hasImage" x-transition>
                    
                </div>
            </div>

            <!-- BROWSER CAMERA MODAL -->
            <div x-show="cameraActive" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" x-transition>
                <div class="bg-base-100 w-full max-w-md rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between">
                        <h4 class="text-[0.65rem] font-black text-primary uppercase tracking-[0.2em]">Kamera Dokumentasi</h4>
                        <button type="button" @click="closeCamera" class="btn btn-ghost btn-circle btn-sm text-error/50 hover:text-error hover:bg-error/10">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>

                    <!-- Video Preview Area -->
                    <div class="relative bg-black aspect-[3/4] flex items-center justify-center overflow-hidden">
                        <video x-ref="video" playsinline class="w-full h-full object-cover"></video>
                        
                        <!-- Overlay Guide -->
                        <div class="absolute inset-8 border-2 border-dashed border-white/20 rounded-2xl pointer-events-none"></div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="p-8 flex flex-col items-center gap-4 bg-base-100">
                        <button type="button" @click="capture" class="w-20 h-20 rounded-full border-4 border-primary/20 p-1 bg-white shadow-xl shadow-primary/20 active:scale-95 transition-all">
                            <div class="w-full h-full rounded-full bg-primary flex items-center justify-center">
                                <i data-lucide="camera" class="h-8 w-8 text-white"></i>
                            </div>
                        </button>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-[0.2em]">Ketuk tombol untuk ambil foto</p>
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

        <!-- SECTION 6: LAMPIRAN FILE (OPSIONAL) -->
        <div class="space-y-3" x-data="{
            fileList: [],
            maxSize: 10 * 1024 * 1024,
            handleFiles(event) {
                const newFiles = Array.from(event.target.files);
                const invalid = newFiles.filter(f => f.size > this.maxSize);
                if (invalid.length) {
                    alert(invalid.map(f => f.name).join('\n') + '\n\nMelebihi batas 10MB, file ini tidak akan ditambahkan.');
                }
                const valid = newFiles.filter(f => f.size <= this.maxSize);
                this.fileList = [...this.fileList, ...valid];
                this.syncInput();
                this.$nextTick(() => { lucide.createIcons(); });
            },
            removeFile(index) {
                this.fileList.splice(index, 1);
                this.syncInput();
                this.$nextTick(() => { lucide.createIcons(); });
            },
            syncInput() {
                const dt = new DataTransfer();
                this.fileList.forEach(f => dt.items.add(f));
                document.getElementById('attachments').files = dt.files;
            },
            formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            },
            getIcon(name) {
                const ext = name.split('.').pop().toLowerCase();
                if (['jpg','jpeg','png','gif','webp','bmp'].includes(ext)) return 'image';
                if (ext === 'pdf') return 'file-text';
                if (['xls','xlsx'].includes(ext)) return 'table-2';
                if (ext === 'csv') return 'file-spreadsheet';
                if (['doc','docx'].includes(ext)) return 'file-type-2';
                if (['ppt','pptx'].includes(ext)) return 'presentation';
                if (['zip','rar','7z'].includes(ext)) return 'archive';
                return 'file';
            }
        }">
            <div class="flex items-center justify-between mx-1">
                <h3 class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.2em]">
                    LAMPIRAN FILE <span class="text-base-content/20 font-bold normal-case tracking-normal">(opsional)</span>
                </h3>
                <span class="text-[0.55rem] font-bold text-base-content/20 uppercase tracking-widest">Maks. 10 MB/file</span>
            </div>

            <div class="bg-base-100 rounded-2xl border border-base-200 overflow-hidden">
                <!-- Drop Zone / Click to Pick -->
                <label for="attachments"
                    class="flex flex-col items-center justify-center gap-3 p-6 cursor-pointer hover:bg-primary/[0.02] active:bg-primary/5 transition-colors border-b border-dashed border-base-200"
                    :class="fileList.length === 0 ? 'border-b-0' : ''">
                    <div class="w-12 h-12 rounded-xl bg-primary/5 border border-primary/10 flex items-center justify-center">
                        <i data-lucide="paperclip" class="h-5 w-5 text-primary/50"></i>
                    </div>
                    <div class="text-center">
                        <p class="text-[0.65rem] font-black text-primary/50 uppercase tracking-[0.15em]">Pilih File Lampiran</p>
                        <p class="text-[0.55rem] font-bold text-base-content/20 mt-0.5">PDF, Word, Excel, CSV, Gambar, ZIP, dll</p>
                    </div>
                    <input type="file" id="attachments" name="attachments[]" multiple class="hidden"
                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx,.zip,.rar,.7z"
                           @change="handleFiles($event)">
                </label>

                <!-- File List Preview -->
                <div x-show="fileList.length > 0" x-transition class="divide-y divide-base-100">
                    <template x-for="(file, index) in fileList" :key="index">
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/5 border border-primary/10 flex items-center justify-center text-primary/40 shrink-0">
                                <i :data-lucide="getIcon(file.name)" class="h-4 w-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[0.65rem] font-bold text-base-content truncate leading-tight" x-text="file.name"></p>
                                <p class="text-[0.55rem] font-bold text-base-content/30 uppercase tracking-widest mt-0.5" x-text="formatSize(file.size)"></p>
                            </div>
                            <button type="button" @click="removeFile(index)"
                                class="btn btn-ghost btn-circle btn-xs text-error hover:bg-error/10 shrink-0 bg-error/[0.03]">
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </template>

                    <!-- Re-pick button -->
                    <label for="attachments" class="flex items-center justify-center gap-2 px-4 py-3 cursor-pointer hover:bg-primary/[0.02] transition-colors">
                        <i data-lucide="plus" class="h-3 w-3 text-primary/40"></i>
                        <span class="text-[0.6rem] font-black text-primary/40 uppercase tracking-widest">Tambah File Lagi</span>
                    </label>
                </div>

                <!-- Empty state -->
                <div x-show="fileList.length === 0" class="px-4 py-3 text-center">
                    <p class="text-[0.6rem] font-bold text-base-content/20 uppercase tracking-widest">Belum ada file dipilih</p>
                </div>
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

    <script>
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
