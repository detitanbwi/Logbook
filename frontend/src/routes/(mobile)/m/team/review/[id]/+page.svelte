<script lang="ts">
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import { managerLogbookService } from '$lib/api/services/managerLogbookService';
	import { toastStore } from '$lib/stores/toast.svelte';
	import type { Logbook, LogbookKpiDetail } from '$lib/types';
	import UserAvatar from '$lib/components/ui/UserAvatar.svelte';
	import LocationMap from '$lib/components/ui/LocationMap.svelte';
	import { resolveStorageUrl } from '$lib/utils/asset-url';

	let logbookId = $derived($page.params.id);

	function getFileExtension(filename: string): string {
		return filename.split('.').pop()?.toLowerCase() || '';
	}
	function isImageFile(filename: string): boolean {
		return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(getFileExtension(filename));
	}
	function isPdfFile(filename: string): boolean {
		return getFileExtension(filename) === 'pdf';
	}
	function getAttachmentUrl(path: string): string {
		return resolveStorageUrl(path);
	}

	let logbook = $state<Logbook | null>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let rating = $state(0);
	let comment = $state('');
	let submitting = $state(false);

	$effect(() => {
		if (logbookId) {
			fetchLogbook(logbookId);
		}
	});

	async function fetchLogbook(id: string) {
		loading = true;
		error = null;
		try {
			const res = await staffLogbookService.getLogbookById(id);
			logbook = res;
	} catch (err: unknown) {
		error = err instanceof Error ? err.message : 'Failed to fetch logbook';
	} finally {
			loading = false;
		}
	}

	async function handleReview(decision: 'ACCEPTED' | 'REJECTED') {
		if (!logbookId) return;
		if (rating === 0) {
			toastStore.error('Silakan berikan rating (1-5 bintang).');
			return;
		}
		if (!comment.trim()) {
			toastStore.error('Silakan berikan komentar / catatan reviewer.');
			return;
		}

		submitting = true;
		try {
			await managerLogbookService.reviewLogbook(logbookId, {
				decision,
				rating,
				reviewer_comment: comment
			});
			toastStore.success(
				decision === 'ACCEPTED'
					? 'Logbook berhasil disetujui.'
					: 'Logbook berhasil ditolak.'
			);
			goto('/m/team');
	} catch (err: unknown) {
		toastStore.error(err instanceof Error ? err.message : 'Gagal menyimpan review.');
	} finally {
			submitting = false;
		}
	}

	function formatDate(dateString: string | undefined) {
		if (!dateString) return '-';
		return new Date(dateString).toLocaleDateString('id-ID', {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});
	}

	function getStatusColor(status: string | undefined) {
		switch (status) {
			case 'SUBMITTED':
				return 'badge-warning';
			case 'ACCEPTED':
				return 'badge-success';
			case 'REJECTED':
				return 'badge-error';
			default:
				return 'badge-ghost';
		}
	}
</script>

