<x-layouts.app :title="'Dashboard Overview'" :active="'dashboard'">
    <div class="max-w-7xl mx-auto space-y-16 animate-in fade-in slide-in-from-bottom-8 duration-1000">
        <!-- Hero Section -->
        <div class="card bg-base-100 rounded-[3rem] p-12 md:p-20 border border-base-300 relative overflow-hidden flex flex-col items-center text-center shadow-sm">
            <div class="badge badge-primary border-none py-4 px-8 text-[0.65rem] font-black tracking-[0.4em] uppercase mb-10 relative z-10 shadow-lg shadow-primary/20">
                PRO_SDM INTEGRATED
            </div>
            
            <h1 class="text-4xl md:text-7xl font-black tracking-tight text-primary mb-8 leading-[1.1] relative z-10">
                Selamat Datang Di<br>HRIS Workspace.
            </h1>
            
            <p class="text-lg md:text-xl font-bold text-base-content/40 max-w-3xl leading-relaxed relative z-10">
                Halo, <span class="text-primary font-black uppercase tracking-tight">{{ auth()->user()->nama }}</span>. Pantau indikator kinerja, kelola logbook harian, dan optimalkan produktivitas tim Anda dalam satu dasbor terpadu.
            </p>

            <div class="mt-16 flex flex-col sm:flex-row gap-5 relative z-10 w-full sm:w-auto">
                @if(auth()->user()->role !== 'staff')
                    <a href="{{ route('employees.index') }}" class="btn btn-primary rounded-2xl h-16 px-12 gap-4 shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest">
                        <i data-lucide="users" class="h-5 w-5"></i>
                        Manajemen Karyawan
                    </a>
                    <a href="{{ route('reviews.index') }}" class="btn btn-ghost bg-base-200 border-base-300 hover:bg-base-300 rounded-2xl h-16 px-10 gap-4 text-xs font-black uppercase tracking-widest">
                        <i data-lucide="clipboard-check" class="h-5 w-5"></i>
                        Review Logbook
                    </a>
                    <a href="{{ route('kpis.index') }}" class="btn btn-ghost bg-base-200 border-base-300 hover:bg-base-300 rounded-2xl h-16 px-10 gap-4 text-xs font-black uppercase tracking-widest">
                        <i data-lucide="target" class="h-5 w-5"></i>
                        Master KPI
                    </a>
                @else
                    <a href="{{ route('logbooks.create') }}" class="btn btn-primary rounded-2xl h-16 px-12 gap-4 shadow-2xl shadow-primary/30 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest">
                        <i data-lucide="plus-circle" class="h-5 w-5"></i>
                        Input Logbook Baru
                    </a>
                    <a href="{{ route('employees.show', auth()->id()) }}" class="btn btn-ghost bg-base-200 border-base-300 hover:bg-base-300 rounded-2xl h-16 px-10 gap-4 text-xs font-black uppercase tracking-widest">
                        <i data-lucide="user-cog" class="h-5 w-5"></i>
                        Pengaturan Profil
                    </a>
                @endif
            </div>

            <!-- Dynamic Glass Decorations -->
            <div class="absolute -top-12 -left-12 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-16 -right-16 w-96 h-96 bg-primary/5 rounded-full blur-[80px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full opacity-5 pointer-events-none">
                <i data-lucide="shield-check" class="w-full h-full text-primary opacity-10"></i>
            </div>
        </div>

        <!-- Quick Access Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card bg-base-100 p-10 rounded-[2.5rem] border border-base-300 shadow-sm hover:border-primary/30 transition-all group overflow-hidden">
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6 transition-transform group-hover:scale-110">
                    <i data-lucide="activity" class="h-6 w-6"></i>
                </div>
                <h4 class="text-xl font-black text-primary uppercase tracking-tight mb-3">Monitoring KPI</h4>
                <p class="text-sm font-bold text-base-content/40 mb-8 leading-relaxed">Pantau perkembangan target kinerja tim secara real-time dan akurat.</p>
                <div class="w-full h-1 bg-base-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary w-1/3 group-hover:w-full transition-all duration-1000"></div>
                </div>
            </div>

            <div class="card bg-base-100 p-10 rounded-[2.5rem] border border-base-300 shadow-sm hover:border-primary/30 transition-all group overflow-hidden">
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6 transition-transform group-hover:scale-110">
                    <i data-lucide="layers" class="h-6 w-6"></i>
                </div>
                <h4 class="text-xl font-black text-primary uppercase tracking-tight mb-3">Arsip Terpadu</h4>
                <p class="text-sm font-bold text-base-content/40 mb-8 leading-relaxed">Akses seluruh riwayat pelaporan dan dokumentasi bukti kerja dalam satu sistem.</p>
                <div class="w-full h-1 bg-base-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary w-1/2 group-hover:w-full transition-all duration-1000"></div>
                </div>
            </div>

            <div class="card bg-base-100 p-10 rounded-[2.5rem] border border-base-300 shadow-sm hover:border-primary/30 transition-all group overflow-hidden">
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6 transition-transform group-hover:scale-110">
                    <i data-lucide="check-circle" class="h-6 w-6"></i>
                </div>
                <h4 class="text-xl font-black text-primary uppercase tracking-tight mb-3">Verifikasi Valid</h4>
                <p class="text-sm font-bold text-base-content/40 mb-8 leading-relaxed">Sistem autentikasi berlapis memastikan setiap laporan tervalidasi dengan benar.</p>
                <div class="w-full h-1 bg-base-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary w-2/3 group-hover:w-full transition-all duration-1000"></div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
