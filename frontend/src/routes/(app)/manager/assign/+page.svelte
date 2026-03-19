<script lang="ts">
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import { SearchInput } from '$lib/components/ui';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import { kpiService } from '$lib/api/services/kpiService';
	import { usersService } from '$lib/api/services/usersService';
	import { invalidate, goto } from '$app/navigation';
	import { page } from '$app/stores';
	import type { KpiAssignmentDto } from '$lib/api/schemas/kpi.schema';

	let { data } = $props();
	let initialSelectedUserId = $derived(data?.selectedUserId || '');

	// State
	let team = $state<any[]>([]);
	let kpis = $state<any[]>([]);
	let assignments = $state<any[]>([]);
	let assignMeta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);
		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
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

	async function fetchData() {
		loading = true;
		error = null;
		try {
			const [usersRes, kpisRes, assignRes] = await Promise.all([
				usersService.getAll({ per_page: 100 }),
				kpiService.getAllMaster({ per_page: 100 }),
				kpiService.getAssignments({ page: currentPage, per_page: perPage, search })
			]);

			const allUsers = Array.isArray(usersRes) ? usersRes : (usersRes as any).data || [];
			team = allUsers.filter((u: any) => u.role === 'Staff');

			kpis = Array.isArray(kpisRes) ? kpisRes : (kpisRes as any).data || [];
			assignments = Array.isArray(assignRes) ? assignRes : (assignRes as any).data || [];
			assignMeta = (assignRes as any).meta || null;
		} catch (e: any) {
			console.error('Failed to fetch data', e);
			error = e.message || 'Gagal memuat data';
		} finally {
			loading = false;
		}
	}

	$effect(() => {
		fetchData();
	});

	let selectedUserId = $state('');

	$effect(() => {
		if (initialSelectedUserId && !selectedUserId) {
			selectedUserId = initialSelectedUserId;
		}
	});

	let selectedKpiId = $state('');
	let isSubmitting = $state(false);

	let showDeleteConfirm = $state(false);
	let assignmentToDelete = $state<string | null>(null);

	async function handleAssign() {
		if (!selectedUserId || !selectedKpiId) {
			toastStore.warning('Pilih staf dan KPI terlebih dahulu!');
			return;
		}

		isSubmitting = true;
		try {
			const payload: KpiAssignmentDto = {
				user_id: selectedUserId,
				kpi_id: selectedKpiId
			};
			await kpiService.assignKpi(payload);

			selectedKpiId = ''; // reset kpi selection
			toastStore.success('KPI berhasil ditetapkan.');
			fetchData();
		} catch (error) {
			console.error('Error assigning KPI:', error);
			toastStore.error('Gagal menetapkan KPI.');
		} finally {
			isSubmitting = false;
		}
	}

	function confirmDelete(id: string) {
		assignmentToDelete = id;
		showDeleteConfirm = true;
	}

	async function executeDelete() {
		if (!assignmentToDelete) return;
		try {
			await kpiService.deleteAssignment(assignmentToDelete);
			toastStore.success('Penugasan KPI berhasil dihapus.');
			fetchData();
		} catch (error) {
			console.error('Error deleting assignment:', error);
			toastStore.error('Gagal menghapus penugasan KPI.');
		} finally {
			assignmentToDelete = null;
		}
	}

	function getUserName(id: string | number) {
		return team.find((u: any) => u.id.toString() === id.toString())?.name || `User #${id}`;
	}
	function getKpiName(id: string | number) {
		return kpis.find((k: any) => k.id.toString() === id.toString())?.nama || `KPI #${id}`;
	}
</script>

<svelte:head>
	<title>Assign KPI | Manager</title>
</svelte:head>

<div class="mb-6">
	<h1 class="text-2xl font-bold">Assign KPI</h1>
	<p class="text-base-content/70">Tetapkan target KPI untuk anggota tim Anda.</p>
</div>

{#if error}
	<div class="alert alert-error mb-6">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={() => fetchData()}>Coba Lagi</button>
	</div>
{/if}

<!-- Form Penugasan -->
<div class="mb-8 grid gap-6 md:grid-cols-2">
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h2 class="mb-4 card-title text-lg">Form Penugasan</h2>

			{#if loading}
				<div class="space-y-4">
					<div class="form-control w-full">
						<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
						<div class="mt-2 h-10 w-full animate-pulse rounded bg-base-300"></div>
					</div>
					<div class="form-control w-full">
						<div class="h-4 w-28 animate-pulse rounded bg-base-300"></div>
						<div class="mt-2 h-10 w-full animate-pulse rounded bg-base-300"></div>
					</div>
					<div class="h-10 w-32 animate-pulse rounded bg-base-300"></div>
				</div>
			{:else}
				<div class="form-control w-full">
					<label class="label" for="userSelect">
						<span class="label-text font-medium">Pilih Staf</span>
					</label>
					<select id="userSelect" class="select-bordered select w-full" bind:value={selectedUserId}>
						<option value="" disabled>-- Pilih Staf --</option>
						{#each team as user}
							<option value={user.id.toString()}>{user.name}</option>
						{/each}
					</select>
				</div>

				<div class="form-control mt-2 w-full">
					<label class="label" for="kpiSelect">
						<span class="label-text font-medium">Pilih KPI Master</span>
					</label>
					<select id="kpiSelect" class="select-bordered select w-full" bind:value={selectedKpiId}>
						<option value="" disabled>-- Pilih KPI --</option>
						{#each kpis as kpi}
							<option value={kpi.id.toString()}>{kpi.nama}</option>
						{/each}
					</select>
				</div>

				<div class="mt-6 card-actions justify-end">
					<button class="btn btn-primary" onclick={handleAssign} disabled={isSubmitting}>
						{isSubmitting ? 'Menyimpan...' : 'Tetapkan KPI'}
					</button>
				</div>
			{/if}
		</div>
	</div>
</div>

<div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
	<h2 class="text-xl font-bold">Daftar Penugasan KPI</h2>
	<SearchInput
		value={search}
		placeholder="Cari nama staff..."
		onSearch={(v) => updateUrl({ search: v })}
		class="w-full max-w-md sm:w-auto"
	/>
</div>

<DataTable>
	{#snippet head()}
		<tr>
			<th>ID Assign</th>
			<th>Staf</th>
			<th>KPI</th>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#if loading}
		{#each Array(5) as _}
			<tr>
				<td>
					<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-40 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-7 w-20 animate-pulse rounded bg-base-300"></div>
				</td>
			</tr>
		{/each}
	{:else if assignments.length === 0}
		<tr>
			<td colspan="4" class="py-4 text-center text-base-content/50"> Belum ada penugasan KPI. </td>
		</tr>
	{:else}
		{#each assignments as assign}
			<tr>
				<td>{assign.id}</td>
				<td class="font-medium">{getUserName(assign.user_id)}</td>
				<td>{getKpiName(assign.kpi_id)}</td>
				<td>
					<button class="btn btn-outline btn-sm btn-error" onclick={() => confirmDelete(assign.id)}>
						Hapus
					</button>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination meta={assignMeta} onPageSizeChange={handlePageSizeChange} />

<ConfirmDialog
	bind:open={showDeleteConfirm}
	title="Hapus Penugasan"
	message="Hapus penugasan KPI ini?"
	confirmText="Hapus"
	type="error"
	onConfirm={executeDelete}
/>