<div class="min-h-screen bg-base-200 pb-24">
	<div class="navbar bg-base-100 sticky top-0 z-10 shadow-sm">
		<div class="flex-none">
			<button class="btn btn-square btn-ghost" aria-label="Kembali ke daftar tim" onclick={() => goto('/m/team')}>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			</button>
		</div>
		<div class="flex-1">
			<h1 class="text-lg font-bold">Review Logbook</h1>
		</div>
	</div>

	<main class="p-4 space-y-4">
		{#if loading}
			<div class="flex justify-center py-10">
				<span class="loading loading-spinner loading-lg text-primary"></span>
			</div>
		{:else if error}
			<div class="alert alert-error shadow-sm">
				<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
				<span>{error}</span>
			</div>
		{:else if logbook}
			<div class="card bg-base-100 shadow-sm border border-base-300">
				<div class="card-body p-4 flex flex-row items-center gap-4">
					<UserAvatar foto={logbook.user?.foto} fotoUrl={logbook.user?.foto_url} name={logbook.user?.nama} size="md" />
					<div>
						<h2 class="card-title text-base">{logbook.user?.nama || 'Unknown User'}</h2>
						<p class="text-sm text-base-content/70">NPP: {logbook.user?.npp || '-'}</p>
					</div>
				</div>
			</div>

			<div class="card bg-base-100 shadow-sm border border-base-300">
				<div class="card-body p-4">
					<div class="flex justify-between items-center mb-2">
						<h3 class="font-bold">Logbook Details</h3>
						<div class="badge {getStatusColor(logbook.status)}">{logbook.status || 'DRAFT'}</div>
					</div>
					<div class="grid grid-cols-2 gap-2 text-sm">
						<div>
							<span class="text-base-content/70 block text-xs">Tanggal</span>
							<span class="font-medium">{formatDate(logbook.tanggal)}</span>
						</div>
						<div>
							<span class="text-base-content/70 block text-xs">Waktu Kerja</span>
							<span class="font-medium">{logbook.start_kerja || '-'} - {logbook.end_kerja || '-'}</span>
						</div>
					</div>
				</div>
			</div>

			{#if logbook.lokasi_lat && logbook.lokasi_lng}
				<div class="card bg-base-100 shadow-sm border border-base-300 overflow-hidden">
					<div class="card-body p-4 pb-0">
						<h3 class="font-bold text-sm">Lokasi GPS</h3>
					</div>
					<div class="p-2">
						<LocationMap lat={logbook.lokasi_lat} lng={logbook.lokasi_lng} zoom={15} height="h-48" />
					</div>
				</div>
			{/if}

			<h3 class="font-bold px-1 mt-6">Capaian KPI</h3>
			
			<div class="space-y-3">
				{#each (logbook.details || []) as detail}
					<div class="card bg-base-100 shadow-sm border border-base-300">
						<div class="card-body p-4">
							<h4 class="font-bold text-sm mb-2">{detail.kpi_nama}</h4>
							
							<div class="flex justify-between items-end mb-1 text-xs">
								<span class="text-base-content/70">Progress</span>
								<span class="font-bold">{detail.capaian_angka} / {detail.target_angka} {detail.satuan}</span>
							</div>
							
							<progress 
								class="progress progress-primary w-full" 
								value={detail.capaian_angka || 0} 
								max={detail.target_angka || 100}
							></progress>

						{#if detail.lampiran_file}
							<div class="mt-4 border-t border-base-200 pt-3">
								<h5 class="text-xs font-semibold text-base-content/70 mb-2">Lampiran:</h5>
								<div class="flex flex-col gap-2">
									{#if isImageFile(detail.lampiran_file)}
										<img 
											src={getAttachmentUrl(detail.lampiran_file)} 
											alt="Lampiran" 
											class="w-full h-auto max-h-48 object-contain rounded bg-base-200"
										/>
										<a href={getAttachmentUrl(detail.lampiran_file)} target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline btn-neutral justify-start">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
												<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
												<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
											</svg>
											<span class="truncate">Lihat Gambar</span>
										</a>
									{:else if isPdfFile(detail.lampiran_file)}
										<div class="flex items-center gap-2 text-sm font-medium text-error">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
											<span class="truncate">{detail.lampiran_file.split('/').pop()}</span>
										</div>
										<a href={getAttachmentUrl(detail.lampiran_file)} target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline btn-error justify-start">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
												<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
												<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
											</svg>
											<span class="truncate">Preview PDF</span>
										</a>
									{:else}
										<a href={getAttachmentUrl(detail.lampiran_file)} target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline btn-neutral justify-start">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
												<path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
											</svg>
											<span class="truncate">Download: {detail.lampiran_file.split('/').pop()}</span>
										</a>
									{/if}
								</div>
							</div>
						{/if}
						</div>
					</div>
				{/each}
				
				{#if !logbook.details || logbook.details.length === 0}
					<div class="text-center p-4 text-base-content/50 text-sm">
						Belum ada capaian KPI yang diisi.
					</div>
				{/if}
			</div>

			{#if logbook.status === 'SUBMITTED'}
				<div class="card bg-base-100 shadow-sm border border-base-300 mt-6">
					<div class="card-body p-4">
						<h3 class="font-bold border-b border-base-200 pb-2 mb-4">Manager Review</h3>
						
						<div class="form-control w-full mb-4">
							<div class="label pt-0"><span class="label-text font-medium">Rating Capaian <span class="text-error">*</span></span></div>
							<div class="rating rating-lg">
								<input type="radio" name="rating-2" class="rating-hidden" checked={rating === 0} value={0} onchange={() => rating = 0} />
								<input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400" checked={rating === 1} value={1} onchange={() => rating = 1} />
								<input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400" checked={rating === 2} value={2} onchange={() => rating = 2} />
								<input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400" checked={rating === 3} value={3} onchange={() => rating = 3} />
								<input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400" checked={rating === 4} value={4} onchange={() => rating = 4} />
								<input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400" checked={rating === 5} value={5} onchange={() => rating = 5} />
							</div>
							<div class="label pb-0"><span class="label-text-alt text-base-content/50">{rating} dari 5 bintang</span></div>
						</div>

						<div class="form-control w-full mb-6">
							<div class="label"><span class="label-text font-medium">Komentar / Catatan <span class="text-error">*</span></span></div>
							<textarea 
								class="textarea textarea-bordered h-24 w-full" 
								placeholder="Berikan catatan atas capaian logbook ini..."
								bind:value={comment}
							></textarea>
						</div>

						<div class="flex gap-3">
							<button 
								class="btn btn-error flex-1 text-white" 
								onclick={() => handleReview('REJECTED')}
								disabled={submitting}
							>
								{#if submitting}
									<span class="loading loading-spinner loading-xs"></span>
								{:else}
									Tolak
								{/if}
							</button>
							<button 
								class="btn btn-success flex-1 text-white" 
								onclick={() => handleReview('ACCEPTED')}
								disabled={submitting}
							>
								{#if submitting}
									<span class="loading loading-spinner loading-xs"></span>
								{:else}
									Setujui
								{/if}
							</button>
						</div>
					</div>
				</div>
			{/if}
		{/if}
	</main>
</div>
