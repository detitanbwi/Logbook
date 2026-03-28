<script lang="ts">
  import { staffLogbookService } from '$lib/api/services/staffLogbookService';
  import { summaryService } from '$lib/api/services/summaryService';
  import PerformanceSummaryCard from '$lib/components/mobile/PerformanceSummaryCard.svelte';
  import KpiProgressBar from '$lib/components/mobile/KpiProgressBar.svelte';
  import type {
    DailyStaffSummary,
    DailyKpiSummary,
    PeriodSummary,
    KpiPeriodItem,
    Logbook
  } from '$lib/types';

  let mode = $state<'daily' | 'period'>('daily');
  
  function toLocalDateInputValue(date: Date): string {
    const timezoneOffsetMs = date.getTimezoneOffset() * 60_000;
    return new Date(date.getTime() - timezoneOffsetMs).toISOString().split('T')[0];
  }

  function normalizeDateKey(dateString: string | null | undefined): string {
    if (!dateString) return '';
    return dateString.split('T')[0];
  }

  const today = toLocalDateInputValue(new Date());
  const startOfMonth = new Date();
  startOfMonth.setDate(1);

  let dateFrom = $state(toLocalDateInputValue(startOfMonth));
  let dateTo = $state(today);

  let loading = $state(false);
  let error = $state<string | null>(null);
  let dailyView = $state<'summary' | 'detail'>('summary');
  let selectedSummaryDate = $state<string | null>(null);
  let expandedLogbookId = $state<string | null>(null);
  let expandedLogbook = $state<Logbook | null>(null);
  let loadingLogbookDetail = $state(false);
  let loadingDailyLogbooks = $state(false);
  let detailRequestId = 0;

  let dailySummaries = $state<DailyStaffSummary[]>([]);
  let dailyKpis = $state<DailyKpiSummary[]>([]);
  let selectedDayLogbooks = $state<Logbook[]>([]);
  let dailyLogbooksMap = $state<Record<string, Logbook[]>>({});

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

  const selectedDailySummary = $derived(
    selectedSummaryDate
      ? dailySummaries.find((summary) => normalizeDateKey(summary.tanggal) === selectedSummaryDate) ?? null
      : null
  );

  const currentDailyKpis = $derived.by(() => {
    if (!selectedSummaryDate) return [];
    return dailyKpis.filter((kpi) => normalizeDateKey(kpi.tanggal) === selectedSummaryDate);
  });

  const averageDailyRating = $derived.by(() => {
    const ratedLogbooks = selectedDayLogbooks.filter((logbook) => logbook.rating != null);
    if (ratedLogbooks.length === 0) return null;

    const totalRating = ratedLogbooks.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0);
    return totalRating / ratedLogbooks.length;
  });

  const averageDailyKpiPercent = $derived.by(() => {
    const kpis = currentDailyKpis;
    if (kpis.length === 0) return 0;

    const validKpis = kpis.filter((kpi) => kpi.target_angka_total > 0);
    if (validKpis.length === 0) return 0;

    const totalPercent = validKpis.reduce((sum: number, kpi: DailyKpiSummary) => {
      return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
    }, 0);

    return totalPercent / validKpis.length;
  });

  const averageDailyKpiUnit = $derived.by(() => {
    const kpis = currentDailyKpis;
    if (kpis.length === 0) return null;

    const uniqueUnits = Array.from(new Set(kpis.map((kpi) => kpi.satuan).filter(Boolean)));
    if (uniqueUnits.length === 0) return null;
    if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
    return `Satuan campuran (${uniqueUnits.length} jenis)`;
  });

  const averagePeriodKpiPercent = $derived.by(() => {
    if (periodKpis.length === 0) return aggregatedPeriodSummary?.progress_percent ?? 0;

    const validKpis = periodKpis.filter((kpi) => kpi.target_angka_total > 0);
    if (validKpis.length === 0) return aggregatedPeriodSummary?.progress_percent ?? 0;

    const totalPercent = validKpis.reduce((sum: number, kpi: KpiPeriodItem) => {
      return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
    }, 0);

    return totalPercent / validKpis.length;
  });

  const periodKpiUnitLabel = $derived.by(() => {
    if (periodKpis.length === 0) return null;

    const uniqueUnits = Array.from(new Set(periodKpis.map((kpi) => kpi.satuan).filter(Boolean)));
    if (uniqueUnits.length === 0) return null;
    if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
    return `Satuan campuran (${uniqueUnits.length} jenis)`;
  });

  function getDailyLogbooks(dateKey: string): Logbook[] {
    return dailyLogbooksMap[normalizeDateKey(dateKey)] ?? [];
  }

  function getAverageRatingForDate(dateKey: string): number | null {
    const ratedLogbooks = getDailyLogbooks(dateKey).filter((logbook) => logbook.rating != null);
    if (ratedLogbooks.length === 0) return null;

    const totalRating = ratedLogbooks.reduce((sum, logbook) => sum + (logbook.rating ?? 0), 0);
    return totalRating / ratedLogbooks.length;
  }

  function getKpisForDate(dateKey: string): DailyKpiSummary[] {
    const normalizedDateKey = normalizeDateKey(dateKey);
    return dailyKpis.filter((kpi) => normalizeDateKey(kpi.tanggal) === normalizedDateKey);
  }

  function getAverageKpiPercentForDate(dateKey: string): number {
    const validKpis = getKpisForDate(dateKey).filter((kpi) => kpi.target_angka_total > 0);
    if (validKpis.length === 0) return 0;

    const totalPercent = validKpis.reduce((sum, kpi) => {
      return sum + Math.min((kpi.capaian_angka_total / kpi.target_angka_total) * 100, 100);
    }, 0);

    return totalPercent / validKpis.length;
  }

  function getKpiUnitLabelForDate(dateKey: string): string | null {
    const uniqueUnits = Array.from(new Set(getKpisForDate(dateKey).map((kpi) => kpi.satuan).filter(Boolean)));
    if (uniqueUnits.length === 0) return null;
    if (uniqueUnits.length === 1) return `Satuan: ${uniqueUnits[0]}`;
    return `Satuan campuran (${uniqueUnits.length} jenis)`;
  }

  async function preloadDailyLogbooks(summaries: DailyStaffSummary[]) {
    const uniqueDates = Array.from(new Set(summaries.map((summary) => normalizeDateKey(summary.tanggal)).filter(Boolean)));

    const responses = await Promise.all(
      uniqueDates.map(async (dateKey) => {
        const response = await staffLogbookService.getLogbooks({
          date_from: dateKey,
          date_to: dateKey,
          per_page: 100,
          sort_by: 'tanggal',
          sort_dir: 'desc'
        });

        return [dateKey, response.data] as const;
      })
    );

    dailyLogbooksMap = Object.fromEntries(responses);
  }

  function formatDate(dateString: string) {
    return new Date(normalizeDateKey(dateString)).toLocaleDateString('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }

  function getStatusBadgeClass(count: number, variant: 'submitted' | 'accepted' | 'rejected') {
    if (count <= 0) return 'badge-ghost';

    switch (variant) {
      case 'submitted':
        return 'badge-warning';
      case 'accepted':
        return 'badge-success';
      case 'rejected':
        return 'badge-error';
    }
  }

  function getLogbookStatusBadgeClass(status: Logbook['status']) {
    switch (status) {
      case 'SUBMITTED':
        return 'badge-warning';
      case 'ACCEPTED':
        return 'badge-success';
      case 'REJECTED':
        return 'badge-error';
    }
  }

  async function openDailyDetail(summary: DailyStaffSummary) {
    const dateKey = normalizeDateKey(summary.tanggal);
    selectedSummaryDate = dateKey;
    dailyView = 'detail';
    error = null;
    expandedLogbookId = null;
    expandedLogbook = null;
    loadingDailyLogbooks = true;

    try {
      if (!dailyLogbooksMap[dateKey]) {
        const logbookResponse = await staffLogbookService.getLogbooks({
          date_from: dateKey,
          date_to: dateKey,
          per_page: 100,
          sort_by: 'tanggal',
          sort_dir: 'desc'
        });
        dailyLogbooksMap = {
          ...dailyLogbooksMap,
          [dateKey]: logbookResponse.data
        };
      }

      selectedDayLogbooks = getDailyLogbooks(dateKey);
    } catch (e: unknown) {
      error = e instanceof Error ? e.message : 'Gagal memuat ringkasan logbook harian';
      selectedDayLogbooks = [];
    } finally {
      loadingDailyLogbooks = false;
    }
  }

  function goBackToDailySummaries() {
    dailyView = 'summary';
    selectedSummaryDate = null;
    selectedDayLogbooks = [];
    expandedLogbookId = null;
    expandedLogbook = null;
    loadingDailyLogbooks = false;
  }

  async function toggleLogbookDetail(logbookId: string) {
    if (expandedLogbookId === logbookId) {
      expandedLogbookId = null;
      expandedLogbook = null;
      return;
    }

    const requestId = ++detailRequestId;
    expandedLogbookId = logbookId;
    expandedLogbook = null;
    loadingLogbookDetail = true;

    try {
      const logbookDetail = await staffLogbookService.getLogbookById(logbookId);
      if (requestId !== detailRequestId) {
        return;
      }
      expandedLogbook = logbookDetail;
    } catch {
      if (requestId === detailRequestId) {
        expandedLogbook = null;
      }
    } finally {
      if (requestId === detailRequestId) {
        loadingLogbookDetail = false;
      }
    }
  }

  async function loadData() {
    loading = true;
    error = null;
    try {
      if (mode === 'daily') {
        const [summaryRes, kpiRes] = await Promise.all([
          summaryService.daily({ date_from: dateFrom, date_to: dateTo, per_page: 100 }),
          summaryService.kpiDaily({ date_from: dateFrom, date_to: dateTo, per_page: 100 })
        ]);
        dailySummaries = summaryRes.data ?? [];
        dailyKpis = kpiRes.data ?? [];
        await preloadDailyLogbooks(dailySummaries);
        if (dailyView === 'detail' && selectedSummaryDate) {
          selectedDayLogbooks = getDailyLogbooks(selectedSummaryDate);
        }
        if (selectedSummaryDate && !summaryRes.data?.some((summary) => normalizeDateKey(summary.tanggal) === selectedSummaryDate)) {
          goBackToDailySummaries();
        }
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

  $effect(() => {
    if (mode === 'daily') {
      dailyView = 'summary';
      selectedSummaryDate = null;
      selectedDayLogbooks = [];
      expandedLogbookId = null;
      expandedLogbook = null;
      loadingDailyLogbooks = false;
    }
  });
</script>

<div class="flex flex-col gap-4 w-full">
  <div class="tabs tabs-boxed w-full">
    <button class="tab flex-1 {mode === 'daily' ? 'tab-active' : ''}" onclick={() => { mode = 'daily'; loadData(); }}>Harian</button>
    <button class="tab flex-1 {mode === 'period' ? 'tab-active' : ''}" onclick={() => { mode = 'period'; loadData(); }}>Periode</button>
  </div>

  {#if mode === 'daily' || mode === 'period'}
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
    {#if mode === 'daily'}
      {#if dailyView === 'summary'}
        <div class="mt-2 flex flex-col gap-3">
          <div class="rounded-2xl border border-base-300 bg-base-100 p-4">
            <h2 class="text-base font-semibold">Rekap Harian</h2>
            <p class="mt-1 text-sm text-base-content/60">
              Pilih tanggal rekap untuk melihat rata-rata bintang dan detail KPI per hari.
            </p>
          </div>

          {#if dailySummaries.length === 0}
            <div class="text-center text-base-content/50 py-8 bg-base-200/50 rounded-xl">
              Tidak ada rekap harian untuk tanggal ini.
            </div>
          {:else}
            {#each dailySummaries as summary (summary.id)}
              <button
                type="button"
                class="w-full rounded-2xl border border-base-300 bg-base-100 p-4 text-left shadow-sm transition hover:border-primary"
                onclick={() => openDailyDetail(summary)}
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="text-sm font-semibold">{formatDate(summary.tanggal)}</div>
                    <div class="mt-1 text-xs text-base-content/60">
                      {summary.total_logbooks} logbook • {summary.total_kpi} KPI
                    </div>
                  </div>
                  <div class="text-sm text-base-content/40">→</div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                  <div class="rounded-lg bg-base-200/70 px-3 py-2">
                    <div class="text-base-content/60">Durasi</div>
                    <div class="font-semibold">{Math.floor(summary.total_work_minutes / 60)}j {summary.total_work_minutes % 60}m</div>
                  </div>
                  <div class="rounded-lg bg-base-200/70 px-3 py-2">
                    <div class="text-base-content/60">Rata-rata KPI</div>
                    <div class="font-semibold">{Math.round(getAverageKpiPercentForDate(summary.tanggal))}%</div>
                    <div class="mt-1 text-[11px] text-base-content/50 line-clamp-1">{getKpiUnitLabelForDate(summary.tanggal) || '-'}</div>
                  </div>
                  <div class="rounded-lg bg-base-200/70 px-3 py-2">
                    <div class="text-base-content/60">Rata-rata Bintang</div>
                    <div class="font-semibold">{#if getAverageRatingForDate(summary.tanggal) != null}⭐ {getAverageRatingForDate(summary.tanggal)?.toFixed(1)}{:else}-{/if}</div>
                  </div>
                  <div class="rounded-lg bg-base-200/70 px-3 py-2">
                    <div class="text-base-content/60">Capaian / Target</div>
                    <div class="font-semibold">{summary.capaian_angka_total} / {summary.target_angka_total}</div>
                  </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                  <div class="badge badge-neutral gap-1 p-3">Total: {summary.total_logbooks}</div>
                  <div class="badge {getStatusBadgeClass(summary.submitted_logbooks, 'submitted')} gap-1 p-3">
                    Submitted: {summary.submitted_logbooks}
                  </div>
                  <div class="badge {getStatusBadgeClass(summary.accepted_logbooks, 'accepted')} gap-1 p-3">
                    Diterima: {summary.accepted_logbooks}
                  </div>
                  <div class="badge {getStatusBadgeClass(summary.rejected_logbooks, 'rejected')} gap-1 p-3">
                    Ditolak: {summary.rejected_logbooks}
                  </div>
                </div>
              </button>
            {/each}
          {/if}
        </div>
      {:else if selectedDailySummary}
        <div class="mt-2 flex flex-col gap-4">
          <div class="flex items-center gap-2">
            <button type="button" class="btn btn-sm btn-ghost btn-circle" onclick={goBackToDailySummaries}>←</button>
            <div>
              <h2 class="text-base font-semibold">Detail Rekap Harian</h2>
              <p class="text-sm text-base-content/60">{formatDate(selectedDailySummary.tanggal)}</p>
            </div>
          </div>

          <PerformanceSummaryCard 
            totalLogbooks={selectedDailySummary.total_logbooks}
            totalWorkMinutes={selectedDailySummary.total_work_minutes}
            progressPercent={averageDailyKpiPercent}
            progressUnit={averageDailyKpiUnit}
            averageRating={averageDailyRating}
          />

          <div class="flex gap-2 flex-wrap">
            <div class="badge badge-neutral gap-1 p-3">Total: {selectedDailySummary.total_logbooks}</div>
            <div class="badge {getStatusBadgeClass(selectedDailySummary.submitted_logbooks, 'submitted')} gap-1 p-3">
              Submitted: {selectedDailySummary.submitted_logbooks}
            </div>
            <div class="badge {getStatusBadgeClass(selectedDailySummary.accepted_logbooks, 'accepted')} gap-1 p-3">
              Diterima: {selectedDailySummary.accepted_logbooks}
            </div>
            <div class="badge {getStatusBadgeClass(selectedDailySummary.rejected_logbooks, 'rejected')} gap-1 p-3">
              Ditolak: {selectedDailySummary.rejected_logbooks}
            </div>
          </div>

          <div class="rounded-2xl border border-base-300 bg-base-100 p-4">
            <h3 class="text-sm font-semibold">Ringkasan Logbook Hari Itu</h3>
            <div class="mt-3 flex flex-col gap-3">
              {#if loadingDailyLogbooks}
                <div class="space-y-2">
                  {#each Array(2) as _}
                    <div class="skeleton h-16 w-full rounded-xl"></div>
                  {/each}
                </div>
              {:else if selectedDayLogbooks.length === 0}
                <div class="rounded-xl bg-base-200/60 px-4 py-5 text-center text-sm text-base-content/60">
                  Tidak ada logbook harian untuk tanggal ini.
                </div>
              {:else}
                {#each selectedDayLogbooks as logbook (logbook.id)}
                  <div class="rounded-xl border border-base-200 bg-base-50 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <div class="text-sm font-semibold">
                          {logbook.start_kerja.slice(0, 5)} - {logbook.end_kerja?.slice(0, 5) || '--:--'}
                        </div>
                        <div class="mt-1 text-xs text-base-content/60 line-clamp-2">
                          {logbook.lokasi || 'Lokasi tidak diisi'}
                        </div>
                      </div>
                      <div class="badge badge-sm {getLogbookStatusBadgeClass(logbook.status)}">
                        {logbook.status}
                      </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                      <div class="rounded-full bg-base-200 px-3 py-1">{logbook.details?.length || 0} KPI</div>
                      {#if logbook.rating != null}
                        <div class="rounded-full bg-warning/15 px-3 py-1 font-medium text-warning-content">
                          ⭐ {logbook.rating}
                        </div>
                      {/if}
                    </div>

                    <button
                      type="button"
                      class="btn btn-xs btn-outline mt-3"
                      onclick={() => toggleLogbookDetail(logbook.id)}
                    >
                      {expandedLogbookId === logbook.id ? 'Sembunyikan Detail' : 'Lihat Detail Logbook'}
                    </button>

                    {#if expandedLogbookId === logbook.id}
                      <div class="mt-3 border-t border-base-200 pt-3">
                        {#if loadingLogbookDetail}
                          <div class="skeleton h-10 w-full rounded"></div>
                        {:else if expandedLogbook?.details && expandedLogbook.details.length > 0}
                          <div class="space-y-3">
                            {#each expandedLogbook.details as detail (detail.id)}
                              <KpiProgressBar
                                nama={detail.kpi_nama}
                                capaian={detail.capaian_angka}
                                target={detail.target_angka}
                                satuan={detail.satuan}
                              />
                            {/each}
                          </div>
                        {:else if expandedLogbook}
                          <div class="text-xs text-base-content/60">Belum ada detail KPI.</div>
                        {:else}
                          <div class="text-xs text-error">Gagal memuat detail logbook.</div>
                        {/if}
                      </div>
                    {/if}
                  </div>
                {/each}
              {/if}
            </div>
          </div>

          <div class="divider font-semibold text-base-content/70">Ringkasan KPI per Hari</div>

          <div class="flex flex-col gap-4">
            {#each currentDailyKpis as kpi}
              <KpiProgressBar 
                nama={kpi.kpi_nama}
                capaian={kpi.capaian_angka_total}
                target={kpi.target_angka_total}
                satuan={kpi.satuan}
              />
            {/each}
            {#if currentDailyKpis.length === 0}
              <div class="text-center text-base-content/50 py-8 bg-base-200/50 rounded-xl">Tidak ada data KPI untuk rekap hari ini</div>
            {/if}
          </div>
        </div>
      {/if}
    {/if}

    {#if mode === 'period' && aggregatedPeriodSummary}
      <div class="mt-2">
        <PerformanceSummaryCard 
          totalLogbooks={aggregatedPeriodSummary.total_logbooks}
          totalWorkMinutes={aggregatedPeriodSummary.total_work_minutes}
          progressPercent={averagePeriodKpiPercent}
          progressUnit={periodKpiUnitLabel}
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
