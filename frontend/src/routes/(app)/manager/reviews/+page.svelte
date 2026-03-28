<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { managerLogbookService } from '$lib/api';
	import { SearchInput, SortableHeader } from '$lib/components/ui';
	import StatusBadge from '$lib/components/ui/StatusBadge.svelte';
	import Modal from '$lib/components/ui/Modal.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';

	let pendingReviews = $state<any[]>([]);
	let meta = $state<any>(null);
	let isLoading = $state(true);
	let errorMsg = $state<string | null>(null);

	// Modal state
	let isModalOpen = $state(false);
	let selectedLogbook = $state<any>(null);
	let rating = $state<number>(5);
	let decision = $state('ACCEPTED');
	let reviewerComment = $state('');
	let isSubmitting = $state(false);

	const selectedCompletedDetails = $derived.by(() => {
		if (!selectedLogbook || !Array.isArray(selectedLogbook.details)) {
			return [];
		}

		return selectedLogbook.details.filter((detail: Record<string, unknown>) => {
			const capaian = Number(detail.capaian_angka || 0);
			return capaian > 0;
		});
	});

	// URL params
	let currentPage = $derived.by(() => Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived.by(() => Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived.by(() => $page.url.searchParams.get('search') || '');
	let dateFrom = $derived.by(() => $page.url.searchParams.get('date_from') || '');
	let dateTo = $derived.by(() => $page.url.searchParams.get('date_to') || '');
	let sortBy = $derived.by(() => $page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived.by(
		() => ($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc'
	);

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);
		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
		});
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	async function fetchPendingReviews() {
		isLoading = true;
		errorMsg = null;
		try {
			const params: Record<string, unknown> = {
				page: currentPage,
				per_page: perPage
			};

			if (search) params.search = search;
			if (dateFrom) params.date_from = dateFrom;
			if (dateTo) params.date_to = dateTo;
			if (sortBy) params.sort_by = sortBy;
			if (sortDir) params.sort_dir = sortDir;

			const res = await managerLogbookService.getPendingReviews(params);
			pendingReviews = Array.isArray(res) ? res : res.data || [];
			meta = (res as any).meta || null;
		} catch (err: any) {
			errorMsg = err.message || 'Gagal memuat daftar review';
		} finally {
			isLoading = false;
		}
	}

	$effect(() => {
		fetchPendingReviews();
	});

	function openReviewModal(logbook: any) {
		selectedLogbook = logbook;
		rating = 5;
		decision = 'ACCEPTED';
		reviewerComment = '';
		isModalOpen = true;
	}

	async function submitReview() {
		if (!selectedLogbook || !reviewerComment.trim()) return;
		isSubmitting = true;
		try {
			await managerLogbookService.reviewLogbook(selectedLogbook.id, {
				decision,
				rating,
				reviewer_comment: reviewerComment
			});
			isModalOpen = false;
			await fetchPendingReviews();
		} catch (err: any) {
			errorMsg = err.message || 'Gagal menyimpan penilaian';
		} finally {
			isSubmitting = false;
		}
	}
</script>

<svelte:head>
	<title>Review Logbook | Manager</title>
</svelte:head>

<div class="flex flex-col gap-6">
	<div class="flex items-center justify-between">
		<h1 class="text-3xl font-bold">Review Logbook Tim</h1>
		<button class="btn btn-primary" onclick={() => fetchPendingReviews()} disabled={isLoading}>
			Refresh
		</button>
	</div>

	{#if errorMsg}
		<div class="alert alert-error shadow-sm">
			<span>{errorMsg}</span>
			<button class="btn btn-ghost btn-sm" onclick={() => fetchPendingReviews()}>Coba Lagi</button>
		</div>
	{/if}

	<div class="mb-4 flex flex-wrap gap-4">
		<SearchInput
			value={search}
			placeholder="Cari nama staff..."
			onSearch={(v) => updateUrl({ search: v })}
		/>
		<div class="flex items-center gap-2">
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

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body p-0">
			{#if isLoading}
				<div class="overflow-x-auto rounded-box">
					<table class="table w-full table-zebra">
						<thead class="bg-base-200 text-base-content">
							<tr>
								<th>Tanggal</th>
								<th>Pegawai</th>
								<th>Progress KPI</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							{#each Array(5) as _ (_)}
								<tr>
									<td>
										<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-4 w-12 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-5 w-20 animate-pulse rounded bg-base-300"></div>
									</td>
									<td>
										<div class="h-7 w-20 animate-pulse rounded bg-base-300"></div>
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
								<th>Pegawai</th>
								<th>Progress KPI</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							{#if pendingReviews.length === 0}
								<tr>
									<td colspan="5" class="py-8 text-center text-base-content/60">
										Tidak ada logbook yang menunggu review.
									</td>
								</tr>
							{:else}
								{#each pendingReviews as log (log.id)}
									<tr>
										<td>
											{new Date(log.created_at || log.tanggal).toLocaleDateString('id-ID', {
												year: 'numeric',
												month: 'short',
												day: 'numeric'
											})}
										</td>
										<td>
											<div class="font-medium">
												{log.user?.nama ?? log.user?.name ?? 'Unknown User'}
											</div>
											<div class="text-xs opacity-70">{log.user?.role || 'Staff'}</div>
										</td>
										<td>
											{#if Array.isArray(log.details) && log.details.length > 0}
												{log.details.reduce(
													(acc: number, d: Record<string, unknown>) =>
														acc + (Number(d.capaian_angka) || 0),
													0
												)} / {log.details.reduce(
													(acc: number, d: Record<string, unknown>) =>
														acc + (Number(d.target_angka) || 0),
													0
												)}
											{:else}
												-
											{/if}
										</td>
										<td>
											<StatusBadge status={log.status} />
										</td>
										<td>
											<button
												class="btn btn-outline btn-sm btn-primary"
												onclick={() => openReviewModal(log)}
											>
												Review
											</button>
										</td>
									</tr>
								{/each}
							{/if}
						</tbody>
					</table>
				</div>
				<div class="p-4">
					<Pagination {meta} onPageSizeChange={handlePageSizeChange} />
				</div>
			{/if}
		</div>
	</div>
</div>

<Modal
	bind:isOpen={isModalOpen}
	title="Review Logbook Harian"
	panelClass="w-11/12 max-w-[96rem] h-[88vh] max-h-[88vh]"
>
	{#if selectedLogbook}
		<div class="flex h-full flex-col gap-4">
			<div class="flex items-center justify-between">
				<div>
					<div class="font-semibold">
						{selectedLogbook.user?.nama ?? selectedLogbook.user?.name ?? 'Staff'}
					</div>
					<div class="text-sm text-base-content/70">
						{new Date(selectedLogbook.created_at || selectedLogbook.tanggal).toLocaleDateString(
							'id-ID',
							{
								weekday: 'long',
								year: 'numeric',
								month: 'long',
								day: 'numeric'
							}
						)}
					</div>
				</div>
				<StatusBadge status={selectedLogbook.status} />
			</div>

			<div class="divider my-0"></div>

			<div class="grid min-h-0 flex-1 gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
				<div class="space-y-4">
					<div>
						<h4 class="mb-2 font-semibold">Daftar Tugas Selesai (KPI)</h4>
						{#if selectedCompletedDetails.length > 0}
							<ul class="max-h-[52vh] list-none space-y-3 overflow-y-auto pr-1">
								{#each selectedCompletedDetails as kpi}
									<li class="bg-base-50 flex flex-col gap-1 rounded-lg border border-base-200 p-3">
										<div class="flex items-center justify-between gap-2">
											<div class="font-medium">{kpi.kpi?.nama || 'Tugas tanpa nama'}</div>
											<span class="badge badge-sm badge-success">Selesai</span>
										</div>
										<div class="flex items-center gap-2 text-sm text-base-content/70">
											<progress
												class="progress w-24 progress-primary"
												value={kpi.capaian_angka || 0}
												max={kpi.target_angka || 1}
											></progress>
											<span
												>{kpi.capaian_angka || 0} / {kpi.target_angka || 0}
												{kpi.kpi?.satuan || ''}</span
											>
										</div>
									</li>
								{/each}
							</ul>
						{:else}
							<p class="text-sm text-base-content/60">Belum ada chores/KPI yang selesai.</p>
						{/if}
					</div>

					<div class="divider my-0"></div>

					<div>
						<h4 class="mb-1 text-sm font-semibold">Lokasi</h4>
						<p class="rounded bg-base-200 p-2 font-mono text-sm">
							{selectedLogbook.lokasi || '-'}
						</p>
					</div>
				</div>

				<div class="space-y-4 rounded-lg border border-base-300 bg-base-100 p-4">
					<h4 class="mb-1 font-semibold">Beri Penilaian</h4>

					<div class="form-control w-full">
						<label class="label" for="review-decision-group">
							<span class="label-text font-medium">Keputusan</span>
						</label>
						<div
							id="review-decision-group"
							class="flex gap-4"
							role="group"
							aria-label="Keputusan review"
						>
							<label class="label cursor-pointer gap-2">
								<input
									type="radio"
									name="decision"
									class="radio radio-success"
									value="ACCEPTED"
									bind:group={decision}
								/>
								<span class="label-text">Terima (Accept)</span>
							</label>
							<label class="label cursor-pointer gap-2">
								<input
									type="radio"
									name="decision"
									class="radio radio-error"
									value="REJECTED"
									bind:group={decision}
								/>
								<span class="label-text">Tolak (Reject)</span>
							</label>
						</div>
					</div>

					<div class="form-control w-full">
						<label class="label" for="review-rating-group">
							<span class="label-text font-medium">Rating</span>
						</label>
						<div
							id="review-rating-group"
							class="rating-lg rating"
							role="group"
							aria-label="Rating review"
						>
							{#each [1, 2, 3, 4, 5] as r}
								<input
									type="radio"
									name="rating-2"
									class="mask mask-star-2 {r <= 2
										? 'bg-error'
										: r === 3
											? 'bg-warning'
											: 'bg-success'}"
									value={r}
									bind:group={rating}
								/>
							{/each}
						</div>
						<p class="mt-1 text-sm text-base-content/70">{rating} Bintang</p>
					</div>

					<div class="form-control w-full">
						<label class="label" for="reviewerComment">
							<span class="label-text font-medium">Komentar <span class="text-error">*</span></span>
						</label>
						<textarea
							id="reviewerComment"
							class="textarea-bordered textarea h-24"
							placeholder="Berikan komentar penilaian..."
							bind:value={reviewerComment}
						></textarea>
					</div>
				</div>
			</div>
		</div>
	{/if}

	{#snippet actions()}
		<button
			class="btn btn-success"
			onclick={submitReview}
			disabled={isSubmitting || !reviewerComment.trim()}
		>
			Simpan Penilaian
		</button>
	{/snippet}
</Modal>
