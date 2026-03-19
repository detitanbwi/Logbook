<script lang="ts">
	let { rows = 5, cols = 4 } = $props();
</script>

<div class="animate-pulse w-full">
	{#each Array(rows) as _, i}
		<div class="flex gap-4 mb-4" role="status" aria-label="Loading">
			{#each Array(cols) as _, j}
				<div class="h-8 bg-base-300 rounded w-full"></div>
			{/each}
		</div>
	{/each}
</div>
