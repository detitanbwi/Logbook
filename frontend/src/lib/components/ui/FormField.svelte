<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		id,
		label,
		error,
		hint,
		required = false,
		children
	}: {
		id?: string;
		label?: string;
		error?: string;
		hint?: string;
		required?: boolean;
		children: Snippet;
	} = $props();
</script>

<div class="form-control w-full">
	{#if label}
		<label for={id} class="label">
			<span class="label-text font-medium">
				{label}
				{#if required}
					<span class="text-error ml-1">*</span>
				{/if}
			</span>
		</label>
	{/if}

	{@render children()}

	{#if error}
		<label for={id} class="label py-1">
			<span class="label-text-alt text-error">{error}</span>
		</label>
	{:else if hint}
		<label for={id} class="label py-1">
			<span class="label-text-alt text-base-content/70">{hint}</span>
		</label>
	{/if}
</div>
