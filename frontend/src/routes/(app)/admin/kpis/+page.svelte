<script lang="ts">
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import Modal from '$lib/components/ui/Modal.svelte';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import { SearchInput, FilterDropdown, SortableHeader } from '$lib/components/ui';
	import { kpiService } from '$lib/api/services/kpiService';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import type { MasterKpiCreateDto, MasterKpiUpdateDto } from '$lib/api/schemas/kpi.schema';

	let { data: _data } = $props();

	// State
	let kpis = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let fetchRequestId = 0;
	let refreshNonce = $state(0);

	// URL-based state
	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let statusAktif = $derived($page.url.searchParams.get('status_aktif') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

	const statusOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Aktif', value: 'true' },
		{ label: 'Tidak Aktif', value: 'false' }
	];

	function refreshList() {
		refreshNonce += 1;
	}

	$effect(() => {
		// Depend on manual refresh trigger in addition to URL-driven params.
		refreshNonce;

		const requestId = ++fetchRequestId;
		loading = true;
		error = null;

		const params: Record<string, unknown> = {
			page: currentPage,
			per_page: perPage
		};

		if (search) params.search = search;
		if (statusAktif) params.status_aktif = statusAktif === 'true';
		if (sortBy) params.sort_by = sortBy;
		if (sortDir) params.sort_dir = sortDir;

		(async () => {
			try {
				const response = await kpiService.getAllMaster(params);

				if (requestId !== fetchRequestId) {
					return;
				}

				kpis = Array.isArray(response) ? response : (response as any).data || [];
				meta = (response as any).meta || null;
			} catch (e: any) {
				if (requestId !== fetchRequestId) {
					return;
				}

				console.error('Failed to fetch KPIs', e);
				error = e.message || 'Gagal memuat data KPI';
			} finally {
				if (requestId === fetchRequestId) {
					loading = false;
				}
			}
		})();
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
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}

	function handleStatusFilter(value: string) {
		updateUrl({ status_aktif: value });
	}

	function handleSort(column: string, dir: 'asc' | 'desc') {
		updateUrl({ sort_by: column, sort_dir: dir });
	}

	let isModalOpen = $state(false);
	let isEditMode = $state(false);
	let currentKpiId = $state<string | null>(null);

	let formData = $state<MasterKpiCreateDto>({
		nama: '',
		target_angka: 0,
		satuan: '',
		deskripsi: '',
		status_aktif: true
	});

	let isSubmitting = $state(false);

	let showDeleteConfirm = $state(false);
	let kpiToDelete = $state<string | null>(null);

	function openCreate() {
		isEditMode = false;
		currentKpiId = null;
		formData = { nama: '', target_angka: 0, satuan: '', deskripsi: '', status_aktif: true };
		isModalOpen = true;
	}

	function openEdit(kpi: any) {
		isEditMode = true;
		currentKpiId = kpi.id;
		formData = {
			nama: kpi.nama,
			target_angka: kpi.target_angka ?? 0,
			satuan: kpi.satuan ?? '',
			deskripsi: kpi.deskripsi ?? '',
			status_aktif: kpi.status_aktif
		};
		isModalOpen = true;
	}

	async function handleSubmit() {
		if (isSubmitting) {
			return;
		}

		const nama = formData.nama.trim();
		if (!nama) {
			toastStore.error('Nama KPI wajib diisi.');
			return;
		}

		if (!formData.satuan || !formData.satuan.trim()) {
			toastStore.error('Satuan wajib diisi.');
			return;
		}

		isSubmitting = true;
		try {
			if (isEditMode && currentKpiId) {
				const updateData: MasterKpiUpdateDto = {
					nama,
					target_angka: formData.target_angka,
					satuan: formData.satuan,
					deskripsi: formData.deskripsi || null,
					status_aktif: formData.status_aktif
				};
				await kpiService.updateMaster(currentKpiId, updateData);
				toastStore.success('KPI berhasil diperbarui.');
			} else {
				await kpiService.createMaster({
					nama,
					target_angka: formData.target_angka,
					satuan: formData.satuan,
					deskripsi: formData.deskripsi || null,
					status_aktif: formData.status_aktif
				});
				toastStore.success('KPI berhasil ditambahkan.');
			}
			isModalOpen = false;
			refreshList();
		} catch (error) {
			console.error('Error submitting KPI:', error);
			toastStore.error('Gagal menyimpan KPI.');
		} finally {
			isSubmitting = false;
		}
	}

	function confirmDelete(id: string) {
		kpiToDelete = id;
		showDeleteConfirm = true;
	}

	async function executeDelete() {
		if (!kpiToDelete) return;
		const deleteId = kpiToDelete;
		const shouldMoveToPreviousPage = kpis.length === 1 && currentPage > 1;

		try {
			await kpiService.deleteMaster(deleteId);
			toastStore.success('KPI berhasil dihapus.');

			if (shouldMoveToPreviousPage) {
				const url = new URL($page.url);
				url.searchParams.set('page', String(currentPage - 1));
				await goto(url.toString(), { replaceState: true, noScroll: true });
				return;
			}

			refreshList();
		} catch (error) {
			console.error('Error deleting KPI:', error);
			toastStore.error('Gagal menghapus KPI.');
		} finally {
			showDeleteConfirm = false;
			kpiToDelete = null;
		}
	}
</script>

<svelte:head>
	<title>Master KPIs | Admin</title>
</svelte:head>

<div class="mb-6 flex items-center justify-between">
	<h1 class="text-2xl font-bold">Master KPIs</h1>
	<button class="btn btn-primary" onclick={openCreate}> + Tambah KPI </button>
</div>

<div class="mb-4 flex flex-wrap gap-4">
	<SearchInput
		value={search}
		placeholder="Cari nama KPI..."
		onSearch={handleSearch}
		class="min-w-[200px] flex-1"
	/>
	<FilterDropdown
		label="Status"
		options={statusOptions}
		value={statusAktif}
		onChange={handleStatusFilter}
	/>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={refreshList}>Coba Lagi</button>
	</div>
{/if}

<DataTable>
	{#snippet head()}
		<tr>
			<th>ID</th>
			<SortableHeader
				column="nama"
				label="Nama KPI"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>Target</th>
			<th>Satuan</th>
			<th>Status</th>
			<SortableHeader
				column="created_at"
				label="Dibuat"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#if loading}
		{#each Array(5) as _, index (index)}
			<tr>
				<td>
					<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-48 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-16 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-5 w-16 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="flex gap-2">
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
					</div>
				</td>
			</tr>
		{/each}
	{:else if kpis.length === 0}
		<tr>
			<td colspan="7" class="py-4 text-center text-base-content/50"> Belum ada data KPI. </td>
		</tr>
	{:else}
		{#each kpis as kpi (kpi.id)}
			<tr>
				<td>{kpi.id}</td>
				<td class="font-medium">{kpi.nama}</td>
				<td>{kpi.target_angka}</td>
				<td>{kpi.satuan}</td>
				<td>
					<span class="badge {kpi.status_aktif ? 'badge-success' : 'badge-error'}">
						{kpi.status_aktif ? 'Aktif' : 'Nonaktif'}
					</span>
				</td>
				<td>
					<span class="text-sm text-base-content/70">
						{new Date(kpi.created_at).toLocaleDateString('id-ID')}
					</span>
				</td>
				<td>
					<div class="flex gap-2">
						<button class="btn btn-outline btn-sm btn-secondary" onclick={() => openEdit(kpi)}>
							Edit
						</button>
						<button class="btn btn-outline btn-sm btn-error" onclick={() => confirmDelete(kpi.id)}>
							Hapus
						</button>
					</div>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={isModalOpen} title={isEditMode ? 'Edit KPI' : 'Tambah KPI Baru'}>
	<form class="flex flex-col gap-4">
		<div class="form-control">
			<label class="label" for="nama">
				<span class="label-text">Nama KPI</span>
			</label>
			<input
				id="nama"
				type="text"
				class="input-bordered input w-full"
				bind:value={formData.nama}
				placeholder="Misal: Penyusunan Laporan"
				required
			/>
		</div>

		<div class="form-control">
			<label class="label" for="target_angka">
				<span class="label-text">Target Angka</span>
			</label>
			<input id="target_angka" type="number" class="input-bordered input w-full" bind:value={formData.target_angka} min="0" step="1" required />
		</div>

		<div class="form-control">
			<label class="label" for="satuan">
				<span class="label-text">Satuan</span>
			</label>
			<input id="satuan" type="text" class="input-bordered input w-full" bind:value={formData.satuan} placeholder="Misal: laporan, persen, unit" required />
		</div>

		<div class="form-control">
			<label class="label" for="deskripsi">
				<span class="label-text">Deskripsi (Opsional)</span>
			</label>
			<textarea id="deskripsi" class="textarea-bordered textarea w-full" bind:value={formData.deskripsi} placeholder="Deskripsi KPI..." rows="3"></textarea>
		</div>

		<div class="form-control">
			<label class="label cursor-pointer justify-start gap-3">
				<input
					type="checkbox"
					class="checkbox checkbox-primary"
					bind:checked={formData.status_aktif}
				/>
				<span class="label-text">Status Aktif</span>
			</label>
		</div>
	</form>

	{#snippet actions()}
		<button class="btn btn-primary" onclick={handleSubmit} disabled={isSubmitting}>
			{isSubmitting ? 'Menyimpan...' : 'Simpan'}
		</button>
	{/snippet}
</Modal>

<ConfirmDialog
	bind:open={showDeleteConfirm}
	title="Hapus KPI"
	message="Apakah Anda yakin ingin menghapus KPI ini?"
	confirmText="Hapus"
	type="error"
	onConfirm={executeDelete}
/>
