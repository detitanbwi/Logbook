<script lang="ts">
	import { goto, invalidate } from '$app/navigation';
	import { page } from '$app/stores';
	import {
		DataTable,
		FilterDropdown,
		Modal,
		Pagination,
		SearchInput,
		SortableHeader
	} from '$lib/components/ui';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import { usersService } from '$lib/api/services/usersService';
	import type { UserCreateDto, UserUpdateDto } from '$lib/api/schemas/user.schema';

	let { data } = $props();
	let initialLoad = $derived(data?.initialLoad ?? false);

	// State
	let users = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	// Read current filter values from URL
	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let role = $derived($page.url.searchParams.get('role') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

	const roleOptions = [
		{ label: 'Semua', value: '' },
		{ label: 'Admin', value: 'Admin' },
		{ label: 'Manager', value: 'Manager' },
		{ label: 'Staff', value: 'Staff' }
	];

	async function fetchUsers() {
		loading = true;
		error = null;

		const params: Record<string, unknown> = {
			page: currentPage,
			per_page: perPage
		};

		if (search) params.search = search;
		if (role) params.role = role;
		if (sortBy) params.sort_by = sortBy;
		if (sortDir) params.sort_dir = sortDir;

		try {
			const response = await usersService.getAll(params);
			users = Array.isArray(response) ? response : (response as any).data || [];
			meta = (response as any).meta || null;
		} catch (e: any) {
			console.error('Failed to fetch users', e);
			error = e.message || 'Gagal memuat data user';
		} finally {
			loading = false;
		}
	}

	// Fetch on mount and when URL params change
	$effect(() => {
		fetchUsers();
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
		url.searchParams.set('page', '1'); // Reset to page 1 on filter change
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}

	function handleRoleFilter(value: string) {
		updateUrl({ role: value });
	}

	function handleSort(column: string, dir: 'asc' | 'desc') {
		updateUrl({ sort_by: column, sort_dir: dir });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	let isModalOpen = $state(false);
	let isEditMode = $state(false);
	let currentUserId = $state<string | null>(null);

	let formData = $state<UserCreateDto>({
		name: '',
		email: '',
		nip: '',
		password: '',
		role: 'Staff'
	});

	let isSubmitting = $state(false);

	let showDeleteConfirm = $state(false);
	let userToDelete = $state<string | null>(null);

	const roles = ['Admin', 'Manager', 'Staff'] as const;

	function openCreate() {
		isEditMode = false;
		currentUserId = null;
		formData = { name: '', email: '', nip: '', password: '', role: 'Staff' };
		isModalOpen = true;
	}

	function openEdit(user: any) {
		isEditMode = true;
		currentUserId = user.id;
		formData = {
			name: user.name,
			email: user.email,
			nip: user.nip,
			password: '',
			role: user.role
		};
		isModalOpen = true;
	}

	async function handleSubmit() {
		isSubmitting = true;
		try {
			if (isEditMode && currentUserId) {
				const updateData: UserUpdateDto = {
					name: formData.name,
					email: formData.email,
					nip: formData.nip,
					role: formData.role
				};
				await usersService.update(currentUserId, updateData);
				toastStore.success('User berhasil diperbarui.');
			} else {
				await usersService.create(formData);
				toastStore.success('User berhasil ditambahkan.');
			}
			isModalOpen = false;
			fetchUsers();
		} catch (error) {
			console.error('Error submitting user:', error);
			toastStore.error('Gagal menyimpan user.');
		} finally {
			isSubmitting = false;
		}
	}

	function confirmDelete(id: string) {
		userToDelete = id;
		showDeleteConfirm = true;
	}

	async function executeDelete() {
		if (!userToDelete) return;
		try {
			await usersService.delete(userToDelete);
			toastStore.success('User berhasil dinonaktifkan/dihapus.');
			fetchUsers();
		} catch (error) {
			console.error('Error deleting user:', error);
			toastStore.error('Gagal menghapus user.');
		} finally {
			userToDelete = null;
		}
	}
</script>

<svelte:head>
	<title>User Management | Admin</title>
</svelte:head>

<div class="mb-6 flex items-center justify-between">
	<h1 class="text-2xl font-bold">User Management</h1>
	<button class="btn btn-primary" onclick={openCreate}> + Tambah User </button>
</div>

<!-- Search and Filter Section -->
<div class="mb-4 flex flex-wrap gap-4">
	<SearchInput value={search} placeholder="Cari nama, email, atau NIP..." onSearch={handleSearch} />
	<FilterDropdown label="Role" options={roleOptions} value={role} onChange={handleRoleFilter} />
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={() => fetchUsers()}>Coba Lagi</button>
	</div>
{/if}

<DataTable>
	{#snippet head()}
		<tr>
			<SortableHeader
				column="nip"
				label="NIP"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				column="name"
				label="Nama"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				column="email"
				label="Email"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				column="role"
				label="Role"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
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
					<div class="h-5 w-16 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="flex gap-2">
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
					</div>
				</td>
			</tr>
		{/each}
	{:else if users.length === 0}
		<tr>
			<td colspan="5" class="py-4 text-center text-base-content/50"> Belum ada data User. </td>
		</tr>
	{:else}
		{#each users as user}
			<tr>
				<td>{user.nip}</td>
				<td class="font-medium">{user.name}</td>
				<td>{user.email}</td>
				<td>
					<span class="badge badge-outline">{user.role}</span>
				</td>
				<td>
					<div class="flex gap-2">
						<button class="btn btn-outline btn-sm btn-secondary" onclick={() => openEdit(user)}>
							Edit
						</button>
						<button class="btn btn-outline btn-sm btn-error" onclick={() => confirmDelete(user.id)}>
							Hapus
						</button>
					</div>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={isModalOpen} title={isEditMode ? 'Edit User' : 'Tambah User Baru'}>
	<form class="flex flex-col gap-4">
		<div class="form-control">
			<label class="label" for="nip">
				<span class="label-text">NIP</span>
			</label>
			<input
				id="nip"
				type="text"
				class="input-bordered input w-full"
				bind:value={formData.nip}
				required
			/>
		</div>

		<div class="form-control">
			<label class="label" for="nama">
				<span class="label-text">Nama Lengkap</span>
			</label>
			<input
				id="nama"
				type="text"
				class="input-bordered input w-full"
				bind:value={formData.name}
				required
			/>
		</div>

		<div class="form-control">
			<label class="label" for="email">
				<span class="label-text">Email</span>
			</label>
			<input
				id="email"
				type="email"
				class="input-bordered input w-full"
				bind:value={formData.email}
				required
			/>
		</div>

		{#if !isEditMode}
			<div class="form-control">
				<label class="label" for="password">
					<span class="label-text">Password</span>
				</label>
				<input
					id="password"
					type="password"
					class="input-bordered input w-full"
					bind:value={formData.password}
					required
				/>
			</div>
		{/if}

		<div class="form-control">
			<label class="label" for="role">
				<span class="label-text">Role</span>
			</label>
			<select id="role" class="select-bordered select w-full" bind:value={formData.role}>
				{#each roles as role}
					<option value={role}>{role}</option>
				{/each}
			</select>
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
	title="Hapus User"
	message="Apakah Anda yakin ingin menonaktifkan/menghapus user ini?"
	confirmText="Hapus"
	type="error"
	onConfirm={executeDelete}
/>
