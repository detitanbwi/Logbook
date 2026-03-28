<script lang="ts">
	import SlideOutDrawer from '$lib/components/ui/SlideOutDrawer.svelte';
	import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
	import type { Logbook, LogbookKpiDetail } from '$lib/types';

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

	const statusClass: Record<string, string> = {
		DRAFT: 'badge-ghost',
		SUBMITTED: 'badge-info',
		ACCEPTED: 'badge-success',
		REJECTED: 'badge-error'
	};

	function getAttachmentUrl(detail: LogbookKpiDetail): string | null {
		return detail.lampiran_file ?? null;
	}
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

		{#if logbook.rating}
			<div class="mb-4 flex items-center gap-2 rounded-lg border border-base-300 bg-base-200/50 p-3">
				<span class="text-sm font-medium">Rating:</span>
				<span class="text-warning">
					{#each Array(5) as _, i}
						<span class={i < (logbook.rating ?? 0) ? '' : 'opacity-20'}>⭐</span>
					{/each}
				</span>
				<span class="text-sm text-base-content/60">({logbook.rating}/5)</span>
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
			<div class="space-y-2">
				{#each logbook.details as detail (detail.id)}
					<div>
						<KpiProgressBar
							nama={detail.kpi_nama}
							capaian={detail.capaian_angka}
							target={detail.target_angka}
							satuan={detail.satuan}
						/>
						{#if getAttachmentUrl(detail)}
							<div class="mt-1 flex items-center gap-1 pl-3 text-xs text-base-content/60">
								<span>📎</span>
								<a
									href={getAttachmentUrl(detail) ?? '#'}
									target="_blank"
									rel="noopener noreferrer"
									class="link link-primary"
								>
									Lihat Lampiran
								</a>
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
