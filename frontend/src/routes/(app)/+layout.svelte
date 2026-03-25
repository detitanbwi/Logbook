<script lang="ts">
	import type { Snippet } from 'svelte';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth.svelte';
	import Sidebar from '$lib/components/navigation/Sidebar.svelte';
	import Navbar from '$lib/components/navigation/Navbar.svelte';

	let { children }: { children: Snippet } = $props();

	// Reactive drawer state
	let isSidebarOpen = $state(false);

	$effect(() => {
		if (!auth.isInitialized && auth.isAuthenticated) {
			auth.fetchMe().catch(() => {});
		}
	});

	$effect(() => {
		if (auth.isInitialized && !auth.isAuthenticated) {
			goto('/login');
		}
	});
</script>

{#if !auth.isInitialized}
	<div class="flex min-h-screen items-center justify-center bg-base-200">
		<span class="loading loading-spinner loading-lg text-primary"></span>
	</div>
{:else if auth.isAuthenticated}
	<div class="drawer min-h-screen bg-base-200 lg:drawer-open">
		<input id="app-drawer" type="checkbox" class="drawer-toggle" bind:checked={isSidebarOpen} />

		<div class="drawer-content flex flex-col items-center justify-start">
			<Navbar bind:isSidebarOpen />

			<main class="mx-auto w-full max-w-7xl p-4 md:p-6 lg:p-8">
				{@render children()}
			</main>
		</div>

		<div class="drawer-side z-40">
			<label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
			<Sidebar bind:isSidebarOpen />
		</div>
	</div>
{/if}
