<script lang="ts">
	import type { Snippet } from 'svelte';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth.svelte';
	import Sidebar from '$lib/components/navigation/Sidebar.svelte';
	import Navbar from '$lib/components/navigation/Navbar.svelte';
	import LoadingSkeleton from '$lib/components/ui/LoadingSkeleton.svelte';

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
	<div class="min-h-screen bg-base-200">
		<div class="border-b border-base-300/80 bg-base-100/90 px-4 py-4 backdrop-blur-md">
			<div class="h-8 w-48 animate-pulse rounded bg-base-300"></div>
		</div>
		<div class="mx-auto w-full max-w-7xl p-4 md:p-6 lg:p-8">
			<LoadingSkeleton rows={6} cols={2} />
		</div>
	</div>
{:else if auth.isAuthenticated}
	<div class="drawer min-h-screen bg-base-200 lg:drawer-open">
		<input id="app-drawer" type="checkbox" class="drawer-toggle" bind:checked={isSidebarOpen} />

		<div class="drawer-content flex flex-col items-center justify-start">
			<Navbar bind:isSidebarOpen />

			<main class="mx-auto w-full max-w-[1240px] p-4 md:p-6 lg:p-8">
				<div class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-6 shadow-sm">
					{@render children()}
				</div>
			</main>
		</div>

		<div class="drawer-side z-40">
			<label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
			<Sidebar bind:isSidebarOpen />
		</div>
	</div>
{/if}
