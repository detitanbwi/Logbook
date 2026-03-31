<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tirta Moico HRIS</title>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="icon" type="image/png" href="{{ asset('images/branding/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Auto-login logic
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkAutoLogin);
        } else {
            checkAutoLogin();
        }

        function checkAutoLogin() {
            const savedNPP = localStorage.getItem('hris_user_npp');
            const savedToken = localStorage.getItem('hris_user_token');

            if (savedNPP && savedToken) {
                fetch('/api/user', {
                    headers: {
                        'Authorization': 'Bearer ' + savedToken,
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        window.location.href = '/dashboard';
                    } else {
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
<body class="bg-base-200 text-base-content antialiased overflow-x-hidden min-h-screen flex items-center justify-center p-4" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="w-full max-w-md" x-data="{ onLoginSubmit(e) {
        localStorage.setItem('hris_user_npp', document.querySelector('input[name=\'npp\']').value);
    } }">
        <div class="card bg-base-100 shadow-2xl border border-base-300 rounded-[2rem] overflow-hidden animate-in fade-in zoom-in duration-500">
            <div class="card-body p-8 md:p-12">
                <div class="mb-10 text-center">
                    <div class="inline-flex w-24 h-24 mb-6 items-center justify-center">
                        <img src="{{ asset('images/branding/logo.png') }}?v={{ time() }}" alt="Tirta Moico" class="w-full h-full object-contain">
                    </div>
                    <p class="text-[0.65rem] font-bold tracking-[0.3em] text-base-content/40 uppercase">Sistem Pelaporan SDM Terintegrasi</p>
                    <h2 class="text-2xl font-black tracking-tight text-primary mt-2">Logbook Portal</h2>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-6" id="loginForm" @submit="onLoginSubmit">
                    @csrf

                    @if(session('error') || $errors->any())
                    <div class="alert alert-error bg-error/10 border-error/20 text-error rounded-2xl flex gap-3 text-xs font-bold uppercase tracking-wide">
                        <i data-lucide="alert-circle" class="h-5 w-5"></i>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success bg-green-50 border-green-200 text-green-700 rounded-2xl flex gap-3 text-xs font-bold uppercase tracking-wide">
                        <i data-lucide="check-circle" class="h-5 w-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    <div class="form-control w-full space-y-2">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.65rem] font-bold tracking-widest text-base-content/50 uppercase">NPP *</span>
                        </label>
                        <div class="relative group">
                            <input type="number" name="npp" placeholder="Masukkan NPP" required 
                                   x-init="$el.value = localStorage.getItem('hris_user_npp') || '{{ old('npp') }}'"
                                   class="input input-bordered w-full h-14 px-6 rounded-2xl font-medium tracking-wide bg-base-200 border-transparent focus:border-primary/30">
                        </div>
                    </div>

                    <div class="form-control w-full space-y-2">
                        <label class="label py-0 ml-1">
                            <span class="label-text text-[0.65rem] font-bold tracking-widest text-base-content/50 uppercase">PASSWORD *</span>
                        </label>
                        <div class="relative group" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="Masukkan Password" required
                                   class="input input-bordered w-full h-14 px-6 pr-14 rounded-2xl font-medium tracking-wide bg-base-200 border-transparent focus:border-primary/30">
                            <button type="button" @click="show = !show" class="absolute right-5 top-1/2 -translate-y-1/2 text-base-content/30 hover:text-primary transition-colors focus:outline-none">
                                <template x-if="!show">
                                    <i data-lucide="eye" class="h-5 w-5"></i>
                                </template>
                                <template x-if="show">
                                    <i data-lucide="eye-off" class="h-5 w-5"></i>
                                </template>
                            </button>
                        </div>
                    </div>

                    <div class="card-actions mt-8">
                        <button type="submit" class="btn btn-primary w-full h-14 rounded-2xl text-white font-bold tracking-[0.15em] uppercase transition-all duration-300 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95" x-data="{ isSubmitting: false }" @submit.window="isSubmitting = true">
                            <span x-show="!isSubmitting">Login</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span>
                                Processing...
                            </span>
                        </button>
                    </div>

                    <div class="mt-10 text-center pt-8 border-t border-base-300">
                        <p class="text-[0.65rem] font-bold tracking-wider text-base-content/30 uppercase leading-relaxed">
                            Lupa password atau kendala akses?<br>
                            <span class="text-base-content/60">Hubungi Administrator</span>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="fixed -bottom-32 -left-32 w-96 h-96 bg-primary/10 rounded-full blur-3xl -z-10"></div>
    <div class="fixed top-20 -right-20 w-64 h-64 bg-primary/10 rounded-full blur-3xl -z-10"></div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
