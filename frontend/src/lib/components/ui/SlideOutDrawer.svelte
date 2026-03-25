<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		isOpen = $bindable(false),
		title,
		width = 'max-w-md',
		children,
		actions
	}: {
		isOpen: boolean;
		title: string;
		width?: string;
		children: Snippet;
		actions?: Snippet;
	} = $props();

	function close() {
		isOpen = false;
	}

	function handleBackdropClick(e: MouseEvent) {
		if (e.target === e.currentTarget) {
			close();
		}
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Escape') {
			close();
		}
	}
</script>

<svelte:window onkeydown={handleKeydown} />

{#if isOpen}
	<button
		class="fixed inset-0 z-40 cursor-default border-none bg-black/50 transition-opacity"
		onclick={handleBackdropClick}
		aria-label="Close drawer"
		tabindex="-1"
	></button>

	<!-- Drawer panel -->
	<div
		class="fixed inset-y-0 right-0 z-50 flex w-full {width} flex-col bg-base-100 shadow-xl transition-transform"
		role="dialog"
		aria-modal="true"
		aria-labelledby="drawer-title"
	>
		<!-- Header -->
		<div class="flex items-center justify-between border-b border-base-300 px-4 py-3">
			<h2 id="drawer-title" class="text-lg font-bold">{title}</h2>
			<button class="btn btn-ghost btn-sm btn-circle" onclick={close} aria-label="Close">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
					<path
						fill-rule="evenodd"
						d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
						clip-rule="evenodd"
					/>
				</svg>
			</button>
		</div>

		<!-- Content -->
		<div class="flex-1 overflow-y-auto p-4">
			{@render children()}
		</div>

		<!-- Footer actions -->
		{#if actions}
			<div class="border-t border-base-300 px-4 py-3">
				{@render actions()}
			</div>
		{/if}
	</div>
{/if}
