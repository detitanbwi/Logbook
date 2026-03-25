<script lang="ts">
	let { rows = 5, cols = 4 } = $props();
</script>

<div class="w-full animate-pulse">
	{#each Array(rows) as _, i}
		<div class="mb-4 flex gap-4" role="status" aria-label="Loading">
			{#each Array(cols) as _, j}
				<div class="h-8 w-full rounded bg-base-300"></div>
			{/each}
		</div>
	{/each}
</div>
