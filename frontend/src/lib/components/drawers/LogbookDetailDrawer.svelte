<script lang="ts">
	import SlideOutDrawer from '$lib/components/ui/SlideOutDrawer.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import type { Logbook, LogbookKpiDetail } from '$lib/types';
	import { resolveStorageUrl } from '$lib/utils/asset-url';

	let {
		isOpen = $bindable(false),
		logbook
	}: {
		isOpen: boolean;
		logbook: Logbook | null;
	} = $props();

	function formatDate(dateString: string): string {
		if (!dateString) return '-';
		return new Date(dateString).toLocaleDateString('id-ID', {
			weekday: 'long',
			day: 'numeric',
			month: 'long',
			year: 'numeric'
		});
	}

	function formatTime(timeString: string | null | undefined): string {
		if (!timeString) return '-';
		return timeString.substring(0, 5);
	}

	function formatDuration(minutes: number | undefined): string {
		if (!minutes) return '-';
		const h = Math.floor(minutes / 60);
		const m = minutes % 60;
		return `${h}h ${m}m`;
	}

	function getAttachmentUrl(filePath: string): string {
		return resolveStorageUrl(filePath);
	}

	function isImageFile(filePath: string): boolean {
		const ext = filePath.split('.').pop()?.toLowerCase() ?? '';
		return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext);
	}

	function isPdfFile(filePath: string): boolean {
		return filePath.split('.').pop()?.toLowerCase() === 'pdf';
	}

	function getFileName(filePath: string): string {
		return filePath.split('/').pop() ?? filePath;
	}

	const statusClass: Record<string, string> = {
		SUBMITTED: 'badge-info',
		ACCEPTED: 'badge-success',
		REJECTED: 'badge-error'
	};
</script>

<SlideOutDrawer bind:isOpen title="Detail Logbook" width="max-w-lg">
	{#if !logbook}
		<div class="flex h-32 items-center justify-center text-base-content/50">Pilih logbook</div>
	{:else}
		<div class="mb-4 space-y-1">
			<div class="flex items-center justify-between">
				<span class="text-sm font-medium">{formatDate(logbook.tanggal)}</span>
				<span class="badge badge-sm {statusClass[logbook.status] ?? ''}">{logbook.status}</span>
			</div>
			<div class="text-xs text-base-content/60">
				{formatTime(logbook.start_kerja)} - {formatTime(logbook.end_kerja)}
			</div>
			{#if logbook.lokasi}
				<div class="text-xs text-base-content/60">📍 {logbook.lokasi}</div>
			{/if}
		</div>

		<div class="mb-4 grid grid-cols-3 gap-2">
			<div class="rounded-lg border border-base-300 p-2 text-center">
				<div class="text-xs text-base-content/60">Gross</div>
				<div class="text-sm font-bold">{formatDuration(logbook.gross_work_minutes)}</div>
			</div>
			<div class="rounded-lg border border-base-300 p-2 text-center">
				<div class="text-xs text-base-content/60">Break</div>
				<div class="text-sm font-bold">{formatDuration(logbook.break_overlap_minutes)}</div>
			</div>
			<div class="rounded-lg border border-base-300 p-2 text-center">
				<div class="text-xs text-base-content/60">Net</div>
				<div class="text-sm font-bold">{formatDuration(logbook.net_work_minutes)}</div>
			</div>
		</div>

		{#if logbook.rating != null}
			<div class="mb-4 flex items-center gap-2 rounded-lg border border-base-300 bg-base-200/50 p-3">
				<span class="text-sm font-medium">Rating:</span>
				<span class="text-warning">
					{#each Array(5) as _, i}
						<span class={i < Math.round(Number(logbook.rating ?? 0)) ? '' : 'opacity-20'}>⭐</span>
					{/each}
				</span>
				<span class="text-sm text-base-content/60">({Number(logbook.rating).toFixed(1)}/5)</span>
			</div>
		{/if}

		{#if logbook.reviewer_comment}
			<div class="mb-4 rounded-lg border border-base-300 bg-base-200/50 p-3">
				<div class="mb-1 text-xs font-medium text-base-content/60">Komentar Reviewer</div>
				<div class="text-sm">{logbook.reviewer_comment}</div>
			</div>
		{/if}

		<h3 class="mb-2 text-sm font-bold">Progress KPI</h3>
		{#if logbook.details && logbook.details.length > 0}
			<div class="space-y-3">
				{#each logbook.details as detail (detail.id)}
					<div>
						<KpiProgressBar
							nama={detail.kpi_nama}
							capaian={detail.capaian_angka}
							target={detail.target_angka}
							satuan={detail.satuan}
						/>
						{#if detail.lampiran_file}
							{@const url = getAttachmentUrl(detail.lampiran_file)}
							<div class="mt-1.5 pl-3">
								{#if isImageFile(detail.lampiran_file)}
									<a href={url} target="_blank" rel="noopener noreferrer" class="group block">
										<img
											src={url}
											alt={getFileName(detail.lampiran_file)}
											class="h-20 w-auto rounded border border-base-300 object-cover transition-opacity group-hover:opacity-80"
										/>
									</a>
								{:else if isPdfFile(detail.lampiran_file)}
									<a
										href={url}
										target="_blank"
										rel="noopener noreferrer"
										class="inline-flex items-center gap-1.5 rounded-md border border-base-300 bg-base-200/50 px-2 py-1 text-xs text-base-content/70 transition-colors hover:bg-base-300"
									>
										<svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
											<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
											<path d="M14 2v6h6" />
											<path d="M10 13h4" />
											<path d="M10 17h4" />
										</svg>
										{getFileName(detail.lampiran_file)}
									</a>
								{:else}
									<a
										href={url}
										target="_blank"
										rel="noopener noreferrer"
										class="inline-flex items-center gap-1.5 rounded-md border border-base-300 bg-base-200/50 px-2 py-1 text-xs text-base-content/70 transition-colors hover:bg-base-300"
									>
										<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
											<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
											<path d="M14 2v6h6" />
										</svg>
										{getFileName(detail.lampiran_file)}
									</a>
								{/if}
							</div>
						{/if}
					</div>
				{/each}
			</div>
		{:else}
			<div class="rounded-lg border border-base-300 p-3 text-center text-sm text-base-content/50">
				Belum ada KPI
			</div>
		{/if}
	{/if}
</SlideOutDrawer>
