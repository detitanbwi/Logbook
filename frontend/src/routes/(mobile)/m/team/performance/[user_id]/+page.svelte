<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { summaryService } from '$lib/api/services/summaryService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import type { DailyKpiSummary, DailyStaffSummary, Logbook } from '$lib/types';

	function normalizeDateKey(dateString: string | null | undefined): string {
		if (!dateString) return '';
		return dateString.split('T')[0];
	}

	const today = new Date();
	const defaultDateTo = new Date(today.getTime() - today.getTimezoneOffset() * 60000)
		.toISOString()
		.split('T')[0];
	const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
	const defaultDateFrom = new Date(
		startOfMonth.getTime() - startOfMonth.getTimezoneOffset() * 60000
	)
		.toISOString()
		.split('T')[0];

	let userId = $derived.by(() => $page.params.user_id || '');
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

	const selectedSummary = $derived.by(() =>
		selectedDate
			? (dailySummaries.find((summary) => normalizeDateKey(summary.tanggal) === selectedDate) ??
				null)
			: null
	);
	const selectedDayKpis = $derived.by(() =>
		selectedDate ? (dailyKpisMap[selectedDate] ?? []) : []
	);
	const selectedDayLogbooks = $derived.by(() =>
		selectedDate ? (dailyLogbooksMap[selectedDate] ?? []) : []
	);

	const selectedAverageRating = $derived.by(() => {
		const rated = selectedDayLogbooks.filter(
			(logbook) => logbook.rating != null && logbook.status === 'ACCEPTED'
		);
		if (rated.length === 0) return null;
		return rated.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0) / rated.length;
	});

	const selectedAverageKpiPercent = $derived.by(() => {
		const validKpis = selectedDayKpis.filter((kpi) => kpi.target_angka_total > 0);
		if (validKpis.length === 0) return selectedSummary?.progress_percent ?? 0;
		const total = validKpis.reduce((sum, kpi) => {
			return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
		}, 0);
		return total / validKpis.length;
	});

	$effect(() => {
		dateFromDraft = dateFrom;
		dateToDraft = dateTo;
	});

	$effect(() => {
		if (!userId) return;
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

	function groupByDate<T extends { tanggal: string }>(items: T[]) {
		return items.reduce<Record<string, T[]>>((acc, item) => {
			const dateKey = normalizeDateKey(item.tanggal);
			if (!dateKey) return acc;
			if (!acc[dateKey]) acc[dateKey] = [];
			acc[dateKey].push(item);
			return acc;
		}, {});
	}

	async function loadStaffData() {
		loading = true;
		error = null;

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
			dailyKpisMap = groupByDate(dailyKpiData);
			dailyLogbooksMap = groupByDate(dailyLogbookData);

			if (dailySummaries.length === 0) {
				selectedDate = null;
			} else if (
				!selectedDate ||
				!dailySummaries.some((summary) => normalizeDateKey(summary.tanggal) === selectedDate)
			) {
				selectedDate = normalizeDateKey(dailySummaries[0].tanggal);
			}
		} catch (err: unknown) {
			error = err instanceof Error ? err.message : 'Gagal memuat data performa staff.';
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

	function formatDate(dateString: string): string {
		if (!dateString) return '-';
		return new Date(normalizeDateKey(dateString)).toLocaleDateString('id-ID', {
			weekday: 'short',
			day: 'numeric',
			month: 'short',
			year: 'numeric'
		});
	}
</script>

<div class="min-h-screen bg-base-200 pb-24">
	<div class="navbar sticky top-0 z-10 bg-base-100 shadow-sm">
		<div class="flex-none">
			<button
				class="btn btn-square btn-ghost"
				aria-label="Kembali ke Team Performance"
				onclick={() => goto('/m/team/performance')}
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					stroke-width="1.5"
					stroke="currentColor"
					class="h-6 w-6"
				>
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			</button>
		</div>
		<div class="flex-1">
			<h1 class="text-lg font-bold">Detail Performa Staff</h1>
		</div>
	</div>

	<main class="space-y-4 p-4">
		<div
			class="rounded-2xl border border-primary/15 bg-gradient-to-br from-primary/10 via-base-100 to-base-100 p-4 shadow-sm"
		>
			<div class="text-xs font-semibold tracking-[0.12em] text-primary/80 uppercase">
				User Monitor
			</div>
			<h2 class="mt-1 text-xl font-bold">Detail Performa Staff</h2>
			<p class="mt-1 text-sm text-base-content/70">
				Filter tanggal untuk melihat ringkasan KPI dan progres harian staff ini.
			</p>
		</div>

		<div class="flex flex-wrap items-end gap-2">
			<div class="form-control">
				<label class="label" for="date_from"><span class="label-text">Dari</span></label>
				<input
					id="date_from"
					type="date"
					class="input-bordered input input-sm"
					bind:value={dateFromDraft}
				/>
			</div>
			<div class="form-control">
				<label class="label" for="date_to"><span class="label-text">Sampai</span></label>
				<input
					id="date_to"
					type="date"
					class="input-bordered input input-sm"
					bind:value={dateToDraft}
				/>
			</div>
			<div class="flex gap-2">
				<button class="btn btn-sm" onclick={applyDateFilters}>Terapkan</button>
				<button class="btn btn-outline btn-sm" onclick={clearDateFilters}>Reset</button>
			</div>
		</div>

		{#if loading}
			<div class="flex justify-center py-10">
				<span class="loading loading-lg loading-spinner text-primary"></span>
			</div>
		{:else if error}
			<div class="alert alert-error">
				<span>{error}</span>
			</div>
		{:else if !selectedSummary}
			<div class="rounded-lg border border-base-300 bg-base-100 p-4 text-base-content/60">
				Tidak ada data performa pada periode ini.
			</div>
		{:else}
			<PerformanceSummaryCard
				totalLogbooks={selectedSummary.total_logbooks}
				totalWorkMinutes={selectedSummary.total_work_minutes}
				progressPercent={selectedAverageKpiPercent}
				averageRating={selectedAverageRating}
			/>

			<div class="divider">Rekap Harian</div>
			<div class="flex flex-wrap gap-2">
				{#each dailySummaries as summary (summary.id)}
					<button
						type="button"
						class="btn btn-sm {selectedDate === normalizeDateKey(summary.tanggal)
							? 'btn-primary'
							: 'btn-outline'}"
						onclick={() => {
							selectedDate = normalizeDateKey(summary.tanggal);
						}}
					>
						{formatDate(summary.tanggal)}
					</button>
				{/each}
			</div>

			<div class="space-y-3">
				<h3 class="text-base font-semibold">Ringkasan KPI</h3>
				{#if selectedDayKpis.length === 0}
					<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-base-content/60">
						Belum ada KPI.
					</div>
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
		{/if}
	</main>
</div>
