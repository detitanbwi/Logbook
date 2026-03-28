<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import { analyticsService } from '$lib/api/services/analyticsService';
	import type { StaffPerformanceSummaryItem } from '$lib/types';

	function openStaffDetail(item: StaffPerformanceSummaryItem) {
		const query = new URLSearchParams();
		if (dateFrom) query.set('date_from', dateFrom);
		if (dateTo) query.set('date_to', dateTo);

		const suffix = query.toString();
		goto(`/admin/staff-performance/${item.user_id}${suffix ? `?${suffix}` : ''}`);
	}

	let items = $state<StaffPerformanceSummaryItem[]>([]);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let fetchRequestId = 0;
	let refreshNonce = $state(0);

	let dateFrom = $derived.by(() => $page.url.searchParams.get('date_from') || '');
	let dateTo = $derived.by(() => $page.url.searchParams.get('date_to') || '');

	let dateFromDraft = $state('');
	let dateToDraft = $state('');

	const totalStaff = $derived(items.length);
	const totalLogbooks = $derived(items.reduce((sum, item) => sum + (item.total_logbooks || 0), 0));
	const avgProgress = $derived(
		totalStaff > 0
			? Math.round((items.reduce((sum, item) => sum + (item.progress_percent || 0), 0) / totalStaff) * 100) /
					100
			: 0
	);

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

		(async () => {
			try {
				const response = await analyticsService.getStaffPerformanceSummary({
					date_from: dateFrom || undefined,
					date_to: dateTo || undefined
				});

				if (requestId !== fetchRequestId) {
					return;
				}

				items = response?.items || [];
			} catch (fetchError: any) {
				if (requestId !== fetchRequestId) {
					return;
				}

				console.error('Failed to fetch staff performance summary', fetchError);
				error = fetchError?.message || 'Gagal memuat ringkasan performa staff.';
			} finally {
				if (requestId === fetchRequestId) {
					loading = false;
				}
			}
		})();
	});

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);

		Object.entries(params).forEach(([key, value]) => {
			if (value) {
				url.searchParams.set(key, value);
			} else {
				url.searchParams.delete(key);
			}
		});

		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function applyDateFilters() {
		updateUrl({ date_from: dateFromDraft, date_to: dateToDraft });
	}

	function clearDateFilters() {
		dateFromDraft = '';
		dateToDraft = '';
		updateUrl({ date_from: '', date_to: '' });
	}

	function formatNumber(value: number | null | undefined): string {
		if (value === null || value === undefined || Number.isNaN(Number(value))) return '0';
		return Number(value).toLocaleString('id-ID');
	}

	function formatPercent(value: number | null | undefined): string {
		if (value === null || value === undefined || Number.isNaN(Number(value))) return '0%';
		return `${Number(value).toFixed(2)}%`;
	}
</script>

<svelte:head>
	<title>Staff Performance | Admin</title>
</svelte:head>

<div class="mb-6 space-y-2">
	<h1 class="text-2xl font-bold">Staff Performance</h1>
	<p class="text-base-content/70">Ringkasan performa staff berdasarkan periode tanggal.</p>
</div>

<div class="mb-4 flex flex-wrap items-end gap-3">
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

<div class="mb-6 grid gap-3 md:grid-cols-3">
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body py-4">
			<div class="text-sm text-base-content/70">Total Staff</div>
			<div class="text-2xl font-semibold">{formatNumber(totalStaff)}</div>
		</div>
	</div>
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body py-4">
			<div class="text-sm text-base-content/70">Total Logbooks</div>
			<div class="text-2xl font-semibold">{formatNumber(totalLogbooks)}</div>
		</div>
	</div>
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body py-4">
			<div class="text-sm text-base-content/70">Rata-rata Progress</div>
			<div class="text-2xl font-semibold">{formatPercent(avgProgress)}</div>
		</div>
	</div>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={refreshData}>Coba Lagi</button>
	</div>
{/if}

<DataTable loading={loading} empty={items.length === 0} columnsCount={8}>
	{#snippet head()}
		<tr>
			<th>Nama</th>
			<th>NPP</th>
			<th>Total / Accepted / Rejected</th>
			<th>Hari Kerja</th>
			<th>Jam Kerja</th>
			<th>Rata-rata Rating</th>
			<th>Progress</th>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#each items as item (item.user_id)}
		<tr class="cursor-pointer hover:bg-base-200" onclick={() => openStaffDetail(item)}>
			<td class="font-medium">{item.nama || '-'}</td>
			<td>{item.npp || '-'}</td>
			<td>
				{formatNumber(item.total_logbooks)} / {formatNumber(item.accepted_logbooks)} /
				{formatNumber(item.rejected_logbooks)}
			</td>
			<td>{formatNumber(item.total_days_worked)}</td>
			<td>{item.total_work_hours != null ? `${item.total_work_hours.toFixed(1)}h` : '-'}</td>
			<td>
				{#if item.average_rating != null && Number.isFinite(Number(item.average_rating))}
					<span class="badge badge-warning gap-1">⭐ {Number(item.average_rating).toFixed(1)}</span>
				{:else}
					<span class="text-base-content/50">-</span>
				{/if}
			</td>
			<td>
				<span class="badge badge-info">{formatPercent(item.progress_percent)}</span>
			</td>
			<td>
				<button
					class="btn btn-outline btn-sm"
					onclick={(event) => {
						event.stopPropagation();
						openStaffDetail(item);
					}}
				>
					Lihat Detail
				</button>
			</td>
		</tr>
	{/each}
</DataTable>
