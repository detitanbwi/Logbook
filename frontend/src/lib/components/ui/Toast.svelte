<script lang="ts">
	import { toastStore } from '$lib/stores/toast.svelte';
	import { fly, fade } from 'svelte/transition';
	import { flip } from 'svelte/animate';

	let toasts = $derived(toastStore.toasts);
</script>

<div class="toast toast-end toast-top z-[1000]">
	{#each toasts as toast (toast.id)}
		<div
			animate:flip={{ duration: 300 }}
			in:fly={{ y: -20, duration: 300 }}
			out:fade={{ duration: 200 }}
			class="alert flex flex-row items-center gap-2 shadow-lg"
			class:alert-info={toast.type === 'info'}
			class:alert-success={toast.type === 'success'}
			class:alert-warning={toast.type === 'warning'}
			class:alert-error={toast.type === 'error'}
		>
			{#if toast.type === 'info'}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					class="h-6 w-6 shrink-0 stroke-current"
					><path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
					></path></svg
				>
			{:else if toast.type === 'success'}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-6 w-6 shrink-0 stroke-current"
					fill="none"
					viewBox="0 0 24 24"
					><path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
					/></svg
				>
			{:else if toast.type === 'warning'}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-6 w-6 shrink-0 stroke-current"
					fill="none"
					viewBox="0 0 24 24"
					><path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
					/></svg
				>
			{:else if toast.type === 'error'}
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-6 w-6 shrink-0 stroke-current"
					fill="none"
					viewBox="0 0 24 24"
					><path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
					/></svg
				>
			{/if}
			<span>{toast.message}</span>
			<button
				class="btn ml-2 btn-circle btn-ghost btn-xs"
				onclick={() => toastStore.remove(toast.id)}
				aria-label="Tutup notifikasi"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-4 w-4"
					fill="none"
					viewBox="0 0 24 24"
					stroke="currentColor"
					><path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M6 18L18 6M6 6l12 12"
					/></svg
				>
			</button>
		</div>
	{/each}
</div>
