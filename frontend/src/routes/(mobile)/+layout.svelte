<script lang="ts">
	import type { Snippet } from 'svelte';
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth.svelte';
	import LoadingSkeleton from '$lib/components/ui/LoadingSkeleton.svelte';

	let { children }: { children: Snippet } = $props();

	let currentPath = $derived($page.url.pathname);
	let hasSubordinates = $derived(auth.user.current?.has_subordinates ?? false);

	interface TabItem {
		label: string;
		href: string;
		icon: string;
		show: boolean;
	}

	let tabs = $derived<TabItem[]>([
		{ label: 'Ringkas', href: '/m/overview', icon: 'home', show: true },
		{ label: 'Performa', href: '/m/performance', icon: 'chart', show: true },
		{ label: 'Logbook', href: '/m/logbook', icon: 'logbook', show: true },
		{ label: 'Tim', href: '/m/team', icon: 'team', show: hasSubordinates },
		{ label: 'Profil', href: '/m/profile', icon: 'profile', show: true }
	]);

	let visibleTabs = $derived(tabs.filter((t) => t.show));

	function isActive(href: string): boolean {
		return currentPath === href || currentPath.startsWith(href + '/');
	}

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
	<div class="flex min-h-screen flex-col bg-base-200">
		<header
			class="sticky top-0 z-30 flex items-center justify-between border-b border-base-300/70 bg-base-100/90 px-4 py-3 text-base-content shadow-sm backdrop-blur-md"
		>
			<h1 class="text-lg font-bold tracking-tight">Logbook</h1>
		</header>
		<main class="flex-1 overflow-y-auto px-4 pt-4 pb-20">
			<LoadingSkeleton rows={8} cols={1} />
		</main>
	</div>
{:else if auth.isAuthenticated}
	<div class="flex min-h-screen flex-col bg-base-200">
		<!-- Header -->
		<header
			class="sticky top-0 z-30 flex items-center justify-between border-b border-base-300/70 bg-base-100/90 px-4 py-3 text-base-content shadow-sm backdrop-blur-md"
		>
			<div>
				<p class="text-[0.68rem] font-semibold tracking-[0.16em] text-base-content/55 uppercase">
					Mobile Workspace
				</p>
				<h1 class="text-lg font-bold tracking-tight">Logbook</h1>
			</div>
			<div class="flex items-center gap-2">
				<span
					class="rounded-full border border-base-300/70 bg-base-100/70 px-2.5 py-1 text-xs font-medium text-base-content/80"
					>{auth.user.current?.nama ?? ''}</span
				>
			</div>
		</header>

		<!-- Content Area -->
		<main class="flex-1 overflow-y-auto px-4 pt-4 pb-24">
			{@render children()}
		</main>

		<!-- Bottom Tab Navigation -->
		<nav
			class="fixed inset-x-3 bottom-3 z-40 flex items-center gap-1 rounded-2xl border border-base-300 bg-base-100 px-1 py-1.5 shadow-lg"
		>
			{#each visibleTabs as tab (tab.href)}
				{@const active = isActive(tab.href)}
				<button
					class="group flex min-w-0 flex-1 flex-col items-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-semibold transition-all duration-200 {active
						? 'bg-primary/15 text-primary shadow-sm'
						: 'text-base-content/65'}"
					aria-current={active ? 'page' : undefined}
					aria-label={tab.label}
					onclick={() => goto(tab.href)}
					type="button"
				>
					{#if tab.icon === 'home'}
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 transition-transform duration-200 group-active:scale-95 {active
								? 'scale-105'
								: ''}"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
							/>
						</svg>
					{:else if tab.icon === 'chart'}
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 transition-transform duration-200 group-active:scale-95 {active
								? 'scale-105'
								: ''}"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
							/>
						</svg>
					{:else if tab.icon === 'logbook'}
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 transition-transform duration-200 group-active:scale-95 {active
								? 'scale-105'
								: ''}"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
							/>
						</svg>
					{:else if tab.icon === 'team'}
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 transition-transform duration-200 group-active:scale-95 {active
								? 'scale-105'
								: ''}"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
							/>
						</svg>
					{:else if tab.icon === 'profile'}
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-5 w-5 transition-transform duration-200 group-active:scale-95 {active
								? 'scale-105'
								: ''}"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
							/>
						</svg>
					{/if}
					<span class="max-w-full truncate px-1 text-[0.62rem] tracking-[0.01em]">{tab.label}</span>
				</button>
			{/each}
		</nav>
	</div>
{/if}
