<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { logbookStore } from '$lib/stores/logbook.svelte';
	import StatusBadge from '$lib/components/ui/StatusBadge.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import { FilterDropdown, SortableHeader } from '$lib/components/ui';
	import type { LogbookFilters } from '$lib/api/services/staffLogbookService';

	const statusOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Draft', value: 'DRAFT' },
		{ label: 'Diajukan', value: 'SUBMITTED' },
		{ label: 'Dikembalikan', value: 'REVERTED' },
		{ label: 'Direview', value: 'REVIEWED' }
	];

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let status = $derived($page.url.searchParams.get('status') || '');
	let dateFrom = $derived($page.url.searchParams.get('date_from') || '');
	let dateTo = $derived($page.url.searchParams.get('date_to') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);
		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
		});
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	$effect(() => {
		const filters: LogbookFilters = {
			page: currentPage,
			per_page: perPage
		};

		if (status) filters.status = status;
		if (dateFrom) filters.date_from = dateFrom;
		if (dateTo) filters.date_to = dateTo;
		if (sortBy) filters.sort_by = sortBy;
		if (sortDir) filters.sort_dir = sortDir;

		logbookStore.fetchLogbooks(filters).catch((err) => {
			console.error('Failed to fetch history', err);
		});
	});

	let sortedLogbooks = $derived(logbookStore.logbooks);
</script>

<svelte:head>
	<title>Riwayat Logbook | Staff</title>
</svelte:head>

<div class="flex flex-col gap-6">
	<div class="flex items-center justify-between">
		<h1 class="text-3xl font-bold">Riwayat Logbook</h1>
	</div>

	<!-- Filter Section -->
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<div class="flex flex-wrap gap-4">
				<FilterDropdown
					label="Status"
					options={statusOptions}
					value={status}
					onChange={(v) => updateUrl({ status: v })}
				/>
				<div class="flex flex-wrap items-center gap-2">
					<label for="date-from" class="text-sm font-medium">Dari:</label>
					<input
						id="date-from"
						type="date"
						class="input-bordered input input-sm"
						value={dateFrom}
						onchange={(e) => updateUrl({ date_from: e.currentTarget.value })}
					/>
					<label for="date-to" class="text-sm font-medium">Sampai:</label>
					<input
						id="date-to"
						type="date"
						class="input-bordered input input-sm"
						value={dateTo}
						onchange={(e) => updateUrl({ date_to: e.currentTarget.value })}
					/>
				</div>
			</div>
		</div>
	</div>

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body p-0">
			{#if logbookStore.isLoading && logbookStore.logbooks.length === 0}
				<div class="overflow-x-auto rounded-box">
					<table class="table w-full table-zebra">
						<thead class="bg-base-200 text-base-content">
							<tr>
								<th>Tanggal</th>
								<th>Status</th>
								<th>Waktu Mulai</th>
								<th>Waktu Selesai</th>
								<th>Tugas Selesai</th>
							</tr>
						</thead>
						<tbody>
							{#each Array(5) as _}
								<tr>
									<td>
										<div class="h-4 w-40 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-5 w-20 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-4 w-16 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-4 w-16 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-4 w-12 animate-pulse rounded bg-base-300"></div>
									</td>
								</tr>
							{/each}
						</tbody>
					</table>
				</div>
			{:else}
				<div class="overflow-x-auto rounded-box">
					<table class="table w-full table-zebra">
						<thead class="bg-base-200 text-base-content">
							<tr>
								<SortableHeader
									column="created_at"
									label="Tanggal"
									currentSort={sortBy}
									currentDir={sortDir}
									onSort={(c, d) => updateUrl({ sort_by: c, sort_dir: d })}
								/>
								<th>Status</th>
								<th>Waktu Mulai</th>
								<th>Waktu Selesai</th>
								<th>Tugas Selesai</th>
							</tr>
						</thead>
						<tbody>
							{#if sortedLogbooks.length === 0}
								<tr>
									<td colspan="5" class="py-8 text-center text-base-content/60">
										Belum ada riwayat logbook.
									</td>
								</tr>
							{:else}
								{#each sortedLogbooks as log (log.id)}
									<tr>
										<td>
											{new Date(log.created_at).toLocaleDateString('id-ID', {
												weekday: 'long',
												year: 'numeric',
												month: 'long',
												day: 'numeric'
											})}
										</td>
										<td>
											<StatusBadge status={log.status} />
										</td>
										<td>
											{log.start_kerja
												? new Date(log.start_kerja).toLocaleTimeString('id-ID', {
														hour: '2-digit',
														minute: '2-digit'
													})
												: '-'}
										</td>
										<td>
											{log.end_kerja
												? new Date(log.end_kerja).toLocaleTimeString('id-ID', {
														hour: '2-digit',
														minute: '2-digit'
													})
												: '-'}
										</td>
										<td>
											{#if Array.isArray(log.details) && log.details.length > 0}
												{log.details.filter((d) => d.is_finished).length} / {log.details.length}
											{:else}
												-
											{/if}
										</td>
									</tr>
								{/each}
							{/if}
						</tbody>
					</table>
				</div>
				<div class="p-4">
					<Pagination meta={logbookStore.meta} onPageSizeChange={handlePageSizeChange} />
				</div>
			{/if}
		</div>
	</div>
</div>
