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

	function handleInput(e: Event) {
		const target = e.target as HTMLInputElement;
		value = target.value;

		clearTimeout(timeout);
		timeout = setTimeout(() => {
			onSearch?.(value);
		}, debounce);
	}

	function clear() {
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
			{value}
			oninput={handleInput}
			class="input-bordered input join-item w-full"
		/>
		{#if value}
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
