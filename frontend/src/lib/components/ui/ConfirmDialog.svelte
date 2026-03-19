<script lang="ts">
	let {
		title = 'Konfirmasi',
		message = 'Apakah Anda yakin ingin melanjutkan?',
		confirmText = 'Ya',
		cancelText = 'Batal',
		type = 'warning',
		open = $bindable(false),
		onConfirm
	}: {
		title?: string;
		message?: string;
		confirmText?: string;
		cancelText?: string;
		type?: 'info' | 'warning' | 'error';
		open: boolean;
		onConfirm: () => void | Promise<void>;
	} = $props();

	let dialog: HTMLDialogElement;
	let loading = $state(false);

	$effect(() => {
		if (dialog) {
			if (open && !dialog.open) dialog.showModal();
			else if (!open && dialog.open) dialog.close();
		}
	});

	async function handleConfirm() {
		try {
			loading = true;
			await onConfirm();
			open = false;
		} finally {
			loading = false;
		}
	}

	function handleCancel() {
		open = false;
	}

	let btnClass = $derived(
		type === 'error' ? 'btn-error' :
		type === 'warning' ? 'btn-warning' :
		'btn-primary'
	);
</script>

<dialog bind:this={dialog} class="modal" onclose={() => open = false}>
	<div class="modal-box">
		<h3 class="font-bold text-lg">{title}</h3>
		<p class="py-4">{message}</p>
		<div class="modal-action">
			<button class="btn btn-ghost" onclick={handleCancel} disabled={loading}>{cancelText}</button>
			<button class="btn {btnClass}" onclick={handleConfirm} disabled={loading}>
				{#if loading}
					<span class="loading loading-spinner loading-sm"></span>
				{/if}
				{confirmText}
			</button>
		</div>
	</div>
	<form method="dialog" class="modal-backdrop">
		<button disabled={loading}>Tutup</button>
	</form>
</dialog>
