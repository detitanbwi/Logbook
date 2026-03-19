<script lang="ts">
	import { goto } from '$app/navigation';
	import { notificationStore } from '$lib/stores/notification.svelte';
	import type { Notification } from '$lib/api/schemas/notification.schema';
	import { Bell, CheckCircle2, Circle } from 'lucide-svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import FilterDropdown from '$lib/components/ui/FilterDropdown.svelte';
	import { page } from '$app/stores';

	let notifications = $state<Notification[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);

	const readStatusOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Belum Dibaca', value: 'false' },
		{ label: 'Sudah Dibaca', value: 'true' }
	];

	const typeOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Penugasan KPI', value: 'KPI_ASSIGNMENT' },
		{ label: 'Logbook Diajukan', value: 'LOGBOOK_SUBMITTED' },
		{ label: 'Logbook Dikembalikan', value: 'LOGBOOK_REVERTED' },
		{ label: 'Logbook Direview', value: 'LOGBOOK_REVIEWED' }
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

	async function fetchNotifications(pageNum: number, isReadFilter: string, typeFilter: string) {
		loading = true;
		try {
			const params: Record<string, any> = { page: pageNum, per_page: perPage };
			if (isReadFilter) params.is_read = isReadFilter === 'true';
			if (typeFilter) params.type = typeFilter;

			const data = await notificationStore.fetchAll(params);
			notifications = Array.isArray(data) ? data : (data as any).data || [];
			meta = (data as any).meta || null;
		} catch (error) {
			console.error('Failed to fetch notifications:', error);
		} finally {
			loading = false;
		}
	}

	$effect(() => {
		fetchNotifications(currentPage, isRead, type);
	});

	async function markAsRead(id: string) {
		try {
			await notificationStore.markAsRead(id);
			// Optimistic update
			notifications = notifications.map((n) =>
				n.id === id ? { ...n, read_at: new Date().toISOString() } : n
			);
		} catch (error) {
			console.error('Failed to mark as read:', error);
		}
	}

	async function markAllAsRead() {
		try {
			await notificationStore.markAllAsRead();
			// Optimistic update
			const now = new Date().toISOString();
			notifications = notifications.map((n) => (n.read_at ? n : { ...n, read_at: now }));
		} catch (error) {
			console.error('Failed to mark all as read:', error);
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

		{#if !loading && notifications.some((n) => !n.read_at)}
			<button class="btn btn-outline btn-sm btn-primary" onclick={markAllAsRead}>
				<CheckCircle2 class="mr-2 h-4 w-4" />
				Tandai Semua Dibaca
			</button>
		{/if}
	</div>

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
			{#if loading}
				<div class="divide-y divide-base-200">
					{#each Array(5) as _}
						<div class="flex items-start gap-4 p-4">
							<div class="mt-1 flex-shrink-0">
								<div class="h-5 w-5 animate-pulse rounded-full bg-base-300"></div>
							</div>
							<div class="min-w-0 flex-1">
								<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
									<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
									<div class="h-3 w-24 animate-pulse rounded bg-base-300"></div>
								</div>
								<div class="mt-2 h-4 w-full max-w-md animate-pulse rounded bg-base-300"></div>
							</div>
							<div class="ml-4 flex-shrink-0">
								<div class="h-6 w-24 animate-pulse rounded bg-base-300"></div>
							</div>
						</div>
					{/each}
				</div>
			{:else if notifications.length === 0}
				<div class="flex flex-col items-center justify-center p-12 text-base-content/50">
					<Bell class="mb-4 h-12 w-12 opacity-50" />
					<p class="text-lg font-medium">Belum ada notifikasi</p>
					<p class="text-sm">Anda akan melihat pemberitahuan aktivitas di sini.</p>
				</div>
			{:else}
				<div class="divide-y divide-base-200">
					{#each notifications as notif}
						<div
							class="flex items-start gap-4 p-4 transition-colors hover:bg-base-200/50 {notif.read_at
								? 'opacity-70'
								: ''}"
						>
							<div class="mt-1 flex-shrink-0">
								{#if notif.read_at}
									<CheckCircle2 class="h-5 w-5 text-base-content/40" />
								{:else}
									<Circle class="h-5 w-5 fill-primary/20 text-primary" />
								{/if}
							</div>

							<div class="min-w-0 flex-1">
								<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
									<p class="truncate text-sm font-semibold text-base-content">
										{notif.type}
									</p>
									<span class="text-xs whitespace-nowrap text-base-content/50">
										{formatDate(notif.created_at)}
									</span>
								</div>

								<p class="mt-1 text-sm text-base-content/80">
									{#if typeof notif.data === 'string'}
										{notif.data}
									{:else if notif.data?.message}
										{notif.data.message}
									{:else}
										{JSON.stringify(notif.data)}
									{/if}
								</p>
							</div>

							{#if !notif.read_at}
								<div class="ml-4 flex-shrink-0">
									<button
										class="btn text-primary btn-ghost btn-xs"
										onclick={() => markAsRead(notif.id)}
									>
										Tandai dibaca
									</button>
								</div>
							{/if}
						</div>
					{/each}
				</div>
				<div class="border-t border-base-200 p-4">
					<Pagination {meta} onPageSizeChange={handlePageSizeChange} />
				</div>
			{/if}
		</div>
	</div>
</div>
