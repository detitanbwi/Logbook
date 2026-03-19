<script lang="ts">
	import type { Snippet } from 'svelte';
	import LoadingSkeleton from './LoadingSkeleton.svelte';
	import EmptyState from './EmptyState.svelte';

	let { 
		head, 
		children,
		loading = false,
		empty = false,
		emptyTitle = 'Data tidak ditemukan',
		emptyDescription = 'Tidak ada data yang sesuai dengan kriteria.',
		columnsCount = 5
	}: { 
		head: Snippet; 
		children?: Snippet;
		loading?: boolean;
		empty?: boolean;
		emptyTitle?: string;
		emptyDescription?: string;
		columnsCount?: number;
	} = $props();
</script>

<div class="overflow-x-auto rounded-box border border-base-300 bg-base-100 shadow-sm" role="region" aria-label="Tabel Data">
	<table class="table w-full table-zebra">
		<thead class="bg-base-200 text-base-content">
			{@render head()}
		</thead>
		<tbody>
			{#if loading}
				<tr>
					<td colspan={columnsCount} class="p-8">
						<LoadingSkeleton rows={5} cols={columnsCount} />
					</td>
				</tr>
			{:else if empty || !children}
				<tr>
					<td colspan={columnsCount} class="p-0">
						<div class="p-8">
							<EmptyState title={emptyTitle} description={emptyDescription} />
						</div>
					</td>
				</tr>
			{:else}
				{@render children()}
			{/if}
		</tbody>
	</table>
</div>
