<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import { browser } from '$app/environment';
	import { logbookStore } from '$lib/stores/logbook.svelte';
	import { goto } from '$app/navigation';
	import StatusBadge from '$lib/components/ui/StatusBadge.svelte';

	let kpiList = $derived(logbookStore.currentLogbook?.details || []);
	let isDraft = $derived(logbookStore.currentLogbook?.status === 'DRAFT');
	let isSubmitting = $state(false);
	let errorMsg = $state<string | null>(null);
	let gpsLoading = $state(false);

	// Photo upload state
	let selectedPhotos = $state<File[]>([]);
	let photoPreviews = $state<string[]>([]);

	/**
	 * Get current GPS location using browser geolocation API
	 * @returns Promise resolving to coordinates string "latitude,longitude"
	 * @throws Error if geolocation is not supported or permission denied
	 */
	async function getGpsLocation(): Promise<string> {
		if (!browser) {
			throw new Error('Geolocation hanya tersedia di browser');
		}

		if (!navigator.geolocation) {
			throw new Error('Browser Anda tidak mendukung geolocation');
		}

		gpsLoading = true;

		return new Promise((resolve, reject) => {
			navigator.geolocation.getCurrentPosition(
				(position) => {
					gpsLoading = false;
					const coords = `${position.coords.latitude},${position.coords.longitude}`;
					resolve(coords);
				},
				(error) => {
					gpsLoading = false;
					let message: string;
					switch (error.code) {
						case error.PERMISSION_DENIED:
							message = 'Izin lokasi ditolak. Mohon aktifkan izin lokasi di browser Anda.';
							break;
						case error.POSITION_UNAVAILABLE:
							message = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
							break;
						case error.TIMEOUT:
							message = 'Waktu permintaan lokasi habis. Silakan coba lagi.';
							break;
						default:
							message = 'Gagal mendapatkan lokasi. Silakan coba lagi.';
					}
					reject(new Error(message));
				},
				{
					enableHighAccuracy: true,
					timeout: 10000,
					maximumAge: 0
				}
			);
		});
	}

	onMount(async () => {
		try {
			await logbookStore.fetchLogbooks();
			const activeLogbook = logbookStore.logbooks.find((l: any) => l.status === 'DRAFT');
			if (activeLogbook) {
				await logbookStore.fetchLogbookById(activeLogbook.id);
			} else {
				logbookStore.currentLogbook = null;
			}
		} catch (err) {
			console.error(err);
		}
	});

	// Cleanup preview URLs when component is destroyed
	onDestroy(() => {
		photoPreviews.forEach((url) => URL.revokeObjectURL(url));
	});

	function handlePhotoSelect(event: Event) {
		const input = event.target as HTMLInputElement;
		if (input.files && input.files.length > 0) {
			// Revoke previous preview URLs to prevent memory leaks
			photoPreviews.forEach((url) => URL.revokeObjectURL(url));

			// Store selected files and create preview URLs
			selectedPhotos = Array.from(input.files);
			photoPreviews = selectedPhotos.map((file) => URL.createObjectURL(file));
		}
	}

	function removePhoto(index: number) {
		// Revoke the preview URL being removed
		URL.revokeObjectURL(photoPreviews[index]);

		// Remove from arrays
		selectedPhotos = selectedPhotos.filter((_, i) => i !== index);
		photoPreviews = photoPreviews.filter((_, i) => i !== index);
	}

	async function startKerja() {
		errorMsg = null;
		try {
			const gpsLocation = await getGpsLocation();
			await logbookStore.startLogbook({ gps_location_start: gpsLocation });
			const activeLogbook = logbookStore.logbooks.find((l: any) => l.status === 'DRAFT');
			if (activeLogbook) {
				await logbookStore.fetchLogbookById(activeLogbook.id);
			}
		} catch (err: any) {
			errorMsg = err.message || 'Failed to start logbook';
		}
	}

	async function toggleTask(detailId: string, currentStatus: boolean) {
		if (!logbookStore.currentLogbook) return;
		try {
			await logbookStore.toggleKpi(logbookStore.currentLogbook.id, detailId, {
				is_finished: !currentStatus
			});
		} catch (err: any) {
			console.error(err);
		}
	}

	async function submitLogbook() {
		if (!logbookStore.currentLogbook) return;
		errorMsg = null;
		isSubmitting = true;
		try {
			const gpsLocation = await getGpsLocation();

			// Create FormData for file upload
			const formData = new FormData();
			formData.append('gps_location_end', gpsLocation);

			// Append selected photos
			selectedPhotos.forEach((photo, index) => {
				formData.append(`gambar_bukti[${index}]`, photo);
			});

			await logbookStore.submitLogbook(logbookStore.currentLogbook.id, formData);

			// Clear photos after successful submission
			photoPreviews.forEach((url) => URL.revokeObjectURL(url));
			selectedPhotos = [];
			photoPreviews = [];

			logbookStore.currentLogbook = null;
			goto('/staff/history');
		} catch (err: any) {
			errorMsg = err.message || 'Failed to submit logbook';
		} finally {
			isSubmitting = false;
		}
	}
