<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		isOpen = $bindable(false),
		title,
		children,
		actions,
		panelClass = ''
	}: {
		isOpen: boolean;
		title: string;
		children: Snippet;
		actions?: Snippet;
		panelClass?: string;
	} = $props();

	let dialog: HTMLDialogElement;

	$effect(() => {
		if (isOpen && dialog && !dialog.open) {
			dialog.showModal();
			// Focus management: focus first focusable element
			const focusable = dialog.querySelector(
				'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			) as HTMLElement;
			if (focusable) {
				focusable.focus();
			}
		} else if (!isOpen && dialog && dialog.open) dialog.close();
	});

	function close() {
		isOpen = false;
	}
</script>

<dialog
	bind:this={dialog}
	class="modal modal-bottom sm:modal-middle"
	onclose={close}
	aria-labelledby="modal-title"
	aria-modal="true"
>
	<div class={`modal-box ${panelClass}`.trim()} role="document">
		<h3 id="modal-title" class="mb-4 text-lg font-bold">{title}</h3>

		<div class="py-2">
			{@render children()}
		</div>

		<div class="modal-action">
			<form method="dialog">
				<!-- if there is a button in form, it will close the modal -->
				<button class="btn btn-ghost" onclick={close}>Tutup</button>
				{#if actions}
					{@render actions()}
				{/if}
			</form>
		</div>
	</div>
	<form method="dialog" class="modal-backdrop">
		<button onclick={close}>close</button>
	</form>
</dialog>
