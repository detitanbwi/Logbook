<x-layouts.app :title="'Dashboard Overview'" :active="'dashboard'">
    <div class="max-w-7xl mx-auto space-y-16 animate-in fade-in slide-in-from-bottom-8 duration-1000">
        <!-- Hero Section -->
        <div class="card bg-base-100 rounded-[3rem] p-12 md:p-20 border border-base-300 relative overflow-hidden flex flex-col items-center text-center shadow-sm">
            <div class="badge badge-primary border-none py-4 px-8 text-[0.65rem] font-black tracking-[0.4em] uppercase mb-10 relative z-10 shadow-lg shadow-primary/20">
                PRO_SDM INTEGRATED
            </div>
            
            <h1 class="text-4xl md:text-7xl font-black tracking-tight text-primary mb-8 leading-[1.1] relative z-10">
                Selamat Datang Di<br>Logbook Workspace.
            </h1>
            
            <p class="text-lg md:text-xl font-bold text-base-content/40 max-w-3xl leading-relaxed relative z-10">
                Halo, <span class="text-primary font-black uppercase tracking-tight">{{ auth()->user()->nama }}</span>. Pantau indikator kinerja, kelola logbook harian, dan optimalkan produktivitas tim Anda dalam satu dasbor terpadu.
            </p>

            <div class="mt-16 flex flex-col sm:flex-row gap-5 relative z-10 w-full sm:w-auto">
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
            </div>

            <!-- Dynamic Glass Decorations -->
            <div class="absolute -top-12 -left-12 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-16 -right-16 w-96 h-96 bg-primary/5 rounded-full blur-[80px]"></div>
        </div>

        <!-- Stats Overview Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Total Karyawan -->
            <div class="card bg-base-100 p-8 rounded-[2.5rem] border border-base-300 shadow-sm transition-all hover:shadow-xl hover:scale-[1.02] flex flex-col items-center">
                <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 mb-6 font-black text-2xl">
                    <i data-lucide="users" class="h-8 w-8"></i>
                </div>
                <div class="text-4xl font-black text-primary">{{ $totalEmployees }}</div>
                <h4 class="text-[0.65rem] font-black text-base-content/40 uppercase tracking-[0.2em] mt-2">Total Karyawan</h4>
            </div>

            <!-- Total Admin -->
            <div class="card bg-base-100 p-8 rounded-[2.5rem] border border-base-300 shadow-sm transition-all hover:shadow-xl hover:scale-[1.02] flex flex-col items-center">
                <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-500 mb-6">
                    <i data-lucide="shield-check" class="h-8 w-8"></i>
                </div>
                <div class="text-4xl font-black text-primary">{{ $totalAdmins }}</div>
                <h4 class="text-[0.65rem] font-black text-base-content/40 uppercase tracking-[0.2em] mt-2">Total Pengurus</h4>
            </div>

            <!-- KPI Aktif -->
            <div class="card bg-base-100 p-8 rounded-[2.5rem] border border-base-300 shadow-sm transition-all hover:shadow-xl hover:scale-[1.02] flex flex-col items-center">
                <div class="w-16 h-16 bg-green-500/10 rounded-2xl flex items-center justify-center text-green-500 mb-6">
                    <i data-lucide="target" class="h-8 w-8"></i>
                </div>
                <div class="text-4xl font-black text-primary">{{ $totalKpis }}</div>
                <h4 class="text-[0.65rem] font-black text-base-content/40 uppercase tracking-[0.2em] mt-2">Master KPI</h4>
            </div>

            <!-- Pending Reviews -->
            <div class="card bg-base-100 p-8 rounded-[2.5rem] border border-base-300 shadow-sm transition-all hover:shadow-xl hover:scale-[1.02] flex flex-col items-center">
                <div class="w-16 h-16 bg-red-500/10 rounded-2xl flex items-center justify-center text-red-500 mb-6">
                    <i data-lucide="bell" class="h-8 w-8"></i>
                </div>
                <div class="text-4xl font-black text-primary">{{ $pendingReviews }}</div>
                <h4 class="text-[0.65rem] font-black text-base-content/40 uppercase tracking-[0.2em] mt-2">Antrean Review</h4>
            </div>
        </div>
    </div>
</x-layouts.app>
