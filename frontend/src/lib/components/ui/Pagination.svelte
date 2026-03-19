<script lang="ts">
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';

	let { meta, onPageSizeChange }: {
		meta: {
			current_page: number;
			last_page: number;
			total: number;
			per_page: number;
		} | null;
		onPageSizeChange?: (size: number) => void;
	} = $props();

	let current_page = $derived(meta?.current_page || 1);
	let last_page = $derived(meta?.last_page || 1);
	let per_page = $derived(meta?.per_page || 15);

	function getPageUrl(pageNum: number) {
		const url = new URL($page.url);
		url.searchParams.set('page', pageNum.toString());
		return url.toString();
	}
	
	function handlePageSizeChange(e: Event) {
		const select = e.target as HTMLSelectElement;
		const size = parseInt(select.value);
		if (onPageSizeChange) {
			onPageSizeChange(size);
		} else {
			// Default behavior if not handled - use client-side navigation
			const url = new URL($page.url);
			url.searchParams.set('per_page', size.toString());
			url.searchParams.set('page', '1'); // Reset to page 1
			goto(url.pathname + url.search, { replaceState: true, noScroll: false, keepFocus: false });
		}
	}
</script>

{#if meta && meta.total > 0}
	<nav class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4" aria-label="Navigasi Halaman">
		<div class="flex items-center gap-2 text-sm text-base-content/70">
			<label for="per_page_select">Tampilkan</label>
			<select 
				id="per_page_select"
				class="select select-bordered select-sm" 
				value={per_page}
				onchange={handlePageSizeChange}
				aria-label="Jumlah item per halaman"
			>
				<option value={10}>10</option>
				<option value={15}>15</option>
				<option value={25}>25</option>
				<option value={50}>50</option>
				<option value={100}>100</option>
			</select>
			<span>dari total {meta.total} data</span>
		</div>

		{#if meta.last_page > 1}
			<div class="join">
				{#if current_page > 1}
					<a href={getPageUrl(current_page - 1)} class="btn join-item btn-sm" aria-label="Halaman Sebelumnya">«</a>
				{:else}
					<button class="btn btn-disabled join-item btn-sm" aria-label="Halaman Sebelumnya">«</button>
				{/if}

				<span class="no-animation btn pointer-events-none join-item btn-sm" aria-current="page">
					Halaman {current_page} dari {last_page}
				</span>

				{#if current_page < last_page}
					<a href={getPageUrl(current_page + 1)} class="btn join-item btn-sm" aria-label="Halaman Berikutnya">»</a>
				{:else}
					<button class="btn btn-disabled join-item btn-sm" aria-label="Halaman Berikutnya">»</button>
				{/if}
			</div>
		{/if}
	</nav>
{/if}
