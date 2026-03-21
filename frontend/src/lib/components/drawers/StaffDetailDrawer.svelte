<script lang="ts">
	import SlideOutDrawer from '$lib/components/ui/SlideOutDrawer.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import { summaryService } from '$lib/api/services/summaryService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import type { KpiPeriodItem, Logbook } from '$lib/types';

	interface StaffInfo {
		user_id: string;
		nama: string;
		npp: string;
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
	let kpis = $state<KpiPeriodItem[]>([]);
	let logbooks = $state<Logbook[]>([]);

	$effect(() => {
		if (isOpen && staff) {
			loadStaffData(staff.user_id);
		}
	});

	async function loadStaffData(userId: string) {
		loading = true;
		error = null;
		try {
			const [kpiData, logbookData] = await Promise.all([
				summaryService.kpiPeriod({ date_from: undefined, date_to: undefined }),
				staffLogbookService.getLogbooks({ per_page: 10, sort_by: 'created_at', sort_dir: 'desc' })
			]);
			kpis = kpiData.items ?? [];
			logbooks = logbookData?.data || [];
		} catch (e: unknown) {
			error = e instanceof Error ? e.message : 'Gagal memuat data staff';
		} finally {
			loading = false;
		}
	}

	function formatDate(dateString: string): string {
		if (!dateString) return '-';
		return new Date(dateString).toLocaleDateString('id-ID', {
			day: 'numeric',
			month: 'short',
			year: 'numeric'
		});
	}

	const statusClass: Record<string, string> = {
		DRAFT: 'badge-ghost',
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
		</div>

		<h3 class="mb-2 text-sm font-bold">KPI Breakdown</h3>
		{#if kpis.length > 0}
			<div class="mb-4 space-y-2">
				{#each kpis as kpi (kpi.kpi_id)}
					<KpiProgressBar
						nama={kpi.kpi_nama}
						capaian={kpi.capaian_angka_total}
						target={kpi.target_angka_total}
					/>
				{/each}
			</div>
		{:else}
			<div class="mb-4 rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">
				Belum ada data KPI
			</div>
		{/if}

		<h3 class="mb-2 text-sm font-bold">Logbook Terakhir</h3>
		{#if logbooks.length > 0}
			<div class="space-y-2">
				{#each logbooks as logbook (logbook.id)}
					<div class="flex items-center justify-between rounded-lg border border-base-300 p-3">
						<div>
							<div class="text-sm font-medium">{formatDate(logbook.created_at)}</div>
							{#if logbook.rating}
								<div class="text-xs text-warning">⭐ {logbook.rating}/5</div>
							{/if}
						</div>
						<span class="badge badge-sm {statusClass[logbook.status] ?? ''}">{logbook.status}</span>
					</div>
				{/each}
			</div>
		{:else}
			<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">
				Belum ada logbook
			</div>
		{/if}
	{/if}
</SlideOutDrawer>
