<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { page } from '$app/state';

	let { isSidebarOpen = $bindable(false) }: { isSidebarOpen?: boolean } = $props();

	// Menu links based on role (keys are lowercase for case-insensitive matching)
	const roleRoutes: Record<string, { label: string; href: string }[]> = {
		admin: [
			{ label: 'Dashboard', href: '/admin/dashboard' },
			{ label: 'Master Data', href: '/admin/kpis' },
			{ label: 'User Management', href: '/admin/users' },
			{ label: 'Audit Logs', href: '/admin/audit-logs' }
		],
		manager: [
			{ label: 'Dashboard', href: '/manager/dashboard' },
			{ label: 'Tim Saya', href: '/manager/team' },
			{ label: 'Review Logbook', href: '/manager/reviews' }
		],
		staff: [
			{ label: 'Dashboard', href: '/staff/dashboard' },
			{ label: 'Mulai Kerja', href: '/staff/logbook' },
			{ label: 'Riwayat', href: '/staff/history' }
		]
	};

	// Normalize role to lowercase for consistent matching
	let role = $derived(auth.role?.toLowerCase() || 'staff');
	let displayRole = $derived(role.charAt(0).toUpperCase() + role.slice(1));
	let links = $derived(roleRoutes[role] || roleRoutes['staff']);

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
			class="mb-1 rounded-md px-4 py-2 hover:bg-base-200 block {page.url.pathname.startsWith(link.href) ? 'bg-primary text-primary-content font-semibold hover:bg-primary-focus' : ''}"
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
			class="mb-1 rounded-md px-4 py-2 hover:bg-base-200 block {page.url.pathname === '/profile' ? 'bg-primary text-primary-content font-semibold hover:bg-primary-focus' : ''}"
			onclick={closeSidebar}
			aria-current={page.url.pathname === '/profile' ? 'page' : undefined}
		>
			Profil Saya
		</a>
	</li>
</ul>
