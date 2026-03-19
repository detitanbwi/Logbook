<script lang="ts">
import { auditService } from '$lib/api/services/auditService';
import DataTable from '$lib/components/ui/DataTable.svelte';
import Pagination from '$lib/components/ui/Pagination.svelte';
import { SearchInput, FilterDropdown, SortableHeader, Popover } from '$lib/components/ui';
import { normalizeAuditLog, type NormalizedAuditLog } from '$lib/utils/auditLog';
import { goto } from '$app/navigation';
import { page } from '$app/stores';

	let logs = $state<NormalizedAuditLog[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
let search = $derived($page.url.searchParams.get('search') || '');
let action = $derived($page.url.searchParams.get('action') || '');
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

	const actionOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Dibuat (created)', value: 'created' },
		{ label: 'Diperbarui (updated)', value: 'updated' },
		{ label: 'Dihapus (deleted)', value: 'deleted' }
	];

async function fetchLogs() {
  loading = true;
  error = null;

  const params: Record<string, unknown> = {
    page: currentPage,
    per_page: perPage
  };

		if (search) params.search = search;
		if (action) params.action = action;
		if (dateFrom) params.date_from = dateFrom;
		if (dateTo) params.date_to = dateTo;
		if (sortBy) params.sort_by = sortBy;
		if (sortDir) params.sort_dir = sortDir;

		try {
			const data = await auditService.getAuditLogs(params);
			if ('data' in data && Array.isArray((data as any).data)) {
				logs = (data as any).data.map(normalizeAuditLog);
				meta = (data as any).meta || null;
			} else {
				logs = (Array.isArray(data) ? data : []).map(normalizeAuditLog);
			}
		} catch (e: any) {
			console.error('Failed to fetch audit logs', e);
			error = e.message || 'Gagal memuat log audit';
		} finally {
			loading = false;
		}
	}

	$effect(() => {
		fetchLogs();
	});

	function formatDate(dateStr: string) {
		if (!dateStr) return '-';

		const date = new Date(dateStr);
		if (Number.isNaN(date.getTime())) return '-';

		return date.toLocaleString('id-ID', {
			dateStyle: 'medium',
			timeStyle: 'short'
		});
	}

	function getEntityLabel(type: string | undefined) {
		const safeType = typeof type === 'string' && type.length > 0 ? type : '-';
		return safeType.split('\\').pop() || safeType;
	}

	function formatValues(values: Record<string, any> | undefined | null) {
		if (!values || Object.keys(values).length === 0) return '-';
		return JSON.stringify(values, null, 2);
	}

function getEventColor(event: string) {
  if (!event) return 'text-info';
  switch (event.toLowerCase()) {
    case 'created':
      return 'text-success';
    case 'updated':
      return 'text-warning';
    case 'deleted':
      return 'text-error';
    default:
      return 'text-info';
  }
}

function handlePageSizeChange(size: number) {
  const url = new URL($page.url);
  url.searchParams.set('per_page', size.toString());
  url.searchParams.set('page', '1');
  goto(url.toString(), { replaceState: true, noScroll: true });
}
</script>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
	<div class="mb-8 flex flex-col gap-4">
		<div>
			<h1 class="text-2xl font-bold text-base-content sm:text-3xl">Audit Logs</h1>
			<p class="mt-1 text-sm text-base-content/70">Pantau aktivitas sistem dan perubahan data</p>
		</div>

		<div class="flex flex-wrap items-center gap-4">
			<div class="w-full max-w-xs sm:w-64">
				<SearchInput
					value={search}
					placeholder="Cari tabel/modul..."
					onSearch={(v) => updateUrl({ search: v })}
				/>
			</div>

			<FilterDropdown
				label="Aksi"
				options={actionOptions}
				value={action}
				onChange={(v) => updateUrl({ action: v })}
			/>

			<div class="flex items-center gap-2">
				<label class="text-sm text-base-content/70" for="date-from">Dari:</label>
				<input
					id="date-from"
					type="date"
					class="input-bordered input input-sm"
					value={dateFrom}
					onchange={(e) => updateUrl({ date_from: e.currentTarget.value })}
				/>
				<label class="text-sm text-base-content/70" for="date-to">Sampai:</label>
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

	{#if error}
		<div class="mb-4 alert alert-error shadow-sm">
			<span>{error}</span>
			<button class="btn btn-ghost btn-sm" onclick={() => fetchLogs()}>Coba Lagi</button>
		</div>
	{/if}

	<DataTable>
		{#snippet head()}
			<tr>
				<SortableHeader
					column="created_at"
					label="Waktu"
					currentSort={sortBy}
					currentDir={sortDir}
					onSort={(c, d) => updateUrl({ sort_by: c, sort_dir: d })}
				/>
				<th>Aksi</th>
				<th>Modul / Tabel</th>
				<th>Pengguna</th>
				<th>Perubahan</th>
			</tr>
		{/snippet}

		{#if loading}
			<tr>
				<td colspan="5" class="py-12 text-center text-base-content/50">
					<span class="loading loading-lg loading-spinner"></span>
					<p class="mt-4">Memuat data...</p>
				</td>
			</tr>
		{:else if logs.length === 0}
			<tr>
				<td colspan="5" class="py-12 text-center text-base-content/50"> Data tidak ditemukan. </td>
			</tr>
		{:else}
			{#each logs as log}
				<tr class="hover">
					<td class="text-sm whitespace-nowrap text-base-content/80">
						{formatDate(log.created_at)}
					</td>
					<td>
						<span class="text-xs font-medium uppercase {getEventColor(log.event)}">
							{log.event}
						</span>
					</td>
					<td>
						<div class="text-sm font-medium">
							{getEntityLabel(log.auditable_type)}
						</div>
						<div class="text-xs text-base-content/50">ID: {log.auditable_id}</div>
					</td>
					<td>
						{#if log.user}
							<div class="text-sm font-medium">{log.user.name}</div>
							<div class="text-xs text-base-content/50">{log.user.email}</div>
						{:else}
							<span class="text-xs text-base-content/50 italic">System / Unknown</span>
						{/if}
					</td>
<td class="max-w-xs xl:max-w-md">
            <Popover>
              {#snippet trigger()}
                <span class="btn btn-ghost btn-xs">Lihat Detail</span>
              {/snippet}
              {#snippet content()}
                <div class="mb-2">
                  <p class="font-bold text-base-content">Old Values:</p>
                  <pre
                    class="mt-1 overflow-x-auto rounded bg-base-200 p-2 text-[10px]">{formatValues(
                    log.old_values
                  )}</pre>
                </div>
                <div>
                  <p class="font-bold text-base-content">New Values:</p>
                  <pre
                    class="mt-1 overflow-x-auto rounded bg-base-200 p-2 text-[10px]">{formatValues(
                    log.new_values
                  )}</pre>
                </div>
              {/snippet}
            </Popover>
          </td>
				</tr>
			{/each}
		{/if}
	</DataTable>
<div class="mt-4">
    <Pagination {meta} onPageSizeChange={handlePageSizeChange} />
  </div>
</div>