</script>

<div class="flex flex-col gap-6">
	<div class="flex items-center justify-between">
		<h1 class="text-3xl font-bold">Logbook Harian</h1>
		{#if logbookStore.currentLogbook}
			<StatusBadge status={logbookStore.currentLogbook.status} />
		{/if}
	</div>

	{#if errorMsg}
		<div class="alert alert-error shadow-sm">
			<span>{errorMsg}</span>
		</div>
	{/if}

	{#if logbookStore.isLoading && !logbookStore.currentLogbook && logbookStore.logbooks.length === 0}
		<div class="flex justify-center p-8">
			<span class="loading loading-lg loading-spinner text-primary"></span>
		</div>
	{:else if !logbookStore.currentLogbook}
		<div class="card border border-base-300 bg-base-100 shadow-sm">
			<div class="card-body items-center text-center">
				<h2 class="card-title">Mulai Kerja</h2>
				<p>
					Anda belum memulai logbook hari ini. Klik tombol di bawah untuk memulai pencatatan dan
					merekam lokasi GPS Anda.
				</p>
				<div class="mt-4 card-actions">
					<button
						class="btn px-8 btn-primary"
						onclick={startKerja}
						disabled={logbookStore.isLoading || gpsLoading}
					>
						{#if logbookStore.isLoading || gpsLoading}
							<span class="loading loading-sm loading-spinner"></span>
						{/if}
						{gpsLoading ? 'Mendapatkan Lokasi...' : 'Start Kerja'}
					</button>
				</div>
			</div>
		</div>
	{:else}
		<div class="card border border-base-300 bg-base-100 shadow-sm">
			<div class="card-body">
				<h2 class="mb-4 card-title">Daftar KPI (Tugas Hari Ini)</h2>

				{#if kpiList.length === 0}
					<p class="text-base-content/70">Belum ada tugas KPI yang diberikan untuk hari ini.</p>
				{:else}
					<div class="flex flex-col gap-3">
						{#each kpiList as kpi (kpi.id)}
							<label
								class="label cursor-pointer justify-start gap-4 rounded-lg border border-base-200 p-3 transition-colors hover:bg-base-200"
							>
								<input
									type="checkbox"
									class="checkbox checkbox-primary"
									checked={kpi.is_finished}
									disabled={!isDraft || logbookStore.isLoading}
									onchange={() => toggleTask(kpi.id, kpi.is_finished)}
								/>
								<span
									class="label-text {kpi.is_finished ? 'text-base-content/50 line-through' : ''}"
								>
									{kpi.kpi?.nama || 'Tugas Tanpa Nama'}
								</span>
							</label>
						{/each}
					</div>
				{/if}

				{#if isDraft}
					<div class="divider"></div>
					<div class="form-control mb-4">
						<label class="label" for="photo-upload">
							<span class="label-text font-medium">Upload Bukti Foto (Opsional)</span>
						</label>
						<input
							id="photo-upload"
							type="file"
							class="file-input-bordered file-input w-full max-w-xs"
							multiple
							accept="image/*"
							onchange={handlePhotoSelect}
						/>
					</div>

					{#if photoPreviews.length > 0}
						<div class="mb-4">
							<span class="label-text font-medium">Preview Foto ({photoPreviews.length})</span>
							<div class="mt-2 flex flex-wrap gap-3">
								{#each photoPreviews as preview, index (preview)}
									<div class="relative">
										<img
											src={preview}
											alt="Preview {index + 1}"
											class="h-24 w-24 rounded-lg border border-base-300 object-cover"
										/>
										<button
											type="button"
											class="btn absolute -top-2 -right-2 btn-circle btn-xs btn-error"
											onclick={() => removePhoto(index)}
											aria-label="Hapus foto"
										>
											✕
										</button>
									</div>
								{/each}
							</div>
						</div>
					{/if}

					<div class="mt-2 card-actions justify-end">
						<button
							class="btn btn-primary"
							onclick={submitLogbook}
							disabled={isSubmitting || logbookStore.isLoading || gpsLoading}
						>
							{#if isSubmitting || gpsLoading}
								<span class="loading loading-sm loading-spinner"></span>
							{/if}
							{gpsLoading ? 'Mendapatkan Lokasi...' : 'Submit Logbook'}
						</button>
					</div>
				{/if}
			</div>
		</div>
	{/if}
</div>
