<script lang="ts">
	import { summaryService } from '$lib/api/services/summaryService';
	import TeamMemberCard from '$lib/components/mobile/TeamMemberCard.svelte';
	import TeamSummaryCard from '$lib/components/mobile/TeamSummaryCard.svelte';
	import type { DailyStaffSummary } from '$lib/types';

	let { onMemberClick }: { onMemberClick?: (member: DailyStaffSummary) => void } = $props();

	const today = new Date().toISOString().split('T')[0];

	let mode = $state<'daily' | 'period'>('daily');
	let date = $state(today);
	let dateFrom = $state(today);
	let dateTo = $state(today);

	let loading = $state(false);
	let error = $state<string | null>(null);
	let members = $state<DailyStaffSummary[]>([]);

	$effect(() => {
		const fetchTeamData = async () => {
			loading = true;
			error = null;
			try {
				if (mode === 'daily') {
					const res = await summaryService.teamDaily({ date, per_page: 100 });
					members = res.data ?? [];
				} else {
					const res = await summaryService.teamDaily({ date_from: dateFrom, date_to: dateTo, per_page: 100 });
					members = res.data ?? [];
				}
			} catch (err: any) {
				error = err.message || 'Failed to fetch team data';
			} finally {
				loading = false;
			}
		};
		fetchTeamData();
	});

	let totalLogbooks = $derived(members.reduce((sum, m) => sum + (m.total_logbooks || 0), 0));
	let avgKpiPercent = $derived(members.length > 0 ? members.reduce((sum, m) => sum + (m.progress_percent || 0), 0) / members.length : 0);
	let pendingReviews = $derived(members.reduce((sum, m) => sum + (m.submitted_logbooks || 0), 0));
</script>

<div class="flex flex-col gap-4 w-full">
	<div class="tabs tabs-boxed bg-base-200 w-full">
		<button class="tab flex-1 {mode === 'daily' ? 'tab-active' : ''}" onclick={() => mode = 'daily'}>Daily</button>
		<button class="tab flex-1 {mode === 'period' ? 'tab-active' : ''}" onclick={() => mode = 'period'}>Period</button>
	</div>

	{#if mode === 'daily'}
		<div class="form-control w-full">
			<label class="label" for="team-date"><span class="label-text font-semibold">Date</span></label>
			<input id="team-date" type="date" class="input input-bordered w-full" bind:value={date} />
		</div>
	{:else}
		<div class="flex gap-2 w-full">
			<div class="form-control w-1/2">
				<label class="label" for="team-date-from"><span class="label-text font-semibold">From</span></label>
				<input id="team-date-from" type="date" class="input input-bordered w-full" bind:value={dateFrom} />
			</div>
			<div class="form-control w-1/2">
				<label class="label" for="team-date-to"><span class="label-text font-semibold">To</span></label>
				<input id="team-date-to" type="date" class="input input-bordered w-full" bind:value={dateTo} />
			</div>
		</div>
	{/if}

	{#if error}
		<div class="alert alert-error shadow-sm">
			<span>{error}</span>
		</div>
	{/if}

	{#if loading}
		<div class="flex flex-col gap-4">
			<div class="skeleton h-32 w-full rounded-2xl"></div>
			<div class="skeleton h-24 w-full rounded-2xl"></div>
			<div class="skeleton h-24 w-full rounded-2xl"></div>
		</div>
	{:else if members.length > 0}
		<TeamSummaryCard 
			{totalLogbooks} 
			{avgKpiPercent} 
			{pendingReviews} 
		/>
		
		<div class="divider my-0">Team Members</div>
		
		<div class="flex flex-col gap-3">
			{#each members as member (member.user?.id || member.user_id)}
				<TeamMemberCard 
					{member} 
					onclick={() => onMemberClick?.(member)} 
				/>
			{/each}
		</div>
	{:else}
		<div class="text-center py-10 text-base-content/60">
			<p>No team data available for this period.</p>
		</div>
	{/if}
</div>
