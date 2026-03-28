<script lang="ts">
	import { Search, X } from 'lucide-svelte';
	import { onDestroy } from 'svelte';

	interface Props {
		value?: string;
		placeholder?: string;
		debounce?: number;
		onSearch?: (value: string) => void;
		class?: string;
	}

	let {
		value = $bindable(''),
		placeholder = 'Cari...',
		debounce = 300,
		onSearch,
		class: className = ''
	}: Props = $props();

	let timeout: ReturnType<typeof setTimeout>;
	let internalValue = $state(value);
	let isFocused = $state(false);

	$effect(() => {
		if (!isFocused && value !== internalValue) {
			internalValue = value;
		}
	});

	function handleInput(e: Event) {
		const target = e.target as HTMLInputElement;
		const nextValue = target.value;
		internalValue = nextValue;
		value = nextValue;

		clearTimeout(timeout);
		timeout = setTimeout(() => {
			onSearch?.(nextValue);
		}, debounce);
	}

	function clear() {
		clearTimeout(timeout);
		internalValue = '';
		value = '';
		onSearch?.('');
	}

	// Clean up timeout on component destroy to prevent memory leaks
	onDestroy(() => {
		clearTimeout(timeout);
	});
</script>

<div class="form-control {className}">
	<div class="join">
		<span class="btn pointer-events-none join-item btn-ghost">
			<Search size={20} class="text-base-content/50" />
		</span>
		<input
			type="text"
			{placeholder}
			value={internalValue}
			oninput={handleInput}
			onfocus={() => (isFocused = true)}
			onblur={() => (isFocused = false)}
			class="input-bordered input join-item w-full"
		/>
		{#if internalValue}
			<button
				type="button"
				class="btn join-item btn-ghost"
				onclick={clear}
				aria-label="Hapus pencarian"
			>
				<X size={20} />
			</button>
		{/if}
	</div>
</div>
