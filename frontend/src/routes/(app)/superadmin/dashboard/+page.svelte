<script lang="ts">
	import { StatCard, ChartWrapper } from '$lib/components/ui';
	import { analyticsService } from '$lib/api/services/analyticsService';
	import type { AdminDashboard } from '$lib/types';

	let data = $state<AdminDashboard | null>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let fetchTriggered = $state(0);

	$effect(() => {
		fetchTriggered;

		loading = true;
		error = null;
		analyticsService
			.getAdminDashboard()
			.then((res) => {
				data = res as AdminDashboard;
			})
			.catch((e) => {
				console.error('Failed to load dashboard', e);
				error = e.message || 'Gagal memuat data dashboard';
			})
			.finally(() => {
				loading = false;
			});
	});

	function retry() {
		fetchTriggered += 1;
	}

	// Transform data for charts
	let logbooksByDay = $derived(Array.isArray(data?.logbooks_by_day) ? data.logbooks_by_day : []);
	let logbooksByStatus = $derived(
		Array.isArray(data?.logbooks_by_status) ? data.logbooks_by_status : []
	);
	let usersByRole = $derived(Array.isArray(data?.users_by_role) ? data.users_by_role : []);

	let logbookActivityData = $derived({
		labels: logbooksByDay.map((d) => d.date),
		datasets: [
			{
				label: 'Logbook Submitted',
				data: logbooksByDay.map((d) => d.count),
				borderColor: 'rgb(59, 130, 246)',
				backgroundColor: 'rgba(59, 130, 246, 0.5)',
				tension: 0.3
			}
		]
	});

	let statusChartData = $derived({
		labels: logbooksByStatus.map((d) => d.status),
		datasets: [
			{
				data: logbooksByStatus.map((d) => d.count),
				backgroundColor: [
					'rgb(59, 130, 246)', // SUBMITTED - blue
					'rgb(34, 197, 94)', // ACCEPTED - green
					'rgb(239, 68, 68)' // REJECTED - red
				]
			}
		]
	});

	let usersByRoleData = $derived({
		labels: usersByRole.map((d) => d.role),
		datasets: [
			{
				data: usersByRole.map((d) => d.count),
				backgroundColor: [
					'rgb(99, 102, 241)', // SuperAdmin - indigo
					'rgb(59, 130, 246)', // Admin - blue
					'rgb(234, 179, 8)', // Manager - yellow
					'rgb(34, 197, 94)' // Staff - green
				]
			}
		]
	});
</script>

<svelte:head>
	<title>Dashboard | SuperAdmin</title>
</svelte:head>

<div class="p-6">
	<div class="mb-6 flex items-center justify-between">
		<h1 class="text-2xl font-bold">Dashboard SuperAdmin</h1>
	</div>

	{#if error}
		<div class="alert alert-error mb-6">
			<span>{error}</span>
			<button class="btn btn-ghost btn-sm" onclick={retry}>Coba Lagi</button>
		</div>
	{/if}

	<!-- Stats Row -->
	<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
		{#if loading}
			{#each Array(4) as _}
				<div class="stats border border-base-300 bg-base-100 shadow-sm">
					<div class="stat">
						<div class="stat-title">
							<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
						</div>
						<div class="stat-value text-primary">
							<div class="mt-2 h-8 w-16 animate-pulse rounded bg-base-300"></div>
						</div>
						<div class="stat-desc mt-1">
							<div class="h-3 w-32 animate-pulse rounded bg-base-300"></div>
						</div>
					</div>
				</div>
			{/each}
		{:else if data}
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
		{/if}
	</div>

	<!-- Charts Row -->
	<div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
		<div class="card border border-base-200 bg-base-100 shadow-xl">
			<div class="card-body">
				<h2 class="card-title text-base">Aktivitas Logbook Bulan Ini</h2>
				<div class="mt-4 h-64 w-full">
					{#if loading}
						<div class="flex h-full items-center justify-center">
							<div class="w-full animate-pulse space-y-4">
								<div class="h-4 w-full rounded bg-base-300"></div>
								<div class="h-32 w-full rounded bg-base-300"></div>
								<div class="h-4 w-3/4 rounded bg-base-300"></div>
							</div>
						</div>
					{:else if data}
						<ChartWrapper type="line" data={logbookActivityData} />
					{/if}
				</div>
			</div>
		</div>

		<div class="card border border-base-200 bg-base-100 shadow-xl">
			<div class="card-body">
				<h2 class="card-title text-base">Status Logbook</h2>
				<div class="mt-4 h-64 w-full">
					{#if loading}
						<div class="flex h-full items-center justify-center">
							<div class="size-32 animate-pulse rounded-full bg-base-300"></div>
						</div>
					{:else if data}
						<ChartWrapper
							type="doughnut"
							data={statusChartData}
							options={{ maintainAspectRatio: false }}
						/>
					{/if}
				</div>
			</div>
		</div>
	</div>

	<!-- Users by Role Chart -->
	{#if !loading && usersByRole.length > 0}
		<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
			<div class="card border border-base-200 bg-base-100 shadow-xl">
				<div class="card-body">
					<h2 class="card-title text-base">Pengguna per Role</h2>
					<div class="mt-4 h-64 w-full">
						<ChartWrapper
							type="doughnut"
							data={usersByRoleData}
							options={{ maintainAspectRatio: false }}
						/>
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>
