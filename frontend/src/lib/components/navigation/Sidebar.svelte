<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { page } from '$app/state';
	import { getNavigationLinks, normalizeRole } from '$lib/auth/permissions';
	import { Home, Users, Target, ClipboardList, BarChart3, FileText, ShieldCheck, UserCircle2 } from 'lucide-svelte';

	let { isSidebarOpen = $bindable(false) }: { isSidebarOpen?: boolean } = $props();

	// Normalize role to lowercase for consistent matching
	let role = $derived(normalizeRole(auth.role));
	let displayRole = $derived(role.charAt(0).toUpperCase() + role.slice(1));
	let links = $derived(getNavigationLinks(auth.role));

	function closeSidebar() {
		isSidebarOpen = false;
	}

	function resolveIcon(label: string) {
		const key = label.toLowerCase();
		if (key.includes('dashboard')) return Home;
		if (key.includes('user') || key.includes('admin')) return Users;
		if (key.includes('kpi')) return Target;
		if (key.includes('logbook')) return ClipboardList;
		if (key.includes('performance') || key.includes('team') || key.includes('tim')) return BarChart3;
		if (key.includes('report') || key.includes('riwayat')) return FileText;
		if (key.includes('audit')) return ShieldCheck;
		return FileText;
	}
</script>

<aside class="flex min-h-full w-80 flex-col border-r border-base-300 bg-base-100 p-4 text-base-content shadow-sm">
	<div class="mb-6 rounded-2xl border border-base-300 bg-base-100 p-4 shadow-sm">
		<div class="mb-3 flex items-center gap-3">
			<div
				class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/90 font-bold text-primary-content shadow-sm"
			>
				L
			</div>
			<div>
				<p class="text-xs font-medium uppercase tracking-[0.18em] text-base-content/60">Workspace</p>
				<p class="text-xl font-bold tracking-tight text-primary">Logbook Suite</p>
			</div>
		</div>
		<p class="text-xs text-base-content/65">Role aktif: <span class="font-semibold">{displayRole}</span></p>
	</div>

	<p class="mb-2 px-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-base-content/55">Navigation</p>
	<ul class="menu w-full gap-1 p-0">
		{#each links as link}
			{@const isActive = page.url.pathname.startsWith(link.href)}
			{@const Icon = resolveIcon(link.label)}
			<li>
				<a
					href={link.href}
					class="group flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm transition-all duration-200 hover:-translate-y-px hover:border-base-300 hover:bg-base-100/95 hover:shadow-sm {isActive
						? 'border-primary/40 bg-primary/10 font-semibold text-primary shadow-sm'
						: 'text-base-content/80'}"
					onclick={closeSidebar}
					aria-current={isActive ? 'page' : undefined}
				>
					<Icon class="h-4 w-4 {isActive ? 'text-primary' : 'text-base-content/65'}" />
					<span>{link.label}</span>
				</a>
			</li>
		{/each}
	</ul>

	<div class="mt-auto pt-4">
		<p class="mb-2 px-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-base-content/55">Account</p>
		<ul class="menu w-full p-0">
			<li>
				<a
					href="/profile"
					class="flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm transition-all duration-200 hover:-translate-y-px hover:border-base-300 hover:bg-base-100/95 hover:shadow-sm {page.url.pathname === '/profile'
						? 'border-primary/40 bg-primary/10 font-semibold text-primary shadow-sm'
						: 'text-base-content/80'}"
					onclick={closeSidebar}
					aria-current={page.url.pathname === '/profile' ? 'page' : undefined}
				>
					<UserCircle2
						class="h-4 w-4 {page.url.pathname === '/profile' ? 'text-primary' : 'text-base-content/65'}"
					/>
					<span>Profil Saya</span>
				</a>
			</li>
		</ul>
	</div>
</aside>
