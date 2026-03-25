<script lang="ts">
	import { page } from '$app/state';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth.svelte';

	let errorStatus = $derived(page.status);
	let errorMessage = $derived(page.error?.message || 'Something went wrong');

	function goHome() {
		if (auth.isAuthenticated) {
			goto('/');
		} else {
			goto('/login');
		}
	}
</script>

<svelte:head>
	<title>{errorStatus} - {errorMessage}</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-base-200 p-4">
	<div class="card w-full max-w-md border border-base-300 bg-base-100 text-center shadow-xl">
		<div class="card-body items-center p-10">
			<h1 class="relative text-9xl font-extrabold text-primary opacity-20">
				<span
					class="absolute inset-0 flex items-center justify-center text-4xl font-bold text-primary opacity-100"
				>
					{errorStatus}
				</span>
			</h1>

			<h2 class="mt-4 mb-2 text-2xl font-bold">Oops! Ada yang salah.</h2>
			<p class="mb-6 text-base-content/70">{errorMessage}</p>

			<button class="btn btn-primary" onclick={goHome}> Kembali ke Beranda </button>
		</div>
	</div>
</div>
