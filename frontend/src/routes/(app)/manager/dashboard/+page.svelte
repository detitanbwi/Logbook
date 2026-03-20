<script lang="ts">
	import { analyticsService } from '$lib/api/services/analyticsService';
	import StatCard from '$lib/components/ui/StatCard.svelte';
	import Map from '$lib/components/ui/Map.svelte';

	let loading = $state(true);
	let error = $state<string | null>(null);
	let data = $state<any>(null);
	let teamLocations = $state<{ lat: number; lng: number; title: string }[]>([]);

	$effect(() => {
		async function load() {
			try {
				const [dashboardData, locationsData] = await Promise.all([
					analyticsService.getManagerDashboard(),
					analyticsService.getTeamLocations()
				]);

				const rawData = (dashboardData as any).data || dashboardData;

				const subordinates = rawData.subordinates || [];
				let teamCompletionRate = 0;
				if (subordinates.length > 0) {
					const totalRate = subordinates.reduce(
						(acc: number, sub: any) => acc + sub.completion_rate,
						0
					);
					teamCompletionRate = Math.round(totalRate / subordinates.length);
				}

				data = {
					team_size: subordinates.length,
					pending_reviews: rawData.pending_logbooks_count || 0,
					team_completion_rate: teamCompletionRate,
					subordinates: subordinates
				};

				const locations = Array.isArray((locationsData as any)?.data)
					? (locationsData as any).data
					: [];

				teamLocations = locations.map((loc: any) => ({
					lat: loc.lat,
					lng: loc.lng,
					title: loc.title
				}));
			} catch (e: any) {
				error = e.message || 'Failed to load dashboard data';
			} finally {
				loading = false;
			}
		}
		load();
	});
</script>

<svelte:head>
	<title>Dashboard | Manager</title>
</svelte:head>

{#if error}
	<div class="alert alert-error mb-6">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={() => loading = true}>Coba Lagi</button>
	</div>
{:else}
	<!-- Stats Row with Skeleton -->
	<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
		{#if loading}
			{#each Array(3) as _}
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
		{:else}
			<StatCard
				title="Team Size"
				value={data.team_size ?? 0}
				description="Number of staff you manage"
			/>
			<StatCard
				title="Pending Reviews"
				value={data.pending_reviews ?? 0}
				description="Logbooks waiting for your review"
			/>
			<div class="stats border border-base-300 bg-base-100 shadow-sm">
				<div class="stat">
					<div class="stat-title">Team Completion Rate</div>
					<div class="stat-value flex items-center gap-3 text-primary">
						{data.team_completion_rate ?? 0}%
						<progress
							class="progress w-24 progress-primary"
							value={data.team_completion_rate ?? 0}
							max="100"
						></progress>
					</div>
					<div class="stat-desc">Overall task completion for your team</div>
				</div>
			</div>
		{/if}
	</div>

	<!-- Team Overview Table -->
	<div class="mt-8">
		<h2 class="mb-4 text-xl font-bold">Team Overview</h2>
		<div class="card border border-base-300 bg-base-100 shadow-sm">
			<div class="card-body p-0">
				<div class="overflow-x-auto">
					<table class="table">
						<thead>
							<tr>
								<th>Nama Staff</th>
								<th>Total KPI</th>
								<th>Selesai</th>
								<th>Progress</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							{#if loading}
								{#each Array(4) as _}
									<tr>
										<td>
											<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
										</td>
										<td>
											<div class="h-4 w-12 animate-pulse rounded bg-base-300"></div>
										</td>
										<td>
											<div class="h-4 w-12 animate-pulse rounded bg-base-300"></div>
										</td>
										<td class="w-1/3">
											<div class="h-4 w-full animate-pulse rounded bg-base-300"></div>
										</td>
										<td>
											<div class="h-7 w-28 animate-pulse rounded bg-base-300"></div>
										</td>
									</tr>
								{/each}
							{:else if data.subordinates && data.subordinates.length > 0}
								{#each data.subordinates as staff}
									<tr>
									<td class="font-medium">{staff.nama ?? '-'}</td>
										<td>{staff.total_kpi}</td>
										<td>{staff.completed_kpi}</td>
										<td class="w-1/3">
											<div class="flex items-center gap-2">
												<progress
													class="progress w-full {staff.completion_rate === 100
														? 'progress-success'
														: 'progress-primary'}"
													value={staff.completion_rate}
													max="100"
												></progress>
												<span class="min-w-8 text-xs font-medium">{staff.completion_rate}%</span>
											</div>
										</td>
										<td>
											<a
										href={`/manager/reviews?search=${encodeURIComponent(staff.nama ?? '')}`}
												class="btn btn-outline btn-sm btn-primary"
											>
												<svg
													xmlns="http://www.w3.org/2000/svg"
													class="mr-1 h-4 w-4"
													fill="none"
													viewBox="0 0 24 24"
													stroke="currentColor"
												>
													<path
														stroke-linecap="round"
														stroke-linejoin="round"
														stroke-width="2"
														d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
													/>
													<path
														stroke-linecap="round"
														stroke-linejoin="round"
														stroke-width="2"
														d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
													/>
												</svg>
												Lihat Logbook
											</a>
										</td>
									</tr>
								{/each}
							{:else}
								<tr>
									<td colspan="5" class="py-4 text-center text-base-content/60"
										>Belum ada data bawahan</td
									>
								</tr>
							{/if}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Staff Locations Map -->
	<div class="mt-8">
		<h2 class="mb-4 text-xl font-bold">Recent Staff Locations</h2>
		<div class="h-[400px] w-full overflow-hidden rounded-xl border border-base-300 shadow-sm">
			{#if loading}
				<div class="flex h-full items-center justify-center">
					<div class="size-32 animate-pulse rounded-full bg-base-300"></div>
				</div>
			{:else if teamLocations.length > 0}
				<Map
					lat={teamLocations[0]?.lat ?? -6.205}
					lng={teamLocations[0]?.lng ?? 106.82}
					zoom={13}
					markers={teamLocations}
				/>
			{:else}
				<div class="flex h-full items-center justify-center text-base-content/60">
					<span>No staff locations available for today</span>
				</div>
			{/if}
		</div>
	</div>
{/if}
