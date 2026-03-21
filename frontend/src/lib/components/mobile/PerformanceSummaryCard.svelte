<script lang="ts">
	let {
		totalLogbooks = 0,
		totalWorkMinutes = 0,
		progressPercent = 0,
		averageRating,
		class: className = ''
	}: {
		totalLogbooks?: number;
		totalWorkMinutes?: number;
		progressPercent?: number;
		averageRating?: number | null;
		class?: string;
	} = $props();

	const durationFormatted = $derived(() => {
		const hours = Math.floor(totalWorkMinutes / 60);
		const mins = totalWorkMinutes % 60;
		return `${hours}h ${mins}m`;
	});
</script>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 {className}">
	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Logbooks</div>
		<div class="text-xl font-bold">{totalLogbooks}</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Durasi</div>
		<div class="text-xl font-bold">{durationFormatted()}</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">KPI %</div>
		<div class="text-xl font-bold">{Math.round(progressPercent)}%</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Rating</div>
		<div class="text-xl font-bold">
			{#if averageRating != null}
				⭐ {averageRating.toFixed(1)}
			{:else}
				-
			{/if}
		</div>
	</div>
</div>
