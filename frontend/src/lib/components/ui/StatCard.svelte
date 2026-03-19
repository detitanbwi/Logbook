<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		title,
		value,
		description,
		icon,
		class: className = '',
		trend,
		trendValue
	}: {
		title: string;
		value: string | number;
		description?: string;
		icon?: Snippet;
		class?: string;
		trend?: 'up' | 'down' | 'neutral';
		trendValue?: string;
	} = $props();
</script>

<div class="stats border border-base-300 bg-base-100 shadow-sm {className}">
	<div class="stat">
		{#if icon}
			<div class="stat-figure text-primary">
				{@render icon()}
			</div>
		{/if}
		<div class="stat-title">{title}</div>
		<div class="stat-value text-primary">{value}</div>
		{#if description || trend}
			<div class="stat-desc mt-1 flex items-center gap-1">
				{#if trend}
					<span class={
						trend === 'up' ? 'text-success' :
						trend === 'down' ? 'text-error' :
						'text-base-content/60'
					}>
						{#if trend === 'up'}
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 inline"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" /></svg>
						{:else if trend === 'down'}
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 inline"><path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" /></svg>
						{/if}
						{trendValue || ''}
					</span>
				{/if}
				{#if description}
					<span class="opacity-80">{description}</span>
				{/if}
			</div>
		{/if}
	</div>
</div>
