<script lang="ts">
  import { slide } from 'svelte/transition';
  import { staffLogbookService } from '$lib/api/services/staffLogbookService';
  import { toastStore } from '$lib/stores/toast.svelte';
  import type { Logbook, LogbookStatus, PaginationMeta } from '$lib/types';
  import LocationMap from '$lib/components/ui/LocationMap.svelte';

  let view = $state<'summary' | 'day' | 'detail' | 'create'>('summary');
  let selectedDate = $state<string | null>(null);
  let currentLogbook = $state<Logbook | null>(null);

  let loading = $state(false);
  let error = $state<string | null>(null);

  // --- List & Summary State ---
  let logbooks = $state<Logbook[]>([]);
  let paginationMeta = $state<PaginationMeta | null>(null);
  let statusFilter = $state<LogbookStatus | 'Semua'>('Semua');

  let groupedLogbooks = $derived.by(() => {
    const groups: Record<string, Logbook[]> = {};
    for (const lb of logbooks) {
      if (!groups[lb.tanggal]) groups[lb.tanggal] = [];
      groups[lb.tanggal].push(lb);
    }
    return Object.entries(groups).sort(
      (a, b) => new Date(b[0]).getTime() - new Date(a[0]).getTime()
    );
  });

  async function loadLogbooks(page = 1, append = false) {
    loading = true;
    error = null;
    try {
      const params = {
        page,
        per_page: 20,
        status: statusFilter === 'Semua' ? undefined : statusFilter,
        sort_by: 'tanggal',
        sort_dir: 'desc' as const
      };
      const res = await staffLogbookService.getLogbooks(params);
      if (append) {
        logbooks = [...logbooks, ...res.data];
      } else {
        logbooks = res.data;
      }
      paginationMeta = res.meta;
    } catch (err: unknown) {
      error = err instanceof Error ? err.message : String(err);
    } finally {
      loading = false;
    }
  }

  $effect(() => {
    if (view === 'summary') {
      loadLogbooks(1);
    }
  });

  // --- Create State ---
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');

  let createForm = $state({
    tanggal: `${yyyy}-${mm}-${dd}`,
    start_kerja: '08:00',
    end_kerja: '17:00',
    lokasi: ''
  });
  let createSubmitting = $state(false);

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
        toastStore.success('Lokasi berhasil diambil.');
      },
      (err) => {
        gpsLoading = false;
        toastStore.error('Tidak dapat mengambil lokasi GPS.');
        console.error(err);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  async function handleCreate(e: Event) {
    e.preventDefault();
    createSubmitting = true;
    error = null;
    try {
      const newLb = await staffLogbookService.startLogbook({
        tanggal: createForm.tanggal,
        start_kerja: createForm.start_kerja,
        end_kerja: createForm.end_kerja || null,
        lokasi: createForm.lokasi,
        lokasi_lat: gpsLat || undefined,
        lokasi_lng: gpsLng || undefined
      });
      await loadDetail(newLb.id);
      view = 'detail';
      // Reset form
      createForm.lokasi = '';
      gpsLat = null;
      gpsLng = null;
    } catch (err: unknown) {
      error = err instanceof Error ? err.message : String(err);
      toastStore.error('Gagal membuat logbook.');
    } finally {
      createSubmitting = false;
    }
  }

  // --- Detail State ---
  let detailLoading = $state(false);
  let submitLoading = $state(false);
  let deleteLoading = $state(false);
  let updatingKpiId = $state<string | null>(null);
  let uploadingKpiId = $state<string | null>(null);
  let deletingAttachmentId = $state<string | null>(null);
  let pendingAttachments = $state<Record<string, File | null>>({});

  // --- Confirm Modal State ---
  let confirmModalOpen = $state(false);
  let confirmModalTitle = $state('');
  let confirmModalMessage = $state('');
  let confirmModalAction = $state<(() => Promise<void>) | null>(null);
  let confirmModalType = $state<'warning' | 'error'>('warning');

  async function loadDetail(id: string) {
    detailLoading = true;
    error = null;
    try {
      currentLogbook = await staffLogbookService.getLogbookById(id);
    } catch (err: unknown) {
      error = err instanceof Error ? err.message : String(err);
    } finally {
      detailLoading = false;
    }
  }

  function goDetail(id: string) {
    view = 'detail';
    loadDetail(id);
  }

  function goDay(date: string) {
    selectedDate = date;
    view = 'day';
  }

  function showConfirm(title: string, message: string, action: () => Promise<void>, type: 'warning' | 'error' = 'warning') {
    confirmModalTitle = title;
    confirmModalMessage = message;
    confirmModalAction = action;
    confirmModalType = type;
    confirmModalOpen = true;
  }

  async function executeConfirm() {
    if (confirmModalAction) {
      await confirmModalAction();
    }
    confirmModalOpen = false;
    confirmModalAction = null;
  }

  function handleDelete() {
    if (!currentLogbook) return;
    showConfirm(
      'Hapus Logbook',
      'Apakah Anda yakin ingin menghapus logbook ini?',
      async () => {
        deleteLoading = true;
        try {
          await staffLogbookService.deleteLogbook(currentLogbook!.id);
          toastStore.success('Logbook berhasil dihapus.');
          view = 'day';
          loadLogbooks(1);
        } catch (err: unknown) {
          toastStore.error(err instanceof Error ? err.message : 'Gagal menghapus logbook.');
        } finally {
          deleteLoading = false;
        }
      },
      'error'
    );
  }

  function handleSubmit() {
    if (!currentLogbook) return;
    showConfirm(
      'Submit Logbook',
      'Submit logbook ini? Logbook yang di-submit tidak bisa diubah.',
      async () => {
        submitLoading = true;
        try {
          const entries = Object.entries(pendingAttachments);
          for (const [detailId, file] of entries) {
            if (file) {
              try {
                await staffLogbookService.uploadKpiAttachment(currentLogbook!.id, detailId, file);
                delete pendingAttachments[detailId];
              } catch (err: unknown) {
                toastStore.error(err instanceof Error ? err.message : 'Gagal mengunggah beberapa lampiran.');
              }
            }
          }

          await staffLogbookService.submitLogbook(currentLogbook!.id);
          toastStore.success('Logbook berhasil di-submit.');
          await loadDetail(currentLogbook!.id);
          loadLogbooks(1);
        } catch (err: unknown) {
          toastStore.error(err instanceof Error ? err.message : 'Gagal submit logbook.');
        } finally {
          submitLoading = false;
        }
      }
    );
  }

  async function updateKpi(detailId: string, capaian: number) {
    if (!currentLogbook) return;
    updatingKpiId = detailId;
    try {
      await staffLogbookService.updateKpiProgress(currentLogbook.id, detailId, {
        capaian_angka: capaian
      });
      toastStore.success('Progress KPI berhasil diperbarui.');
      await loadDetail(currentLogbook.id);
    } catch (err: unknown) {
      toastStore.error(err instanceof Error ? err.message : 'Gagal memperbarui progress KPI.');
    } finally {
      updatingKpiId = null;
    }
  }

  function uploadAttachment(detailId: string, event: Event) {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;

    pendingAttachments[detailId] = target.files[0];
    target.value = '';
  }

  function removePendingAttachment(detailId: string) {
    pendingAttachments[detailId] = null;
  }

  async function deleteAttachment(detailId: string) {
    if (!currentLogbook) return;
    showConfirm(
      'Hapus Lampiran',
      'Hapus lampiran ini?',
      async () => {
        deletingAttachmentId = detailId;
        try {
          await staffLogbookService.deleteKpiAttachment(currentLogbook!.id, detailId);
          toastStore.success('Lampiran berhasil dihapus.');
          await loadDetail(currentLogbook!.id);
        } catch (err: unknown) {
          toastStore.error(err instanceof Error ? err.message : 'Gagal menghapus lampiran.');
        } finally {
          deletingAttachmentId = null;
        }
      },
      'error'
    );
  }

  // --- Utils ---
  function getStatusBadgeClass(status: LogbookStatus) {
    switch (status) {
      case 'SUBMITTED': return 'badge-warning';
      case 'ACCEPTED': return 'badge-success';
      case 'REJECTED': return 'badge-error';
      default: return 'badge-ghost';
    }
  }

  function formatDate(dateStr: string) {
    try {
      return new Date(dateStr).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    } catch {
      return dateStr;
    }
  }

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
    return path.startsWith('http') ? path : `/storage/${path}`;
  }
