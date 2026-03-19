<script lang="ts">
	import { analyticsService } from '$lib/api/services/analyticsService';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import StatCard from '$lib/components/ui/StatCard.svelte';
	import ChartWrapper from '$lib/components/ui/ChartWrapper.svelte';
	import type { AnalyticsDashboardResponse } from '$lib/api/schemas/analytics.schema';
	import type { Logbook } from '$lib/types';

	const formatDate = (dateString: string) => {
		if (!dateString) return '-';
		return new Date(dateString).toLocaleDateString('id-ID', {
			day: 'numeric',
			month: 'short',
			year: 'numeric'
		});
	};

	let loading = $state(true);
	let error = $state<string | null>(null);
	let data = $state<any>(null);
	let recentLogbooks = $state<Logbook[]>([]);

	$effect(() => {
		async function load() {
			try {
				const [dashboardRes, logbooksRes] = await Promise.all([
					analyticsService.getStaffDashboard(),
					staffLogbookService.getLogbooks({ per_page: 5, sort_by: 'created_at', sort_dir: 'desc' })
				]);
				
				// Extract the actual data whether it's wrapped in a data object or not
				data = (dashboardRes as any).data || dashboardRes;
				recentLogbooks = logbooksRes.data || [];
			} catch (e: any) {
				error = e.message || 'Failed to load dashboard data';
			} finally {
				loading = false;
			}
		}
		load();
	});

	let kpiChartData = $derived({
		labels: data?.kpi_achievements?.map((k: any) => k.name) || [],
		datasets: [
			{
				label: 'KPI Completion (%)',
				data: data?.kpi_achievements?.map((k: any) => k.completion_rate) || [],
				backgroundColor: 'rgba(59, 130, 246, 0.6)',
				borderColor: 'rgb(59, 130, 246)',
				borderWidth: 1,
				borderRadius: 4
			}
		]
	});
</script>

{#if loading}
	<div class="flex h-48 w-full items-center justify-center">
		<span class="loading loading-lg loading-spinner text-primary"></span>
	</div>
{:else if error}
	<div class="alert alert-error">
		<span>{error}</span>
	</div>
{:else if data}
	<div class="mb-6 flex items-center justify-between">
		<h1 class="text-2xl font-bold">Dashboard Staff</h1>
	</div>

	<div class="grid grid-cols-1 gap-4 md:grid-cols-4">
		<StatCard
			title="Total Logbook"
			value={data.total_logbooks ?? 0}
			description="Logbook bulan ini"
		/>
		
		<StatCard
			title="Target Terlewat"
			value={data.missed_logbooks_count ?? 0}
			description="Hari tanpa logbook"
		>
			{#snippet icon()}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-8 w-8 text-error"
					viewBox="0 0 20 20"
					fill="currentColor"
				>
					<path
						fill-rule="evenodd"
						d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
						clip-rule="evenodd"
					/>
				</svg>
			{/snippet}
		</StatCard>

		<StatCard
			title="Rata-rata Rating"
			value={data.average_rating ?? 0}
			description="Dari review manager"
		>
			{#snippet icon()}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-8 w-8 text-warning"
					viewBox="0 0 20 20"
					fill="currentColor"
				>
					<path
						d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
					/>
				</svg>
			{/snippet}
		</StatCard>
		
		<div class="stats border border-base-300 bg-base-100 shadow-sm">
			<div class="stat">
				<div class="stat-title">Capaian KPI</div>
				<div class="stat-value flex items-center gap-3 text-primary">
					{data.personal_kpi_completion_rate ?? 0}%
					<div
						class="radial-progress text-primary"
						style="--value:{data.personal_kpi_completion_rate ?? 0}; --size:3rem; --thickness: 4px;"
						role="progressbar"
					>
						<span class="text-xs">{data.personal_kpi_completion_rate ?? 0}%</span>
					</div>
				</div>
				<div class="stat-desc">Persentase bulan ini</div>
			</div>
		</div>
	</div>

	<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
		<div class="card bg-base-100 border border-base-200 shadow-sm">
			<div class="card-body">
				<h2 class="card-title text-lg">Progres per KPI</h2>
				{#if data.kpi_achievements && data.kpi_achievements.length > 0}
					<div class="mt-4 h-64 w-full">
						<ChartWrapper 
							type="bar" 
							data={kpiChartData} 
							options={{
								maintainAspectRatio: false,
								scales: {
									y: {
										beginAtZero: true,
										max: 100
									}
								}
							}}
						/>
					</div>
				{:else}
					<div class="flex h-64 items-center justify-center text-base-content/50">
						Belum ada data KPI bulan ini
					</div>
				{/if}
			</div>
		</div>

		<div class="card bg-base-100 border border-base-200 shadow-sm">
			<div class="card-body p-0">
				<div class="p-6 pb-2 border-b border-base-200 flex justify-between items-center">
					<h2 class="card-title text-lg">Logbook Terakhir</h2>
					<a href="/staff/logbooks" class="btn btn-sm btn-ghost">Lihat Semua</a>
				</div>
				<div class="overflow-x-auto">
					<table class="table w-full">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							{#if recentLogbooks.length > 0}
								{#each recentLogbooks as logbook}
									<tr class="hover:bg-base-50">
										<td>
											<div class="font-medium">{formatDate(logbook.created_at)}</div>
											{#if logbook.rating}
												<div class="flex text-warning text-xs mt-1">
													{#each Array(5) as _, i}
														<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {i < logbook.rating ? 'fill-current' : 'text-base-300'}" viewBox="0 0 20 20">
															<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
														</svg>
													{/each}
												</div>
											{/if}
										</td>
										<td>
											<span class="badge badge-sm
												{logbook.status === 'DRAFT' ? 'badge-ghost' : ''}
												{logbook.status === 'SUBMITTED' ? 'badge-info' : ''}
												{logbook.status === 'REVIEWED' ? 'badge-success' : ''}
												{logbook.status === 'REVERTED' ? 'badge-error' : ''}
											">
												{logbook.status}
											</span>
										</td>
										<td>
											<a href="/staff/logbooks/{logbook.id}" class="btn btn-xs btn-outline">Detail</a>
										</td>
									</tr>
								{/each}
							{:else}
								<tr>
									<td colspan="3" class="text-center py-6 text-base-content/50">Belum ada logbook</td>
								</tr>
							{/if}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
{/if}
