<script lang="ts">
	import type { Snippet } from 'svelte';
	import { browser } from '$app/environment';
	import { tick } from 'svelte';

	let {
		trigger,
		content,
		open = $bindable(false),
		class: className = ''
	}: {
		trigger: Snippet;
		content: Snippet;
		open?: boolean;
		class?: string;
	} = $props();

	let triggerEl: HTMLButtonElement;
	let position = $state({ top: 0, left: 0 });

	async function calculatePosition() {
		if (!triggerEl || !browser) return;

		const triggerRect = triggerEl.getBoundingClientRect();
		const padding = 8;
		const viewportWidth = window.innerWidth;
		const viewportHeight = window.innerHeight;

		// Default to bottom-end placement
		let top = triggerRect.bottom + padding;
		let left = triggerRect.right;

		// Estimate popover width (can be refined)
		const estimatedWidth = 320;

		// Adjust horizontal position
		left = triggerRect.right - estimatedWidth;
		if (left < padding) {
			left = padding;
		}

		// Adjust vertical position if would overflow
		// We'll handle this dynamically with max-height
		if (top < padding) {
			top = padding;
		}

		position = { top, left };
	}

	async function toggle() {
		open = !open;
		if (open) {
			await tick();
			await calculatePosition();
		}
	}

	function close() {
		open = false;
	}

	function handleClickOutside(e: MouseEvent) {
		if (open && triggerEl && !triggerEl.contains(e.target as Node)) {
			close();
		}
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Escape' && open) {
			close();
		}
	}

	$effect(() => {
		if (browser && open) {
			document.addEventListener('click', handleClickOutside);
			document.addEventListener('keydown', handleKeydown);
			window.addEventListener('resize', calculatePosition);
			window.addEventListener('scroll', calculatePosition, true);
		}

		return () => {
			if (browser) {
				document.removeEventListener('click', handleClickOutside);
				document.removeEventListener('keydown', handleKeydown);
				window.removeEventListener('resize', calculatePosition);
				window.removeEventListener('scroll', calculatePosition, true);
			}
		};
	});
</script>

<button
	bind:this={triggerEl}
	type="button"
	onclick={toggle}
	aria-expanded={open}
	aria-haspopup="dialog"
	class={className}
>
	{@render trigger()}
</button>

{#if browser && open}
	{@html '<div class="fixed inset-0 z-[9998]" aria-hidden="true"></div>'}
	<div
		class="fixed z-[9999] max-h-[80vh] w-80 overflow-auto rounded-box border border-base-200 bg-base-100 p-4 shadow-xl"
		style="top: {position.top}px; left: {position.left}px;"
		role="dialog"
		aria-modal="false"
	>
		{@render content()}
	</div>
{/if}