</script>

<div class="flex flex-col gap-4 p-4 pb-24 max-w-md mx-auto">
  {#if error}
    <div class="alert alert-error shadow-sm mb-2">
      <span class="text-sm">{error}</span>
      <button class="btn btn-sm btn-circle btn-ghost" onclick={() => (error = null)}>✕</button>
    </div>
  {/if}

  {#if view === 'summary'}
    <div transition:slide={{ duration: 200 }}>
      <div class="flex justify-between items-center mb-4">
        <select
          class="select select-bordered select-sm w-36"
          bind:value={statusFilter}
          onchange={() => loadLogbooks(1)}
        >
          <option value="Semua">Semua</option>
          <option value="SUBMITTED">SUBMITTED</option>
          <option value="ACCEPTED">ACCEPTED</option>
          <option value="REJECTED">REJECTED</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick={() => (view = 'create')}>+ Buat Logbook</button>
      </div>

      {#if loading && logbooks.length === 0}
        <div class="flex justify-center p-8">
          <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
      {:else if logbooks.length === 0}
        <div class="text-center text-base-content/60 py-12 bg-base-100 rounded-xl border border-base-200 shadow-sm">
          Tidak ada logbook ditemukan.
        </div>
      {:else}
        <div class="flex flex-col gap-3">
          {#each groupedLogbooks as [date, items] (date)}
            <!-- svelte-ignore a11y_click_events_have_key_events -->
            <!-- svelte-ignore a11y_no_static_element_interactions -->
            <div
              class="card bg-base-100 border border-base-200 shadow-sm cursor-pointer hover:border-primary transition-colors"
              onclick={() => goDay(date)}
            >
              <div class="card-body p-4 flex flex-row justify-between items-center">
                <div>
                  <h2 class="text-base font-semibold text-base-content">{formatDate(date)}</h2>
                  <p class="text-xs text-base-content/70 mt-1">{items.length} Logbook</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                  {#each Array.from(new Set(items.map(i => i.status))) as st}
                    <div class="badge badge-sm {getStatusBadgeClass(st)}">{st}</div>
                  {/each}
                  <span class="text-base-content/40 ml-2">→</span>
                </div>
              </div>
            </div>
          {/each}
        </div>

        {#if paginationMeta && paginationMeta.current_page < paginationMeta.last_page}
          <button
            class="btn btn-outline btn-block mt-6"
            onclick={() => loadLogbooks(paginationMeta!.current_page + 1, true)}
            disabled={loading}
          >
            {loading ? 'Memuat...' : 'Load More'}
          </button>
        {/if}
      {/if}
    </div>

  {:else if view === 'day' && selectedDate}
    {@const dayLogbooks = groupedLogbooks.find(g => g[0] === selectedDate)?.[1] || []}
    <div transition:slide={{ duration: 200 }}>
      <div class="flex items-center gap-3 mb-4">
        <button class="btn btn-sm btn-ghost btn-circle" onclick={() => (view = 'summary')}>←</button>
        <h2 class="text-lg font-semibold">{formatDate(selectedDate)}</h2>
      </div>

      <div class="flex flex-col gap-3">
        {#each dayLogbooks as lb (lb.id)}
          <!-- svelte-ignore a11y_click_events_have_key_events -->
          <!-- svelte-ignore a11y_no_static_element_interactions -->
          <div
            class="card bg-base-100 border border-base-200 shadow-sm cursor-pointer hover:border-primary transition-colors"
            onclick={() => goDetail(lb.id)}
          >
            <div class="card-body p-4 gap-2">
              <div class="flex justify-between items-start">
                <span class="font-mono text-sm font-semibold text-base-content">
                  {lb.start_kerja.slice(0, 5)} - {lb.end_kerja ? lb.end_kerja.slice(0, 5) : '...'}
                </span>
                <div class="badge badge-sm {getStatusBadgeClass(lb.status)}">
                  {lb.status}
                </div>
              </div>
              <div class="text-sm text-base-content/80 line-clamp-2">
                📍 {lb.lokasi || 'Tidak ada lokasi'}
              </div>
              {#if lb.details && lb.details.length > 0}
                <div class="text-xs text-base-content/60 mt-1">
                  {lb.details.length} KPI
                </div>
              {/if}
            </div>
          </div>
        {/each}
      </div>
    </div>

  {:else if view === 'detail'}
    <div transition:slide={{ duration: 200 }}>
      <div class="flex items-center gap-2 mb-4">
        <button
          class="btn btn-sm btn-ghost btn-circle"
          onclick={() => {
            view = selectedDate ? 'day' : 'summary';
            currentLogbook = null;
          }}
        >←</button>
        <h2 class="text-lg font-semibold">Detail Logbook</h2>
      </div>

      {#if detailLoading}
        <div class="flex justify-center p-8">
          <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
      {:else if currentLogbook}
        <div class="card bg-base-100 border border-base-200 shadow-sm mb-6 overflow-hidden">
          <div class="card-body p-4 gap-2">
            <div class="flex justify-between items-start">
              <div class="text-sm font-medium text-base-content/80">
                {formatDate(currentLogbook.tanggal)}
              </div>
              <div class="badge badge-sm {getStatusBadgeClass(currentLogbook.status)}">
                {currentLogbook.status}
              </div>
            </div>
            <div class="font-mono text-base font-semibold mt-1">
              {currentLogbook.start_kerja.slice(0, 5)} - {currentLogbook.end_kerja
                ? currentLogbook.end_kerja.slice(0, 5)
                : 'Sekarang'}
            </div>
            {#if currentLogbook.lokasi}
              <div class="text-sm mt-1 text-base-content/90">📍 {currentLogbook.lokasi}</div>
            {/if}

            {#if currentLogbook.status === 'ACCEPTED' || currentLogbook.status === 'REJECTED'}
              <div class="mt-4 p-3 bg-base-200 rounded-lg text-sm border border-base-300">
                <div class="font-semibold mb-1">Catatan Reviewer:</div>
                <div class="text-base-content/80">
                  {currentLogbook.reviewer_comment || 'Tidak ada komentar'}
                </div>
                {#if currentLogbook.rating}
                  <div class="mt-2 text-warning font-medium">
                    Rating: {'⭐'.repeat(currentLogbook.rating)}
                  </div>
                {/if}
              </div>
            {/if}
          </div>

          {#if currentLogbook.lokasi_lat && currentLogbook.lokasi_lng}
            <div class="w-full border-t border-base-200">
              <LocationMap lat={currentLogbook.lokasi_lat} lng={currentLogbook.lokasi_lng} zoom={15} height="h-48" />
            </div>
          {/if}
        </div>

        <h3 class="text-md font-semibold mb-3 px-1 text-base-content/80">Detail Progress KPI</h3>
        <div class="flex flex-col gap-3 mb-6">
          {#if currentLogbook.details && currentLogbook.details.length > 0}
            {#each currentLogbook.details as detail (detail.id)}
              <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-4 gap-3">
                  <div class="font-semibold text-sm leading-tight text-base-content">
                    {detail.kpi_nama}
                  </div>
                  <div class="flex justify-between text-xs text-base-content/70 bg-base-200/50 p-2 rounded-md">
                    <span class="font-medium">Target: {detail.target_angka} {detail.satuan}</span>
                    <span class="font-medium">Capaian: {detail.capaian_angka} {detail.satuan}</span>
                  </div>

                  {#if currentLogbook.status === 'SUBMITTED' || currentLogbook.status === 'REJECTED'}
                    <div class="flex gap-2 mt-2 items-center">
                      <input
                        type="number"
                        class="input input-bordered input-sm flex-1 font-mono text-center"
                        bind:value={detail.capaian_angka}
                        min="0"
                        step="0.01"
                        disabled={updatingKpiId === detail.id}
                      />
                      <button
                        class="btn btn-sm btn-primary px-4"
                        onclick={() => updateKpi(detail.id, detail.capaian_angka)}
                        disabled={updatingKpiId === detail.id}
                      >
                        {#if updatingKpiId === detail.id}
                          <span class="loading loading-spinner loading-xs"></span>
                        {:else}
                          Update
                        {/if}
                      </button>
                    </div>
                  {/if}

                  <div class="mt-2">
                    {#if detail.lampiran_file}
                      <div class="flex flex-col gap-2 p-3 bg-base-200 border border-base-300 rounded-lg">
                        {#if isImageFile(detail.lampiran_file)}
                          <img 
                            src={getAttachmentUrl(detail.lampiran_file)} 
                            alt="Lampiran" 
                            class="w-full h-auto max-h-48 object-contain rounded bg-base-100"
                          />
                        {:else if isPdfFile(detail.lampiran_file)}
                          <div class="flex items-center gap-2 text-sm font-medium text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            <a href={getAttachmentUrl(detail.lampiran_file)} target="_blank" rel="noreferrer" class="link link-hover break-all">
                              Preview PDF: {detail.lampiran_file.split('/').pop()}
                            </a>
                          </div>
                        {:else}
                          <div class="flex items-center gap-2 text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                            <a href={getAttachmentUrl(detail.lampiran_file)} target="_blank" rel="noreferrer" class="link link-hover break-all">
                              Download: {detail.lampiran_file.split('/').pop()}
                            </a>
                          </div>
                        {/if}

                        {#if currentLogbook.status === 'SUBMITTED' || currentLogbook.status === 'REJECTED'}
                          <div class="flex justify-end mt-2 border-t border-base-300 pt-2">
                            <button
                              class="btn btn-xs btn-error btn-outline"
                              onclick={() => deleteAttachment(detail.id)}
                              disabled={deletingAttachmentId === detail.id}
                            >
                              {#if deletingAttachmentId === detail.id}
                                <span class="loading loading-spinner loading-xs"></span> Menghapus
                              {:else}
                                ✕ Hapus File
                              {/if}
                            </button>
                          </div>
                        {/if}
                      </div>
                    {:else if currentLogbook.status === 'SUBMITTED' || currentLogbook.status === 'REJECTED'}
                      <div class="form-control w-full">
                        {#if pendingAttachments[detail.id]}
                          <div class="flex items-center justify-between p-2 bg-base-200 border border-base-300 rounded-lg mt-2">
                            <div class="flex items-center gap-2 overflow-hidden">
                              <span class="badge badge-warning badge-sm whitespace-nowrap">Pending upload</span>
                              <span class="text-xs truncate" title={pendingAttachments[detail.id]?.name}>
                                {pendingAttachments[detail.id]?.name}
                              </span>
                            </div>
                            <button
                              class="btn btn-xs btn-ghost text-error"
                              onclick={() => removePendingAttachment(detail.id)}
                            >
                              Batal
                            </button>
                          </div>
                        {:else if uploadingKpiId === detail.id}
                          <div class="flex items-center gap-2 text-sm text-base-content/70 p-2">
                            <span class="loading loading-spinner loading-xs"></span>
                            Mengunggah...
                          </div>
                        {:else}
                          <input
                            type="file"
                            class="file-input file-input-bordered file-input-sm w-full text-xs mt-2"
                            onchange={(e) => uploadAttachment(detail.id, e)}
                          />
                        {/if}
                      </div>
                    {/if}
                  </div>
                </div>
              </div>
            {/each}
          {:else}
            <div class="text-sm text-center py-6 text-base-content/60 bg-base-100 rounded-xl border border-base-200 shadow-sm">
              Tidak ada detail KPI terkait.
            </div>
          {/if}
        </div>

        {#if currentLogbook.status === 'SUBMITTED' || currentLogbook.status === 'REJECTED'}
          <div class="flex flex-col gap-3 mt-8">
            <button
              class="btn btn-success w-full font-bold text-white shadow-sm"
              onclick={handleSubmit}
              disabled={submitLoading || deleteLoading}
            >
              {#if submitLoading}
                <span class="loading loading-spinner loading-sm"></span>
                Submitting...
              {:else}
                Submit Logbook
              {/if}
            </button>
            <button
              class="btn btn-error btn-outline w-full shadow-sm"
              onclick={handleDelete}
              disabled={deleteLoading || submitLoading}
            >
              {#if deleteLoading}
                <span class="loading loading-spinner loading-sm"></span>
                Menghapus...
              {:else}
                Hapus Logbook
              {/if}
            </button>
          </div>
        {/if}
      {/if}
    </div>

  {:else if view === 'create'}
    <div transition:slide={{ duration: 200 }} class="card bg-base-100 border border-base-200 shadow-sm">
      <div class="card-body p-4 gap-4">
        <div class="flex items-center gap-2 mb-2">
          <button class="btn btn-sm btn-ghost btn-circle" onclick={() => (view = 'summary')}>←</button>
          <h2 class="text-lg font-semibold">Buat Logbook</h2>
        </div>

        <form onsubmit={handleCreate} class="flex flex-col gap-4">
          <div class="form-control">
            <div class="label"><span class="label-text font-medium">Tanggal</span></div>
            <input
              type="date"
              class="input input-bordered"
              bind:value={createForm.tanggal}
              required
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="form-control">
              <div class="label"><span class="label-text font-medium">Mulai</span></div>
              <input
                type="time"
                class="input input-bordered"
                bind:value={createForm.start_kerja}
                required
              />
            </div>
            <div class="form-control">
              <div class="label"><span class="label-text font-medium">Selesai</span></div>
              <input
                type="time"
                class="input input-bordered"
                bind:value={createForm.end_kerja}
              />
            </div>
          </div>
          <div class="form-control">
            <div class="label"><span class="label-text font-medium">Lokasi Text</span></div>
            <input
              type="text"
              class="input input-bordered"
              bind:value={createForm.lokasi}
              placeholder="Kantor, WFH, dll"
            />
          </div>

          <div class="form-control bg-base-200 p-3 rounded-lg border border-base-300">
            <div class="label pt-0"><span class="label-text font-medium">Lokasi GPS (Opsional)</span></div>
            <button type="button" class="btn btn-sm btn-outline mb-2 w-full" onclick={getGPS} disabled={gpsLoading}>
              {#if gpsLoading}
                <span class="loading loading-spinner loading-xs"></span> Mencari...
              {:else}
                📍 Ambil Lokasi GPS
              {/if}
            </button>
            
            {#if gpsLat && gpsLng}
              <div class="text-xs text-success font-medium mb-2 text-center">
                Lokasi berhasil diambil: {gpsLat.toFixed(5)}, {gpsLng.toFixed(5)}
              </div>
              <div class="rounded overflow-hidden border border-base-300">
                <LocationMap lat={gpsLat} lng={gpsLng} zoom={15} height="h-32" />
              </div>
            {/if}
          </div>

          <button
            type="submit"
            class="btn btn-primary w-full mt-4"
            disabled={createSubmitting}
          >
            {createSubmitting ? 'Menyimpan...' : 'Mulai & Buat KPI'}
          </button>
        </form>
      </div>
    </div>
  {/if}
</div>

<!-- Confirm Modal -->
{#if confirmModalOpen}
  <div class="modal modal-open">
    <div class="modal-box">
      <h3 class="font-bold text-lg">{confirmModalTitle}</h3>
      <p class="py-4">{confirmModalMessage}</p>
      <div class="modal-action">
        <button class="btn btn-ghost" onclick={() => { confirmModalOpen = false; confirmModalAction = null; }}>
          Batal
        </button>
        <button
          class="btn {confirmModalType === 'error' ? 'btn-error' : 'btn-warning'}"
          onclick={executeConfirm}
        >
          Konfirmasi
        </button>
      </div>
    </div>
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="modal-backdrop" onclick={() => { confirmModalOpen = false; confirmModalAction = null; }}></div>
  </div>
{/if}
