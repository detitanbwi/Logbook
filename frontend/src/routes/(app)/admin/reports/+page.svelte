<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { analyticsService } from '$lib/api/services/analyticsService';
	import type { ExportReportResponse } from '$lib/api/services/analyticsService';
	import { toastStore } from '$lib/stores/toast.svelte';

	type ReportOption = {
		label: string;
		value: string;
	};

	const reportOptions: ReportOption[] = [
		{ label: 'Staff Performance', value: 'staff_performance' },
		{ label: 'Logbook Summary', value: 'logbook_summary' }
	];

	let reportType = $state('staff_performance');
	let dateFrom = $derived($page.url.searchParams.get('date_from') || '');
	let dateTo = $derived($page.url.searchParams.get('date_to') || '');

	let dateFromDraft = $state('');
	let dateToDraft = $state('');

	let isGenerating = $state(false);
	let error = $state<string | null>(null);
	let exportResult = $state<ExportReportResponse | null>(null);

	$effect(() => {
		dateFromDraft = dateFrom;
		dateToDraft = dateTo;
	});

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);

		Object.entries(params).forEach(([key, value]) => {
			if (value) {
				url.searchParams.set(key, value);
			} else {
				url.searchParams.delete(key);
			}
		});

		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function applyDateFilters() {
		updateUrl({ date_from: dateFromDraft, date_to: dateToDraft });
	}

	function resetDateFilters() {
		dateFromDraft = '';
		dateToDraft = '';
		updateUrl({ date_from: '', date_to: '' });
	}

	async function generateReport() {
		if (isGenerating) return;

		isGenerating = true;
		error = null;
		exportResult = null;

		try {
			const response: ExportReportResponse = await analyticsService.exportReports({
				report_type: reportType,
				date_from: dateFrom || undefined,
				date_to: dateTo || undefined
			});

			exportResult = response;
			toastStore.success(response.message || 'Report berhasil dibuat.');
		} catch (e: any) {
			console.error('Failed to export report', e);
			error = e?.message || 'Gagal membuat report.';
			toastStore.error(error || 'Gagal membuat report.');
		} finally {
			isGenerating = false;
		}
	}
</script>

<svelte:head>
	<title>Admin - Reports</title>
</svelte:head>

<div class="mb-6 space-y-2">
	<h1 class="text-2xl font-bold">Reports</h1>
	<p class="text-base-content/70">Export laporan admin dengan filter tanggal berbasis URL.</p>
</div>

<div class="mb-4 grid gap-4 md:grid-cols-4">
	<div class="form-control md:col-span-2">
		<label class="label" for="report_type">
			<span class="label-text">Jenis Report</span>
		</label>
		<select id="report_type" class="select-bordered select" bind:value={reportType}>
			{#each reportOptions as option (option.value)}
				<option value={option.value}>{option.label}</option>
			{/each}
		</select>
	</div>

	<div class="form-control">
		<label class="label" for="date_from">
			<span class="label-text">Date From</span>
		</label>
		<input id="date_from" type="date" class="input-bordered input" bind:value={dateFromDraft} />
	</div>

	<div class="form-control">
		<label class="label" for="date_to">
			<span class="label-text">Date To</span>
		</label>
		<input id="date_to" type="date" class="input-bordered input" bind:value={dateToDraft} />
	</div>
</div>

<div class="mb-6 flex flex-wrap gap-2">
	<button class="btn" onclick={applyDateFilters}>Terapkan Tanggal</button>
	<button class="btn btn-outline" onclick={resetDateFilters}>Reset Tanggal</button>
	<button class="btn btn-primary" onclick={generateReport} disabled={isGenerating}>
		{isGenerating ? 'Generating...' : 'Generate Report'}
	</button>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
	</div>
{/if}

{#if exportResult?.download_url}
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body gap-3">
			<h2 class="card-title text-lg">Report Generated</h2>
			<p class="text-sm text-base-content/70">{exportResult.message}</p>
			<p class="break-all text-sm">URL: {exportResult.download_url}</p>
			<div class="card-actions">
				<a class="btn btn-primary btn-sm" href={exportResult.download_url} target="_blank" rel="noopener noreferrer">
					Open Download URL
				</a>
			</div>
		</div>
	</div>
{/if}
