<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import { kpiService } from '$lib/api/services/kpiService';
	import { usersService } from '$lib/api/services/usersService';
	import { toastStore } from '$lib/stores/toast.svelte';

	let staffOptions = $state<any[]>([]);
	let masterKpis = $state<any[]>([]);
	let assignments = $state<any[]>([]);

	let loading = $state(true);
	let error = $state<string | null>(null);
	let fetchRequestId = 0;
	let refreshNonce = $state(0);

	let assigningKpiId = $state<string | null>(null);
	let removingAssignmentId = $state<string | null>(null);

	let selectedUserId = $derived($page.url.searchParams.get('user_id') || '');

	function normalizeId(value: unknown): string {
		if (value === null || value === undefined) return '';
		return String(value);
	}

	const selectedStaff = $derived(
		staffOptions.find((staff) => normalizeId(staff.id) === normalizeId(selectedUserId)) || null
	);

	const assignedForSelectedStaff = $derived.by(() => {
		if (!selectedUserId) return [];

		return assignments.filter(
			(assignment) => normalizeId(assignment.user_id) === normalizeId(selectedUserId)
		);
	});

	const assignedKpiIdSet = $derived.by(
		() => new Set(assignedForSelectedStaff.map((assignment) => normalizeId(assignment.kpi_id)))
	);

	const availableKpis = $derived.by(() => {
		if (!selectedUserId) return [];

		return masterKpis.filter((kpi) => {
			const id = normalizeId(kpi.id);
			const isAssigned = assignedKpiIdSet.has(id);
			const isActive = kpi.status_aktif !== false;
			return !isAssigned && isActive;
		});
	});

	function refreshData() {
		refreshNonce += 1;
	}

	function setSelectedUser(nextUserId: string) {
		const url = new URL($page.url);

		if (nextUserId) {
			url.searchParams.set('user_id', nextUserId);
		} else {
			url.searchParams.delete('user_id');
		}

		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	$effect(() => {
		refreshNonce;

		const requestId = ++fetchRequestId;
		loading = true;
		error = null;

		(async () => {
			try {
				const [staffResponse, kpiResponse, assignmentResponse] = await Promise.all([
					usersService.getAll({
						per_page: 200,
						role: 'Staff',
						sort_by: 'nama',
						sort_dir: 'asc'
					}),
					kpiService.getAllMaster({
						per_page: 200,
						sort_by: 'nama',
						sort_dir: 'asc'
					} as any),
					kpiService.getAssignments({ per_page: 500 })
				]);

				if (requestId !== fetchRequestId) {
					return;
				}

				const rawStaff = Array.isArray(staffResponse) ? staffResponse : (staffResponse as any).data || [];
				staffOptions = rawStaff;
				masterKpis = Array.isArray(kpiResponse) ? kpiResponse : (kpiResponse as any).data || [];
				assignments = Array.isArray(assignmentResponse)
					? assignmentResponse
					: (assignmentResponse as any).data || [];
			} catch (fetchError: any) {
				if (requestId !== fetchRequestId) {
					return;
				}

				console.error('Failed to fetch KPI assignment page data', fetchError);
				error = fetchError.message || 'Gagal memuat data penugasan KPI.';
			} finally {
				if (requestId === fetchRequestId) {
					loading = false;
				}
			}
		})();
	});

	$effect(() => {
		if (loading) return;

		if (staffOptions.length === 0) {
			if (selectedUserId) {
				setSelectedUser('');
			}
			return;
		}

		if (!selectedUserId) {
			setSelectedUser(normalizeId(staffOptions[0].id));
			return;
		}

		const selectedStillExists = staffOptions.some(
			(staff) => normalizeId(staff.id) === normalizeId(selectedUserId)
		);

		if (!selectedStillExists) {
			setSelectedUser(normalizeId(staffOptions[0].id));
		}
	});

	async function handleAssign(kpiId: string) {
		if (!selectedUserId) {
			toastStore.warning('Pilih staff terlebih dahulu.');
			return;
		}

		assigningKpiId = kpiId;
		try {
			await kpiService.assignKpi({
				user_id: selectedUserId,
				kpi_id: kpiId
			});

			toastStore.success('KPI berhasil ditugaskan ke staff.');
			refreshData();
		} catch (assignError) {
			console.error('Error assigning KPI', assignError);
			toastStore.error('Gagal menugaskan KPI.');
		} finally {
			assigningKpiId = null;
		}
	}

	async function handleRemoveAssignment(assignmentId: string) {
		removingAssignmentId = assignmentId;

		try {
			await kpiService.deleteAssignment(assignmentId);
			toastStore.success('Penugasan KPI berhasil dihapus.');
			refreshData();
		} catch (deleteError) {
			console.error('Error removing KPI assignment', deleteError);
			toastStore.error('Gagal menghapus penugasan KPI.');
		} finally {
			removingAssignmentId = null;
		}
	}

	function getStaffLabel(staff: any): string {
		const nama = staff?.nama || '-';
		const npp = staff?.npp || '-';
		return `${nama} (${npp})`;
	}

	function getAssignmentKpiLabel(assignment: any): string {
		const fromRelation = assignment?.kpi?.nama;
		if (fromRelation) return fromRelation;

		const fromMaster = masterKpis.find(
			(kpi) => normalizeId(kpi.id) === normalizeId(assignment.kpi_id)
		);
		return fromMaster?.nama ?? `KPI #${assignment.kpi_id}`;
	}
</script>

<svelte:head>
	<title>KPI Assignments | Admin</title>
</svelte:head>

<div class="mb-6 space-y-2">
	<h1 class="text-2xl font-bold">KPI Assignments</h1>
	<p class="text-base-content/70">Tetapkan dan hapus penugasan KPI untuk staff.</p>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={refreshData}>Coba Lagi</button>
	</div>
{/if}

<div class="card mb-6 border border-base-300 bg-base-100 shadow-sm">
	<div class="card-body gap-4">
		<div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
			<div class="form-control">
				<label class="label" for="staff-select">
					<span class="label-text font-medium">Pilih Staff</span>
				</label>
				<select
					id="staff-select"
					class="select-bordered select w-full"
					value={selectedUserId}
					onchange={(event) =>
						setSelectedUser((event.currentTarget as HTMLSelectElement).value)}
					disabled={loading || staffOptions.length === 0}
				>
					{#if staffOptions.length === 0}
						<option value="">Tidak ada staff</option>
					{:else}
						{#each staffOptions as staff (staff.id)}
							<option value={normalizeId(staff.id)}>{getStaffLabel(staff)}</option>
						{/each}
					{/if}
				</select>
			</div>

			<button class="btn btn-outline" onclick={refreshData} disabled={loading}>Refresh</button>
		</div>

		{#if selectedStaff}
			<div class="text-sm text-base-content/70">
				Selected: <span class="font-medium text-base-content">{selectedStaff.nama || '-'}</span>
				(NPP: {selectedStaff.npp || '-'})
			</div>
		{/if}
	</div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body gap-4">
			<h2 class="card-title text-lg">KPI Ditugaskan</h2>

			<DataTable loading={loading} empty={!!selectedUserId && assignedForSelectedStaff.length === 0} columnsCount={3}>
				{#snippet head()}
					<tr>
						<th>Nama KPI</th>
						<th>Status</th>
						<th>Aksi</th>
					</tr>
				{/snippet}

				{#if selectedUserId}
					{#each assignedForSelectedStaff as assignment (assignment.id)}
						<tr>
							<td class="font-medium">{getAssignmentKpiLabel(assignment)}</td>
							<td>
								<span class="badge badge-success">Assigned</span>
							</td>
							<td>
								<button
									class="btn btn-outline btn-sm btn-error"
									onclick={() => handleRemoveAssignment(normalizeId(assignment.id))}
									disabled={
										removingAssignmentId === normalizeId(assignment.id) ||
										assigningKpiId !== null
									}
								>
									{removingAssignmentId === normalizeId(assignment.id) ? 'Menghapus...' : 'Hapus'}
								</button>
							</td>
						</tr>
					{/each}
				{/if}
			</DataTable>
		</div>
	</div>

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body gap-4">
			<h2 class="card-title text-lg">KPI Tersedia (Belum Ditugaskan)</h2>

			<DataTable loading={loading} empty={!!selectedUserId && availableKpis.length === 0} columnsCount={2}>
				{#snippet head()}
					<tr>
						<th>Nama KPI</th>
						<th>Aksi</th>
					</tr>
				{/snippet}

				{#if selectedUserId}
					{#each availableKpis as kpi (kpi.id)}
						<tr>
							<td class="font-medium">{kpi.nama}</td>
							<td>
								<button
									class="btn btn-primary btn-sm"
									onclick={() => handleAssign(normalizeId(kpi.id))}
									disabled={
										assigningKpiId === normalizeId(kpi.id) || removingAssignmentId !== null
									}
								>
									{assigningKpiId === normalizeId(kpi.id) ? 'Assigning...' : 'Assign'}
								</button>
							</td>
						</tr>
					{/each}
				{/if}
			</DataTable>
		</div>
	</div>
</div>
