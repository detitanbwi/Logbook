<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth.svelte';
	import { summaryService } from '$lib/api/services/summaryService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
	import type { DailyStaffSummary, DailyKpiSummary, Logbook } from '$lib/types';

	let loading = $state(true);
	let error = $state<string | null>(null);

	let summary = $state<DailyStaffSummary | null>(null);
	let kpis = $state<DailyKpiSummary[]>([]);
	let logbooks = $state<Logbook[]>([]);

	const averageRating = $derived.by(() => {
		const ratedLogbooks = logbooks.filter(
			(logbook) => logbook.rating != null && logbook.status === 'ACCEPTED'
		);
		if (ratedLogbooks.length === 0) return null;

		const totalRating = ratedLogbooks.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0);
		return totalRating / ratedLogbooks.length;
	});

	const averageKpiPercent = $derived.by(() => {
		if (kpis.length === 0) return summary?.progress_percent || 0;

		const validKpis = kpis.filter((kpi) => kpi.target_angka_total > 0);
		if (validKpis.length === 0) return summary?.progress_percent || 0;

		const totalPercent = validKpis.reduce((sum, kpi) => {
			return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
		}, 0);

		return totalPercent / validKpis.length;
	});

	const averageKpiUnit = $derived.by(() => {
		const uniqueUnits = Array.from(new Set(kpis.map((kpi) => kpi.satuan).filter(Boolean)));
		if (uniqueUnits.length === 0) return null;
		if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
		return `Satuan campuran (${uniqueUnits.length} jenis)`;
	});

	const today = new Date();
	const todayStrAPI = today.toLocaleDateString('en-CA'); // YYYY-MM-DD
	const todayStrID = new Intl.DateTimeFormat('id-ID', {
		weekday: 'long',
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	}).format(today);

	onMount(async () => {
		try {
			loading = true;
			error = null;

			const [summaryRes, kpiRes, logbooksData] = await Promise.all([
				summaryService.daily({ date: todayStrAPI, date_from: todayStrAPI, date_to: todayStrAPI }),
				summaryService.kpiDaily({ date: todayStrAPI, date_from: todayStrAPI, date_to: todayStrAPI }),
				staffLogbookService.getLogbooks({ date_from: todayStrAPI, date_to: todayStrAPI })
			]);

		summary = summaryRes.data?.[0] ?? null;
		kpis = kpiRes.data ?? [];
		logbooks = logbooksData.data;
		} catch (err: unknown) {
			error = err instanceof Error ? err.message : 'Terjadi kesalahan saat memuat data';
		} finally {
			loading = false;
		}
	});

	function getStatusBadgeClass(status: string): string {
		switch (status) {
			case 'DRAFT': return 'badge-ghost';
			case 'SUBMITTED': return 'badge-warning';
			case 'ACCEPTED': return 'badge-success';
			case 'REJECTED': return 'badge-error';
			default: return 'badge-ghost';
		}
	}
</script>

<div class="flex flex-col gap-4 p-4 pb-24 max-w-md mx-auto">
	<header class="flex flex-col gap-1">
		<h1 class="text-2xl font-bold">
			Halo, {auth.user.current?.nama || 'Pengguna'} 👋
		</h1>
		<p class="text-sm text-base-content/70">{todayStrID}</p>
	</header>

	{#if loading}
		<div class="flex items-center justify-center py-12">
			<span class="loading loading-spinner loading-lg text-primary"></span>
		</div>
	{:else if error}
		<div class="alert alert-error">
			<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
			<span>{error}</span>
		</div>
	{:else}
		<section>
			<PerformanceSummaryCard 
				totalLogbooks={summary?.total_logbooks || 0}
				totalWorkMinutes={summary?.total_work_minutes || 0}
				progressPercent={averageKpiPercent}
				progressUnit={averageKpiUnit}
				averageRating={averageRating}
			/>
		</section>

		<section>
			<button 
				class="btn btn-primary w-full shadow-sm"
				onclick={() => goto('/m/logbook')}
			>
				<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
				Buat Logbook Baru
			</button>
		</section>

		{#if kpis.length > 0}
			<section class="flex flex-col gap-3">
				<h2 class="text-lg font-semibold">Progres KPI Hari Ini</h2>
				<div class="flex flex-col gap-3">
					{#each kpis as kpi}
						<KpiProgressBar 
							nama={kpi.kpi_nama || ''}
							capaian={kpi.capaian_angka_total || 0}
							target={kpi.target_angka_total || 0}
							satuan={kpi.satuan || ''}
						/>
					{/each}
				</div>
			</section>
		{/if}

		<section class="flex flex-col gap-3 mt-2">
			<h2 class="text-lg font-semibold">Logbook Hari Ini</h2>
			
			{#if logbooks.length === 0}
				<div class="bg-base-200 rounded-xl p-6 text-center text-sm text-base-content/70">
					Belum ada logbook hari ini.
				</div>
			{:else}
				<div class="flex flex-col gap-3">
					{#each logbooks as logbook}
						<div class="card bg-base-100 border border-base-200 shadow-sm">
							<div class="card-body p-4 gap-2">
								<div class="flex items-start justify-between gap-2">
									<h3 class="font-medium text-sm leading-tight line-clamp-2 flex-1">
										{logbook.details && logbook.details.length > 0 ? logbook.details[0].kpi_nama : 'Tanpa Deskripsi'}
									</h3>
									<div class={`badge badge-sm whitespace-nowrap ${getStatusBadgeClass(logbook.status)}`}>
										{logbook.status}
									</div>
								</div>
								
								<div class="flex items-center justify-between text-xs text-base-content/70 mt-1">
									<div class="flex items-center gap-1 font-mono">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
										{logbook.start_kerja?.slice(0, 5) || '--:--'} - {logbook.end_kerja?.slice(0, 5) || '--:--'}
									</div>
									
									{#if logbook.rating != null}
										<div class="flex items-center gap-1 text-warning font-medium">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
												<path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
											</svg>
											{Number(logbook.rating).toFixed(1)}
										</div>
									{/if}
								</div>
							</div>
						</div>
					{/each}
				</div>
			{/if}
		</section>
	{/if}
</div>
