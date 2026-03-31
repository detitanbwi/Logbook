<x-layouts.app :title="'Detail Karyawan'" :active="'profile'">
    <div x-data="{ showDeleteModal: false }">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 pb-8 border-b border-base-300">
            <div class="max-w-2xl text-center md:text-left">
                <p class="text-[0.65rem] font-bold text-primary/60 uppercase tracking-[0.2em] mb-2">Personnel Metadata</p>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-primary leading-tight">Profil Detail Karyawan</h1>
                <p class="text-base-content/50 font-medium leading-relaxed text-sm mt-2">Pratinjau kredensial personel yang telah terverifikasi dalam sistem integritas HRIS.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                 @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                 <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary rounded-2xl gap-3 px-8 shadow-lg shadow-primary/20 hover:scale-[1.03] transition-all w-full sm:w-auto">
                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                    <span class="text-xs font-black uppercase tracking-widest">Edit Profil</span>
                 </a>
                 @endif

                 @if(auth()->user()->role === 'super_admin' && $employee->id !== auth()->id())
                 <button @click="showDeleteModal = true" class="btn btn-ghost text-error bg-error/5 hover:bg-error/10 rounded-2xl gap-3 px-8 w-full sm:w-auto">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    <span class="text-xs font-black uppercase tracking-widest">Hapus Akun</span>
                 </button>
                 @endif

                 @if($employee->id === auth()->id())
                 <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-base-content/40 hover:text-error hover:bg-error/5 border border-base-300 rounded-2xl gap-3 px-8 w-full">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Keluar Sesi</span>
                    </button>
                 </form>
                 @endif
            </div>
        </div>

        <!-- Form Section -->
        <div class="card bg-base-100 rounded-3xl shadow-sm border border-base-300 overflow-hidden">
            <div class="card-body p-6 md:p-10">
                @include('employees._form', ['readonly' => true])
            </div>
        </div>

        <!-- Delete Confirmation Modal (DaisyUI style) -->
        <div class="modal z-[100]" :class="showDeleteModal ? 'modal-open' : ''">
            <div class="modal-box rounded-3xl p-10 max-w-md border border-base-300 shadow-2xl">
                <div class="text-center">
                    <div class="w-16 h-16 bg-error/10 rounded-2xl mx-auto flex items-center justify-center text-error mb-6">
                        <i data-lucide="alert-triangle" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-2xl font-black text-primary mb-3">Hapus Karyawan?</h3>
                    <p class="text-sm font-medium text-base-content/50 leading-relaxed px-4 mb-8">
                        Tindakan ini tidak dapat dibatalkan. Seluruh data dan riwayat personel ini akan dihapus secara permanen.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="showDeleteModal = false" class="btn btn-ghost flex-1 rounded-2xl text-[0.7rem] font-black uppercase tracking-widest border border-base-300">Batal</button>
                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error w-full text-white rounded-2xl text-[0.7rem] font-black uppercase tracking-widest shadow-lg shadow-error/20">Ya, Hapus</button>
                    </form>
                </div>
            </div>
            <div class="modal-backdrop bg-primary/40 backdrop-blur-md" @click="showDeleteModal = false"></div>
        </div>
    </div>
</x-layouts.app>
