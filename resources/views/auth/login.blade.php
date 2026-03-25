<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tirta Moico Logbook</title>

    <!-- Material Symbols Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Auto-login if user has valid session token saved
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkAutoLogin);
        } else {
            checkAutoLogin();
        }

        function checkAutoLogin() {
            const savedNPP = localStorage.getItem('hris_user_npp');
            const savedToken = localStorage.getItem('hris_user_token');

            if (savedNPP && savedToken) {
                // Try to restore session with saved token
                fetch('/api/user', {
                    headers: {
                        'Authorization': 'Bearer ' + savedToken,
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        // Token is valid, redirect to dashboard
                        window.location.href = '/dashboard';
                    } else {
                        // Token expired, clear saved data
                        localStorage.removeItem('hris_user_token');
                        localStorage.removeItem('hris_user_npp');
                    }
                }).catch(() => {
                    localStorage.removeItem('hris_user_token');
                    localStorage.removeItem('hris_user_npp');
                });
            }
        }
    </script>
</head>
<body class="bg-surface font-body overflow-x-hidden">
    <div class="min-h-screen flex items-center justify-center py-10 lg:py-0 lg:-mt-10" x-data="{ onLoginSubmit(e) {
        localStorage.setItem('hris_user_npp', document.querySelector('input[name=\\\"npp\\\"]').value);
    } }">
        <div class="w-full max-w-lg p-6 lg:p-12 bg-transparent lg:bg-white lg:editorial-shadow rounded-none lg:rounded-xl animate-in fade-in zoom-in duration-700">
            <div class="mb-12 text-center">
                <div class="inline-flex w-20 h-20 mb-6 items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/branding/logo.jpeg') }}" alt="Tirta Moico" class="w-full h-full object-cover">
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-primary mb-2">Tirta Moico Logbook</h1>
                <p class="text-[0.7rem] font-bold tracking-[0.3em] text-on-surface/40 uppercase">Sistem Pelaporan SDM Terintegrasi</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-6" id="loginForm" @submit="onLoginSubmit">
                @csrf
                 @if(session('error'))
                <div class="p-4 bg-error/5 border border-error/10 text-error rounded-xl flex items-center gap-3 animate-shake duration-500 mb-6">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span class="text-xs font-bold tracking-wide uppercase">{{ session('error') }}</span>
                </div>
                @endif

                @if(session('success'))
                <div class="p-4 bg-primary/5 border border-primary/10 text-primary rounded-xl flex items-center gap-3 animate-in fade-in duration-500 mb-6">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span class="text-xs font-bold tracking-wide uppercase">{{ session('success') }}</span>
                </div>
                @endif

                 @if($errors->any())
                <div class="p-4 bg-error/5 border border-error/10 text-error rounded-xl flex items-center gap-3 animate-shake duration-500">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span class="text-xs font-bold tracking-wide uppercase">{{ $errors->first() }}</span>
                </div>
                @endif

                <div class="space-y-2">
                    <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">NPP *</label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-xl text-on-surface/20 group-focus-within:text-primary transition-colors">person</span>
                        <input type="number" name="npp" placeholder="Masukkan NPP" required x-init="$el.value = localStorage.getItem('hris_user_npp') || '{{ old('npp') }}'"
                               class="w-full h-16 pl-14 pr-6 rounded-xl font-medium tracking-wide">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[0.65rem] font-bold tracking-widest text-on-surface/50 uppercase ml-1">PASSWORD *</label>
                    <div class="relative group" x-data="{ show: false }">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-xl text-on-surface/20 group-focus-within:text-primary transition-colors">lock</span>
                        <input :type="show ? 'text' : 'password'" name="password" placeholder="Masukkan Password" required
                               class="w-full h-16 pl-14 pr-16 rounded-xl font-medium tracking-wide">
                        <button type="button" @click="show = !show" class="absolute right-6 top-1/2 -translate-y-1/2 text-on-surface/20 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined" x-text="show ? 'visibility_off' : 'visibility'"></span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full h-16 primary-gradient text-white font-bold tracking-[0.15em] uppercase rounded-xl transition-all duration-300 hover:shadow-2xl hover:shadow-primary/30 active:scale-95 relative overflow-hidden" x-data="{ isSubmitting: false }" @submit.window="saveLoginInfo(); isSubmitting = true; setTimeout(() => { isSubmitting = false }, 10000)">
                    <!-- Progress bar -->
                    <div x-show="isSubmitting" class="absolute inset-0 h-full bg-white/20" x-transition>
                        <div class="h-full bg-white/10" style="animation: progress 10s linear;"></div>
                    </div>
                    <span x-show="!isSubmitting" class="relative z-10 flex items-center justify-center">Login</span>
                    <div x-show="isSubmitting" class="relative z-10 flex items-center justify-center gap-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-sm font-bold">Processing...</span>
                    </div>
                </button>
                <script>
                    function saveLoginInfo() {
                        const nppInput = document.querySelector('input[name="npp"]');
                        if (nppInput && nppInput.value) {
                            localStorage.setItem('hris_user_npp', nppInput.value);
                        }
                    }
                </script>
                <style>
                    @keyframes progress {
                        0% { width: 0; }
                        90% { width: 90%; }
                        100% { width: 100%; }
                    }
                </style>

                <div class="pt-10 text-center border-t border-outline-variant/10">
                    <p class="text-[0.65rem] font-bold tracking-wider text-on-surface/30 uppercase leading-relaxed">
                        Lupa password atau kendala akses?<br>
                        <span class="text-on-surface/60">Hubungi Administrator</span>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="fixed -bottom-32 -left-32 w-96 h-96 bg-primary/5 rounded-full blur-3xl -z-10"></div>
    <div class="fixed top-20 -right-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10"></div>
    <div class="fixed bottom-10 right-20 w-32 h-32 bg-primary/2 rounded-full blur-xl -z-10"></div>

</body>
</html>
