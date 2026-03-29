<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { managerLogbookService } from '$lib/api/services/managerLogbookService';

	let reviews = $state<any[]>([]);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let search = $derived.by(() => $page.url.searchParams.get('search') || '');

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);
		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
		});
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	$effect(() => {
		(async () => {
			loading = true;
			error = null;
			try {
				const res = await managerLogbookService.getPendingReviews({
					per_page: 100,
					search: search || undefined,
					sort_by: 'created_at',
					sort_dir: 'desc'
				});
				reviews = Array.isArray(res) ? res : res.data || [];
			} catch (err: any) {
				error = err?.message || 'Gagal memuat daftar review.';
			} finally {
				loading = false;
			}
		})();
	});

	function openReview(logbookId: string) {
		goto(`/m/team/review/${logbookId}`);
	}

	function formatDate(value: string | undefined) {
		if (!value) return '-';
		return new Date(value).toLocaleDateString('id-ID', {
			weekday: 'short',
			year: 'numeric',
			month: 'short',
			day: 'numeric'
		});
	}

	const pendingCount = $derived(reviews.length);
</script>

<div class="min-h-screen bg-base-200 pb-24">
	<div class="navbar sticky top-0 z-10 bg-base-100 shadow-sm">
		<div class="flex-none">
			<button class="btn btn-square btn-ghost" aria-label="Kembali" onclick={() => goto('/m/team')}>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					stroke-width="1.5"
					stroke="currentColor"
					class="h-6 w-6"
				>
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			</button>
		</div>
		<div class="flex-1">
			<h1 class="text-lg font-bold">Team Review</h1>
		</div>
	</div>

	<main class="space-y-4 p-4">
		<div
			class="rounded-2xl border border-primary/15 bg-gradient-to-br from-primary/10 via-base-100 to-base-100 p-4 shadow-sm"
		>
			<div class="text-xs font-semibold tracking-[0.12em] text-primary/80 uppercase">
				Team Review
			</div>
			<h2 class="mt-1 text-xl font-bold">Antrian Review Logbook</h2>
			<p class="mt-1 text-sm text-base-content/70">
				{pendingCount} logbook menunggu review. Ketuk kartu untuk buka detail review.
			</p>
		</div>

		<input
			type="text"
			class="input-bordered input w-full"
			placeholder="Cari nama staff..."
			value={search}
			onchange={(e) => updateUrl({ search: e.currentTarget.value })}
		/>

		{#if loading}
			<div class="flex justify-center py-10">
				<span class="loading loading-lg loading-spinner text-primary"></span>
			</div>
		{:else if error}
			<div class="alert alert-error shadow-sm">
				<span>{error}</span>
			</div>
		{:else if reviews.length === 0}
			<div
				class="rounded-lg border border-base-300 bg-base-100 p-4 text-center text-base-content/60"
			>
				Tidak ada logbook yang menunggu review.
			</div>
		{:else}
			<div class="space-y-3">
				{#each reviews as review (review.id)}
					<button
						type="button"
						class="group card w-full border border-base-300 bg-base-100 p-4 text-left shadow-sm transition hover:border-primary/40 hover:bg-base-200 active:scale-[0.99]"
						onclick={() => openReview(review.id)}
					>
						<div class="flex items-start justify-between gap-3">
							<div>
								<div class="font-semibold">{review.user?.nama ?? 'Unknown User'}</div>
								<div class="text-xs text-base-content/70">{review.user?.npp ?? '-'}</div>
								<div class="mt-1 text-xs text-base-content/70">
									{formatDate(review.tanggal || review.created_at)}
								</div>
								<div class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-primary">
									Lihat detail review
									<svg
										xmlns="http://www.w3.org/2000/svg"
										class="h-3.5 w-3.5 transition group-hover:translate-x-0.5"
										viewBox="0 0 20 20"
										fill="currentColor"
									>
										<path
											fill-rule="evenodd"
											d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
											clip-rule="evenodd"
										/>
									</svg>
								</div>
							</div>
							<span class="badge badge-warning">SUBMITTED</span>
						</div>
					</button>
				{/each}
			</div>
		{/if}
	</main>
</div>
