<x-layouts.app :title="'Detail Karyawan'" :breadcrumb="'Manajemen Karyawan / Detail'" :active="'profile'">
    <div x-data="{ showDeleteModal: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 md:mb-20 border-b border-outline-variant/10 pb-8 md:pb-12 text-center md:text-left">
            <div class="max-w-2xl">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-primary mb-3 md:mb-4 leading-tight">Profil Detail Karyawan</h1>
                <p class="text-on-surface/40 font-medium leading-relaxed text-sm">Pratinjau kredensial personel yang telah terverifikasi dalam sistem.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 md:gap-6 w-full md:w-auto">
                 @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                 <a href="{{ route('employees.edit', $employee->id) }}" class="inline-flex items-center justify-center px-10 md:px-12 py-4 md:py-5 primary-gradient text-white rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase shadow-2xl shadow-primary/30 active:scale-95 transition-all duration-300 w-full sm:w-auto gap-3">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Edit Profil</span>
                 </a>
                 @endif

                 @if(auth()->user()->role === 'super_admin' && $employee->id !== auth()->id())
                 <button @click="showDeleteModal = true" class="inline-flex items-center justify-center px-10 md:px-12 py-4 md:py-5 bg-error/5 text-error border border-error/20 rounded-xl text-[0.65rem] font-bold tracking-[0.2em] uppercase hover:bg-error/10 transition-all w-full sm:w-auto gap-3">
                    <span class="material-symbols-outlined text-sm">delete_forever</span>
                    <span>Hapus Akun</span>
                 </button>
                 @endif

                 @if($employee->id === auth()->id())
                 <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-10 md:px-12 py-4 md:py-5 bg-surface text-on-surface/40 border border-outline-variant/10 rounded-xl text-[0.6rem] font-bold tracking-[0.2em] uppercase hover:bg-error/5 hover:text-error transition-all w-full gap-3">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        <span>Keluar Sesi</span>
                    </button>
                 </form>
                 @endif
            </div>
        </div>

        @include('employees._form', ['readonly' => true])

        <!-- Delete Confirmation Modal -->
        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-12 overflow-hidden" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-primary/40 backdrop-blur-3xl" @click="showDeleteModal = false"></div>

            <div class="relative bg-white w-full max-w-lg p-12 rounded-radius-md editorial-shadow animate-in zoom-in duration-300">
                <div class="mb-10 text-center">
                    <div class="w-16 h-16 bg-error/10 rounded-xl mx-auto flex items-center justify-center text-error mb-8">
                        <span class="material-symbols-outlined text-4xl">warning</span>
                    </div>
                    <h4 class="text-2xl font-bold text-primary mb-4 leading-tight">Hapus Data Karyawan?</h4>
                    <p class="text-sm font-medium text-on-surface/40 leading-relaxed px-8">Tindakan ini tidak dapat dibatalkan. Seluruh kredensial dan riwayat personel ini akan dihapus secara permanen dari sistem integrasi HR.</p>
                </div>

                <div class="flex gap-4 px-4">
                    <button @click="showDeleteModal = false" class="flex-1 py-5 bg-surface text-on-surface/40 rounded-xl text-[0.6rem] font-bold tracking-[0.2em] uppercase border border-outline-variant/10">Batal</button>

                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-5 bg-error text-white rounded-xl text-[0.6rem] font-bold tracking-[0.2em] uppercase shadow-xl shadow-error/20">Ya, Hapus Permanen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
