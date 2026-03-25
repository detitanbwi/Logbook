<x-layouts.app :title="'Daftar Karyawan Baru'" :breadcrumb="'Manajemen Karyawan / Tambah'">
    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-12 lg:mb-20 border-b border-outline-variant/10 pb-8 lg:pb-12">
            <div class="max-w-2xl">
                <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-primary mb-3 lg:mb-4 leading-tight">Daftar Karyawan Baru</h1>
                <p class="text-on-surface/40 font-medium leading-relaxed text-sm">Integrasi data dan kredensial personel baru ke dalam sistem perusahaan.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 lg:gap-6 w-full lg:w-auto">
                <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center px-8 py-4 lg:py-5 bg-surface text-primary border border-outline-variant/20 rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase hover:bg-primary/5 transition-all w-full sm:w-auto">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-10 lg:px-12 py-4 lg:py-5 primary-gradient text-white rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase shadow-2xl shadow-primary/30 active:scale-95 transition-all duration-300 w-full sm:w-auto">
                    <span class="material-symbols-outlined font-bold mr-3 text-sm">save_as</span>
                    <span>Simpan Data</span>
                </button>
            </div>
        </div>

        @include('employees._form', ['readonly' => false, 'supervisors' => $supervisors])
    </form>

</x-layouts.app>
