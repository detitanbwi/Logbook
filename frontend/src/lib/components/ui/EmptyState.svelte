<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		icon,
		title,
		description,
		action
	}: {
		icon?: Snippet;
		title: string;
		description?: string;
		action?: Snippet;
	} = $props();
</script>

<div class="flex flex-col items-center justify-center p-8 text-center rounded-box border border-base-300 bg-base-100/50">
	{#if icon}
		<div class="text-base-content/30 mb-4 h-16 w-16 flex items-center justify-center">
			{@render icon()}
		</div>
	{:else}
		<div class="text-base-content/30 mb-4">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
				<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
			</svg>
		</div>
	{/if}
	
	<h3 class="text-lg font-bold text-base-content">{title}</h3>
	
	{#if description}
		<p class="text-base-content/70 mt-2 max-w-sm">{description}</p>
	{/if}
	
	{#if action}
		<div class="mt-6">
			{@render action()}
		</div>
	{/if}
</div>
