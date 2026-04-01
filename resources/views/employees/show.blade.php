<x-layouts.app :title="'Profil Saya'" active="profile" hideNav="true" :backUrl="route('dashboard')">
    <div x-data="{ showDeleteModal: false }">

        <!-- Profile Hero - No card, just space -->
        <div class="flex flex-col items-center text-center pb-6 border-b border-base-200">
            <!-- Avatar -->
            <div class="w-24 h-24 rounded-3xl bg-base-200 flex items-center justify-center overflow-hidden border-4 border-base-100 shadow-xl mb-4 relative">
                @if($employee->foto)
                    <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    <i data-lucide="user" class="h-12 w-12 text-base-content/20"></i>
                @endif
            </div>
            <p class="text-[0.55rem] font-black text-primary/40 uppercase tracking-[0.25em] mb-1">{{ strtoupper($employee->role ?? 'STAFF') }}</p>
            <h1 class="text-2xl font-black text-primary leading-tight mb-2">{{ $employee->nama }}</h1>
            <span class="bg-primary/10 text-primary font-black text-[0.6rem] tracking-widest uppercase px-4 py-1.5 rounded-full">ID {{ $employee->npp }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="py-4 flex flex-col gap-2 border-b border-base-200">
            @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary rounded-xl h-12 gap-2 font-black text-xs uppercase tracking-widest">
                    <i data-lucide="edit-3" class="h-4 w-4"></i> Edit Profil
                </a>
            @endif
            @if($employee->id === auth()->id())
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-error hover:bg-error/10 border border-base-200 rounded-xl h-12 w-full gap-2 font-black text-xs uppercase tracking-widest">
                        <i data-lucide="log-out" class="h-4 w-4"></i> Keluar Sesi
                    </button>
                </form>
            @endif
            @if(auth()->user()->role === 'super_admin' && $employee->id !== auth()->id())
                <button @click="showDeleteModal = true" class="btn btn-ghost text-error hover:bg-error/10 border border-error/20 rounded-xl h-12 gap-2 font-black text-xs uppercase tracking-widest">
                    <i data-lucide="trash-2" class="h-4 w-4"></i> Hapus Akun
                </button>
            @endif
        </div>

        <!-- Data Sections: flat list with dividers -->
        <div class="divide-y divide-base-200">

            {{-- INFORMASI DASAR --}}
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Informasi Dasar</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Nama Lengkap</p>
                        <p class="text-sm font-bold text-base-content">{{ $employee->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">NPP</p>
                        <p class="text-sm font-bold text-base-content font-mono">{{ $employee->npp ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- DATA KELAHIRAN --}}
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Data Kelahiran</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Tempat Lahir</p>
                        <p class="text-sm font-bold text-base-content">{{ $employee->tempat_lahir ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Tanggal Lahir</p>
                        <p class="text-sm font-bold text-base-content font-mono">{{ $employee->tanggal_lahir?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- STATUS --}}
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Status</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Status Perkawinan</p>
                        <span class="inline-block text-[0.6rem] font-black uppercase tracking-widest px-3 py-1 rounded-full {{ $employee->status_perkawinan === 'menikah' ? 'bg-primary/10 text-primary' : 'bg-base-200 text-base-content/50' }}">
                            {{ $employee->status_perkawinan === 'menikah' ? 'Menikah' : 'Belum Menikah' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Role Sistem</p>
                        <span class="inline-block text-[0.6rem] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-primary/10 text-primary">
                            {{ ucfirst(str_replace('_', ' ', $employee->role)) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ADMINISTRASI --}}
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Data Administratif</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">NIK</p>
                        <p class="text-sm font-bold text-base-content font-mono">{{ $employee->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">NPWP</p>
                        <p class="text-sm font-bold text-base-content font-mono">{{ $employee->npwp ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Alamat</p>
                        <p class="text-sm font-medium text-base-content/70 leading-relaxed">{{ $employee->alamat ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- PENDIDIKAN & KARIR --}}
            @if($employee->riwayat_pendidikan || $employee->riwayat_karir)
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Pendidikan & Karir</p>
                @if($employee->riwayat_pendidikan)
                <div>
                    <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Riwayat Pendidikan</p>
                    <p class="text-xs font-medium text-base-content/70 leading-relaxed whitespace-pre-line">{{ $employee->riwayat_pendidikan }}</p>
                </div>
                @endif
                @if($employee->riwayat_karir)
                <div>
                    <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Pengalaman Karir</p>
                    <p class="text-xs font-medium text-base-content/70 leading-relaxed whitespace-pre-line">{{ $employee->riwayat_karir }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- SUPERVISOR --}}
            <div class="py-5 space-y-4">
                <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Akses & Struktur</p>
                <div>
                    <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mb-1">Supervisor / Atasan</p>
                    @if($employee->supervisor)
                        <div class="flex items-center gap-3 mt-1">
                            <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary text-xs font-black">
                                {{ substr($employee->supervisor->nama, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-base-content">{{ $employee->supervisor->nama }}</p>
                                <p class="text-[0.55rem] font-bold text-base-content/40 uppercase tracking-widest">{{ $employee->supervisor->role }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm font-bold text-base-content/30">— Tidak ada —</p>
                    @endif
                </div>
            </div>

            {{-- KPI --}}
            @if($employee->kpis && $employee->kpis->count() > 0)
            <div class="py-5 space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[0.6rem] font-black text-primary/40 uppercase tracking-[0.2em]">Target KPI</p>
                    <span class="text-[0.5rem] font-black text-primary bg-primary/10 px-2 py-1 rounded-full uppercase tracking-widest">{{ $employee->kpis->count() }} Item</span>
                </div>
                <div class="space-y-2">
                    @foreach($employee->kpis as $kpi)
                    <div class="flex items-start gap-3 py-2 border-b border-base-200/50 last:border-0">
                        <div class="w-5 h-5 rounded-md bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="target" class="h-3 w-3 text-primary"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-base-content leading-tight">{{ $kpi->description }}</p>
                            <p class="text-[0.55rem] font-black text-base-content/30 uppercase tracking-widest mt-0.5">Target: {{ $kpi->target }} {{ $kpi->unit }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ONESIGNAL DEBUG (Temporary) --}}
             <div class="py-5 bg-primary/5 rounded-3xl p-5 mt-6 border border-primary/20 shadow-inner">
                 <div class="flex items-center gap-2 mb-4">
                     <i data-lucide="bell-ring" class="h-4 w-4 text-primary"></i>
                     <p class="text-[0.65rem] font-bold text-primary uppercase tracking-[0.2em]">OneSignal Debug</p>
                 </div>
                 <div class="space-y-4">
                     <div class="flex justify-between items-center bg-white/40 p-2 rounded-xl">
                         <span class="text-[0.6rem] text-primary/60 uppercase font-bold">Permissions</span>
                         <p id="debug-permission" class="text-[0.7rem] font-bold text-primary">Checking...</p>
                     </div>
                     <div class="flex flex-col bg-white/40 p-2 rounded-xl">
                         <span class="text-[0.6rem] text-primary/60 uppercase font-bold mb-1">OneSignal ID</span>
                         <p id="debug-onesignal-id" class="text-[0.65rem] font-mono break-all">None</p>
                     </div>
                     <div class="flex flex-col bg-white/40 p-2 rounded-xl">
                         <span class="text-[0.6rem] text-primary/60 uppercase font-bold mb-1">Push Token</span>
                         <p id="debug-subscription-id" class="text-[0.65rem] font-mono break-all font-bold text-emerald-600">None</p>
                     </div>
                 </div>
                 <script>
                     function updateOneSignalDebug() {
                         const os = window.OneSignal || (window.plugins && window.plugins.OneSignal);
                         if (os) {
                             const pStatus = os.Notifications?.permission ? "Granted" : "Denied/Prompt";
                             document.getElementById('debug-permission').innerText = pStatus;
                             
                             if (os.User) {
                                document.getElementById('debug-onesignal-id').innerText = os.User.OneSignalId || 'Waiting...';
                                document.getElementById('debug-subscription-id').innerText = os.User.PushSubscription?.id || 'No Token Yet';
                             } else if (os.getDeviceState) {
                                os.getDeviceState(function(state) {
                                   document.getElementById('debug-onesignal-id').innerText = state.userId || 'V4 Pending...';
                                   document.getElementById('debug-subscription-id').innerText = state.pushToken || 'No V4 Token';
                                });
                             }
                         }
                     }
                     setInterval(updateOneSignalDebug, 1000);
                 </script>
             </div>
 
         </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal z-[100]" :class="showDeleteModal ? 'modal-open' : ''">
            <div class="modal-box rounded-3xl p-8 max-w-sm border border-base-300">
                <div class="text-center">
                    <div class="w-14 h-14 bg-error/10 rounded-2xl mx-auto flex items-center justify-center text-error mb-4">
                        <i data-lucide="alert-triangle" class="h-7 w-7"></i>
                    </div>
                    <h3 class="text-xl font-black text-primary mb-2">Hapus Karyawan?</h3>
                    <p class="text-xs font-medium text-base-content/50 leading-relaxed mb-6">
                        Tindakan ini tidak dapat dibatalkan. Seluruh data personel akan dihapus permanen.
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <button @click="showDeleteModal = false" class="btn btn-ghost rounded-xl text-xs font-black uppercase tracking-widest border border-base-300">Batal</button>
                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error w-full text-white rounded-xl text-xs font-black uppercase tracking-widest">Ya, Hapus Permanen</button>
                    </form>
                </div>
            </div>
            <div class="modal-backdrop bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        </div>
    </div>
</x-layouts.app>
