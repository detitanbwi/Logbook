<script lang="ts">
	import { Bell } from 'lucide-svelte';

	interface Props {
		loading?: boolean;
		errorMessage?: string | null;
		empty?: boolean;
		hasFilters?: boolean;
		onRetry: () => void;
	}

	let {
		loading = false,
		errorMessage = null,
		empty = false,
		hasFilters = false,
		onRetry
	}: Props = $props();
</script>

{#if loading}
	<div class="divide-y divide-base-200">
		{#each Array(5) as _, index (`skeleton-${index}`)}
			<div class="flex items-start gap-4 p-4">
				<div class="mt-1 flex-shrink-0">
					<div class="h-5 w-5 animate-pulse rounded-full bg-base-300"></div>
				</div>
				<div class="min-w-0 flex-1">
					<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
						<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
						<div class="h-3 w-24 animate-pulse rounded bg-base-300"></div>
					</div>
					<div class="mt-2 h-4 w-full max-w-md animate-pulse rounded bg-base-300"></div>
				</div>
				<div class="ml-4 flex-shrink-0">
					<div class="h-6 w-24 animate-pulse rounded bg-base-300"></div>
				</div>
			</div>
		{/each}
	</div>
{:else if errorMessage}
	<div class="flex flex-col items-center justify-center p-12 text-center">
		<p class="text-lg font-semibold text-error">Gagal memuat notifikasi</p>
		<p class="mt-1 text-sm text-base-content/70">{errorMessage}</p>
		<button class="btn btn-sm btn-outline mt-4" onclick={onRetry}>
			Coba Lagi
		</button>
	</div>
{:else if empty}
	<div class="flex flex-col items-center justify-center p-12 text-base-content/50">
		<Bell class="mb-4 h-12 w-12 opacity-50" />
		<p class="text-lg font-medium">
			{hasFilters ? 'Tidak ada notifikasi yang cocok' : 'Belum ada notifikasi'}
		</p>
		<p class="text-sm">
			{hasFilters
				? 'Coba ubah filter untuk melihat notifikasi lainnya.'
				: 'Anda akan melihat pemberitahuan aktivitas di sini.'}
		</p>
	</div>
{/if}
