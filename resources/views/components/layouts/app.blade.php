<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'HRIS Logbook' }}</title>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="icon" type="image/png" href="{{ asset('images/branding/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- OneSignal Bridge File (Virtual file, DO NOT DELETE) -->
    <script src="cordova.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        html,
        body {
            max-width: 100%;
        }

        /* Custom animation for page content */
        .page-transition {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-base-200 text-base-content antialiased min-h-screen"
    style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="drawer lg:drawer-open min-h-screen" x-data="{ sidebarOpen: false }">
        <input id="app-drawer" type="checkbox" class="drawer-toggle" :checked="sidebarOpen" />

        <div class="drawer-content flex flex-col">
            <!-- Top Navbar -->
            <header
                class="sticky top-0 z-40 flex h-16 w-full justify-center bg-base-100/80 backdrop-blur-md border-b border-base-300">
                <div class="navbar w-full max-w-[1240px] px-4 md:px-6">
                    <div class="flex-none lg:hidden">
                        <label for="app-drawer" aria-label="open sidebar" class="btn btn-square btn-ghost"
                            @click="sidebarOpen = true">
                            <i data-lucide="menu" class="h-6 w-6 text-primary"></i>
                        </label>
                    </div>

                    <div class="flex-1 px-2 mx-2 flex items-center gap-3">
                        <img src="{{ asset('images/branding/logo.png') }}" class="h-8 w-auto object-contain" alt="Logo">
                        <h1 class="text-base font-bold text-base-content lg:text-lg">{{ $title ?? 'HRIS' }}</h1>
                    </div>

                    <div class="flex-none items-center gap-2">
                        @if(auth()->user()->role === 'staff' && !($backUrl ?? false))
                            <div class="hidden md:flex flex-col items-end mr-3">
                                <span
                                    class="text-[0.65rem] font-bold text-base-content/40 uppercase tracking-widest leading-none">Logbook
                                    Portal</span>
                                <span class="text-xs font-bold text-primary">{{ auth()->user()->nama }}</span>
                            </div>
                        @endif

                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button"
                                class="btn btn-ghost btn-circle avatar border-2 border-primary/10 shadow-sm focus:border-primary/30">
                                <div
                                    class="w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                    @if(auth()->user()->foto)
                                        <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Avatar" />
                                    @else
                                        <span class="text-xs font-black">{{ substr(auth()->user()->nama, 0, 1) }}</span>
                                    @endif
                                </div>
                            </div>
                            <ul tabindex="0"
                                class="menu dropdown-content bg-base-100 rounded-2xl z-40 w-52 p-2 shadow-xl border border-base-300 mt-4">
                                <li>
                                    <a href="{{ route('employees.show', auth()->id()) }}"
                                        class="flex items-center gap-3 py-3 px-4 hover:bg-base-200 rounded-xl transition-all">
                                        <i data-lucide="user-circle-2" class="h-4 w-4 text-base-content/60"></i>
                                        <span class="text-xs font-bold uppercase tracking-widest">Profil Saya</span>
                                    </a>
                                </li>
                                <li class="border-t border-base-200 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}" onsubmit="return handleLogout(event)">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 py-3 px-4 text-error hover:bg-error/10 rounded-xl transition-all">
                                            <i data-lucide="log-out" class="h-4 w-4"></i>
                                            <span class="text-xs font-bold uppercase tracking-widest">Keluar</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="mx-auto w-full max-w-[1240px] p-4 md:p-6 lg:p-8 flex-1">
                @if(session('success'))
                    <div
                        class="alert alert-success bg-green-50 border-green-200 text-green-700 rounded-2xl shadow-sm mb-6 flex gap-3 text-xs font-bold uppercase tracking-wide">
                        <i data-lucide="check-circle" class="h-5 w-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div
                        class="alert alert-error bg-red-50 border-red-200 text-red-700 rounded-2xl shadow-sm mb-6 flex gap-3 text-xs font-bold uppercase tracking-wide">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error bg-red-50 border-red-200 text-red-700 rounded-2xl shadow-sm mb-6 flex flex-col items-start gap-2 text-xs font-bold tracking-wide">
                        <div class="flex items-center gap-3 uppercase">
                            <i data-lucide="alert-circle" class="h-5 w-5"></i>
                            <span>Mohon periksa kembali form Anda:</span>
                        </div>
                        <ul class="list-disc list-inside ml-8 text-[0.65rem]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($flat ?? false)
                    {{ $slot }}
                @else
                    <div
                        class="card bg-base-100 rounded-2xl border border-base-300 shadow-sm page-transition min-h-[calc(100vh-12rem)] md:min-h-0">
                        <div class="card-body p-4 md:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                @endif

                <!-- Footer / Extra space -->
                <div class="h-12 md:h-0"></div>
            </main>
        </div>

        <div class="drawer-side z-50">
            <label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"
                @click="sidebarOpen = false"></label>
            <aside
                class="flex h-screen sticky top-0 w-80 flex-col border-r border-base-300 bg-base-100 p-4 text-base-content shadow-sm overflow-hidden">
                <!-- Sidebar Header -->
                <div class="mb-6 rounded-2xl border border-base-300 bg-base-100 p-4 shadow-sm">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-base-300 p-1.5 shadow-sm">
                            <img src="{{ asset('images/branding/logo.png') }}" class="h-full w-full object-contain" alt="Logo">
                        </div>
                        <div>
                            <p class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-base-content/50">Workspace</p>
                            <p class="text-lg font-black tracking-tight text-primary">Logbook Portal</p>
                        </div>
                    </div>
                    <p class="text-[0.6rem] text-base-content/60 uppercase tracking-widest font-bold">
                        Role: <span class="text-primary">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    </p>
                </div>

                <!-- Navigation Section -->
                <p class="mb-3 px-3 text-[0.65rem] font-bold uppercase tracking-[0.2em] text-base-content/40">Main Menu
                </p>
                <ul class="menu w-full gap-1.5 p-0">
                    {{-- Common Links --}}
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('dashboard') ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                            <i data-lucide="layout-grid" class="h-4 w-4"></i>
                            <span class="text-xs uppercase tracking-widest">Dashboard</span>
                        </a>
                    </li>

                    @if(auth()->user()->role !== 'staff')
                        {{-- Admin & Super Admin Specific --}}
                        <li>
                            <a href="{{ route('employees.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('employees.*') ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                                <i data-lucide="users" class="h-4 w-4"></i>
                                <span class="text-xs uppercase tracking-widest">Karyawan</span>
                            </a>
                        </li>
                    @else
                        {{-- Staff Specific --}}
                        <li>
                            <a href="{{ route('logbooks.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('logbooks.*') ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                                <i data-lucide="book-open" class="h-4 w-4"></i>
                                <span class="text-xs uppercase tracking-widest">Logbook</span>
                            </a>
                        </li>
                    @endif

                    {{-- Review Section (Visible to Admin/Super Admin or anyone with Subordinates) --}}
                    @if(auth()->user()->role !== 'staff' || auth()->user()->subordinates()->exists())
                        <li>
                            <a href="{{ route('reviews.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('reviews.*') ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                                <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                                <span class="text-xs uppercase tracking-widest">Review Log</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role !== 'staff')
                        <li>
                            <a href="{{ route('kpis.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('kpis.*') ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                                <i data-lucide="target" class="h-4 w-4"></i>
                                <span class="text-xs uppercase tracking-widest">KPI Target</span>
                            </a>
                        </li>
                    @endif

                    {{-- Common Profile Link --}}
                    <li>
                        <a href="{{ route('employees.show', auth()->id()) }}"
                            class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-3 text-sm transition-all duration-200 hover:border-base-300 hover:bg-base-200 {{ request()->routeIs('employees.show') && request()->route('employee') == auth()->id() ? 'border-primary/20 bg-primary/10 font-bold text-primary shadow-sm' : 'text-base-content/70' }}">
                            <i data-lucide="user-circle" class="h-4 w-4"></i>
                            <span class="text-xs uppercase tracking-widest">Profil Saya</span>
                        </a>
                    </li>
                </ul>

                <!-- Sidebar Footer -->
                <div class="mt-auto pt-6 border-t border-base-300">
                    <form action="{{ route('logout') }}" method="POST" onsubmit="return handleLogout(event)">
                        @csrf
                        <button type="submit"
                            class="group w-full flex items-center gap-3 px-4 py-3 text-error/70 hover:bg-error/10 rounded-xl transition-all">
                            <i data-lucide="log-out" class="h-4 w-4 group-hover:scale-110 transition-transform"></i>
                            <span class="text-xs font-black uppercase tracking-widest">Logout</span>
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>


    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed bottom-20 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-2 w-full max-w-[90%] pointer-events-none"></div>

    <!-- Initialize Lucide Icons & Global Helpers -->
    <script>
        lucide.createIcons();

        // OneSignal Initialization
        document.addEventListener("deviceready", OneSignalInit, false);
        function OneSignalInit() {
            console.log("OneSignal: Device Ready triggered");
            
            let os = window.OneSignal || (window.plugins && window.plugins.OneSignal);
            
            if (os) {
                console.log("OneSignal: SDK Found", os);
                
                // Nesting Check (Common in Module implementations)
                if (os.default) {
                    console.log("OneSignal: Found nested default object", os.default);
                    // Merge properties if they are missing at top level
                    for(let k in os.default) { if(!os[k]) os[k] = os.default[k]; }
                }
                
                if (os.OneSignalPlugin) {
                    console.log("OneSignal: Found OneSignalPlugin object", os.OneSignalPlugin);
                    // Merge properties if they are missing at top level
                    for(let k in os.OneSignalPlugin) { if(!os[k]) os[k] = os.OneSignalPlugin[k]; }
                }

                console.log("OneSignal: Scanned Keys:", Object.keys(os));
                
                // window.showAlert("OneSignal Ready", "success");

                try {
                    const appId = "{{ config('services.onesignal.app_id') }}";
                    
                    // The "Golden" search for the init function
                    let initFunc = null;
                    let target = os;

                    if (typeof os.initialize === 'function') { initFunc = os.initialize; }
                    else if (os.default && typeof os.default.initialize === 'function') { initFunc = os.default.initialize; target = os.default; }
                    else if (os.OneSignalPlugin && typeof os.OneSignalPlugin.initialize === 'function') { initFunc = os.OneSignalPlugin.initialize; target = os.OneSignalPlugin; }
                    else if (typeof os.setAppId === 'function') { initFunc = os.setAppId; }
                    else if (typeof os.initWithContext === 'function') { initFunc = os.initWithContext; }
                    
                    if (initFunc) {
                        console.log("OneSignal: Executing initialization...");
                        initFunc.call(target, appId);
                        console.log("OneSignal: Initialization command sent.");
                    } else {
                        console.error("OneSignal: TRULY no initialization function found!", os);
                    }

                    @auth
                        const userId = "{{ auth()->id() }}";
                        console.log("OneSignal: Target External ID -> " + userId);
                        
                        const syncExternalId = (attempts = 0) => {
                            if (attempts > 15) {
                                console.error("OneSignal: External ID sync timeout.");
                                return;
                            }

                            try {
                                // Find the method in any possible location
                                let loginFunc = null;
                                let loginTarget = os;

                                // Check list of targets
                                const targets = [os, os.OneSignalPlugin, os.default, window.plugins ? window.plugins.OneSignal : null];
                                
                                for (const t of targets) {
                                    if (!t) continue;
                                    if (typeof t.login === 'function') {
                                        loginFunc = t.login;
                                        loginTarget = t;
                                        break;
                                    } else if (typeof t.setExternalUserId === 'function') {
                                        loginFunc = t.setExternalUserId;
                                        loginTarget = t;
                                        break;
                                    }
                                }

                                if (loginFunc) {
                                    loginFunc.call(loginTarget, userId.toString());
                                    console.log("OneSignal: Login successful using " + (loginFunc === loginTarget.login ? "login" : "setExternalUserId"));
                                } else {
                                    console.warn("OneSignal: Sync method not found in any target, retrying... (" + attempts + ")");
                                    setTimeout(() => syncExternalId(attempts + 1), 2000);
                                }
                            } catch (e) {
                                console.error("OneSignal: Sync error", e);
                                setTimeout(() => syncExternalId(attempts + 1), 2000);
                            }
                        };

                        // Wait for registration before trying to sync ID
                        setTimeout(syncExternalId, 4000);
                    @endauth

                    // Permission Request
                    if (os.Notifications && os.Notifications.requestPermission) {
                        os.Notifications.requestPermission(true).then((success) => {
                            // window.showAlert("Push Permission: " + (success ? "GRANTED" : "DENIED"), success ? "success" : "error");
                        });
                    } else if (typeof os.promptForPushNotificationsWithUserResponse === 'function') {
                        os.promptForPushNotificationsWithUserResponse(true);
                    }
                    
                    // Listener (v5: Notifications, v4: handleNotificationOpened)
                    if (os.Notifications && os.Notifications.addEventListener) {
                        os.Notifications.addEventListener('click', (event) => {
                             console.log('Notification clicked:', event);
                        });
                    } else if (typeof os.handleNotificationOpened === 'function') {
                        os.handleNotificationOpened( (openResult) => {
                            console.log('Notification opened:', openResult);
                        });
                    }

                    // Debug Player/Subscription ID
                    let checkCount = 0;
                    const checkInterval = setInterval(async () => {
                        checkCount++;
                        
                        try {
                            const osUser = os.User;
                            let pushId = null;
                            let osId = osUser ? osUser.oneSignalId : null;
                            
                            if (osUser && osUser.pushSubscription && typeof osUser.pushSubscription.getIdAsync === 'function') {
                                pushId = await osUser.pushSubscription.getIdAsync();
                            }
                            
                            // Check v4 fallback if necessary
                            if (!pushId && typeof os.getDeviceState === 'function') {
                                os.getDeviceState((state) => {
                                    if (state && state.userId) pushId = state.userId;
                                });
                            }

                            if (pushId) {
                                console.log("OneSignal Status: Registered Successfully!");
                                console.log("Subscription ID: " + pushId);
                                if (osId) console.log("OneSignal User ID: " + osId);
                                
                                // Update UI Debug panel
                                const idLabel = document.getElementById('debug-onesignal-id');
                                const subLabel = document.getElementById('debug-subscription-id');
                                if (idLabel) idLabel.innerText = osId || "Ready";
                                if (subLabel) {
                                    subLabel.innerText = pushId;
                                    subLabel.style.color = "#10b981"; 
                                }

                                // window.showAlert("OneSignal Registered!", "success");
                                clearInterval(checkInterval);
                            } else {
                                console.log("OneSignal Status: Waiting for registration... (" + checkCount + ")");
                                if (checkCount >= 20) {
                                    console.warn("OneSignal: Registration timeout.");
                                    clearInterval(checkInterval);
                                }
                            }
                        } catch (e) {
                            console.error("OneSignal Polling Error:", e);
                        }
                    }, 2000);


                } catch (e) {
                    console.error("OneSignal: Initialization Error", e);
                    // window.showAlert("OneSignal Error: " + e.message, "error");
                }
            } else {
                console.error("OneSignal: window.OneSignal not found!");
                // window.showAlert("OneSignal SDK Missing", "error");
            }
        }

        // Global Alert/Toast Helper
        window.showAlert = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-error' : 'bg-primary');
            const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info');
            
            toast.className = `flex items-center gap-3 px-6 py-4 ${bgColor} text-white rounded-2xl shadow-2xl animate-bounce-in pointer-events-auto transform transition-all duration-300`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="h-5 w-5"></i>
                <span class="text-xs font-black uppercase tracking-widest">${message}</span>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();

            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-4', 'scale-95');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };

        // Logout Cleanup for OneSignal
        window.handleLogout = function(event) {
            if (window.OneSignal) {
                window.OneSignal.removeExternalUserId();
            }
            return true;
        };

    </script>
    @stack('scripts')
</body>

</html>

    <style>
        @keyframes bounce-in {
            0% { opacity: 0; transform: translateY(20px) scale(0.9); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.02); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-bounce-in {
            animation: bounce-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
    </style>
</body>

</html>