<x-layouts.app :title="'Daftar Karyawan Baru'">
    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-12 border-b border-base-300 pb-12">
            <div class="text-center lg:text-left">
                <p class="text-[0.65rem] font-bold text-primary/40 uppercase tracking-[0.4em] mb-3 leading-none">Management Console</p>
                <h1 class="text-3xl lg:text-5xl font-black tracking-tight text-primary leading-none">Entry Personel Baru</h1>
                <p class="text-base-content/50 font-medium leading-relaxed text-sm mt-4">Integrasi data dan kredensial personel baru ke dalam ekosistem integritas HRIS.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                <a href="{{ route('employees.index') }}" class="btn btn-ghost bg-base-200 hover:bg-base-300 rounded-2xl px-10 text-xs font-black uppercase tracking-widest h-16 w-full sm:w-auto">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary rounded-2xl gap-3 px-12 shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all text-xs font-black uppercase tracking-widest h-16 w-full sm:w-auto">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan Data
                </button>
            </div>
        </div>

        <div class="card bg-base-100 rounded-[2.5rem] shadow-sm border border-base-300 overflow-hidden">
            <div class="card-body p-8 lg:p-12">
                @include('employees._form', ['readonly' => false, 'supervisors' => $supervisors])
            </div>
        </div>
    </form>
</x-layouts.app>
