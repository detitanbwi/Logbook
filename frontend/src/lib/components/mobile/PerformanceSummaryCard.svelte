<script lang="ts">
	let {
		totalLogbooks = 0,
		totalWorkMinutes = 0,
		progressPercent = 0,
		progressUnit = null,
		averageRating,
		class: className = ''
	}: {
		totalLogbooks?: number;
		totalWorkMinutes?: number;
		progressPercent?: number;
		progressUnit?: string | null;
		averageRating?: number | null;
		class?: string;
	} = $props();

	const durationFormatted = $derived(() => {
		const hours = Math.floor(totalWorkMinutes / 60);
		const mins = totalWorkMinutes % 60;
		return `${hours}h ${mins}m`;
	});

	const normalizedAverageRating = $derived.by(() => {
		if (averageRating == null) return null;
		const parsed = Number(averageRating);
		return Number.isFinite(parsed) ? parsed : null;
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
		<div class="text-xs text-base-content/60">Rata-rata KPI</div>
		<div class="text-xl font-bold">{Math.round(progressPercent)}%</div>
		<div class="mt-1 text-[11px] text-base-content/50 line-clamp-1">{progressUnit || '-'}</div>
	</div>

	<div class="rounded-lg border border-base-300 bg-base-100 p-3 text-center">
		<div class="text-xs text-base-content/60">Rata-rata Bintang</div>
		<div class="text-xl font-bold">
			{#if normalizedAverageRating != null}
				⭐ {normalizedAverageRating.toFixed(1)}
			{:else}
				-
			{/if}
		</div>
	</div>
</div>
