<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { goto } from '$app/navigation';
	import { LogOut, User, Settings, Bell, Menu } from 'lucide-svelte';
	import { notificationStore } from '$lib/stores/notification.svelte';
	import { onMount } from 'svelte';

	let { isSidebarOpen = $bindable(false) }: { isSidebarOpen?: boolean } = $props();

	let userInitial = $derived(auth.user.current?.name?.charAt(0).toUpperCase() || 'U');
	let userName = $derived(auth.user.current?.name || 'User');

	let notifications = $derived(notificationStore.unread);
	let unreadCount = $derived(notificationStore.unreadCount);

	onMount(() => {
		if (auth.user.current) {
			notificationStore.fetchUnread();
			const interval = setInterval(() => notificationStore.fetchUnread(), 60000); // Poll every minute
			return () => clearInterval(interval);
		}
	});

	async function handleLogout() {
		await auth.logout();
		goto('/login');
	}
</script>

<div class="navbar sticky top-0 z-30 w-full border-b border-base-300 bg-base-100 px-4 shadow-sm">
	<div class="flex-none lg:hidden">
		<button
			aria-label="open sidebar"
			class="btn btn-square btn-ghost"
			onclick={() => (isSidebarOpen = true)}
		>
			<Menu class="h-6 w-6" />
		</button>
	</div>

	<div class="flex-1">
		<!-- Search or title could go here -->
	</div>

	<div class="flex-none gap-4">
		<div class="dropdown dropdown-end">
			<button class="btn btn-circle btn-ghost" aria-label="Notifikasi" tabindex="0">
				<div class="indicator">
					<Bell class="h-5 w-5" />
					{#if unreadCount > 0}
						<span class="indicator-item badge badge-xs badge-error">{unreadCount}</span>
					{/if}
				</div>
			</button>

			<ul
				tabindex="-1"
				class="dropdown-content menu z-[1] mt-3 w-80 rounded-box border border-base-200 bg-base-100 p-2 shadow-lg"
			>
				<li class="flex flex-row items-center justify-between menu-title px-4 py-2">
					<span class="font-bold text-base-content">Notifikasi</span>
					{#if unreadCount > 0}
						<button
							class="h-auto min-h-0 border-none bg-transparent p-0 text-xs text-primary hover:underline"
							onclick={async () => {
								await notificationStore.markAllAsRead();
								await notificationStore.fetchUnread();
							}}
						>
							Tandai semua dibaca
						</button>
					{/if}
				</li>
				<div class="divider my-0"></div>

				{#if notifications.length === 0}
					<li class="p-4 text-center text-sm text-base-content/70">Tidak ada notifikasi baru</li>
				{:else}
					{#each notifications as notif}
						<li>
							<a href="/notifications" class="flex flex-col items-start gap-1 py-3">
								<span class="text-sm font-medium">{notif.type}</span>
								{#if typeof notif.data === 'string'}
									<span class="line-clamp-2 text-xs text-base-content/70">{notif.data}</span>
								{:else if notif.data?.message}
									<span class="line-clamp-2 text-xs text-base-content/70">{notif.data.message}</span
									>
								{/if}
							</a>
						</li>
					{/each}
				{/if}

				<div class="divider my-0"></div>
				<li>
					<a href="/notifications" class="justify-center font-medium text-primary">
						Lihat Semua Notifikasi
					</a>
				</li>
			</ul>
		</div>

		<div class="dropdown dropdown-end">
			<button tabindex="0" class="btn gap-2 pl-2 btn-ghost">
				<div class="placeholder avatar">
					<div class="w-8 rounded-full bg-primary text-primary-content">
						<span class="text-sm font-semibold">{userInitial}</span>
					</div>
				</div>
				<span class="hidden text-sm font-medium md:inline-block">{userName}</span>
			</button>

			<ul
				tabindex="-1"
				class="dropdown-content menu z-[1] mt-3 w-52 rounded-box border border-base-200 bg-base-100 p-2 shadow-lg"
			>
				<li class="menu-title px-4 py-2">
					<span class="block truncate font-bold text-base-content">{userName}</span>
					<span class="text-xs font-normal opacity-70">{auth.role}</span>
				</li>
				<div class="divider my-0"></div>
				<li>
					<a href="/profile" class="gap-3">
						<User class="h-4 w-4" />
						Profil Saya
					</a>
				</li>
				<div class="divider my-1"></div>
				<li>
					<button onclick={handleLogout} class="gap-3 font-medium text-error">
						<LogOut class="h-4 w-4" />
						Keluar
					</button>
				</li>
			</ul>
		</div>
	</div>
</div>
