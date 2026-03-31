@php
    $readonly = $readonly ?? false;
    $employee = $employee ?? null;
@endphp

<div class="space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700">
    <!-- Header Summary Section in Form -->
    <div class="flex flex-col sm:flex-row items-center sm:items-start lg:items-center gap-8 md:gap-12 mb-16 p-8 rounded-3xl bg-base-200/50 border border-base-300">
        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-2xl bg-base-100 flex items-center justify-center overflow-hidden border border-base-300 shadow-md relative group shrink-0">
            @if($employee && $employee->foto)
                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" class="w-full h-full object-cover">
            @else
                <i data-lucide="user" class="h-16 w-16 text-base-content/10"></i>
            @endif

            @if(!$readonly)
            <div class="absolute inset-0 bg-primary/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white cursor-pointer p-6 text-center">
                <i data-lucide="upload" class="h-6 w-6 mb-2"></i>
                <span class="text-[0.65rem] font-semibold tracking-wide uppercase leading-none">Klik Unggah</span>
                <input type="file" name="foto" class="absolute inset-0 opacity-0 cursor-pointer">
            </div>
            @endif
        </div>
        
        <div class="w-full text-center sm:text-left">
            <h2 class="text-xs font-bold tracking-wider text-primary/40 uppercase mb-3">Identitas Personel</h2>
            <h1 class="text-3xl lg:text-5xl font-bold tracking-tight text-primary break-words max-w-4xl leading-none">
                {{ $employee ? $employee->nama : 'Entri Baru' }}
            </h1>
            <div class="mt-4 flex flex-wrap justify-center sm:justify-start gap-4">
                <div class="badge badge-primary badge-lg py-4 px-6 text-xs font-bold uppercase tracking-wide leading-none border-none">
                    ID_{{ $employee ? $employee->npp : 'UNDETERMINED' }}
                </div>
                <div class="badge bg-base-100 border-base-300 badge-lg py-4 px-6 text-xs font-bold uppercase tracking-wide text-base-content/40 leading-none">
                    {{ strtoupper($employee?->role ?? 'PENDING') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Data Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
        <!-- Main Form Column (Left) -->
        <div class="lg:col-span-8 space-y-16">
            
            <!-- SECTION: DATA UTAMA -->
            <section class="space-y-10">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary shadow-inner">
                        <i data-lucide="shield-check" class="h-5 w-5"></i>
                    </div>
                    <h3 class="text-sm font-bold tracking-wider text-primary/60 uppercase">Informasi Dasar</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Nama Lengkap *</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama', $employee?->nama) }}" placeholder="Nama wajib diisi" required {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                        @error('nama') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-2 ml-1 flex items-center gap-1"><i data-lucide="alert-circle" class="h-3 w-3"></i> {{ $message }}</p> @enderror
                    </div>
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">NPP *</span>
                        </label>
                        <input type="number" name="npp" value="{{ old('npp', $employee?->npp) }}" placeholder="NPP unik" required {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content font-mono text-sm tabular-nums bg-base-200/50 border-base-300 focus:border-primary/20">
                        @error('npp') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-2 ml-1 flex items-center gap-1"><i data-lucide="alert-circle" class="h-3 w-3"></i> {{ $message }}</p> @enderror
                    </div>
                </div>

                @if(!$readonly || request()->routeIs('employees.create') || request()->routeIs('employees.edit'))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="form-control w-full space-y-3" x-data="{ show: false }">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Kredensial Password {{ $employee ? '(Opsional)' : '*' }}</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="Min. 8 Karakter" {{ $employee ? '' : 'required' }} 
                                   class="input input-bordered w-full h-14 md:h-16 px-6 pr-16 rounded-2xl font-medium text-base-content text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                            <button type="button" @click="show = !show" class="absolute right-6 top-1/2 -translate-y-1/2 text-base-content/30 hover:text-primary transition-colors">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </section>

             <!-- SECTION: KELAHIRAN -->
             <section class="space-y-10">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary shadow-inner">
                        <i data-lucide="cake" class="h-5 w-5"></i>
                    </div>
                    <h3 class="text-sm font-bold tracking-wider text-primary/60 uppercase">Data Kelahiran</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Tempat Lahir</span>
                        </label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $employee?->tempat_lahir) }}" {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                    </div>
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Tanggal Lahir</span>
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $employee?->tanggal_lahir?->format('Y-m-d')) }}" {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content font-mono text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                    </div>
                </div>
            </section>

             <!-- SECTION: ADMINISTRASI -->
             <section class="space-y-10 pt-4">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary shadow-inner">
                        <i data-lucide="file-badge" class="h-5 w-5"></i>
                    </div>
                    <h3 class="text-sm font-bold tracking-wider text-primary/60 uppercase">Data Administratif</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">NIK (16 DIGIT)</span>
                        </label>
                        <input type="number" name="nik" value="{{ old('nik', $employee?->nik) }}" maxlength="16" {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content font-mono text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                    </div>
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">NOMOR NPWP</span>
                        </label>
                        <input type="text" name="npwp" value="{{ old('npwp', $employee?->npwp) }}" {{ $readonly ? 'disabled' : '' }}
                               class="input input-bordered w-full h-14 md:h-16 px-6 rounded-2xl font-medium text-base-content font-mono text-sm bg-base-200/50 border-base-300 focus:border-primary/20">
                    </div>
                </div>

                <div class="form-control w-full space-y-3">
                    <label class="label py-0 ml-1">
                        <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Alamat Lengkap</span>
                    </label>
                    <textarea name="alamat" rows="4" {{ $readonly ? 'disabled' : '' }}
                              class="textarea textarea-bordered w-full rounded-2xl p-6 text-sm font-medium leading-relaxed bg-base-200/50 border-base-300 focus:border-primary/20">{{ old('alamat', $employee?->alamat) }}</textarea>
                </div>
            </section>

             <!-- SECTION: RIWAYAT -->
             <section class="space-y-10 pt-4">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary shadow-inner">
                        <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                    </div>
                    <h3 class="text-sm font-bold tracking-wider text-primary/60 uppercase">Pendidikan & Karir</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-10">
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Riwayat Pendidikan</span>
                        </label>
                        <textarea name="riwayat_pendidikan" rows="8" {{ $readonly ? 'disabled' : '' }}
                                  class="textarea textarea-bordered w-full rounded-2xl p-8 text-sm font-medium leading-relaxed bg-base-200/50 border-base-300 focus:border-primary/20">{{ old('riwayat_pendidikan', $employee?->riwayat_pendidikan) }}</textarea>
                    </div>
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/50 uppercase">Pengalaman Karir</span>
                        </label>
                        <textarea name="riwayat_karir" rows="8" {{ $readonly ? 'disabled' : '' }}
                                  class="textarea textarea-bordered w-full rounded-2xl p-8 text-sm font-medium leading-relaxed bg-base-200/50 border-base-300 focus:border-primary/20">{{ old('riwayat_karir', $employee?->riwayat_karir) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar Form Column (Right) -->
        <div class="lg:col-span-4 space-y-12">
            <!-- SECTION: STATUS PERKAWINAN -->
            <section class="card bg-base-100 p-10 rounded-3xl border border-base-300 shadow-sm space-y-10">
                <div class="flex items-center gap-4">
                    <h3 class="text-sm font-bold tracking-wider text-primary/40 uppercase">Status Personal</h3>
                </div>
                
                <div class="space-y-6">
                    <div class="flex p-2 bg-base-200 rounded-2xl gap-2 h-16 items-stretch relative" x-data="{ married: {{ old('status_perkawinan', $employee?->status_perkawinan ?? 'belum_menikah') === 'menikah' ? 'true' : 'false' }} }">
                        <input type="hidden" name="status_perkawinan" :value="married ? 'menikah' : 'belum_menikah'">
                        <button type="button" @click="!{{ $readonly ? 'true' : 'false' }} && (married = false)"
                                :class="!married ? 'bg-base-100 text-primary shadow-sm ring-1 ring-base-300' : 'text-base-content/30 opacity-70 hover:opacity-100'"
                                class="flex-1 rounded-xl text-[0.65rem] font-black uppercase tracking-[0.1em] transition-all duration-300">
                            BELUM MENIKAH
                        </button>
                        <button type="button" @click="!{{ $readonly ? 'true' : 'false' }} && (married = true)"
                                :class="married ? 'bg-base-100 text-primary shadow-sm ring-1 ring-base-300' : 'text-base-content/30 opacity-70 hover:opacity-100'"
                                class="flex-1 rounded-xl text-[0.65rem] font-black uppercase tracking-[0.1em] transition-all duration-300">
                            MENIKAH
                        </button>
                    </div>
                </div>
            </section>

             <!-- SECTION: ROLE & STRUKTUR -->
             <section class="card bg-base-100 p-10 rounded-3xl border border-base-300 shadow-sm space-y-12 lg:sticky lg:top-24">
                <div class="space-y-8">
                    <h3 class="text-sm font-bold tracking-wider text-primary/40 uppercase">Akses & Struktur</h3>
                    
                    <div class="form-control w-full space-y-3">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/30 uppercase">Supervisor</span>
                        </label>
                        <div class="relative">
                            <select name="supervisor_id" {{ $readonly ? 'disabled' : '' }}
                                    class="select select-bordered w-full h-16 px-8 rounded-2xl font-medium text-base-content bg-base-200 border-transparent focus:border-primary/20 appearance-none">
                                <option value="">— NO SUPERVISOR —</option>
                                @foreach($supervisors as $sv)
                                <option value="{{ $sv->id }}" {{ old('supervisor_id', $employee?->supervisor_id) == $sv->id ? 'selected' : '' }}>
                                    {{ $sv->nama }} ({{ strtoupper($sv->role) }})
                                </option>
                                @endforeach
                            </select>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <i data-lucide="chevron-down" class="h-5 w-5 text-primary/30"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-control w-full space-y-3 pt-6 border-t border-base-200">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.7rem] font-semibold tracking-wide text-base-content/30 uppercase">Role Sistem</span>
                        </label>
                        <div class="relative">
                            <select name="role" {{ $readonly ? 'disabled' : '' }}
                                    class="select select-bordered w-full h-16 px-8 rounded-2xl font-medium text-base-content bg-base-200 border-transparent focus:border-primary/20 appearance-none">
                                <option value="staff" {{ old('role', $employee?->role) == 'staff' ? 'selected' : '' }}>STAF OPERASIONAL</option>
                                @if(auth()->user()->role === 'super_admin')
                                 <option value="admin" {{ old('role', $employee?->role) == 'admin' ? 'selected' : '' }}>ADMINISTRATOR</option>
                                 <option value="super_admin" {{ old('role', $employee?->role) == 'super_admin' ? 'selected' : '' }}>SUPER ADMIN</option>
                                @endif
                            </select>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <i data-lucide="shield-check" class="h-5 w-5 text-primary/30"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 pt-10 border-t border-base-200">
                     <div class="flex items-center justify-between">
                         <h3 class="text-sm font-bold tracking-wider text-primary/40 uppercase">Target KPI</h3>
                         <span class="badge badge-primary border-none text-[0.5rem] font-black tracking-tighter uppercase px-3 py-3">Wajib Assign</span>
                     </div>

                     <div class="grid grid-cols-1 gap-3 max-h-72 overflow-y-auto pr-3 custom-scrollbar">
                         @foreach($kpis as $kpi)
                         <label class="flex items-start gap-5 p-5 rounded-2xl bg-base-200 border-2 border-transparent transition-all cursor-pointer peer-checked:border-primary peer-checked:bg-primary/5 group">
                             <div class="mt-1">
                                 <input type="checkbox" name="kpi_ids[]" value="{{ $kpi->id }}" {{ $readonly ? 'disabled' : '' }}
                                        {{ in_array($kpi->id, old('kpi_ids', $employee?->kpis->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}
                                        class="checkbox checkbox-primary peer border-2 border-base-300 rounded-lg">
                             </div>
                             <div class="flex-1">
                                 <p class="text-xs font-semibold text-base-content leading-tight group-hover:text-primary transition-colors">{{ $kpi->description }}</p>
                                 <p class="text-[0.65rem] font-bold text-base-content/40 mt-1.5 uppercase tracking-widest">{{ $kpi->target }} {{ $kpi->unit }}</p>
                             </div>
                         </label>
                         @endforeach
                     </div>
                     @error('kpi_ids') <p class="text-[0.6rem] font-bold text-error uppercase tracking-widest mt-4 ml-1 flex items-center gap-1"><i data-lucide="alert-circle" class="h-3 w-3"></i> {{ $message }}</p> @enderror
                </div>
            </section>
        </div>
    </div>
</div>
