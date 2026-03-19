<script lang="ts">
	import { StatCard, ChartWrapper } from '$lib/components/ui';
	import { analyticsService } from '$lib/api/services/analyticsService';
	import type { AdminDashboard } from '$lib/types';

	let data = $state<AdminDashboard | null>(null);
	let loading = $state(true);

	$effect(() => {
		analyticsService.getAdminDashboard().then((res) => {
			// Based on backend implementation, it might be res.data or just res.
			// Assuming res is the data itself if we used custom wrapper, but let's check what analyticsService.getAdminDashboard() returns.
			// In the old code it was: data = await analyticsService.getAdminDashboard();
			data = res as unknown as AdminDashboard;
			loading = false;
		});
	});

	// Transform data for charts
	let logbookActivityData = $derived({
		labels: data?.logbooks_by_day?.map((d) => d.date) || [],
		datasets: [
			{
				label: 'Logbook Submitted',
				data: data?.logbooks_by_day?.map((d) => d.count) || [],
				borderColor: 'rgb(59, 130, 246)',
				backgroundColor: 'rgba(59, 130, 246, 0.5)',
				tension: 0.3
			}
		]
	});

	let statusChartData = $derived({
		labels: data?.logbooks_by_status?.map((d) => d.status) || [],
		datasets: [
			{
				data: data?.logbooks_by_status?.map((d) => d.count) || [],
				backgroundColor: [
					'rgb(156, 163, 175)', // DRAFT - gray
					'rgb(59, 130, 246)', // SUBMITTED - blue
					'rgb(239, 68, 68)', // REVERTED - red
					'rgb(34, 197, 94)' // REVIEWED - green
				]
			}
		]
	});
</script>

<div class="p-6">
	<div class="mb-6 flex items-center justify-between">
		<h1 class="text-2xl font-bold">Dashboard Admin</h1>
	</div>

	{#if loading}
		<div class="flex justify-center py-12">
			<span class="loading loading-lg loading-spinner text-primary"></span>
		</div>
	{:else if data}
		<!-- Stats Row -->
		<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
			<StatCard
				title="Total Pengguna Aktif"
				value={data.total_active_users}
				description="pengguna terdaftar"
			/>
			<StatCard
				title="Logbook Bulan Ini"
				value={data.total_logbooks_this_month}
				description={`${data.logbook_trend! >= 0 ? '+' : ''}${data.logbook_trend ?? 0}% dari bulan lalu`}
			/>
			<StatCard
				title="Menunggu Review"
				value={data.pending_logbooks_count}
				description="perlu ditindaklanjuti"
			/>
			<StatCard title="KPI Aktif" value={data.active_kpis} description="dalam sistem" />
		</div>

		<!-- Charts Row -->
		<div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
			<div class="card border border-base-200 bg-base-100 shadow-xl">
				<div class="card-body">
					<h2 class="card-title text-base">Aktivitas Logbook Bulan Ini</h2>
					<div class="mt-4 h-64 w-full">
						<ChartWrapper type="line" data={logbookActivityData} />
					</div>
				</div>
			</div>

			<div class="card border border-base-200 bg-base-100 shadow-xl">
				<div class="card-body">
					<h2 class="card-title text-base">Status Logbook</h2>
					<div class="mt-4 h-64 w-full">
						<ChartWrapper
							type="doughnut"
							data={statusChartData}
							options={{ maintainAspectRatio: false }}
						/>
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>
