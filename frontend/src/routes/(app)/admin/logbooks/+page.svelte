<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import Modal from '$lib/components/ui/Modal.svelte';
	import { SearchInput, FilterDropdown, SortableHeader } from '$lib/components/ui';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import { managerLogbookService } from '$lib/api/services/managerLogbookService';
	import { toastStore } from '$lib/stores/toast.svelte';

	type SortDir = 'asc' | 'desc';

	const statusOptions = [
		{ label: 'Submitted', value: 'SUBMITTED' },
		{ label: 'Accepted', value: 'ACCEPTED' },
		{ label: 'Rejected', value: 'REJECTED' }
	];

	let logbooks = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let fetchRequestId = 0;
	let refreshNonce = $state(0);

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let status = $derived($page.url.searchParams.get('status') || '');
	let dateFrom = $derived($page.url.searchParams.get('date_from') || '');
	let dateTo = $derived($page.url.searchParams.get('date_to') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as SortDir) || 'desc');

	let dateFromDraft = $state('');
	let dateToDraft = $state('');

	let reviewModalOpen = $state(false);
	let selectedLogbook = $state<any | null>(null);
	let selectedRating = $state(5);
	let selectedDecision = $state<'ACCEPTED' | 'REJECTED'>('ACCEPTED');
	let reviewerComment = $state('');
	let reviewing = $state(false);
	let loadingDetail = $state(false);

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
		if (status) params.status = status;
		if (dateFrom) params.date_from = dateFrom;
		if (dateTo) params.date_to = dateTo;

		(async () => {
			try {
				const response = await staffLogbookService.getLogbooks(params as any);

				if (requestId !== fetchRequestId) {
					return;
				}

				logbooks = Array.isArray(response) ? response : (response as any).data || [];
				meta = (response as any).meta || null;
			} catch (fetchError: any) {
				if (requestId !== fetchRequestId) {
					return;
				}

				console.error('Failed to fetch logbooks', fetchError);
				error = fetchError.message || 'Gagal memuat data logbook.';
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

	function handleStatusFilter(value: string) {
		updateUrl({ status: value });
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

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function getStaffName(logbook: any): string {
		return logbook?.user?.nama ?? '-';
	}

	function getStaffNpp(logbook: any): string {
		return logbook?.user?.npp ?? '-';
	}

	function getRating(logbook: any): number | null {
		const value = logbook?.rating;
		if (typeof value !== 'number') return null;
		if (!Number.isFinite(value)) return null;
		return value;
	}

	function formatDate(value: string | null | undefined): string {
		if (!value) return '-';
		const date = new Date(value);
		if (Number.isNaN(date.getTime())) return '-';
		return date.toLocaleDateString('id-ID');
	}

	function getLogbookDate(logbook: any): string {
		return formatDate(logbook?.tanggal ?? logbook?.created_at);
	}

	function getStatusBadgeClass(logbookStatus: string): string {
		switch (logbookStatus) {
			case 'SUBMITTED':
				return 'badge-warning';
			case 'ACCEPTED':
				return 'badge-success';
			case 'REJECTED':
				return 'badge-error';
			default:
				return 'badge-ghost';
		}
	}

	function getAttachmentUrl(filePath: string): string {
		if (!filePath) return '#';
		if (filePath.startsWith('http')) return filePath;
		return `/storage/${filePath}`;
	}

	function isImageFile(filePath: string): boolean {
		const ext = filePath.split('.').pop()?.toLowerCase() ?? '';
		return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext);
	}

	function isPdfFile(filePath: string): boolean {
		return filePath.split('.').pop()?.toLowerCase() === 'pdf';
	}

	function getFileName(filePath: string): string {
		return filePath.split('/').pop() ?? filePath;
	}

	async function openReview(logbook: any) {
		selectedLogbook = logbook;
		selectedRating = getRating(logbook) ?? 5;
		selectedDecision = 'ACCEPTED';
		reviewerComment = '';
		reviewModalOpen = true;
		loadingDetail = true;

		try {
			const fullLogbook = await staffLogbookService.getLogbookById(String(logbook.id));
			selectedLogbook = fullLogbook;
		} catch (e) {
			console.error('Failed to load logbook detail', e);
		} finally {
			loadingDetail = false;
		}
	}

	async function submitReview() {
		if (!selectedLogbook?.id) return;
		if (!reviewerComment.trim()) {
			toastStore.error('Komentar reviewer wajib diisi.');
			return;
		}

		reviewing = true;
		try {
			await managerLogbookService.reviewLogbook(String(selectedLogbook.id), {
				decision: selectedDecision,
				rating: Number(selectedRating),
				reviewer_comment: reviewerComment.trim()
			});

			toastStore.success(
				selectedDecision === 'ACCEPTED'
					? 'Logbook berhasil disetujui.'
					: 'Logbook berhasil ditolak.'
			);
			reviewModalOpen = false;
			selectedLogbook = null;
			refreshData();
		} catch (reviewError) {
			console.error('Failed to review logbook', reviewError);
			toastStore.error('Gagal menyimpan review logbook.');
		} finally {
			reviewing = false;
		}
	}

</script>

<svelte:head>
	<title>Monitoring Logbooks | Admin</title>
</svelte:head>

<div class="mb-6 space-y-2">
	<h1 class="text-2xl font-bold">Monitoring Logbooks</h1>
	<p class="text-base-content/70">Pantau logbook staff dan lakukan review bila diperlukan.</p>
</div>

<div class="mb-4 flex flex-wrap items-end gap-3">
	<SearchInput
		value={search}
		placeholder="Cari staff (nama / NPP)..."
		onSearch={handleSearch}
		class="min-w-[220px] flex-1"
	/>

	<FilterDropdown
		label="Status"
		options={statusOptions}
		value={status}
		onChange={handleStatusFilter}
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

<DataTable loading={loading} empty={logbooks.length === 0} columnsCount={6}>
	{#snippet head()}
		<tr>
			<SortableHeader
				column="created_at"
				label="Tanggal"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>Staff</th>
			<SortableHeader
				column="status"
				label="Status"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				column="rating"
				label="Rating"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>Dibuat</th>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#each logbooks as logbook (logbook.id)}
		<tr>
			<td>{getLogbookDate(logbook)}</td>
			<td>
				<div class="font-medium">{getStaffName(logbook)}</div>
				<div class="text-xs text-base-content/70">{getStaffNpp(logbook)}</div>
			</td>
			<td>
				<span class="badge {getStatusBadgeClass(logbook.status)}">{logbook.status ?? '-'}</span>
			</td>
			<td>
				{#if getRating(logbook) !== null}
					<span>{getRating(logbook)}/5</span>
				{:else}
					<span class="text-base-content/50">-</span>
				{/if}
			</td>
			<td>{formatDate(logbook.created_at)}</td>
			<td>
				<button class="btn btn-outline btn-sm btn-secondary" onclick={() => openReview(logbook)}>
					Review
				</button>
			</td>
		</tr>
	{/each}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={reviewModalOpen} title="Review Logbook">
	<div class="space-y-4">
		{#if selectedLogbook}
			<div class="text-sm text-base-content/70">
				<div>
					Staff:
					<span class="font-medium text-base-content">{getStaffName(selectedLogbook)}</span>
				</div>
				<div>
					Tanggal:
					<span class="font-medium text-base-content">{getLogbookDate(selectedLogbook)}</span>
				</div>
			</div>

			<div class="divider my-1"></div>
			<h3 class="text-sm font-bold">Progress KPI</h3>

			{#if loadingDetail}
				<div class="space-y-2">
					{#each Array(3) as _}
						<div class="h-12 w-full animate-pulse rounded-lg bg-base-300"></div>
					{/each}
				</div>
			{:else if selectedLogbook.details && selectedLogbook.details.length > 0}
				<div class="max-h-64 space-y-3 overflow-y-auto">
					{#each selectedLogbook.details as detail (detail.id)}
						<div class="rounded-lg border border-base-300 p-3">
							<div class="mb-1 flex items-center justify-between">
								<span class="text-sm font-medium">{detail.kpi_nama}</span>
								<span class="text-xs text-base-content/60">
									{detail.capaian_angka}/{detail.target_angka} {detail.satuan}
								</span>
							</div>
							<progress
								class="progress progress-primary w-full"
								value={detail.target_angka > 0 ? (detail.capaian_angka / detail.target_angka) * 100 : 0}
								max="100"
							></progress>

							{#if detail.lampiran_file}
								{@const url = getAttachmentUrl(detail.lampiran_file)}
								<div class="mt-2 rounded-lg border border-base-200 bg-base-200/50 p-2">
									{#if isImageFile(detail.lampiran_file)}
										<a href={url} target="_blank" rel="noopener noreferrer">
											<img
												src={url}
												alt="Lampiran {detail.kpi_nama}"
												class="max-h-40 w-full rounded-md object-contain"
											/>
										</a>
									{:else if isPdfFile(detail.lampiran_file)}
										<a href={url} target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-primary hover:underline">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-error" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
												<polyline points="14 2 14 8 20 8"/>
												<path d="M10 13v4"/>
												<path d="M14 13v4"/>
												<path d="M10 17h4"/>
											</svg>
											<span class="truncate">{getFileName(detail.lampiran_file)}</span>
										</a>
									{:else}
										<a href={url} target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-primary hover:underline">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
												<polyline points="14 2 14 8 20 8"/>
											</svg>
											<span class="truncate">{getFileName(detail.lampiran_file)}</span>
										</a>
									{/if}
								</div>
							{/if}
						</div>
					{/each}
				</div>
			{:else}
				<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">
					Belum ada KPI
				</div>
			{/if}

			<div class="divider my-1"></div>

			<div class="form-control">
				<label class="label" for="decision-select">
					<span class="label-text font-medium">Keputusan</span>
				</label>
				<select id="decision-select" class="select-bordered select w-full" bind:value={selectedDecision}>
					<option value="ACCEPTED">Setujui (Accepted)</option>
					<option value="REJECTED">Tolak (Rejected)</option>
				</select>
			</div>

			<div class="form-control">
				<label class="label" for="rating-select">
					<span class="label-text font-medium">Rating</span>
				</label>
				<select id="rating-select" class="select-bordered select w-full" bind:value={selectedRating}>
					{#each [1, 2, 3, 4, 5] as value (value)}
						<option value={value}>{value}</option>
					{/each}
				</select>
			</div>

			<div class="form-control">
				<label class="label" for="reviewer-comment">
					<span class="label-text font-medium">Komentar / Catatan <span class="text-error">*</span></span>
				</label>
				<textarea
					id="reviewer-comment"
					class="textarea textarea-bordered h-24 w-full"
					placeholder="Berikan catatan atas capaian logbook ini..."
					bind:value={reviewerComment}
				></textarea>
			</div>
		{/if}
	</div>

	{#snippet actions()}
		<button
			class="btn {selectedDecision === 'REJECTED' ? 'btn-error' : 'btn-primary'}"
			onclick={submitReview}
			disabled={reviewing}
		>
			{reviewing ? 'Menyimpan...' : selectedDecision === 'REJECTED' ? 'Tolak Logbook' : 'Setujui Logbook'}
		</button>
	{/snippet}
</Modal>


