<script lang="ts">
	import { goto } from '$app/navigation';
	import { notificationStore } from '$lib/stores/notification.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import type { Notification } from '$lib/api/schemas/notification.schema';
	import { CheckCircle2 } from 'lucide-svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import FilterDropdown from '$lib/components/ui/FilterDropdown.svelte';
	import NotificationListItem from '$lib/components/notifications/NotificationListItem.svelte';
	import NotificationsStatePanel from '$lib/components/notifications/NotificationsStatePanel.svelte';
	import { page } from '$app/stores';
	import { handleNotificationClick, isNotificationUnread } from '$lib/utils/notification';

	let notifications = $state<Notification[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let errorMessage = $state<string | null>(null);
	let actionMessage = $state<string | null>(null);
	let pendingReadId = $state<string | null>(null);
	let markingAllAsRead = $state(false);

	const readStatusOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Belum Dibaca', value: 'false' },
		{ label: 'Sudah Dibaca', value: 'true' }
	];

	const typeOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Penugasan KPI', value: 'KPI_ASSIGNMENT' },
		{ label: 'Logbook Diajukan', value: 'LOGBOOK_SUBMITTED' },
		{ label: 'Logbook Diterima', value: 'LOGBOOK_ACCEPTED' },
		{ label: 'Logbook Ditolak', value: 'LOGBOOK_REJECTED' }
	];

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let isRead = $derived($page.url.searchParams.get('is_read') || '');
	let type = $derived($page.url.searchParams.get('type') || '');

	function updateUrl(params: Record<string, string | undefined>) {
		const url = new URL($page.url);
		for (const [key, value] of Object.entries(params)) {
			if (value) {
				url.searchParams.set(key, value);
			} else {
				url.searchParams.delete(key);
			}
		}
		// Reset to page 1 when filters change
		if (!('page' in params)) {
			url.searchParams.delete('page');
		}
		goto(url.toString(), { replaceState: true, keepFocus: true });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, keepFocus: true });
	}

	async function loadNotifications() {
		const params: Record<string, any> = { page: currentPage, per_page: perPage };
		if (isRead) params.is_read = isRead === 'true';
		if (type) params.type = type;

		loading = true;
		errorMessage = null;
		await notificationStore
			.fetchAll(params)
			.then((data) => {
				notifications = Array.isArray(data) ? data : (data as any).data || [];
				meta = (data as any).meta || null;
			})
			.catch((error) => {
				errorMessage =
					(error as { message?: string })?.message ||
					'Gagal memuat notifikasi. Silakan coba lagi.';
				console.error('Failed to fetch notifications:', error);
			})
			.finally(() => {
				loading = false;
			});
	}

	$effect(() => {
		void loadNotifications();
	});

	async function markAsRead(id: string) {
		if (pendingReadId || markingAllAsRead) {
			return;
		}

		pendingReadId = id;
		actionMessage = null;

		try {
			await notificationStore.markAsRead(id);
			// Optimistic update
			notifications = notifications.map((n) =>
				n.id === id ? { ...n, is_read: true, read_at: new Date().toISOString() } : n
			);
			actionMessage = 'Notifikasi berhasil ditandai sebagai dibaca.';
			toastStore.success(actionMessage);
		} catch (error) {
			actionMessage =
				(error as { message?: string })?.message || 'Gagal menandai notifikasi sebagai dibaca.';
			toastStore.error(actionMessage);
			console.error('Failed to mark as read:', error);
		} finally {
			pendingReadId = null;
		}
	}

	async function markAllAsRead() {
		if (markingAllAsRead || pendingReadId) {
			return;
		}

		markingAllAsRead = true;
		actionMessage = null;

		try {
			await notificationStore.markAllAsRead();
			// Optimistic update
			const now = new Date().toISOString();
			notifications = notifications.map((n) =>
				isNotificationUnread(n) ? { ...n, is_read: true, read_at: now } : n
			);
			actionMessage = 'Semua notifikasi berhasil ditandai sebagai dibaca.';
			toastStore.success(actionMessage);
		} catch (error) {
			actionMessage =
				(error as { message?: string })?.message ||
				'Gagal menandai semua notifikasi sebagai dibaca.';
			toastStore.error(actionMessage);
			console.error('Failed to mark all as read:', error);
		} finally {
			markingAllAsRead = false;
		}
	}

	async function handleNotificationItemClick(notification: Notification) {
		if (pendingReadId || markingAllAsRead) {
			return;
		}

		const wasUnread = isNotificationUnread(notification);
		const optimisticReadAt = new Date().toISOString();

		if (isNotificationUnread(notification)) {
			notifications = notifications.map((n) =>
				n.id === notification.id ? { ...n, is_read: true, read_at: optimisticReadAt } : n
			);
		}

		try {
			await handleNotificationClick(notification, {
				markAsRead: (id) => notificationStore.markAsRead(id),
				navigate: (to) => goto(to)
			});
		} catch (error) {
			if (wasUnread) {
				notifications = notifications.map((n) =>
					n.id === notification.id ? { ...n, is_read: false, read_at: null } : n
				);
			}

			actionMessage =
				(error as { message?: string })?.message || 'Gagal membuka notifikasi. Silakan coba lagi.';
			toastStore.error(actionMessage);
			console.error('Failed to open notification:', error);
		}
	}

	function formatDate(dateStr: string) {
		return new Date(dateStr).toLocaleString('id-ID', {
			dateStyle: 'medium',
			timeStyle: 'short'
		});
	}
