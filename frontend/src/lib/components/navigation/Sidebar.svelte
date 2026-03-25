<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { page } from '$app/state';
	import { getNavigationLinks, normalizeRole } from '$lib/auth/permissions';

	let { isSidebarOpen = $bindable(false) }: { isSidebarOpen?: boolean } = $props();

	// Normalize role to lowercase for consistent matching
	let role = $derived(normalizeRole(auth.role));
	let displayRole = $derived(role.charAt(0).toUpperCase() + role.slice(1));
	let links = $derived(getNavigationLinks(auth.role));

	function closeSidebar() {
		isSidebarOpen = false;
	}
</script>

<ul class="menu min-h-full w-72 border-r border-base-300 bg-base-100 p-4 text-base-content">
	<div class="mb-6 flex items-center gap-2 px-4">
		<div
			class="flex h-8 w-8 items-center justify-center rounded bg-primary font-bold text-primary-content"
		>
			L
		</div>
		<span class="text-xl font-bold tracking-tight text-primary">Logbook</span>
	</div>

	<li class="mt-2 mb-1 menu-title text-xs tracking-widest uppercase">Menu Utama ({displayRole})</li>
	{#each links as link}
		<li>
			<a
				href={link.href}
				class="mb-1 block rounded-md px-4 py-2 hover:bg-base-200 {page.url.pathname.startsWith(
					link.href
				)
					? 'hover:bg-primary-focus bg-primary font-semibold text-primary-content'
					: ''}"
				onclick={closeSidebar}
				aria-current={page.url.pathname.startsWith(link.href) ? 'page' : undefined}
			>
				{link.label}
			</a>
		</li>
	{/each}

	<div class="divider my-2"></div>

	<li class="mb-1 menu-title text-xs tracking-widest uppercase">Akun</li>
	<li>
		<a
			href="/profile"
			class="mb-1 block rounded-md px-4 py-2 hover:bg-base-200 {page.url.pathname === '/profile'
				? 'hover:bg-primary-focus bg-primary font-semibold text-primary-content'
				: ''}"
			onclick={closeSidebar}
			aria-current={page.url.pathname === '/profile' ? 'page' : undefined}
		>
			Profil Saya
		</a>
	</li>
</ul>
