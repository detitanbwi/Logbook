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

<div
	class="flex flex-col items-center justify-center rounded-box border border-base-300 bg-base-100/50 p-8 text-center"
>
	{#if icon}
		<div class="mb-4 flex h-16 w-16 items-center justify-center text-base-content/30">
			{@render icon()}
		</div>
	{:else}
		<div class="mb-4 text-base-content/30">
			<svg
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
				stroke-width="1.5"
				stroke="currentColor"
				class="h-16 w-16"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
				/>
			</svg>
		</div>
	{/if}

	<h3 class="text-lg font-bold text-base-content">{title}</h3>

	{#if description}
		<p class="mt-2 max-w-sm text-base-content/70">{description}</p>
	{/if}

	{#if action}
		<div class="mt-6">
			{@render action()}
		</div>
	{/if}
</div>
