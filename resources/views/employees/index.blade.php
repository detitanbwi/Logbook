<x-layouts.app :title="'Data Karyawan'">
    <div class="animate-in fade-in slide-in-from-bottom-8 duration-700">
        <!-- Action Section -->
        @if(auth()->user()->role !== 'staff')
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                <a href="{{ route('employees.create') }}" class="btn btn-primary rounded-xl h-14 px-8 gap-3 shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all w-full md:w-auto">
                    <i data-lucide="user-plus" class="h-5 w-5"></i>
                    <span class="text-sm font-bold uppercase tracking-wider">Tambah Karyawan</span>
                </a>
                
                <form action="{{ route('employees.index') }}" method="GET" 
                      x-data 
                      @input.debounce.500ms="$el.submit()"
                      class="relative w-full md:w-80 group">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-base-content/40 group-focus-within:text-primary transition-colors"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama atau NPP..." 
                           autocomplete="off"
                           class="input input-bordered h-14 w-full pl-12 pr-4 bg-base-100 border-2 border-base-300 rounded-xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold"
                    />
                    @if(request('search'))
                        <a href="{{ route('employees.index') }}" class="absolute right-4 top-1/2 -translate-y-1/2 hover:text-error transition-colors">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-3 bg-base-200 p-2 rounded-xl border border-base-300">
                    <span class="text-[0.7rem] font-bold uppercase tracking-widest text-base-content/40 pl-2">Show</span>
                    <div class="flex gap-1">
                        @foreach([10, 25, 50, 100] as $size)
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['per_page' => $size, 'page' => 1])) }}" 
                               class="btn btn-sm btn-square {{ request('per_page', 25) == $size ? 'btn-primary' : 'btn-ghost' }} font-bold text-xs rounded-lg w-10">
                                {{ $size }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="card bg-base-100 rounded-2xl shadow-md border-2 border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full min-w-[1000px]">
                    <thead>
                        <tr class="bg-base-200 border-b-2 border-base-300">
                            <th class="px-6 py-5 text-sm font-bold text-base-content uppercase tracking-wider">Personel</th>
                            <th class="px-6 py-5 text-sm font-bold text-base-content uppercase tracking-wider text-center">NPP</th>
                            <th class="px-6 py-5 text-sm font-bold text-base-content uppercase tracking-wider text-center">Role</th>
                            <th class="px-6 py-5 text-sm font-bold text-base-content uppercase tracking-wider">Atasan Langsung</th>
                            <th class="px-6 py-5 text-sm font-bold text-base-content uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-base-200">
                        @forelse($employees as $employee)
                        <tr class="hover:bg-primary/5 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="avatar ring-2 ring-primary/20 rounded-xl overflow-hidden shadow-sm">
                                        <div class="w-14 h-14 bg-base-200 flex items-center justify-center">
                                            @if($employee->foto)
                                                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Profile" />
                                            @else
                                                <i data-lucide="user" class="h-7 w-7 text-base-content/30"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-base font-bold text-base-content">{{ $employee->nama }}</p>
                                        <p class="text-xs font-bold text-primary/60 tracking-widest uppercase mt-0.5">ID: {{ $employee->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-bold text-base-content/80 bg-base-200 px-3 py-1.5 rounded-lg border border-base-300 font-mono tracking-wider">{{ $employee->npp }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @php
                                    $roleLabels = [
                                        'super_admin' => 'SUPER ADMIN',
                                        'admin' => 'ADMIN',
                                        'staff' => 'STAFF'
                                    ];
                                    $roleColors = [
                                        'super_admin' => 'bg-error text-error-content',
                                        'admin' => 'bg-primary text-primary-content',
                                        'staff' => 'bg-neutral text-neutral-content'
                                    ];
                                @endphp
                                <span class="badge {{ $roleColors[$employee->role] ?? 'badge-ghost' }} px-4 py-3 font-black text-[0.7rem] tracking-widest border-none rounded-md">
                                    {{ $roleLabels[$employee->role] ?? $employee->role }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                @if($employee->supervisor)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-base-content/80">{{ $employee->supervisor->nama }}</span>
                                        <span class="text-[0.65rem] font-black text-primary/40 uppercase tracking-widest mt-0.5">DIRECT SUPERVISOR</span>
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-base-content/20 uppercase tracking-widest">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('employees.show', $employee->id) }}" 
                                       class="btn btn-outline btn-primary btn-sm rounded-lg gap-2" title="Lihat Detail">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                        <span class="hidden xl:inline text-[0.6rem] font-black uppercase">Detail</span>
                                    </a>
                                    @if(auth()->user()->role !== 'staff' && (auth()->user()->role === 'super_admin' || $employee->role === 'staff'))
                                    <a href="{{ route('employees.edit', $employee->id) }}" 
                                       class="btn btn-neutral btn-sm rounded-lg gap-2" title="Edit Data">
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                        <span class="hidden xl:inline text-[0.6rem] font-black uppercase">Edit</span>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-32 text-center text-base-content/30">
                                <div class="flex flex-col items-center gap-4">
                                    <i data-lucide="users" class="h-16 w-16 opacity-10"></i>
                                    <p class="text-sm font-bold tracking-widest uppercase">Data Karyawan Tidak Ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($employees->hasPages())
        <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 px-4 bg-base-200/50 p-6 rounded-2xl border-2 border-base-200">
            <div class="text-sm font-bold text-base-content/60 tracking-wide">
                Menampilkan <span class="text-primary">{{ $employees->firstItem() }}</span> sampai <span class="text-primary">{{ $employees->lastItem() }}</span> dari total <span class="text-primary">{{ $employees->total() }}</span> karyawan
            </div>
            
            <div class="pagination-wrapper flex items-center scale-110 origin-right">
                {{ $employees->links() }}
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
