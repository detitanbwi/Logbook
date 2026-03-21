<script lang="ts">
  import { summaryService } from '$lib/api/services/summaryService';
  import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
  import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
  import type { DailyStaffSummary, DailyKpiSummary, PeriodSummary, KpiPeriodItem } from '$lib/types';

  let mode = $state<'daily' | 'period'>('daily');
  
  const today = new Date().toISOString().split('T')[0];
  let date = $state(today);
  let dateFrom = $state(today);
  let dateTo = $state(today);

  let loading = $state(false);
  let error = $state<string | null>(null);

  let dailySummary = $state<DailyStaffSummary | null>(null);
  let dailyKpis = $state<DailyKpiSummary[]>([]);

  let periodSummary = $state<PeriodSummary | null>(null);
  let periodKpis = $state<KpiPeriodItem[]>([]);

  let aggregatedPeriodSummary = $derived.by(() => {
    if (!periodSummary) return null;
    return {
      total_logbooks: periodSummary.total_logbooks,
      submitted_logbooks: periodSummary.submitted_logbooks,
      accepted_logbooks: periodSummary.accepted_logbooks,
      rejected_logbooks: periodSummary.rejected_logbooks,
      total_work_minutes: periodSummary.total_work_minutes,
      progress_percent: periodSummary.progress_percent
    };
  });

  async function loadData() {
    loading = true;
    error = null;
    try {
      if (mode === 'daily') {
        const [summaryRes, kpiRes] = await Promise.all([
          summaryService.daily({ date }),
          summaryService.kpiDaily({ date })
        ]);
        dailySummary = summaryRes.data?.[0] ?? null;
        dailyKpis = kpiRes.data ?? [];
      } else {
        const [periodRes, kpiRes] = await Promise.all([
          summaryService.period({ date_from: dateFrom, date_to: dateTo }),
          summaryService.kpiPeriod({ date_from: dateFrom, date_to: dateTo })
        ]);
        periodSummary = periodRes;
        periodKpis = kpiRes.items ?? [];
      }
    } catch (e: any) {
      error = e.message || 'Gagal memuat data performa';
    } finally {
      loading = false;
    }
  }

  $effect(() => {
    loadData();
  });
</script>

<div class="flex flex-col gap-4 w-full">
  <div class="tabs tabs-boxed w-full">
    <button class="tab flex-1 {mode === 'daily' ? 'tab-active' : ''}" onclick={() => { mode = 'daily'; loadData(); }}>Harian</button>
    <button class="tab flex-1 {mode === 'period' ? 'tab-active' : ''}" onclick={() => { mode = 'period'; loadData(); }}>Periode</button>
  </div>

  {#if mode === 'daily'}
    <div class="form-control w-full">
      <label class="label" for="date-input">
        <span class="label-text font-medium">Pilih Tanggal</span>
      </label>
      <input id="date-input" type="date" class="input input-bordered w-full" bind:value={date} onchange={loadData} />
    </div>
  {:else}
    <div class="flex gap-2 w-full">
      <div class="form-control flex-1">
        <label class="label" for="date-from">
          <span class="label-text font-medium">Dari</span>
        </label>
        <input id="date-from" type="date" class="input input-bordered w-full" bind:value={dateFrom} onchange={loadData} />
      </div>
      <div class="form-control flex-1">
        <label class="label" for="date-to">
          <span class="label-text font-medium">Sampai</span>
        </label>
        <input id="date-to" type="date" class="input input-bordered w-full" bind:value={dateTo} onchange={loadData} />
      </div>
    </div>
  {/if}

  {#if loading}
    <div class="flex flex-col gap-4 animate-pulse mt-2">
      <div class="skeleton h-32 w-full rounded-2xl"></div>
      <div class="flex gap-2">
        <div class="skeleton h-6 w-16 rounded-full"></div>
        <div class="skeleton h-6 w-16 rounded-full"></div>
        <div class="skeleton h-6 w-16 rounded-full"></div>
      </div>
      <div class="skeleton h-4 w-1/4 mt-4"></div>
      <div class="skeleton h-20 w-full rounded-xl"></div>
      <div class="skeleton h-20 w-full rounded-xl"></div>
    </div>
  {:else if error}
    <div class="alert alert-error mt-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      <span>{error}</span>
      <button class="btn btn-sm" onclick={loadData}>Coba Lagi</button>
    </div>
  {:else}
    {#if mode === 'daily' && dailySummary}
      <div class="mt-2">
        <PerformanceSummaryCard 
          totalLogbooks={dailySummary.total_logbooks}
          totalWorkMinutes={dailySummary.total_work_minutes}
          progressPercent={dailySummary.progress_percent}
        />
        <div class="flex gap-2 mt-4 flex-wrap">
          <div class="badge badge-neutral gap-1 p-3">Total: {dailySummary.total_logbooks}</div>
          <div class="badge badge-info gap-1 p-3">Draft: {dailySummary.submitted_logbooks}</div>
          <div class="badge badge-success gap-1 p-3">Diterima: {dailySummary.accepted_logbooks}</div>
          <div class="badge badge-error gap-1 p-3">Ditolak: {dailySummary.rejected_logbooks}</div>
        </div>
      </div>
      
      <div class="divider font-semibold text-base-content/70">Capaian KPI</div>
      
      <div class="flex flex-col gap-4">
        {#each dailyKpis as kpi}
          <KpiProgressBar 
            nama={kpi.kpi_nama}
            capaian={kpi.capaian_angka_total}
            target={kpi.target_angka_total}
            satuan={kpi.satuan}
          />
        {/each}
        {#if dailyKpis.length === 0}
          <div class="text-center text-base-content/50 py-8 bg-base-200/50 rounded-xl">Tidak ada data KPI untuk tanggal ini</div>
        {/if}
      </div>
    {/if}

    {#if mode === 'period' && aggregatedPeriodSummary}
      <div class="mt-2">
        <PerformanceSummaryCard 
          totalLogbooks={aggregatedPeriodSummary.total_logbooks}
          totalWorkMinutes={aggregatedPeriodSummary.total_work_minutes}
          progressPercent={aggregatedPeriodSummary.progress_percent}
        />
        <div class="flex gap-2 mt-4 flex-wrap">
          <div class="badge badge-neutral gap-1 p-3">Total: {aggregatedPeriodSummary.total_logbooks}</div>
          <div class="badge badge-info gap-1 p-3">Draft: {aggregatedPeriodSummary.submitted_logbooks}</div>
          <div class="badge badge-success gap-1 p-3">Diterima: {aggregatedPeriodSummary.accepted_logbooks}</div>
          <div class="badge badge-error gap-1 p-3">Ditolak: {aggregatedPeriodSummary.rejected_logbooks}</div>
        </div>
      </div>
      
      <div class="divider font-semibold text-base-content/70">Capaian KPI</div>
      
      <div class="flex flex-col gap-4">
        {#each periodKpis as kpi}
          <KpiProgressBar 
            nama={kpi.kpi_nama}
            capaian={kpi.capaian_angka_total}
            target={kpi.target_angka_total}
            satuan={kpi.satuan}
          />
        {/each}
        {#if periodKpis.length === 0}
          <div class="text-center text-base-content/50 py-8 bg-base-200/50 rounded-xl">Tidak ada data KPI untuk periode ini</div>
        {/if}
      </div>
    {/if}
  {/if}
</div>