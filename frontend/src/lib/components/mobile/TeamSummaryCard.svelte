<script lang="ts">
	let {
		totalLogbooks = 0,
		avgKpiPercent = 0,
		avgRating,
		pendingReviews = 0,
		class: className = ''
	}: {
		totalLogbooks?: number;
		avgKpiPercent?: number;
		avgRating?: number | null;
		pendingReviews?: number;
		class?: string;
	} = $props();
</script>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 {className}">
	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Total Logbooks</div>
		<div class="text-xl font-bold">{totalLogbooks}</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Avg KPI %</div>
		<div class="text-xl font-bold">{Math.round(avgKpiPercent)}%</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Avg Rating</div>
		<div class="text-xl font-bold">
			{#if avgRating != null}
				⭐ {avgRating.toFixed(1)}
			{:else}
				-
			{/if}
		</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Pending</div>
		<div class="text-xl font-bold text-warning">{pendingReviews}</div>
	</div>
</div>
