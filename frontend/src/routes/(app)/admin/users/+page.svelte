<script lang="ts">
	import { goto } from '$app/navigation';
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
	import { auth } from '$lib/stores/auth.svelte';
	import { usersService } from '$lib/api/services/usersService';
	import type { UserCreateDto, UserUpdateDto } from '$lib/api/schemas/user.schema';

	let { data } = $props();
	let initialLoad = $derived(data?.initialLoad ?? false);

	let users = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let role = $derived($page.url.searchParams.get('role') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');
	let currentRole = $derived(auth.user.current?.role ?? null);

	let roleOptions = $derived(
		currentRole === 'SuperAdmin'
			? [
					{ label: 'Semua', value: '' },
					{ label: 'Admin', value: 'Admin' },
					{ label: 'Staff', value: 'Staff' }
				]
			: [
					{ label: 'Semua', value: '' },
					{ label: 'Staff', value: 'Staff' }
				]
	);

	let isModalOpen = $state(false);
	let isEditMode = $state(false);
	let currentUserId = $state<string | null>(null);
	let isSubmitting = $state(false);

	let showDeleteConfirm = $state(false);
	let userToDelete = $state<string | null>(null);
	let showResetPasswordModal = $state(false);
	let userToResetPassword = $state<string | null>(null);
	let newPassword = $state('');
	let isResettingPassword = $state(false);

	let formData = $state<UserCreateDto>({
		nama: '',
		email: '',
		npp: '',
		password: '',
		role: 'Staff',
		manager_id: null
	});

	let roles = $derived(
		currentRole === 'SuperAdmin' ? (['Admin', 'Staff'] as const) : (['Staff'] as const)
	);
	let managerCandidates = $state<any[]>([]);
	let loadingManagers = $state(false);
	let subordinateIds = $state<Set<string>>(new Set());

	const managerOptions = $derived.by(() => {
		const selectedUserId = isEditMode ? currentUserId : null;

		return managerCandidates.filter((candidate) => {
			if (selectedUserId && candidate.id === selectedUserId) {
				return false;
			}

			if (subordinateIds.has(candidate.id)) {
				return false;
			}

			return true;
		});
	});

	const canAssignManager = $derived(formData.role === 'Staff');

	function canManageUser(user: any): boolean {
		if (currentRole === 'SuperAdmin') {
			return true;
		}

		return user.role === 'Staff';
	}

	async function loadManagerCandidates() {
		loadingManagers = true;
		try {
			const response = await usersService.getAll({
				per_page: 100,
				role: 'Staff',
				sort_by: 'nama',
				sort_dir: 'asc'
			});

			const rawUsers = Array.isArray(response) ? response : (response as any).data || [];
			managerCandidates = rawUsers;
		} catch (managerError) {
			console.error('Failed to load manager candidates', managerError);
			managerCandidates = [];
		} finally {
			loadingManagers = false;
		}
	}

	async function loadSubordinates(userId: string) {
		try {
			const response = await usersService.getSubordinates(userId, { per_page: 100 });
			const items = Array.isArray(response) ? response : (response as any).data || [];
			subordinateIds = new Set(items.map((item: any) => item.id));
		} catch (subordinateError) {
			console.error('Failed to load subordinates for manager filtering', subordinateError);
			subordinateIds = new Set();
		}
	}

	$effect(() => {
		loading = true;
		error = null;

		const params: Record<string, unknown> = {
			page: currentPage,
			per_page: perPage
		};

		if (search) params.search = search;
		if (currentRole === 'Admin') {
			params.role = 'Staff';
		} else if (role) {
			params.role = role;
		}
		if (sortBy) params.sort_by = sortBy;
		if (sortDir) params.sort_dir = sortDir;

		usersService
			.getAll(params)
			.then((response) => {
				const rawUsers = Array.isArray(response) ? response : (response as any).data || [];
				users = rawUsers;
				meta = (response as any).meta || null;
			})
			.catch((fetchError: any) => {
				console.error('Failed to fetch users', fetchError);
				error = fetchError.message || 'Gagal memuat data user';
			})
			.finally(() => {
				loading = false;
			});
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

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}

	function handleRoleFilter(value: string) {
		updateUrl({ role: value });
	}

	function handleFormRoleChange(value: UserCreateDto['role']) {
		formData.role = value;
		if (value !== 'Staff') {
			formData.manager_id = null;
		}
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

	function openCreate() {
		isEditMode = false;
		currentUserId = null;
		subordinateIds = new Set();
		formData = { nama: '', email: '', npp: '', password: '', role: 'Staff', manager_id: null };
		loadManagerCandidates();
		isModalOpen = true;
	}

	async function openEdit(user: any) {
		if (!canManageUser(user)) {
			toastStore.error('Anda hanya dapat mengelola user role Staff.');
			return;
		}

		isEditMode = true;
		currentUserId = user.id;
		await Promise.all([loadManagerCandidates(), loadSubordinates(user.id)]);
		formData = {
			nama: user.nama ?? '',
			email: user.email,
			npp: user.npp ?? '',
			password: '',
			role: user.role,
			manager_id: user.manager_id ?? null
		};
		isModalOpen = true;
	}

	async function handleSubmit() {
		isSubmitting = true;
		try {
			const normalizedManagerId = canAssignManager ? formData.manager_id || null : null;

			if (isEditMode && currentUserId) {
				const updateData: UserUpdateDto = {
					nama: formData.nama,
					email: formData.email,
					npp: formData.npp,
					role: formData.role,
					manager_id: normalizedManagerId
				};
				await usersService.update(currentUserId, updateData);
				toastStore.success('User berhasil diperbarui.');
			} else {
				const createData: UserCreateDto = {
					nama: formData.nama,
					email: formData.email,
					npp: formData.npp,
					password: formData.password,
					role: formData.role,
					manager_id: normalizedManagerId
				};

				await usersService.create(createData);
				toastStore.success('User berhasil ditambahkan.');
			}
			isModalOpen = false;
			updateUrl({});
		} catch (submitError) {
			console.error('Error submitting user:', submitError);
			toastStore.error('Gagal menyimpan user.');
		} finally {
			isSubmitting = false;
		}
	}

	function confirmDelete(id: string) {
		const user = users.find((item) => item.id === id);
		if (user && !canManageUser(user)) {
			toastStore.error('Anda hanya dapat menghapus user role Staff.');
			return;
		}

		userToDelete = id;
		showDeleteConfirm = true;
	}

	async function executeDelete() {
		if (!userToDelete) {
			return;
		}

		try {
			await usersService.delete(userToDelete);
			toastStore.success('User berhasil dinonaktifkan/dihapus.');
			updateUrl({});
		} catch (deleteError) {
			console.error('Error deleting user:', deleteError);
			toastStore.error('Gagal menghapus user.');
		} finally {
			userToDelete = null;
		}
	}

	function openResetPassword(id: string) {
		const user = users.find((item) => item.id === id);
		if (user && !canManageUser(user)) {
			toastStore.error('Anda hanya dapat reset password user role Staff.');
			return;
		}

		userToResetPassword = id;
		newPassword = '';
		showResetPasswordModal = true;
	}

	async function handleResetPassword() {
		if (!userToResetPassword) {
			return;
		}

		if (newPassword.length < 8) {
			toastStore.error('Password baru minimal 8 karakter.');
			return;
		}

		isResettingPassword = true;

		try {
			await usersService.resetPassword(userToResetPassword, newPassword);
			toastStore.success('Password berhasil direset.');
			showResetPasswordModal = false;
			userToResetPassword = null;
			newPassword = '';
		} catch (resetError) {
			console.error('Error resetting password:', resetError);
			toastStore.error('Gagal reset password.');
		} finally {
			isResettingPassword = false;
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
	<SearchInput value={search} placeholder="Cari nama, email, atau NPP..." onSearch={handleSearch} />
	{#if currentRole === 'SuperAdmin'}
		<FilterDropdown label="Role" options={roleOptions} value={role} onChange={handleRoleFilter} />
	{/if}
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={() => updateUrl({})}>Coba Lagi</button>
	</div>
{/if}

<DataTable>
	{#snippet head()}
		<tr>
			<SortableHeader
				column="npp"
				label="NPP"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				column="nama"
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
			<th>Manager</th>
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
					<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-40 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-5 w-16 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
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
			<td colspan="6" class="py-4 text-center text-base-content/50"> Belum ada data User. </td>
		</tr>
	{:else}
		{#each users as user (user.id)}
			<tr>
				<td>{user.npp || '-'}</td>
				<td class="font-medium">{user.nama || '-'}</td>
				<td>{user.email}</td>
				<td>
					<span class="badge badge-outline">{user.role}</span>
				</td>
				<td>{user.manager?.nama || '-'}</td>
				<td>
					<div class="flex gap-2">
						<button
							class="btn btn-outline btn-sm btn-secondary"
							onclick={() => openEdit(user)}
							disabled={!canManageUser(user)}
						>
							Edit
						</button>
						<button
							class="btn btn-outline btn-sm"
							onclick={() => openResetPassword(user.id)}
							disabled={!canManageUser(user)}
						>
							Reset Password
						</button>
						<button
							class="btn btn-outline btn-sm btn-error"
							onclick={() => confirmDelete(user.id)}
							disabled={!canManageUser(user)}
						>
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
			<label class="label" for="npp">
				<span class="label-text">NPP</span>
			</label>
			<input
				id="npp"
				type="text"
				class="input-bordered input w-full"
				bind:value={formData.npp}
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
				bind:value={formData.nama}
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
			<select
				id="role"
				class="select-bordered select w-full"
				value={formData.role}
				onchange={(event) =>
					handleFormRoleChange((event.currentTarget as HTMLSelectElement).value as UserCreateDto['role'])}
				disabled={currentRole === 'Admin'}
			>
				{#each roles as role (role)}
					<option value={role}>{role}</option>
				{/each}
			</select>
		</div>

		<div class="form-control">
			<label class="label" for="manager_id">
				<span class="label-text">Atasan (Manager)</span>
			</label>
			<select
				id="manager_id"
				class="select-bordered select w-full"
				bind:value={formData.manager_id}
				disabled={!canAssignManager || loadingManagers}
			>
				<option value="">Tanpa Atasan</option>
				{#each managerOptions as manager (manager.id)}
					<option value={manager.id}>
						{manager.nama || '-'} ({manager.npp || '-'})
					</option>
				{/each}
			</select>
			{#if loadingManagers}
				<span class="label-text-alt mt-1">Memuat kandidat atasan...</span>
			{:else if canAssignManager && managerOptions.length === 0}
				<span class="label-text-alt mt-1">Tidak ada kandidat atasan yang tersedia.</span>
			{/if}
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

<Modal bind:isOpen={showResetPasswordModal} title="Reset Password User">
	<form class="flex flex-col gap-4">
		<div class="form-control">
			<label class="label" for="new-password">
				<span class="label-text">Password Baru</span>
			</label>
			<input
				id="new-password"
				type="password"
				class="input-bordered input w-full"
				bind:value={newPassword}
				placeholder="Masukkan password baru (min. 8 karakter)"
				required
			/>
		</div>
	</form>

	{#snippet actions()}
		<button
			class="btn btn-primary"
			onclick={handleResetPassword}
			disabled={isResettingPassword || newPassword.length < 8}
		>
			{isResettingPassword ? 'Memproses...' : 'Reset Password'}
		</button>
	{/snippet}
</Modal>
