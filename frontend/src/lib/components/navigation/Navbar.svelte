<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { goto } from '$app/navigation';
	import { LogOut, User, Bell, Menu } from 'lucide-svelte';
	import { notificationStore } from '$lib/stores/notification.svelte';
	import {
		getNotificationPreviewText,
		handleNotificationClick
	} from '$lib/utils/notification';
	import { onMount } from 'svelte';
	import UserAvatar from '$lib/components/ui/UserAvatar.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';

	let { isSidebarOpen = $bindable(false) }: { isSidebarOpen?: boolean } = $props();

	let userName = $derived(auth.user.current?.nama || 'User');

	let notifications = $derived(notificationStore.unread);
	let unreadCount = $derived(notificationStore.unreadCount);
	let notificationsLoading = $derived(notificationStore.loading);

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

	async function handleOpenNotification(notification: (typeof notifications)[number]) {
		await handleNotificationClick(notification, {
			markAsRead: (id) => notificationStore.markAsRead(id),
			navigate: (to) => goto(to)
		});
	}
</script>

<div class="navbar sticky top-0 z-30 w-full border-b border-base-300/80 bg-base-100/90 px-4 shadow-sm backdrop-blur-md">
	<div class="flex-none lg:hidden">
		<button
			aria-label="open sidebar"
			class="btn btn-square btn-ghost rounded-xl"
			onclick={() => (isSidebarOpen = true)}
		>
			<Menu class="h-6 w-6" />
		</button>
	</div>

	<div class="flex-1 px-2">
		<div class="hidden md:block">
			<p class="text-xs font-semibold uppercase tracking-[0.16em] text-base-content/55">Operations</p>
			<p class="text-sm font-semibold text-base-content">Welcome back, {userName}</p>
		</div>
	</div>

	<div class="flex-none gap-2 md:gap-3">
		<div class="dropdown dropdown-end">
			<button class="btn btn-circle btn-ghost border border-base-300/70 bg-base-100/80" aria-label="Notifikasi" tabindex="0">
				<div class="indicator">
					<Bell class="h-5 w-5" />
					{#if unreadCount > 0}
						<span class="indicator-item badge badge-xs badge-error">{unreadCount}</span>
					{/if}
				</div>
			</button>

			<ul
				tabindex="-1"
				class="dropdown-content menu z-[1] mt-3 w-80 rounded-2xl border border-base-200 bg-base-100/95 p-2 shadow-xl"
			>
				<li class="flex flex-row items-center justify-between menu-title px-4 py-2">
					<span class="font-bold text-base-content">Notifikasi</span>
					{#if unreadCount > 0}
						<button
							class="h-auto min-h-0 border-none bg-transparent p-0 text-xs text-primary hover:underline"
							disabled={notificationsLoading}
							onclick={async () => {
								await notificationStore.markAllAsRead();
								await notificationStore.fetchUnread();
								toastStore.success('Semua notifikasi telah ditandai dibaca');
							}}
						>
							Tandai semua dibaca
						</button>
					{/if}
				</li>
				<li aria-hidden="true" class="my-0 h-px bg-base-300/80"></li>

				{#if notificationsLoading}
					<li class="p-4 text-center text-sm text-base-content/70">
						<span class="loading loading-spinner loading-sm"></span>
					</li>
				{:else if notifications.length === 0}
					<li class="p-4 text-center text-sm text-base-content/70">Tidak ada notifikasi baru</li>
				{:else}
					{#each notifications as notif (notif.id)}
						<li>
							<button
								type="button"
								class="flex w-full flex-col items-start gap-1 py-3 text-left"
								onclick={() => handleOpenNotification(notif)}
							>
								<span class="text-sm font-medium">{notif.type}</span>
								<span class="line-clamp-2 text-xs text-base-content/70">
									{getNotificationPreviewText(notif)}
								</span>
							</button>
						</li>
					{/each}
				{/if}

				<li aria-hidden="true" class="my-0 h-px bg-base-300/80"></li>
				<li>
					<a href="/notifications" class="justify-center font-medium text-primary">
						Lihat Semua Notifikasi
					</a>
				</li>
			</ul>
		</div>

		<div class="dropdown dropdown-end">
			<button tabindex="0" class="btn btn-ghost gap-2 rounded-xl border border-base-300/70 bg-base-100/80 pl-2">
				<UserAvatar foto={auth.user.current?.foto} fotoUrl={auth.user.current?.foto_url} name={userName} size="sm" />
				<span class="hidden text-sm font-medium md:inline-block">{userName}</span>
			</button>

			<ul
				tabindex="-1"
				class="dropdown-content menu z-[1] mt-3 w-56 rounded-2xl border border-base-200 bg-base-100/95 p-2 shadow-xl"
			>
				<li class="menu-title px-4 py-2">
					<span class="block truncate font-bold text-base-content">{userName}</span>
					<span class="text-xs font-normal opacity-70">{auth.role}</span>
				</li>
				<li aria-hidden="true" class="my-0 h-px bg-base-300/80"></li>
				<li>
					<a href="/profile" class="gap-3">
						<User class="h-4 w-4" />
						Profil Saya
					</a>
				</li>
				<li aria-hidden="true" class="my-1 h-px bg-base-300/80"></li>
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
