<script lang="ts">
	import { ArrowUp, ArrowDown, ArrowUpDown } from 'lucide-svelte';

	interface Props {
		column: string;
		label: string;
		currentSort?: string;
		currentDir?: 'asc' | 'desc';
		onSort?: (column: string, dir: 'asc' | 'desc') => void;
		class?: string;
	}

	let {
		column,
		label,
		currentSort,
		currentDir = 'asc',
		onSort,
		class: className = ''
	}: Props = $props();

	let isActive = $derived(currentSort === column);

	function handleClick() {
		if (isActive) {
			// Toggle direction if already active
			onSort?.(column, currentDir === 'asc' ? 'desc' : 'asc');
		} else {
			// Default to ascending for new sort
			onSort?.(column, 'asc');
		}
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			handleClick();
		}
	}
</script>

<th
	class="cursor-pointer transition-colors select-none hover:bg-base-200 {className}"
	onclick={handleClick}
	onkeydown={handleKeydown}
	tabindex="0"
	role="columnheader"
	aria-sort={isActive ? (currentDir === 'asc' ? 'ascending' : 'descending') : 'none'}
>
	<div class="flex items-center gap-1">
		<span>{label}</span>
		{#if isActive}
			{#if currentDir === 'asc'}
				<ArrowUp size={14} class="text-primary" />
			{:else}
				<ArrowDown size={14} class="text-primary" />
			{/if}
		{:else}
			<ArrowUpDown size={14} class="opacity-30" />
		{/if}
	</div>
</th>
