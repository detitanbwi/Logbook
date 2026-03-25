<x-layouts.app :title="'Dashboard Overview'" :breadcrumb="'Dashboard'">
    <div class="max-w-6xl animate-in fade-in slide-in-from-bottom duration-1000">
        <div class="mb-12 md:mb-20">
            <span class="inline-block px-4 py-2 bg-primary text-white rounded-lg text-[0.6rem] md:text-[0.65rem] font-bold tracking-[0.25em] uppercase mb-8 md:mb-10 shadow-lg shadow-primary/20">PORTAL SDM INTERNAL</span>
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-extrabold tracking-tighter text-primary mb-6 md:mb-8 leading-tight">Selamat datang Kembali,<br>{{ auth()->user()->nama }}.</h1>
            <p class="text-lg md:text-xl font-medium text-on-surface/40 max-w-2xl leading-relaxed">Sistem Manajemen Sumber Daya Manusia terintegrasi untuk mendukung operasional perusahaan.</p>
        </div>

        <div class="mt-12 md:mt-20">
            <div class="bg-white rounded-xl editorial-shadow p-8 md:p-12 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 primary-gradient opacity-0 blur-3xl rounded-full translate-x-1/2 -translate-y-1/2 transition-opacity duration-1000 group-hover:opacity-10"></div>
                
                <h4 class="text-xl md:text-2xl font-bold text-primary mb-4 md:mb-6">Menu Navigasi</h4>
                <p class="text-sm md:text-base text-on-surface/50 leading-relaxed mb-8 md:mb-10 max-w-xl">
                    Kelola data personel dan pantau indikator kinerja utama secara terpusat untuk efisiensi tim.
                </p>

                @if(auth()->user()->role !== 'staff')
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center gap-3 px-8 py-4 primary-gradient text-white rounded-xl text-[0.6rem] md:text-[0.65rem] font-bold tracking-widest uppercase shadow-xl shadow-primary/20 hover:scale-105 transition-all w-full sm:w-auto">
                        <span class="material-symbols-outlined text-sm">view_list</span> Daftar Karyawan
                    </a>
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-surface text-primary border border-outline-variant/20 rounded-xl text-[0.6rem] md:text-[0.65rem] font-bold tracking-widest uppercase hover:bg-primary/5 transition-all w-full sm:w-auto">
                        <span class="material-symbols-outlined text-sm">person_add</span> Tambah Karyawan
                    </a>
                </div>
                @else
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('employees.show', auth()->id()) }}" class="inline-flex items-center justify-center gap-3 px-8 py-4 primary-gradient text-white rounded-xl text-[0.6rem] lg:text-[0.65rem] font-bold tracking-widest uppercase shadow-xl shadow-primary/20 hover:scale-105 transition-all w-full sm:w-auto">
                        <span class="material-symbols-outlined text-sm">account_circle</span> Profil Saya
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
