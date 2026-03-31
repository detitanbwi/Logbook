<x-layouts.app :title="'Dashboard Overview'" :active="'dashboard'">
    <div class="max-w-7xl mx-auto space-y-12 py-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Dashboard Header -->
        <div class="flex flex-col gap-1 mb-8">
            <h1 class="text-3xl font-black text-primary tracking-tight">Dashboard Overview</h1>
            <p class="text-[0.6rem] font-bold text-base-content/30 uppercase tracking-[0.25em]">Real-time statistics & activity monitor</p>
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
