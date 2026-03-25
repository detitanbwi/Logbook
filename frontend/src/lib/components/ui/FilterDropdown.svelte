<script lang="ts">
	import { Filter, ChevronDown } from 'lucide-svelte';

	interface FilterOption {
		label: string;
		value: string;
	}

	interface Props {
		label: string;
		options: FilterOption[];
		value?: string;
		onChange?: (value: string) => void;
		allLabel?: string;
		class?: string;
	}

	let {
		label,
		options,
		value = $bindable(''),
		onChange,
		allLabel = 'Semua',
		class: className = ''
	}: Props = $props();

	let isOpen = $state(false);

	function select(newValue: string) {
		value = newValue;
		onChange?.(newValue);
		isOpen = false;
	}

	let selectedLabel = $derived(
		value ? options.find((o) => o.value === value)?.label || value : null
	);

	let chevronClass = $derived(`transition-transform ${isOpen ? 'rotate-180' : ''}`);
</script>

<div class="dropdown {className}" class:dropdown-open={isOpen}>
	<button
		type="button"
		tabindex="0"
		class="btn gap-2 btn-outline btn-sm"
		onclick={() => (isOpen = !isOpen)}
		onblur={() => setTimeout(() => (isOpen = false), 150)}
	>
		<Filter size={16} />
		{label}
		{#if selectedLabel}
			<span class="badge badge-sm badge-primary">{selectedLabel}</span>
		{/if}
		<ChevronDown size={14} class={chevronClass} />
	</button>

	{#if isOpen}
		<ul class="dropdown-content menu z-[1] mt-1 w-52 rounded-box bg-base-100 p-2 shadow">
			<li>
				<button type="button" class:active={!value} onclick={() => select('')}>
					{allLabel}
				</button>
			</li>
			<div class="divider my-1"></div>
			{#each options as option (option.value)}
				<li>
					<button
						type="button"
						class:active={value === option.value}
						onclick={() => select(option.value)}
					>
						{option.label}
					</button>
				</li>
			{/each}
		</ul>
	{/if}
</div>
