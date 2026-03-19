<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { goto } from '$app/navigation';

	let nip = $state('');
	let password = $state('');

	$effect(() => {
		if (auth.isAuthenticated) {
			goto('/');
		}
	});

	async function handleSubmit(event: Event) {
		event.preventDefault();
		try {
			await auth.login({ nip, password });
			goto('/');
		} catch (e) {
			// Error state handled inside auth store
		}
	}
</script>

<div class="card border border-base-300 bg-base-100 shadow-xl">
	<div class="card-body">
		<div class="mb-6 text-center">
			<h2 class="card-title justify-center text-2xl font-bold text-primary">Logbook & KPI</h2>
			<p class="mt-1 text-base-content/60">Masuk ke akun Anda</p>
		</div>

		<form onsubmit={handleSubmit} class="space-y-4">
			{#if auth.error}
				<div class="alert rounded-lg p-3 text-sm alert-error">
					<span>{auth.error}</span>
				</div>
			{/if}

			<div class="form-control w-full">
				<label class="label" for="nip">
					<span class="label-text font-medium">NIP</span>
				</label>
				<input
					id="nip"
					type="text"
					bind:value={nip}
					placeholder="Masukkan NIP Anda"
					class="input-bordered input w-full"
					required
					disabled={auth.isLoading}
				/>
			</div>

			<div class="form-control w-full">
				<label class="label" for="password">
					<span class="label-text font-medium">Password</span>
				</label>
				<input
					id="password"
					type="password"
					bind:value={password}
					placeholder="Masukkan Password"
					class="input-bordered input w-full"
					required
					disabled={auth.isLoading}
				/>
			</div>

			<div class="form-control mt-6">
				<button
					type="submit"
					class="btn w-full btn-primary"
					disabled={auth.isLoading || !nip || !password}
				>
					{#if auth.isLoading}
						<span class="loading loading-sm loading-spinner"></span>
						Memproses...
					{:else}
						Masuk
					{/if}
				</button>
			</div>
		</form>
	</div>
</div>
