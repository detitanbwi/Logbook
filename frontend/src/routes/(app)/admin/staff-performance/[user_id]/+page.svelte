<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { summaryService } from '$lib/api/services/summaryService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import type { DailyKpiSummary, DailyStaffSummary, Logbook } from '$lib/types';

	function toLocalDateInputValue(date: Date): string {
		const timezoneOffsetMs = date.getTimezoneOffset() * 60_000;
		return new Date(date.getTime() - timezoneOffsetMs).toISOString().split('T')[0];
	}

	function normalizeDateKey(dateString: string | null | undefined): string {
		if (!dateString) return '';
		return dateString.split('T')[0];
	}

	const today = new Date();
	const defaultDateTo = toLocalDateInputValue(today);
	const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
	const defaultDateFrom = toLocalDateInputValue(startOfMonth);

	let userId = $derived.by(() => {
		const params = $page.params as Record<string, string>;
		return params.user_id || params.id || '';
	});
	let dateFrom = $derived.by(() => $page.url.searchParams.get('date_from') || defaultDateFrom);
	let dateTo = $derived.by(() => $page.url.searchParams.get('date_to') || defaultDateTo);

	let dateFromDraft = $state('');
	let dateToDraft = $state('');

	let loading = $state(false);
	let error = $state<string | null>(null);

	let dailySummaries = $state<DailyStaffSummary[]>([]);
	let dailyKpisMap = $state<Record<string, DailyKpiSummary[]>>({});
	let dailyLogbooksMap = $state<Record<string, Logbook[]>>({});

	let selectedDate = $state<string | null>(null);
	let expandedLogbookId = $state<string | null>(null);
	let expandedLogbook = $state<Logbook | null>(null);
	let loadingDetail = $state(false);
	let detailRequestId = 0;

	const selectedSummary = $derived.by(() =>
		selectedDate
			? dailySummaries.find((summary) => normalizeDateKey(summary.tanggal) === selectedDate) ?? null
			: null
	);
	const selectedDayKpis = $derived.by(() => (selectedDate ? dailyKpisMap[selectedDate] ?? [] : []));
	const selectedDayLogbooks = $derived.by(() =>
		selectedDate ? dailyLogbooksMap[selectedDate] ?? [] : []
	);

	const selectedAverageRating = $derived.by(() => {
		const ratedLogbooks = selectedDayLogbooks.filter(
			(logbook) => logbook.rating != null && logbook.status === 'ACCEPTED'
		);
		if (ratedLogbooks.length === 0) return null;

		const totalRating = ratedLogbooks.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0);
		return totalRating / ratedLogbooks.length;
	});

	const selectedAverageKpiPercent = $derived.by(() => {
		const validKpis = selectedDayKpis.filter((kpi) => kpi.target_angka_total > 0);
		if (validKpis.length === 0) return selectedSummary?.progress_percent ?? 0;

		const totalPercent = validKpis.reduce((sum, kpi) => {
			return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
		}, 0);

		return totalPercent / validKpis.length;
	});

	const selectedKpiUnit = $derived.by(() => {
		const uniqueUnits = Array.from(new Set(selectedDayKpis.map((kpi) => kpi.satuan).filter(Boolean)));
		if (uniqueUnits.length === 0) return null;
		if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
		return `Satuan campuran (${uniqueUnits.length} jenis)`;
	});

	$effect(() => {
		dateFromDraft = dateFrom;
		dateToDraft = dateTo;
	});

	$effect(() => {
		loadStaffData();
	});

	async function fetchAllPages<T>(
		fetchPage: (pageNum: number) => Promise<{ data: T[]; meta: { last_page: number } }>
	): Promise<T[]> {
		let pageNum = 1;
		let lastPage = 1;
		const allItems: T[] = [];

		while (pageNum <= lastPage) {
			const response = await fetchPage(pageNum);
			allItems.push(...(response.data ?? []));
			lastPage = response.meta?.last_page ?? pageNum;
			pageNum += 1;
		}

		return allItems;
	}

	function groupKpisByDate(items: DailyKpiSummary[]) {
		return items.reduce<Record<string, DailyKpiSummary[]>>((acc, item) => {
			const dateKey = normalizeDateKey(item.tanggal);
			if (!dateKey) {
				return acc;
			}
			if (!acc[dateKey]) acc[dateKey] = [];
			acc[dateKey].push(item);
			return acc;
		}, {});
	}

	function groupLogbooksByDate(items: Logbook[]) {
		return items.reduce<Record<string, Logbook[]>>((acc, item) => {
			const dateKey = normalizeDateKey(item.tanggal);
			if (!dateKey) {
				return acc;
			}
			if (!acc[dateKey]) acc[dateKey] = [];
			acc[dateKey].push(item);
			return acc;
		}, {});
	}

	async function loadStaffData() {
		if (!userId) return;

		loading = true;
		error = null;
		expandedLogbookId = null;
		expandedLogbook = null;

		try {
			const [dailySummaryData, dailyKpiData, dailyLogbookData] = await Promise.all([
				fetchAllPages((pageNum) =>
					summaryService.dailyByUser(userId, {
						date_from: dateFrom || undefined,
						date_to: dateTo || undefined,
						per_page: 100,
						page: pageNum
					})
				),
				fetchAllPages((pageNum) =>
					summaryService.kpiDaily({
						user_id: userId,
						date_from: dateFrom || undefined,
						date_to: dateTo || undefined,
						per_page: 100,
						page: pageNum
					})
				),
				fetchAllPages((pageNum) =>
					staffLogbookService.getLogbooks({
						user_id: userId,
						date_from: dateFrom || undefined,
						date_to: dateTo || undefined,
						per_page: 100,
						sort_by: 'tanggal',
						sort_dir: 'desc',
						page: pageNum
					})
				)
			]);

			dailySummaries = [...dailySummaryData].sort((a, b) =>
				normalizeDateKey(b.tanggal).localeCompare(normalizeDateKey(a.tanggal))
			);
			dailyKpisMap = groupKpisByDate(dailyKpiData);
			dailyLogbooksMap = groupLogbooksByDate(dailyLogbookData);

			if (dailySummaries.length === 0) {
				selectedDate = null;
			} else if (!selectedDate || !dailySummaries.some((summary) => normalizeDateKey(summary.tanggal) === selectedDate)) {
				selectedDate = normalizeDateKey(dailySummaries[0].tanggal);
			}
		} catch (e: unknown) {
			error = e instanceof Error ? e.message : 'Gagal memuat data detail staff';
		} finally {
			loading = false;
		}
	}

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);

		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
		});

		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function applyDateFilters() {
		updateUrl({ date_from: dateFromDraft, date_to: dateToDraft });
	}

	function clearDateFilters() {
		dateFromDraft = defaultDateFrom;
		dateToDraft = defaultDateTo;
		updateUrl({ date_from: defaultDateFrom, date_to: defaultDateTo });
	}

	function backToList() {
		const query = new URLSearchParams();
		if (dateFrom) query.set('date_from', dateFrom);
		if (dateTo) query.set('date_to', dateTo);
		const suffix = query.toString();
		goto(`/admin/staff-performance${suffix ? `?${suffix}` : ''}`);
	}

	function formatDate(dateString: string): string {
		if (!dateString) return '-';
		return new Date(normalizeDateKey(dateString)).toLocaleDateString('id-ID', {
			day: 'numeric',
			month: 'short',
			year: 'numeric'
		});
	}

	function formatDuration(minutes: number): string {
		const hours = Math.floor(minutes / 60);
		const mins = minutes % 60;
		return `${hours}j ${mins}m`;
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

	const statusClass: Record<string, string> = {
		SUBMITTED: 'badge-info',
		ACCEPTED: 'badge-success',
		REJECTED: 'badge-error'
	};

	async function toggleLogbookDetail(logbookId: string) {
		if (expandedLogbookId === logbookId) {
			expandedLogbookId = null;
			expandedLogbook = null;
			return;
		}

		const requestId = ++detailRequestId;
		expandedLogbookId = logbookId;
		expandedLogbook = null;
		loadingDetail = true;
		try {
			const logbookDetail = await staffLogbookService.getLogbookById(logbookId);
			if (requestId !== detailRequestId) return;
			expandedLogbook = logbookDetail;
		} catch {
			if (requestId === detailRequestId) expandedLogbook = null;
		} finally {
			if (requestId === detailRequestId) loadingDetail = false;
		}
	}
</script>

<svelte:head>
	<title>Detail Performa Staff | Admin</title>
</svelte:head>

<div class="mb-6 flex items-center justify-between">
	<div>
		<h1 class="text-2xl font-bold">Detail Performa Staff</h1>
		<p class="text-base-content/70">Analisis harian KPI, rating, dan logbook</p>
	</div>
	<button class="btn btn-outline" onclick={backToList}>Kembali ke Daftar</button>
</div>

<div class="mb-4 flex flex-wrap items-end gap-3">
	<div class="form-control">
		<label class="label" for="date_from"><span class="label-text">Dari</span></label>
		<input id="date_from" type="date" class="input input-bordered input-sm" bind:value={dateFromDraft} />
	</div>
	<div class="form-control">
		<label class="label" for="date_to"><span class="label-text">Sampai</span></label>
		<input id="date_to" type="date" class="input input-bordered input-sm" bind:value={dateToDraft} />
	</div>
	<div class="flex gap-2">
		<button class="btn btn-sm" onclick={applyDateFilters}>Terapkan</button>
		<button class="btn btn-outline btn-sm" onclick={clearDateFilters}>Reset</button>
	</div>
</div>

{#if loading}
	<div class="space-y-4">
		{#each Array(4) as _}
			<div class="h-20 w-full animate-pulse rounded bg-base-300"></div>
		{/each}
	</div>
{:else if error}
	<div class="alert alert-error">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={loadStaffData}>Coba Lagi</button>
	</div>
{:else if !selectedSummary}
	<div class="rounded-lg border border-base-300 p-4 text-base-content/60">Tidak ada data pada periode ini.</div>
{:else}
	<PerformanceSummaryCard
		totalLogbooks={selectedSummary.total_logbooks}
		totalWorkMinutes={selectedSummary.total_work_minutes}
		progressPercent={selectedAverageKpiPercent}
		progressUnit={selectedKpiUnit}
		averageRating={selectedAverageRating}
	/>

	<div class="divider">Rekap Harian</div>
	<div class="mb-6 flex flex-wrap gap-2">
		{#each dailySummaries as summary (summary.id)}
			<button
				type="button"
				class="btn btn-sm {selectedDate === normalizeDateKey(summary.tanggal) ? 'btn-primary' : 'btn-outline'}"
				onclick={() => {
					selectedDate = normalizeDateKey(summary.tanggal);
					expandedLogbookId = null;
					expandedLogbook = null;
				}}
			>
				{formatDate(summary.tanggal)}
			</button>
		{/each}
	</div>

	<div class="grid gap-6 lg:grid-cols-2">
		<div class="space-y-3">
			<h3 class="text-lg font-semibold">Ringkasan KPI per Hari</h3>
			{#if selectedDayKpis.length === 0}
				<div class="rounded-lg border border-base-300 p-3 text-base-content/60">Belum ada KPI.</div>
			{:else}
				{#each selectedDayKpis as kpi (kpi.id)}
					<KpiProgressBar
						nama={kpi.kpi_nama}
						capaian={kpi.capaian_angka_total}
						target={kpi.target_angka_total}
						satuan={kpi.satuan}
					/>
				{/each}
			{/if}
		</div>

		<div class="space-y-3">
			<h3 class="text-lg font-semibold">Logbook Hari Terpilih</h3>
			{#if selectedDayLogbooks.length === 0}
				<div class="rounded-lg border border-base-300 p-3 text-base-content/60">Belum ada logbook.</div>
			{:else}
				{#each selectedDayLogbooks as logbook (logbook.id)}
					<div class="rounded-lg border border-base-300">
						<button
							type="button"
							class="flex w-full items-center justify-between p-3 text-left hover:bg-base-200"
							onclick={() => toggleLogbookDetail(logbook.id)}
						>
							<div>
								<div class="font-medium">{logbook.start_kerja?.slice(0, 5) || '--:--'} - {logbook.end_kerja?.slice(0, 5) || '--:--'}</div>
								<div class="text-xs text-base-content/60">{logbook.lokasi || 'Lokasi tidak diisi'}</div>
								{#if logbook.rating != null}
									<div class="text-xs text-warning">⭐ {Number(logbook.rating).toFixed(1)}/5</div>
								{/if}
							</div>
							<span class="badge badge-sm {statusClass[logbook.status] ?? ''}">{logbook.status}</span>
						</button>

						{#if expandedLogbookId === logbook.id}
							<div class="border-t border-base-300 p-3">
								{#if loadingDetail}
									<div class="h-10 w-full animate-pulse rounded bg-base-300"></div>
								{:else if expandedLogbook?.details?.length}
									<div class="space-y-3">
										{#each expandedLogbook.details as detail (detail.id)}
											<div>
												<KpiProgressBar nama={detail.kpi_nama} capaian={detail.capaian_angka} target={detail.target_angka} satuan={detail.satuan} />
												{#if detail.lampiran_file}
													{@const url = getAttachmentUrl(detail.lampiran_file)}
													<div class="mt-1.5">
														{#if isImageFile(detail.lampiran_file)}
															<a href={url} target="_blank" rel="noopener noreferrer"><img src={url} alt={getFileName(detail.lampiran_file)} class="h-24 rounded border border-base-300 object-cover" /></a>
														{:else if isPdfFile(detail.lampiran_file)}
															<a href={url} target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline">PDF: {getFileName(detail.lampiran_file)}</a>
														{:else}
															<a href={url} target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline">File: {getFileName(detail.lampiran_file)}</a>
														{/if}
													</div>
												{/if}
											</div>
										{/each}
									</div>
								{:else}
									<div class="text-sm text-base-content/60">Belum ada detail KPI.</div>
								{/if}
							</div>
						{/if}
					</div>
				{/each}
			{/if}
		</div>
	</div>
{/if}
