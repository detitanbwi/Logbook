<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { DataTable, FilterDropdown, Modal, Pagination, SearchInput, SortableHeader } from '$lib/components/ui';
	import AuditLogDetailsContent from '$lib/components/audit/AuditLogDetailsContent.svelte';
	import { auditService } from '$lib/api/services/auditService';
	import { toastStore } from '$lib/stores/toast.svelte';
	import type { AuditLog } from '$lib/types';

	type SortDir = 'asc' | 'desc';
	type AuditAction = 'created' | 'updated' | 'deleted';

	const actionOptions = [
		{ label: 'Created', value: 'created' },
		{ label: 'Updated', value: 'updated' },
		{ label: 'Deleted', value: 'deleted' }
	];

	const sortFieldOptions = [
		{ label: 'Performed At', value: 'performed_at' },
		{ label: 'Created At', value: 'created_at' }
	];

	let logs = $state<AuditLog[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let fetchRequestId = 0;
	let refreshNonce = $state(0);

	let selectedLog = $state<AuditLog | null>(null);
	let detailsModalOpen = $state(false);

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let action = $derived(($page.url.searchParams.get('action') as AuditAction | null) || '');
	let dateFrom = $derived($page.url.searchParams.get('date_from') || '');
	let dateTo = $derived($page.url.searchParams.get('date_to') || '');
	let sortBy = $derived(($page.url.searchParams.get('sort_by') as 'performed_at' | 'created_at' | null) || 'performed_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as SortDir | null) || 'desc');

	let dateFromDraft = $state('');
	let dateToDraft = $state('');

	$effect(() => {
		dateFromDraft = dateFrom;
		dateToDraft = dateTo;
	});

	function refreshData() {
		refreshNonce += 1;
	}

	$effect(() => {
		refreshNonce;

		const requestId = ++fetchRequestId;
		loading = true;
		error = null;

		const params: Record<string, unknown> = {
			page: currentPage,
			per_page: perPage,
			sort_by: sortBy,
			sort_dir: sortDir
		};

		if (search) params.search = search;
		if (action) params.action = action;
		if (dateFrom) params.date_from = dateFrom;
		if (dateTo) params.date_to = dateTo;

		(async () => {
			try {
				const response = await auditService.getAuditLogs(params);

				if (requestId !== fetchRequestId) return;

				logs = Array.isArray(response) ? response : (response as any).data || [];
				meta = (response as any).meta || null;
			} catch (fetchError: any) {
				if (requestId !== fetchRequestId) return;

				console.error('Failed to fetch audit logs', fetchError);
				const errorMessage = fetchError.message || 'Gagal memuat audit logs.';
				error = errorMessage;
				toastStore.error(errorMessage);
			} finally {
				if (requestId === fetchRequestId) {
					loading = false;
				}
			}
		})();
	});

	function updateUrl(params: Record<string, string>, resetPage = true) {
		const url = new URL($page.url);

		Object.entries(params).forEach(([key, value]) => {
			if (value) {
				url.searchParams.set(key, value);
			} else {
				url.searchParams.delete(key);
			}
		});

		if (resetPage) {
			url.searchParams.set('page', '1');
		}

		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}

	function handleActionFilter(value: string) {
		updateUrl({ action: value });
	}

	function applyDateFilters() {
		updateUrl({ date_from: dateFromDraft, date_to: dateToDraft });
	}

	function clearDateFilters() {
		dateFromDraft = '';
		dateToDraft = '';
		updateUrl({ date_from: '', date_to: '' });
	}

	function handleSort(column: string, dir: SortDir) {
		updateUrl({ sort_by: column, sort_dir: dir });
	}

	function handleSortField(value: string) {
		if (value === 'performed_at' || value === 'created_at') {
			updateUrl({ sort_by: value });
		}
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function openDetails(log: AuditLog) {
		selectedLog = log;
		detailsModalOpen = true;
	}

	function formatDateTime(value: string | null | undefined): string {
		if (!value) return '-';
		const date = new Date(value);
		if (Number.isNaN(date.getTime())) return value;
		return date.toLocaleString('id-ID');
	}

	function getUserName(log: AuditLog): string {
		return log.user?.nama ?? log.performed_by ?? 'System';
	}

	function getUserNpp(log: AuditLog): string {
		return log.user?.npp ?? '-';
	}

	function toPrettyJson(value: unknown): string {
		if (value === null || value === undefined) {
			return '-';
		}

		if (typeof value === 'string') {
			try {
				const parsed = JSON.parse(value);
				return JSON.stringify(parsed, null, 2);
			} catch {
				return value;
			}
		}

		try {
			return JSON.stringify(value, null, 2);
		} catch {
			return String(value);
		}
	}
</script>

<svelte:head>
	<title>SuperAdmin - Audit Logs</title>
</svelte:head>

<div class="mb-6 space-y-2">
	<h1 class="text-2xl font-bold">Audit Logs</h1>
	<p class="text-base-content/70">Pantau seluruh jejak aktivitas sistem untuk kebutuhan audit.</p>
</div>

<div class="mb-4 flex flex-wrap items-end gap-3">
	<SearchInput
		value={search}
		placeholder="Cari user, table, atau record..."
		onSearch={handleSearch}
		class="min-w-[220px] flex-1"
	/>

	<FilterDropdown
		label="Action"
		options={actionOptions}
		value={action}
		onChange={handleActionFilter}
	/>

	<FilterDropdown
		label="Sort By"
		options={sortFieldOptions}
		value={sortBy}
		onChange={handleSortField}
	/>

	<div class="form-control">
		<label class="label" for="date_from">
			<span class="label-text">Dari</span>
		</label>
		<input id="date_from" type="date" class="input-bordered input input-sm" bind:value={dateFromDraft} />
	</div>

	<div class="form-control">
		<label class="label" for="date_to">
			<span class="label-text">Sampai</span>
		</label>
		<input id="date_to" type="date" class="input-bordered input input-sm" bind:value={dateToDraft} />
	</div>

	<div class="flex gap-2">
		<button class="btn btn-sm" onclick={applyDateFilters}>Terapkan</button>
		<button class="btn btn-outline btn-sm" onclick={clearDateFilters}>Reset Tanggal</button>
	</div>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={refreshData}>Coba Lagi</button>
	</div>
{/if}

<DataTable loading={loading} empty={logs.length === 0} columnsCount={5}>
	{#snippet head()}
		<tr>
			<SortableHeader
				column={sortBy}
				label="Waktu"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>User</th>
			<th>Table</th>
			<th>Action</th>
			<th>Details</th>
		</tr>
	{/snippet}

	{#each logs as log (log.id)}
		<tr>
			<td>{formatDateTime(log.performed_at ?? log.created_at)}</td>
			<td>
				<div class="font-medium">{getUserName(log)}</div>
				<div class="text-xs text-base-content/70">{getUserNpp(log)}</div>
			</td>
			<td class="font-mono text-xs">{log.table_name ?? '-'}</td>
			<td>
				<span class="badge badge-outline uppercase">{log.action ?? '-'}</span>
			</td>
			<td>
				<button class="btn btn-outline btn-sm" onclick={() => openDetails(log)}>Details</button>
			</td>
		</tr>
	{/each}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={detailsModalOpen} title="Audit Log Details">
	{#if selectedLog}
		<AuditLogDetailsContent
			log={selectedLog}
			{getUserName}
			{formatDateTime}
			{toPrettyJson}
		/>
	{/if}
</Modal>
