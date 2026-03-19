<script lang="ts">
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import Modal from '$lib/components/ui/Modal.svelte';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import { SearchInput, FilterDropdown, SortableHeader } from '$lib/components/ui';
	import { kpiService } from '$lib/api/services/kpiService';
	import { goto, invalidate } from '$app/navigation';
	import { page } from '$app/stores';
	import type { MasterKpiCreateDto, MasterKpiUpdateDto } from '$lib/api/schemas/kpi.schema';

	let { data } = $props();
	let kpis = $derived(data.kpis);
	let meta = $derived(data.meta);

	// URL-based state
	let search = $derived($page.url.searchParams.get('search') || '');
	let statusAktif = $derived($page.url.searchParams.get('status_aktif') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

	const statusOptions = [
		{ label: 'Aktif', value: 'true' },
		{ label: 'Tidak Aktif', value: 'false' }
	];

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

	let isModalOpen = $state(false);
	let isEditMode = $state(false);
	let currentKpiId = $state<string | null>(null);

	let formData = $state<MasterKpiCreateDto>({
		nama: '',
		status_aktif: true
	});

	let isSubmitting = $state(false);
	
	let showDeleteConfirm = $state(false);
	let kpiToDelete = $state<string | null>(null);

	function openCreate() {
		isEditMode = false;
		currentKpiId = null;
		formData = { nama: '', status_aktif: true };
		isModalOpen = true;
	}

	function openEdit(kpi: any) {
		isEditMode = true;
		currentKpiId = kpi.id;
		formData = {
			nama: kpi.nama,
			status_aktif: kpi.status_aktif
		};
		isModalOpen = true;
	}

	async function handleSubmit() {
		isSubmitting = true;
		try {
			if (isEditMode && currentKpiId) {
				const updateData: MasterKpiUpdateDto = {
					nama: formData.nama,
					status_aktif: formData.status_aktif
				};
				await kpiService.updateMaster(currentKpiId, updateData);
				toastStore.success('KPI berhasil diperbarui.');
			} else {
				await kpiService.createMaster(formData);
				toastStore.success('KPI berhasil ditambahkan.');
			}
			isModalOpen = false;
			invalidate('kpi:list');
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
		try {
			await kpiService.deleteMaster(kpiToDelete);
			toastStore.success('KPI berhasil dihapus.');
			invalidate('kpi:list');
		} catch (error) {
			console.error('Error deleting KPI:', error);
			toastStore.error('Gagal menghapus KPI.');
		} finally {
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
		onSearch={(v) => updateUrl({ search: v })}
		class="min-w-[200px] flex-1"
	/>
	<FilterDropdown
		label="Status"
		options={statusOptions}
		value={statusAktif}
		onChange={(v) => updateUrl({ status_aktif: v })}
	/>
</div>

<DataTable>
	{#snippet head()}
		<tr>
			<th>ID</th>
			<SortableHeader
				column="nama"
				label="Nama KPI"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={(c, d) => updateUrl({ sort_by: c, sort_dir: d })}
			/>
			<th>Status</th>
			<SortableHeader
				column="created_at"
				label="Dibuat"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={(c, d) => updateUrl({ sort_by: c, sort_dir: d })}
			/>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#each kpis as kpi (kpi.id)}
		<tr>
			<td>{kpi.id}</td>
			<td class="font-medium">{kpi.nama}</td>
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
	{:else}
		<tr>
			<td colspan="5" class="py-4 text-center text-base-content/50"> Belum ada data KPI. </td>
		</tr>
	{/each}
</DataTable>

<Pagination {meta} />

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
