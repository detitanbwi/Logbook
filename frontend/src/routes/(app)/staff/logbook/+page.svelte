<script lang="ts">
	import { onMount } from 'svelte';
	import { logbookStore } from '$lib/stores/logbook.svelte';
	import { staffLogbookService } from '$lib/api/services/staffLogbookService';
	import { goto } from '$app/navigation';
	import StatusBadge from '$lib/components/ui/StatusBadge.svelte';
	import LocationMap from '$lib/components/ui/LocationMap.svelte';
	import type { LogbookKpiDetail } from '$lib/types';
	import { toastStore } from '$lib/stores/toast.svelte';

	let kpiList = $derived(logbookStore.currentLogbook?.details || []);
	let isEditable = $derived(
		logbookStore.currentLogbook?.status === 'SUBMITTED' ||
			logbookStore.currentLogbook?.status === 'REJECTED'
	);
	let isSubmitting = $state(false);
	let errorMsg = $state<string | null>(null);

	let tanggal = $state(new Date().toISOString().split('T')[0]);
	let startKerja = $state('08:00');
	let endKerja = $state('');
	let lokasi = $state('');

	let gpsLat = $state<number | null>(null);
	let gpsLng = $state<number | null>(null);
	let gpsLoading = $state(false);

	function getGPS() {
		if (!navigator.geolocation) {
			toastStore.error('Geolokasi tidak didukung oleh browser ini.');
			return;
		}
		gpsLoading = true;
		navigator.geolocation.getCurrentPosition(
			(pos) => {
				gpsLat = pos.coords.latitude;
				gpsLng = pos.coords.longitude;
				gpsLoading = false;
				toastStore.success('Lokasi GPS berhasil diambil.');
			},
			(err) => {
				gpsLoading = false;
				toastStore.error('Tidak dapat mengambil lokasi GPS.');
				console.error(err);
			},
			{ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
		);
	}

	let kpiProgressValues = $state<Record<string, number>>({});
	let kpiUpdating = $state<Record<string, boolean>>({});
	let attachmentUploading = $state<Record<string, boolean>>({});

	onMount(async () => {
		try {
			await logbookStore.fetchLogbooks();
			const activeLogbook = logbookStore.logbooks.find(
				(l) => l.status === 'SUBMITTED' || l.status === 'REJECTED'
			);
			if (activeLogbook) {
				await logbookStore.fetchLogbookById(activeLogbook.id);
				syncKpiProgressValues();
			} else {
				logbookStore.currentLogbook = null;
			}
		} catch (err) {
			console.error(err);
		}
	});

	function syncKpiProgressValues() {
		if (!logbookStore.currentLogbook?.details) return;
		const values: Record<string, number> = {};
		for (const detail of logbookStore.currentLogbook.details) {
			values[detail.id] = detail.capaian_angka ?? 0;
		}
		kpiProgressValues = values;
	}

	function getProgressPercent(detail: LogbookKpiDetail): number {
		const target = detail.target_angka || 1;
		return Math.min(100, Math.round((detail.capaian_angka / target) * 100));
	}

	async function handleStartLogbook() {
		errorMsg = null;
		try {
			await logbookStore.startLogbook({
				tanggal,
				start_kerja: startKerja,
				end_kerja: endKerja || null,
				lokasi,
				lokasi_lat: gpsLat || undefined,
				lokasi_lng: gpsLng || undefined
			});
			if (logbookStore.currentLogbook) {
				await logbookStore.fetchLogbookById(logbookStore.currentLogbook.id);
				syncKpiProgressValues();
			}
			gpsLat = null;
			gpsLng = null;
		} catch (err: unknown) {
			const e = err as { message?: string };
			errorMsg = e.message || 'Gagal memulai logbook';
		}
	}

	async function updateProgress(detailId: string) {
		if (!logbookStore.currentLogbook) return;
		const value = kpiProgressValues[detailId] ?? 0;
		kpiUpdating = { ...kpiUpdating, [detailId]: true };
		try {
			await staffLogbookService.updateKpiProgress(
				logbookStore.currentLogbook.id,
				detailId,
				{ capaian_angka: value }
			);
			await logbookStore.fetchLogbookById(logbookStore.currentLogbook.id);
			syncKpiProgressValues();
		} catch (err: unknown) {
			const e = err as { message?: string };
			errorMsg = e.message || 'Gagal memperbarui progress KPI';
		} finally {
			kpiUpdating = { ...kpiUpdating, [detailId]: false };
		}
	}

	async function handleAttachmentUpload(detailId: string, event: Event) {
		if (!logbookStore.currentLogbook) return;
		const input = event.target as HTMLInputElement;
		const file = input.files?.[0];
		if (!file) return;

		attachmentUploading = { ...attachmentUploading, [detailId]: true };
		try {
			await staffLogbookService.uploadKpiAttachment(
				logbookStore.currentLogbook.id,
				detailId,
				file
			);
			await logbookStore.fetchLogbookById(logbookStore.currentLogbook.id);
		} catch (err: unknown) {
			const e = err as { message?: string };
			errorMsg = e.message || 'Gagal mengunggah lampiran';
		} finally {
			attachmentUploading = { ...attachmentUploading, [detailId]: false };
			input.value = '';
		}
	}

	async function handleAttachmentDelete(detailId: string) {
		if (!logbookStore.currentLogbook) return;
		attachmentUploading = { ...attachmentUploading, [detailId]: true };
		try {
			await staffLogbookService.deleteKpiAttachment(
				logbookStore.currentLogbook.id,
				detailId
			);
			await logbookStore.fetchLogbookById(logbookStore.currentLogbook.id);
		} catch (err: unknown) {
			const e = err as { message?: string };
			errorMsg = e.message || 'Gagal menghapus lampiran';
		} finally {
			attachmentUploading = { ...attachmentUploading, [detailId]: false };
		}
	}

	async function submitLogbook() {
		if (!logbookStore.currentLogbook) return;
		errorMsg = null;
		isSubmitting = true;
		try {
			await logbookStore.submitLogbook(logbookStore.currentLogbook.id);
			logbookStore.currentLogbook = null;
			goto('/staff/history');
		} catch (err: unknown) {
			const e = err as { message?: string };
			errorMsg = e.message || 'Gagal submit logbook';
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
			<div class="card-body">
				<h2 class="card-title">Mulai Logbook</h2>
				<p class="text-base-content/70">
					Isi detail kerja untuk memulai pencatatan logbook hari ini.
				</p>

				<div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
					<div class="form-control">
						<label class="label" for="tanggal">
							<span class="label-text">Tanggal</span>
						</label>
						<input
							id="tanggal"
							type="date"
							class="input-bordered input w-full"
							bind:value={tanggal}
							required
						/>
					</div>

					<div class="form-control">
						<label class="label" for="lokasi">
							<span class="label-text">Lokasi</span>
						</label>
						<input
							id="lokasi"
							type="text"
							class="input-bordered input w-full"
							bind:value={lokasi}
							placeholder="Nama kantor / lokasi kerja"
							required
						/>
					</div>

					<div class="form-control">
						<label class="label" for="start-kerja">
							<span class="label-text">Jam Mulai</span>
						</label>
						<input
							id="start-kerja"
							type="time"
							class="input-bordered input w-full"
							bind:value={startKerja}
							required
						/>
					</div>

					<div class="form-control">
						<label class="label" for="end-kerja">
							<span class="label-text">Jam Selesai (Opsional)</span>
						</label>
						<input
							id="end-kerja"
							type="time"
							class="input-bordered input w-full"
							bind:value={endKerja}
						/>
					</div>
				</div>

				<div class="mt-4 rounded-lg border border-base-300 bg-base-200/50 p-4">
					<div class="label pt-0"><span class="label-text font-medium">Lokasi GPS (Opsional)</span></div>
					<button type="button" class="btn btn-sm btn-outline w-full sm:w-auto" onclick={getGPS} disabled={gpsLoading}>
						{#if gpsLoading}
							<span class="loading loading-spinner loading-xs"></span> Mencari lokasi...
						{:else}
							Ambil Lokasi GPS
						{/if}
					</button>

					{#if gpsLat && gpsLng}
						<div class="mt-3 text-sm text-success font-medium">
							Lokasi berhasil diambil: {gpsLat.toFixed(5)}, {gpsLng.toFixed(5)}
						</div>
						<div class="mt-2 rounded-lg overflow-hidden border border-base-300">
							<LocationMap lat={gpsLat} lng={gpsLng} zoom={15} height="h-40" />
						</div>
					{/if}
				</div>

				<div class="mt-4 card-actions justify-end">
					<button
						class="btn px-8 btn-primary"
						onclick={handleStartLogbook}
						disabled={logbookStore.isLoading || !tanggal || !startKerja || !lokasi}
					>
						{#if logbookStore.isLoading}
							<span class="loading loading-sm loading-spinner"></span>
						{/if}
						Mulai Logbook
					</button>
				</div>
			</div>
		</div>
	{:else}
		<div class="card border border-base-300 bg-base-100 shadow-sm">
			<div class="card-body">
				<div class="mb-2 flex flex-wrap items-center gap-4 text-sm text-base-content/70">
					<span>Tanggal: <strong>{logbookStore.currentLogbook.tanggal}</strong></span>
					<span>Mulai: <strong>{logbookStore.currentLogbook.start_kerja}</strong></span>
					{#if logbookStore.currentLogbook.end_kerja}
						<span>Selesai: <strong>{logbookStore.currentLogbook.end_kerja}</strong></span>
					{/if}
					{#if logbookStore.currentLogbook.lokasi}
						<span>Lokasi: <strong>{logbookStore.currentLogbook.lokasi}</strong></span>
					{/if}
				</div>

				{#if logbookStore.currentLogbook.lokasi_lat && logbookStore.currentLogbook.lokasi_lng}
					<div class="mb-4 rounded-lg overflow-hidden border border-base-300">
						<LocationMap lat={logbookStore.currentLogbook.lokasi_lat} lng={logbookStore.currentLogbook.lokasi_lng} zoom={15} height="h-48" />
					</div>
				{/if}

				<h2 class="mb-4 card-title">Daftar KPI</h2>

				{#if kpiList.length === 0}
					<p class="text-base-content/70">Belum ada tugas KPI yang diberikan untuk hari ini.</p>
				{:else}
					<div class="flex flex-col gap-4">
						{#each kpiList as kpi (kpi.id)}
							{@const progress = getProgressPercent(kpi)}
							<div class="rounded-lg border border-base-200 p-4">
								<div class="mb-2 flex items-start justify-between">
									<div>
										<span class="font-medium">
											{kpi.kpi?.nama || 'Tugas Tanpa Nama'}
										</span>
										<div class="mt-1 text-sm text-base-content/60">
											Target: {kpi.target_angka} {kpi.satuan}
										</div>
									</div>
									<span class="text-sm font-semibold {progress >= 100 ? 'text-success' : 'text-base-content/70'}">
										{progress}%
									</span>
								</div>

								<progress
									class="progress w-full {progress >= 100 ? 'progress-success' : 'progress-primary'}"
									value={progress}
									max="100"
								></progress>

							{#if isEditable}
								<div class="mt-3 flex items-end gap-2">
										<div class="form-control flex-1">
											<label class="label" for="capaian-{kpi.id}">
												<span class="label-text text-xs">Capaian ({kpi.satuan})</span>
											</label>
											<input
												id="capaian-{kpi.id}"
												type="number"
												class="input-bordered input input-sm w-full"
												bind:value={kpiProgressValues[kpi.id]}
												min="0"
												step="1"
											/>
										</div>
										<button
											class="btn btn-sm btn-primary"
											onclick={() => updateProgress(kpi.id)}
											disabled={kpiUpdating[kpi.id]}
										>
											{#if kpiUpdating[kpi.id]}
												<span class="loading loading-xs loading-spinner"></span>
											{/if}
											Update
										</button>
									</div>

									<div class="mt-3">
										{#if kpi.lampiran_file}
											<div class="flex items-center gap-2 text-sm">
												<span class="truncate text-base-content/70">{kpi.lampiran_file}</span>
												<button
													class="btn btn-outline btn-xs btn-error"
													onclick={() => handleAttachmentDelete(kpi.id)}
													disabled={attachmentUploading[kpi.id]}
												>
													{#if attachmentUploading[kpi.id]}
														<span class="loading loading-xs loading-spinner"></span>
													{/if}
													Hapus
												</button>
											</div>
										{:else}
											<input
												type="file"
												class="file-input file-input-bordered file-input-xs w-full max-w-xs"
												onchange={(e) => handleAttachmentUpload(kpi.id, e)}
												disabled={attachmentUploading[kpi.id]}
											/>
										{/if}
									</div>
							{/if}
						</div>
					{/each}
				</div>
			{/if}

			{#if isEditable}
				<div class="divider"></div>
					<div class="card-actions justify-end">
						<button
							class="btn btn-primary"
							onclick={submitLogbook}
							disabled={isSubmitting || logbookStore.isLoading}
						>
							{#if isSubmitting}
								<span class="loading loading-sm loading-spinner"></span>
							{/if}
							Submit Logbook
						</button>
					</div>
				{/if}
			</div>
		</div>
	{/if}
</div>
