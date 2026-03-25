<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'HRIS Editorial' }}</title>

    <!-- Material Symbols Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .primary-gradient { background: linear-gradient(180deg, #002a58 0%, #004080 100%); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        html, body { max-width: 100%; overflow-x: hidden; }

        /* Smooth transitions for mobile */
        .page-enter { animation: fadeInUp 0.4s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-surface font-sans text-[#191c1e] antialiased min-h-screen">

    @if(auth()->user()->role === 'staff')
        {{-- MOBILE-FIRST STAFF LAYOUT --}}
        <div class="flex flex-col min-h-screen" style="{{ ($hideNav ?? false) ? '' : 'padding-bottom: calc(6rem + env(safe-area-inset-bottom));' }}">
            <!-- Global Staff Header -->
            <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-primary/5 px-6 flex items-center justify-between" style="padding-top: env(safe-area-inset-top); height: calc(4rem + env(safe-area-inset-top));">
                <div class="flex items-center gap-3">
                    @if($backUrl ?? false)
                        <a href="{{ $backUrl }}" class="w-10 h-10 -ml-2 rounded-full flex items-center justify-center text-primary/40 hover:text-primary active:scale-90 transition-all">
                            <span class="material-symbols-outlined font-black">arrow_back_ios_new</span>
                        </a>
                    @else
                        <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-primary/10 shadow-sm">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full primary-gradient flex items-center justify-center text-white text-[10px] font-black">{{ substr(auth()->user()->nama, 0, 1) }}</div>
                            @endif
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="text-[0.65rem] font-black text-primary/40 uppercase tracking-widest leading-none mb-0.5">Logbook Portal</span>
                        <h1 class="text-sm font-black text-primary uppercase tracking-tight">{{ $title }}</h1>
                    </div>
                </div>

                @if($backUrl ?? false)
                    <a href="{{ $backUrl }}" class="text-[0.65rem] font-black text-primary/30 uppercase tracking-widest hover:text-red-500 transition-colors">Batal</a>
                @else
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-10 h-10 -mr-2 rounded-full flex items-center justify-center text-primary/20 hover:text-red-500 active:scale-90 transition-all">
                            <span class="material-symbols-outlined font-black text-[1.4rem]">logout</span>
                        </button>
                    </form>
                @endif
            </header>

            <!-- Content Area -->
            <main class="flex-1 p-5 page-enter max-w-lg mx-auto w-full">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl flex items-center gap-3 text-[0.6rem] font-black uppercase tracking-wide">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Anchored Bottom Nav -->
            @if(!($hideNav ?? false))
            <nav class="fixed bottom-0 left-0 w-full z-50 px-4 pt-4 bg-white/95 backdrop-blur-2xl border-t border-primary/5 flex justify-around items-end rounded-t-[2.5rem] shadow-[0_-10px_40px_rgba(0,42,88,0.05)]" style="padding-bottom: calc(2rem + env(safe-area-inset-bottom));">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 group transition-all w-20 {{ ($active ?? '') === 'dashboard' ? 'text-primary' : 'text-primary/25' }}">
                    <span class="material-symbols-outlined text-[1.4rem]" style="font-variation-settings: 'FILL' {{ ($active ?? '') === 'dashboard' ? '1' : '0' }};">grid_view</span>
                    <span class="text-[0.55rem] font-black uppercase tracking-[0.1em] scale-90">Logs</span>
                </a>
                <a href="{{ route('logbooks.create') }}" class="flex flex-col items-center gap-1 group transition-all w-20 {{ ($active ?? '') === 'create' ? 'text-primary' : 'text-primary/25' }}">
                    <span class="material-symbols-outlined text-[1.4rem]" style="font-variation-settings: 'FILL' {{ ($active ?? '') === 'create' ? '1' : '0' }};">add_circle</span>
                    <span class="text-[0.55rem] font-black uppercase tracking-[0.1em] scale-90">Create</span>
                </a>
                @if(auth()->user()->subordinates()->exists())
                <a href="{{ route('reviews.index') }}" class="flex flex-col items-center gap-1 group transition-all w-20 {{ ($active ?? '') === 'reviews' ? 'text-primary' : 'text-primary/25' }}">
                    <span class="material-symbols-outlined text-[1.4rem]" style="font-variation-settings: 'FILL' {{ ($active ?? '') === 'reviews' ? '1' : '0' }};">rate_review</span>
                    <span class="text-[0.55rem] font-black uppercase tracking-[0.1em] scale-90">Reviews</span>
                </a>
                @endif
                <a href="{{ route('employees.show', auth()->id()) }}" class="flex flex-col items-center gap-1 group transition-all w-20 {{ ($active ?? '') === 'profile' ? 'text-primary' : 'text-primary/25' }}">
                    <span class="material-symbols-outlined text-[1.4rem]" style="font-variation-settings: 'FILL' {{ ($active ?? '') === 'profile' ? '1' : '0' }};">person</span>
                    <span class="text-[0.55rem] font-black uppercase tracking-[0.1em] scale-90">Profile</span>
                </a>
            </nav>
            @endif
        </div>

    @else
        {{-- DESKTOP-FIRST ADMIN LAYOUT --}}
        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
            <!-- Sidebar Backdrop -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-primary/20 backdrop-blur-sm z-40 lg:hidden"></div>

            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                   class="fixed lg:static inset-y-0 left-0 w-72 bg-white border-r border-primary/5 transition-transform duration-300 z-50 p-8 flex flex-col">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-10 h-10 primary-gradient rounded-xl flex items-center justify-center text-white">
                        <span class="material-symbols-outlined font-bold">water_drop</span>
                    </div>
                    <span class="text-xs font-black tracking-widest uppercase text-primary">HRIS Portal</span>
                </div>

                <nav class="flex-1 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-lg' : 'text-outline hover:bg-surface-container' }}">
                        <span class="material-symbols-outlined">grid_view</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Dashboard</span>
                    </a>
                    <a href="{{ route('employees.index') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl {{ request()->routeIs('employees.*') ? 'bg-primary text-white shadow-lg' : 'text-outline hover:bg-surface-container' }}">
                        <span class="material-symbols-outlined">badge</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Karyawan</span>
                    </a>
                    <a href="{{ route('reviews.index') }}" class="flex items-center gap-4 px-6 py-4 rounded-2xl {{ request()->routeIs('reviews.*') ? 'bg-primary text-white shadow-lg' : 'text-outline hover:bg-surface-container' }}">
                        <span class="material-symbols-outlined">rate_review</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Reviews</span>
                    </a>
                </nav>

                <div class="mt-auto pt-6 border-t border-primary/5">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-4 px-6 py-4 text-red-500/60 hover:bg-red-50 rounded-xl transition-all">
                            <span class="material-symbols-outlined">logout</span>
                            <span class="text-xs font-black uppercase tracking-widest">Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Admin Area -->
            <main class="flex-1 overflow-y-auto bg-surface p-8 lg:p-12">
                <header class="flex items-center justify-between mb-12 lg:hidden">
                    <button @click="sidebarOpen = true" class="w-12 h-12 flex items-center justify-center text-primary bg-white rounded-xl shadow-sm">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-sm font-black text-primary uppercase">{{ $title }}</h1>
                </header>

                <div class="max-w-6xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    @endif

</body>
</html>