</script>

<svelte:head>
	<title>Notifikasi</title>
</svelte:head>

<div class="container mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
	<div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
		<div>
			<h1 class="text-2xl font-bold text-base-content sm:text-3xl">Notifikasi</h1>
			<p class="mt-1 text-sm text-base-content/70">Pemberitahuan sistem dan aktivitas tim Anda</p>
		</div>

		{#if !loading && notifications.some((n) => isNotificationUnread(n))}
			<button
				class="btn btn-outline btn-sm btn-primary"
				onclick={markAllAsRead}
				disabled={markingAllAsRead || !!pendingReadId}
			>
				<CheckCircle2 class="mr-2 h-4 w-4" />
				{markingAllAsRead ? 'Memproses...' : 'Tandai Semua Dibaca'}
			</button>
		{/if}
	</div>

	{#if actionMessage}
		<div class="alert mb-4" class:alert-success={!actionMessage.toLowerCase().includes('gagal')} class:alert-error={actionMessage.toLowerCase().includes('gagal')} role="status" aria-live="polite">
			<span>{actionMessage}</span>
		</div>
	{/if}

	<div class="mb-4 flex flex-wrap gap-4">
		<FilterDropdown
			label="Status"
			options={readStatusOptions}
			value={isRead}
			onChange={(v) => updateUrl({ is_read: v })}
		/>
		<FilterDropdown
			label="Tipe"
			options={typeOptions}
			value={type}
			onChange={(v) => updateUrl({ type: v })}
		/>
	</div>

	<div class="card border border-base-200 bg-base-100 shadow-sm">
		<div class="card-body p-0">
			{#if loading || !!errorMessage || notifications.length === 0}
				<NotificationsStatePanel
					{loading}
					{errorMessage}
					empty={notifications.length === 0}
					hasFilters={!!isRead || !!type}
					onRetry={loadNotifications}
				/>
			{:else}
				<div class="divide-y divide-base-200">
					{#each notifications as notif (notif.id)}
						<NotificationListItem
							notification={notif}
							{pendingReadId}
							{markingAllAsRead}
							{formatDate}
							onOpen={handleNotificationItemClick}
							onMarkAsRead={markAsRead}
						/>
					{/each}
				</div>
				<div class="border-t border-base-200 p-4">
					<Pagination {meta} onPageSizeChange={handlePageSizeChange} />
				</div>
			{/if}
		</div>
	</div>
</div>
