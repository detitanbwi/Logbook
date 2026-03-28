<script lang="ts">
	import SlideOutDrawer from '$lib/components/ui/SlideOutDrawer.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
	import { summaryService } from '$lib/api/services/summaryService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import type { DailyKpiSummary, DailyStaffSummary, Logbook } from '$lib/types';
	import { resolveStorageUrl } from '$lib/utils/asset-url';

	function normalizeDateKey(dateString: string | null | undefined): string {
		if (!dateString) return '';
		return dateString.split('T')[0];
	}

	interface StaffInfo {
		user_id: string;
		nama: string;
		npp: string;
		date_from?: string;
		date_to?: string;
	}

	let {
		isOpen = $bindable(false),
		staff
	}: {
		isOpen: boolean;
		staff: StaffInfo | null;
	} = $props();

	let loading = $state(false);
	let error = $state<string | null>(null);
	let dailySummaries = $state<DailyStaffSummary[]>([]);
	let dailyKpisMap = $state<Record<string, DailyKpiSummary[]>>({});
	let dailyLogbooksMap = $state<Record<string, Logbook[]>>({});
	let selectedDate = $state<string | null>(null);
	let view = $state<'summary' | 'detail'>('summary');

	let expandedLogbookId = $state<string | null>(null);
	let expandedLogbook = $state<Logbook | null>(null);
	let loadingDetail = $state(false);
	let loadRequestId = 0;
	let detailRequestId = 0;

	const selectedSummary = $derived(
		selectedDate
			? dailySummaries.find((summary) => normalizeDateKey(summary.tanggal) === selectedDate) ?? null
			: null
	);

	const selectedDayKpis = $derived(selectedDate ? dailyKpisMap[selectedDate] ?? [] : []);
	const selectedDayLogbooks = $derived(selectedDate ? dailyLogbooksMap[selectedDate] ?? [] : []);

	const selectedAverageRating = $derived.by(() => {
		const ratedLogbooks = selectedDayLogbooks.filter((logbook) => logbook.rating != null);
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
		if (isOpen && staff) {
			loadStaffData(staff.user_id);
		}
	});

	async function loadStaffData(userId: string) {
		const requestId = ++loadRequestId;
		loading = true;
		error = null;
		view = 'summary';
		selectedDate = null;
		expandedLogbookId = null;
		expandedLogbook = null;
		try {
			const [dailySummaryData, dailyKpiData, dailyLogbookData] = await Promise.all([
				fetchAllPages((page) =>
					summaryService.dailyByUser(userId, {
						date_from: staff?.date_from,
						date_to: staff?.date_to,
						per_page: 100,
						page
					})
				),
				fetchAllPages((page) =>
					summaryService.kpiDaily({
						user_id: userId,
						date_from: staff?.date_from,
						date_to: staff?.date_to,
						per_page: 100,
						page
					})
				),
				fetchAllPages((page) =>
					staffLogbookService.getLogbooks({
						user_id: userId,
						date_from: staff?.date_from,
						date_to: staff?.date_to,
						per_page: 100,
						sort_by: 'tanggal',
						sort_dir: 'desc',
						page
					})
				)
			]);

			if (requestId !== loadRequestId) {
				return;
			}

			dailySummaries = [...dailySummaryData].sort((a, b) =>
				normalizeDateKey(b.tanggal).localeCompare(normalizeDateKey(a.tanggal))
			);
			dailyKpisMap = groupKpisByDate(dailyKpiData);
			dailyLogbooksMap = groupLogbooksByDate(dailyLogbookData);
		} catch (e: unknown) {
			if (requestId !== loadRequestId) {
				return;
			}
			error = e instanceof Error ? e.message : 'Gagal memuat data staff';
		} finally {
			if (requestId === loadRequestId) {
				loading = false;
			}
		}
	}

	async function fetchAllPages<T>(
		fetchPage: (page: number) => Promise<{ data: T[]; meta: { last_page: number } }>
	): Promise<T[]> {
		let page = 1;
		let lastPage = 1;
		const allItems: T[] = [];

		while (page <= lastPage) {
			const response = await fetchPage(page);
			allItems.push(...(response.data ?? []));
			lastPage = response.meta?.last_page ?? page;
			page += 1;
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

	function openDailyDetail(dateKey: string) {
		selectedDate = normalizeDateKey(dateKey);
		view = 'detail';
		expandedLogbookId = null;
		expandedLogbook = null;
	}

	function backToSummaries() {
		view = 'summary';
		selectedDate = null;
		expandedLogbookId = null;
		expandedLogbook = null;
	}

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
			if (requestId !== detailRequestId) {
				return;
			}
			expandedLogbook = logbookDetail;
		} catch {
			if (requestId === detailRequestId) {
				expandedLogbook = null;
			}
		} finally {
			if (requestId === detailRequestId) {
				loadingDetail = false;
			}
		}
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

	function getAverageRatingForDate(dateKey: string): number | null {
		const ratedLogbooks = (dailyLogbooksMap[dateKey] ?? []).filter((logbook) => logbook.rating != null);
		if (ratedLogbooks.length === 0) return null;

		const totalRating = ratedLogbooks.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0);
		return totalRating / ratedLogbooks.length;
	}

	function getAverageKpiPercentForDate(dateKey: string): number {
		const validKpis = (dailyKpisMap[dateKey] ?? []).filter((kpi) => kpi.target_angka_total > 0);
		if (validKpis.length === 0) {
			return dailySummaries.find((summary) => normalizeDateKey(summary.tanggal) === dateKey)?.progress_percent ?? 0;
		}

		const totalPercent = validKpis.reduce((sum, kpi) => {
			return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
		}, 0);

		return totalPercent / validKpis.length;
	}

	function getKpiUnitLabelForDate(dateKey: string): string | null {
		const uniqueUnits = Array.from(
			new Set((dailyKpisMap[dateKey] ?? []).map((kpi) => kpi.satuan).filter(Boolean))
		);
		if (uniqueUnits.length === 0) return null;
		if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
		return `Satuan campuran (${uniqueUnits.length} jenis)`;
	}

	function hasMixedUnits(dateKey: string): boolean {
		const uniqueUnits = Array.from(
			new Set((dailyKpisMap[dateKey] ?? []).map((kpi) => kpi.satuan).filter(Boolean))
		);
		return uniqueUnits.length > 1;
	}

	function getAttachmentUrl(filePath: string): string {
		return resolveStorageUrl(filePath);
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
</script>

<SlideOutDrawer bind:isOpen title={staff?.nama ?? 'Staff Detail'} width="max-w-lg">
	{#if !staff}
		<div class="flex h-32 items-center justify-center text-base-content/50">Pilih staff</div>
	{:else if loading}
		<div class="space-y-4">
			{#each Array(4) as _}
				<div class="h-16 w-full animate-pulse rounded-lg bg-base-300"></div>
			{/each}
		</div>
	{:else if error}
		<div class="alert alert-error">
			<span>{error}</span>
			<button class="btn btn-ghost btn-sm" onclick={() => staff && loadStaffData(staff.user_id)}>Coba Lagi</button>
		</div>
	{:else}
		<div class="mb-4 rounded-lg border border-base-300 bg-base-200/50 p-3">
			<div class="text-sm font-medium">{staff.nama}</div>
			<div class="text-xs text-base-content/60">NPP: {staff.npp}</div>
			{#if staff.date_from || staff.date_to}
				<div class="mt-1 text-[11px] text-base-content/50">
					Periode: {staff.date_from || 'awal'} - {staff.date_to || 'hari ini'}
				</div>
			{/if}
		</div>

		{#if view === 'summary'}
			<div class="space-y-3">
				<h3 class="text-sm font-bold">Rekap Harian</h3>
				{#if dailySummaries.length === 0}
					<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">
						Belum ada rekap harian pada periode ini
					</div>
				{:else}
					{#each dailySummaries as summary (summary.id)}
						<button
							type="button"
							class="w-full rounded-xl border border-base-300 bg-base-100 p-4 text-left transition hover:border-primary"
							onclick={() => openDailyDetail(summary.tanggal)}
						>
							<div class="flex items-start justify-between gap-3">
								<div>
									<div class="text-sm font-semibold">{formatDate(summary.tanggal)}</div>
									<div class="mt-1 text-xs text-base-content/60">
										{summary.total_logbooks} logbook • {summary.total_kpi} KPI • {formatDuration(summary.total_work_minutes)}
									</div>
								</div>
								<div class="text-sm text-base-content/40">→</div>
							</div>

							<div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
								<div class="rounded-lg bg-base-200/70 px-3 py-2">
									<div class="text-[11px] text-base-content/60">Rata-rata KPI</div>
									<div class="font-semibold">{Math.round(getAverageKpiPercentForDate(summary.tanggal))}%</div>
									<div class="mt-1 text-[11px] text-base-content/50 line-clamp-1">{getKpiUnitLabelForDate(summary.tanggal) || '-'}</div>
								</div>
								<div class="rounded-lg bg-base-200/70 px-3 py-2">
									<div class="text-[11px] text-base-content/60">Rata-rata Bintang</div>
									<div class="font-semibold">{#if getAverageRatingForDate(summary.tanggal) != null}⭐ {getAverageRatingForDate(summary.tanggal)?.toFixed(1)}{:else}-{/if}</div>
								</div>
								<div class="rounded-lg bg-base-200/70 px-3 py-2">
									<div class="text-[11px] text-base-content/60">Ringkasan Total</div>
									{#if hasMixedUnits(summary.tanggal)}
										<div class="font-semibold">Lihat detail KPI</div>
										<div class="mt-1 text-[11px] text-base-content/50">Total mentah disembunyikan karena satuan campuran</div>
									{:else}
										<div class="font-semibold">{summary.capaian_angka_total} / {summary.target_angka_total}</div>
										<div class="mt-1 text-[11px] text-base-content/50">{getKpiUnitLabelForDate(summary.tanggal) || '-'}</div>
									{/if}
								</div>
							</div>

							<div class="mt-3 flex flex-wrap gap-2">
								<span class="badge badge-neutral">Total: {summary.total_logbooks}</span>
								<span class="badge {statusClass.SUBMITTED}">Submitted: {summary.submitted_logbooks}</span>
								<span class="badge {statusClass.ACCEPTED}">Diterima: {summary.accepted_logbooks}</span>
								<span class="badge {statusClass.REJECTED}">Ditolak: {summary.rejected_logbooks}</span>
							</div>
						</button>
					{/each}
				{/if}
			</div>
		{:else if selectedSummary}
			<div class="space-y-4">
				<div class="flex items-center gap-2">
					<button type="button" class="btn btn-sm btn-ghost btn-circle" onclick={backToSummaries}>←</button>
					<div>
						<h3 class="text-sm font-bold">Detail Rekap Harian</h3>
						<p class="text-xs text-base-content/60">{formatDate(selectedSummary.tanggal)}</p>
					</div>
				</div>

				<PerformanceSummaryCard
					totalLogbooks={selectedSummary.total_logbooks}
					totalWorkMinutes={selectedSummary.total_work_minutes}
					progressPercent={selectedAverageKpiPercent}
					progressUnit={selectedKpiUnit}
					averageRating={selectedAverageRating}
				/>

				<div class="space-y-2">
					<h4 class="text-sm font-semibold">Ringkasan KPI per Hari</h4>
					{#if selectedDayKpis.length === 0}
						<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">Belum ada KPI pada hari ini</div>
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

				<div class="space-y-2">
					<h4 class="text-sm font-semibold">Summary Logbook Hari Itu</h4>
					{#if selectedDayLogbooks.length === 0}
						<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">Belum ada logbook pada hari ini</div>
					{:else}
						{#each selectedDayLogbooks as logbook (logbook.id)}
							<div class="rounded-lg border border-base-300 transition-colors hover:bg-base-200/30">
								<button
									type="button"
									class="flex w-full items-center justify-between p-3 text-left"
									onclick={() => toggleLogbookDetail(logbook.id)}
								>
									<div>
										<div class="text-sm font-medium">{logbook.start_kerja.slice(0, 5)} - {logbook.end_kerja?.slice(0, 5) || '--:--'}</div>
										<div class="text-xs text-base-content/60">{logbook.lokasi || 'Lokasi tidak diisi'}</div>
										{#if logbook.rating != null}
											<div class="text-xs text-warning">⭐ {Number(logbook.rating).toFixed(1)}/5</div>
										{/if}
									</div>
									<div class="flex items-center gap-2">
										<span class="badge badge-sm {statusClass[logbook.status] ?? ''}">{logbook.status}</span>
										<svg class="h-4 w-4 text-base-content/40 transition-transform {expandedLogbookId === logbook.id ? 'rotate-180' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
										</svg>
									</div>
								</button>

								{#if expandedLogbookId === logbook.id}
									<div class="border-t border-base-300 px-3 pb-3 pt-2">
										{#if loadingDetail}
											<div class="space-y-2">
												{#each Array(2) as _}
													<div class="h-10 w-full animate-pulse rounded bg-base-300"></div>
												{/each}
											</div>
										{:else if expandedLogbook && expandedLogbook.details && expandedLogbook.details.length > 0}
											<div class="mb-2 text-xs font-semibold text-base-content/60">Progress KPI</div>
											<div class="space-y-3">
												{#each expandedLogbook.details as detail (detail.id)}
													<div>
														<KpiProgressBar nama={detail.kpi_nama} capaian={detail.capaian_angka} target={detail.target_angka} satuan={detail.satuan} />
														{#if detail.lampiran_file}
															{@const url = getAttachmentUrl(detail.lampiran_file)}
															<div class="mt-1.5 pl-3">
																{#if isImageFile(detail.lampiran_file)}
																	<a href={url} target="_blank" rel="noopener noreferrer" class="group block">
																		<img src={url} alt={getFileName(detail.lampiran_file)} class="h-20 w-auto rounded border border-base-300 object-cover transition-opacity group-hover:opacity-80" />
																	</a>
																{:else if isPdfFile(detail.lampiran_file)}
																	<a href={url} target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-md border border-base-300 bg-base-200/50 px-2 py-1 text-xs text-base-content/70 transition-colors hover:bg-base-300">
																		<svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /><path d="M10 13h4" /><path d="M10 17h4" /></svg>
																		{getFileName(detail.lampiran_file)}
																	</a>
																{:else}
																	<a href={url} target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-md border border-base-300 bg-base-200/50 px-2 py-1 text-xs text-base-content/70 transition-colors hover:bg-base-300">
																		<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /></svg>
																		{getFileName(detail.lampiran_file)}
																	</a>
																{/if}
															</div>
														{/if}
													</div>
												{/each}
											</div>
										{:else if expandedLogbook}
											<div class="text-center text-xs text-base-content/50">Belum ada detail KPI</div>
										{:else}
											<div class="text-center text-xs text-error">Gagal memuat detail</div>
										{/if}
									</div>
								{/if}
							</div>
						{/each}
					{/if}
				</div>
			</div>
		{/if}
	{/if}
</SlideOutDrawer>
