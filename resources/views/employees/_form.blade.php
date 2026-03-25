@php
    $readonly = $readonly ?? false;
    $employee = $employee ?? null;
@endphp

<div class="space-y-12 md:space-y-16 animate-in fade-in slide-in-from-bottom duration-1000">
    <!-- Header Summary Section in Form -->
    <div class="flex flex-col sm:flex-row items-center sm:items-start md:items-center gap-6 md:gap-12 mb-10 md:mb-16 pb-10 md:pb-16 border-b border-outline-variant/10 text-center sm:text-left">
        <div class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 rounded-xl bg-surface-container-highest flex items-center justify-center overflow-hidden border border-outline-variant/20 shadow-2xl relative group shrink-0">
            @if($employee && $employee->foto)
                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" class="w-full h-full object-cover">
            @else
                <span class="material-symbols-outlined text-4xl md:text-6xl text-on-surface/10">person</span>
            @endif

            @if(!$readonly)
            <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white cursor-pointer overflow-hidden p-4 md:p-6 text-center">
                <span class="material-symbols-outlined text-xl md:text-3xl mb-1 md:mb-2">upload</span>
                <span class="text-[0.5rem] md:text-[0.65rem] font-bold tracking-[0.2em] uppercase leading-relaxed">Klik untuk unggah</span>
                <input type="file" name="foto" class="absolute inset-0 opacity-0 cursor-pointer">
            </div>
            @endif
        </div>
        
        <div class="w-full overflow-hidden">
            <h2 class="text-[0.55rem] md:text-[0.7rem] font-bold tracking-[0.5em] text-primary/40 uppercase mb-2 md:mb-4">Identitas Karyawan</h2>
            <h1 class="text-2xl sm:text-3xl md:text-5xl font-extrabold tracking-tighter text-primary break-words md:max-w-3xl">
                {{ $employee ? $employee->nama : 'Personel Baru' }}
            </h1>
            <p class="text-base md:text-xl font-medium text-on-surface/30 mt-2 md:mt-4 leading-relaxed tracking-tight tabular-nums">NPP_{{ $employee ? $employee->npp : 'UNDETERMINED' }}</p>
        </div>
    </div>

    <!-- Data Sections -->
    <div class="flex flex-col md:grid md:grid-cols-12 gap-12 md:gap-16">
        <!-- Main Form Column (Left) -->
        <div class="w-full md:col-span-8 space-y-12 md:space-y-16">
            
            <!-- SECTION: DATA UTAMA -->
            <section class="space-y-8 md:space-y-10 group">
                <div class="flex items-center gap-6">
                    <span class="w-10 h-0.5 bg-primary/20 group-focus-within:bg-primary group-focus-within:w-16 transition-all duration-700"></span>
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/60 uppercase group-focus-within:text-primary transition-colors">Data Utama</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 md:gap-10">
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Nama Lengkap *</label>
                        <input type="text" name="nama" value="{{ old('nama', $employee?->nama) }}" placeholder="Nama wajib diisi" required {{ $readonly ? 'disabled' : '' }}
                               class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight text-sm">
                        @error('nama') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-2 ml-1 flex items-center gap-1"><span class="material-symbols-outlined text-xs">error</span> {{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">NPP *</label>
                        <input type="number" name="npp" value="{{ old('npp', $employee?->npp) }}" placeholder="NPP unik" required {{ $readonly ? 'disabled' : '' }}
                               class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight tabular-nums text-sm">
                        @error('npp') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-2 ml-1 flex items-center gap-1"><span class="material-symbols-outlined text-xs">error</span> {{ $message }}</p> @enderror
                    </div>
                </div>

                @if(!$readonly || request()->routeIs('employees.create') || request()->routeIs('employees.edit'))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 md:gap-10">
                    <div class="space-y-4" x-data="{ show: false }">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Password {{ $employee ? '(Kosongkan jika tidak ganti)' : '*' }}</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="Min. 8 Karakter" {{ $employee ? '' : 'required' }} 
                                   class="w-full h-14 md:h-16 pl-6 md:pl-8 pr-16 rounded-xl font-medium text-primary tracking-tight text-sm">
                            <button type="button" @click="show = !show" class="absolute right-6 top-1/2 -translate-y-1/2 text-on-surface/20 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </section>

             <!-- SECTION: DATA KELAHIRAN -->
             <section class="space-y-8 md:space-y-10 group">
                <div class="flex items-center gap-6">
                    <span class="w-10 h-0.5 bg-primary/20 group-focus-within:bg-primary group-focus-within:w-16 transition-all duration-700"></span>
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/60 uppercase group-focus-within:text-primary transition-colors">Data Kelahiran</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 md:gap-10">
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $employee?->tempat_lahir) }}" {{ $readonly ? 'disabled' : '' }}
                               class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight text-sm">
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Tanggal Lahir</label>
                        <div class="relative">
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $employee?->tanggal_lahir?->format('Y-m-d')) }}" {{ $readonly ? 'disabled' : '' }}
                                   class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight tabular-nums text-sm">
                        </div>
                    </div>
                </div>
            </section>

             <!-- SECTION: DATA ADMINISTRATIF -->
             <section class="space-y-8 md:space-y-10 group">
                <div class="flex items-center gap-6">
                    <span class="w-10 h-0.5 bg-primary/20 group-focus-within:bg-primary group-focus-within:w-16 transition-all duration-700"></span>
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/60 uppercase group-focus-within:text-primary transition-colors">Data Administratif</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 md:gap-10">
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">NIK (16 DIGIT)</label>
                        <input type="number" name="nik" value="{{ old('nik', $employee?->nik) }}" maxlength="16" {{ $readonly ? 'disabled' : '' }}
                               class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight tabular-nums text-sm">
                        @error('nik') <p class="text-[0.6rem] font-bold text-error uppercase mt-2">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">NPWP (ANGKA & SIMBOL)</label>
                        <input type="text" name="npwp" value="{{ old('npwp', $employee?->npwp) }}" {{ $readonly ? 'disabled' : '' }}
                               class="w-full h-14 md:h-16 px-6 md:px-8 rounded-xl font-medium text-primary tracking-tight tabular-nums text-sm">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Alamat Korespondensi</label>
                    <textarea name="alamat" rows="4" {{ $readonly ? 'disabled' : '' }}
                              class="w-full p-6 md:p-8 rounded-xl font-medium text-primary tracking-tight leading-relaxed text-sm">{{ old('alamat', $employee?->alamat) }}</textarea>
                </div>
            </section>

             <!-- SECTION: RIWAYAT -->
             <section class="space-y-8 md:space-y-10 group">
                <div class="flex items-center gap-6">
                    <span class="w-10 h-0.5 bg-primary/20 group-focus-within:bg-primary group-focus-within:w-16 transition-all duration-700"></span>
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/60 uppercase group-focus-within:text-primary transition-colors">Riwayat</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 md:gap-10">
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Riwayat Pendidikan</label>
                        <textarea name="riwayat_pendidikan" rows="6" {{ $readonly ? 'disabled' : '' }}
                                  class="w-full p-6 md:p-8 rounded-xl font-medium text-primary tracking-tight leading-relaxed text-sm">{{ old('riwayat_pendidikan', $employee?->riwayat_pendidikan) }}</textarea>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">Riwayat Karir</label>
                        <textarea name="riwayat_karir" rows="6" {{ $readonly ? 'disabled' : '' }}
                                  class="w-full p-6 md:p-8 rounded-xl font-medium text-primary tracking-tight leading-relaxed text-sm">{{ old('riwayat_karir', $employee?->riwayat_karir) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar Form Column (Right) -->
        <div class="w-full md:col-span-4 space-y-12 md:space-y-16">
            <!-- SECTION: STATUS PERSONAL (Toggle) -->
            <section class="p-10 bg-white rounded-xl editorial-shadow border border-outline-variant/10 space-y-10">
                <h3 class="text-xs font-bold tracking-[0.4em] text-primary/40 uppercase">Status Personal</h3>
                
                <div class="space-y-6">
                    <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/30 uppercase leading-relaxed">Status Perkawinan</label>
                    <div class="flex p-2 bg-surface-container-highest rounded-xl gap-2 h-16 items-stretch relative" x-data="{ married: {{ old('status_perkawinan', $employee?->status_perkawinan ?? 'belum_menikah') === 'menikah' ? 'true' : 'false' }} }">
                        <input type="hidden" name="status_perkawinan" :value="married ? 'menikah' : 'belum_menikah'">
                        
                        <button type="button" @click="!{{ $readonly ? 'true' : 'false' }} && (married = false)"
                                :class="!married ? 'bg-white text-primary shadow-lg shadow-black/5' : 'text-on-surface/30 hover:text-on-surface/60'"
                                class="flex-1 rounded-lg text-[0.65rem] font-extrabold tracking-[0.2em] uppercase transition-all duration-300">
                            BELUM MENIKAH
                        </button>
                        <button type="button" @click="!{{ $readonly ? 'true' : 'false' }} && (married = true)"
                                :class="married ? 'bg-white text-primary shadow-lg shadow-black/5' : 'text-on-surface/30 hover:text-on-surface/60'"
                                class="flex-1 rounded-lg text-[0.65rem] font-extrabold tracking-[0.2em] uppercase transition-all duration-300">
                            MENIKAH
                        </button>
                    </div>
                </div>
            </section>

             <!-- SECTION: STRUKTUR & ROLE -->
             <section class="p-10 bg-white rounded-xl editorial-shadow border border-outline-variant/10 space-y-12">
                <div class="space-y-10">
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/40 uppercase">Struktur</h3>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/30 uppercase">Atasan Langsung</label>
                        <select name="supervisor_id" {{ $readonly ? 'disabled' : '' }}
                                class="w-full h-16 px-8 rounded-xl font-bold text-primary tracking-tight appearance-none cursor-pointer">
                            <option value="">— NO SUPERVISOR —</option>
                            @foreach($supervisors as $sv)
                            <option value="{{ $sv->id }}" {{ old('supervisor_id', $employee?->supervisor_id) == $sv->id ? 'selected' : '' }}>
                                {{ $sv->nama }} ({{ strtoupper($sv->role) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-10 pt-10 border-t border-outline-variant/5">
                    <h3 class="text-xs font-bold tracking-[0.4em] text-primary/40 uppercase">Role Sistem</h3>
                    <div class="space-y-4">
                        <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/30 uppercase">Akses Role</label>
                        <select name="role" {{ $readonly ? 'disabled' : '' }}
                                class="w-full h-16 px-8 rounded-xl font-bold text-primary tracking-tight appearance-none cursor-pointer">
                            <option value="staff" {{ old('role', $employee?->role) == 'staff' ? 'selected' : '' }}>STAF</option>
                            @if(auth()->user()->role === 'super_admin')
                             <option value="admin" {{ old('role', $employee?->role) == 'admin' ? 'selected' : '' }}>ADMINISTRATOR</option>
                             <option value="super_admin" {{ old('role', $employee?->role) == 'super_admin' ? 'selected' : '' }}>SUPER ADMIN</option>
                            @endif
                        </select>
                    </div>
                </div>
            </section>
              <!-- SECTION: KPI ASSIGNMENT [FASE 2] -->
              <section class="p-10 bg-white rounded-xl editorial-shadow border border-outline-variant/10 space-y-10 mt-16">
                 <div class="flex items-center justify-between">
                     <h3 class="text-xs font-bold tracking-[0.4em] text-primary/40 uppercase">Key Performance Indicators</h3>
                     <span class="text-[0.6rem] font-bold text-primary/60 bg-primary/5 px-2 py-1 rounded truncate uppercase tracking-tighter">Wajib Min. 1</span>
                 </div>

                 <div class="space-y-4 max-h-80 overflow-y-auto pr-4 custom-scrollbar">
                     @foreach($kpis as $kpi)
                     <label class="flex items-start gap-4 p-4 rounded-xl hover:bg-surface-container-highest transition-all cursor-pointer group hover:scale-[1.02] active:scale-[0.98]">
                         <div class="relative flex items-center h-5 mt-1">
                             <input type="checkbox" name="kpi_ids[]" value="{{ $kpi->id }}" {{ $readonly ? 'disabled' : '' }}
                                    {{ in_array($kpi->id, old('kpi_ids', $employee?->kpis->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded-lg border-2 border-outline-variant/30 text-primary focus:ring-primary/20 transition-all checked:bg-primary checked:border-primary">
                         </div>
                         <div class="flex-1">
                             <p class="text-xs font-bold text-primary/80 group-hover:text-primary transition-colors">{{ $kpi->description }}</p>
                             <p class="text-[0.6rem] font-medium text-on-surface/40 mt-1 tracking-wide">{{ $kpi->target }} {{ $kpi->unit }}</p>
                         </div>
                     </label>
                     @endforeach
                 </div>
                 @error('kpi_ids') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-4 ml-1 flex items-center gap-1"><span class="material-symbols-outlined text-xs">error</span> {{ $message }}</p> @enderror
              </section>
        </div>
    </div>
</div>
