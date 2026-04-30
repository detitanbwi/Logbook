<x-layouts.app :title="$title" :active="$active">
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
        <!-- Dashboard Header (Smaller Banner) -->
        <div class="card bg-base-100 rounded-3xl p-6 border border-base-300 relative overflow-hidden flex flex-row items-center justify-between shadow-sm">
            <div class="flex items-center gap-6 relative z-10">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary shadow-inner">
                    <i data-lucide="clipboard-check" class="h-8 w-8"></i>
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-primary uppercase tracking-tight leading-none">Review Logbook</h1>
                    <p class="text-[0.6rem] font-bold text-base-content/30 uppercase tracking-[0.3em] mt-2">
                        Performa & Akuntabilitas Tim
                    </p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-3 px-6 py-3 bg-primary/5 rounded-xl border border-primary/10 relative z-10">
                <p class="text-xl font-black text-primary leading-none">{{ $logbooks->total() }}</p>
                <p class="text-[0.6rem] font-bold text-primary/60 uppercase tracking-widest">Entry</p>
            </div>

            <!-- Decorative blobs -->
            <div class="absolute -top-12 -left-12 w-48 h-48 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
        </div>

        <!-- Action Section -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <form action="{{ route('reviews.index') }}" method="GET" 
                  x-data 
                  @input.debounce.500ms="$el.submit()"
                  class="relative w-full md:w-80 group">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-base-content/40 group-focus-within:text-primary transition-colors"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama karyawan..." 
                       autocomplete="off"
                       class="input input-bordered h-14 w-full pl-12 pr-4 bg-base-100 border-2 border-base-300 rounded-xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold"
                />
            </form>

        </div>

        <!-- Table Section -->
        <div class="card bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full min-w-[1000px]">
                    <thead>
                        <tr class="bg-base-200 border-b border-base-300">
                            @php
                                $headers = [
                                    ['label' => 'Karyawan', 'sort' => 'nama'],
                                    ['label' => 'Tanggal Log', 'sort' => 'created_at'],
                                    ['label' => 'Durasi', 'sort' => null],
                                    ['label' => 'Status', 'sort' => 'status'],
                                    ['label' => 'Terakhir Diubah', 'sort' => 'terakhir_diubah'],
                                ];
                            @endphp
                            @foreach($headers as $h)
                                <th class="px-6 py-4 text-[0.65rem] font-black text-base-content/50 uppercase tracking-widest border-none">
                                    @if($h['sort'])
                                        <a href="{{ route('reviews.index', array_merge(request()->query(), ['sort' => $h['sort'], 'direction' => (request('sort') == $h['sort'] && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="flex items-center gap-2 hover:text-primary transition-colors">
                                            {{ $h['label'] }}
                                            @if(request('sort') == $h['sort'])
                                                <i data-lucide="{{ request('direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3"></i>
                                            @else
                                                <i data-lucide="chevrons-up-down" class="h-3 w-3 opacity-30"></i>
                                            @endif
                                        </a>
                                    @else
                                        {{ $h['label'] }}
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-6 py-4 text-[0.65rem] font-black text-base-content/50 uppercase tracking-widest border-none text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($logbooks as $log)
                        <tr class="hover:bg-primary/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="avatar border border-primary/10 rounded-lg overflow-hidden bg-base-200">
                                        <div class="w-10 h-10">
                                            @if($log->employee->foto)
                                                <img src="{{ asset('storage/' . $log->employee->foto) }}">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary text-xs font-black">
                                                    {{ substr($log->employee->nama, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-base-content">{{ $log->employee->nama }}</p>
                                        <p class="text-[0.6rem] font-bold text-base-content/30 uppercase tracking-widest leading-none mt-1">NPP: {{ $log->employee->npp }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-base-content/70">{{ $log->start_time->isoFormat('D MMM Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-primary">{{ $log->start_time->format('H:i') }} - {{ $log->end_time->format('H:i') }}</span>
                                    <span class="text-[0.6rem] font-black text-base-content/20 uppercase tracking-widest">{{ number_format($log->start_time->diffInMinutes($log->end_time) / 60, 1) }} Jam</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusConfig = [
                                        'pending' => ['bg' => 'bg-warning/20', 'text' => 'text-warning-content', 'label' => 'BUTUH REVIEW'],
                                        'approved' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'label' => 'DISETUJUI'],
                                        'rejected' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'label' => 'DITOLAK'],
                                    ];
                                    $s = $statusConfig[$log->status] ?? $statusConfig['pending'];
                                @endphp
                                <span class="badge {{ $s['bg'] }} {{ $s['text'] }} border-none font-black text-[0.55rem] tracking-widest py-3 px-3 rounded-md">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->latestReview)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-base-content/70">{{ $log->latestReview->reviewed_at->isoFormat('D MMM Y, HH:mm') }}</span>
                                        <span class="text-[0.6rem] font-black text-primary/40 uppercase tracking-widest">Reviewer: {{ $log->latestReview->reviewer->nama ?? 'Sistem' }}</span>
                                    </div>
                                @else
                                    <span class="text-[0.6rem] font-black text-base-content/20 uppercase tracking-[0.3em]">Belum Dinilai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('reviews.edit', $log->id) }}"
                                        class="btn {{ $log->status === 'pending' ? 'btn-primary shadow-md' : 'btn-outline border-base-300' }} btn-sm rounded-lg gap-2">
                                        <i data-lucide="{{ $log->status === 'pending' ? 'edit-3' : 'eye' }}" class="h-4 w-4"></i>
                                        <span class="text-[0.65rem] font-black uppercase tracking-wider">
                                            {{ $log->status === 'pending' ? 'Beri Nilai' : 'Ubah Nilai' }}
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-32 text-center">
                                <div class="flex flex-col items-center gap-4 opacity-20">
                                    <i data-lucide="clipboard-list" class="h-16 w-16"></i>
                                    <p class="text-sm font-bold tracking-[0.4em] uppercase">Data Logbook Tidak Ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logbooks->hasPages())
        <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 px-4 bg-base-200/50 p-6 rounded-2xl border border-base-200">
            <div class="text-[0.7rem] font-bold text-base-content/40 uppercase tracking-widest">
                Menampilkan <span class="text-primary">{{ $logbooks->firstItem() }}-{{ $logbooks->lastItem() }}</span> dari {{ $logbooks->total() }} Logbook
            </div>
            <div class="pagination-wrapper flex items-center">
                {{ $logbooks->links() }}
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
