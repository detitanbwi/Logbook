<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { DataTable, Modal, Pagination, SearchInput, SortableHeader } from '$lib/components/ui';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import { usersService } from '$lib/api/services/usersService';
	import type { UserCreateDto, UserUpdateDto } from '$lib/api/schemas/user.schema';
	import { toastStore } from '$lib/stores/toast.svelte';

	let admins = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let refreshNonce = $state(0);

	let currentPage = $derived.by(() => Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived.by(() => Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived.by(() => $page.url.searchParams.get('search') || '');
	let sortBy = $derived.by(() => $page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived.by(() => ($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

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
		role: 'Admin',
		manager_id: null
	});

	function refreshList() {
		refreshNonce += 1;
	}

	$effect(() => {
		refreshNonce;

		loading = true;
		error = null;

		const params: Record<string, unknown> = {
			page: currentPage,
			per_page: perPage,
			role: 'Admin',
			sort_by: sortBy,
			sort_dir: sortDir
		};

		if (search) params.search = search;

		usersService
			.getAll(params)
			.then((response) => {
				const rawAdmins = Array.isArray(response) ? response : (response as any).data || [];
				admins = rawAdmins;
				meta = (response as any).meta || null;
			})
			.catch((fetchError: any) => {
				console.error('Failed to fetch admins', fetchError);
				error = fetchError.message || 'Gagal memuat data admin';
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
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}

	function handleSort(column: string, dir: 'asc' | 'desc') {
		updateUrl({ sort_by: column, sort_dir: dir });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function openCreate() {
		isEditMode = false;
		currentUserId = null;
		formData = { nama: '', email: '', npp: '', password: '', role: 'Admin', manager_id: null };
		isModalOpen = true;
	}

	function openEdit(user: any) {
		isEditMode = true;
		currentUserId = user.id;
		formData = {
			nama: user.nama ?? '',
			email: user.email,
			npp: user.npp ?? '',
			password: '',
			role: 'Admin',
			manager_id: null
		};
		isModalOpen = true;
	}

	async function handleSubmit() {
		if (isSubmitting) {
			return;
		}

		isSubmitting = true;
		try {
			if (isEditMode && currentUserId) {
				const updateData: UserUpdateDto = {
					nama: formData.nama,
					email: formData.email,
					npp: formData.npp,
					role: 'Admin'
				};

				await usersService.update(currentUserId, updateData);
				toastStore.success('Admin berhasil diperbarui.');
			} else {
				const createData: UserCreateDto = {
					nama: formData.nama,
					email: formData.email,
					npp: formData.npp,
					password: formData.password,
					role: 'Admin',
					manager_id: null
				};

				await usersService.create(createData);
				toastStore.success('Admin berhasil ditambahkan.');
			}

			isModalOpen = false;
			refreshList();
		} catch (submitError) {
			console.error('Error submitting admin:', submitError);
			toastStore.error('Gagal menyimpan admin.');
		} finally {
			isSubmitting = false;
		}
	}

	function confirmDelete(id: string) {
		userToDelete = id;
		showDeleteConfirm = true;
	}

	async function executeDelete() {
		if (!userToDelete) {
			return;
		}

		try {
			await usersService.delete(userToDelete);
			toastStore.success('Admin berhasil dinonaktifkan/dihapus.');
			refreshList();
		} catch (deleteError) {
			console.error('Error deleting admin:', deleteError);
			toastStore.error('Gagal menghapus admin.');
		} finally {
			userToDelete = null;
		}
	}

	function openResetPassword(id: string) {
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
			toastStore.success('Password admin berhasil direset.');
			showResetPasswordModal = false;
			userToResetPassword = null;
			newPassword = '';
		} catch (resetError) {
			console.error('Error resetting admin password:', resetError);
			toastStore.error('Gagal reset password admin.');
		} finally {
			isResettingPassword = false;
		}
	}
</script>

<svelte:head>
	<title>Manajemen Admin | SuperAdmin</title>
</svelte:head>

<div class="mb-6 flex items-center justify-between">
	<h1 class="text-2xl font-bold">Manajemen Admin</h1>
	<button class="btn btn-primary" onclick={openCreate}> + Tambah Admin </button>
</div>

<div class="mb-4 flex flex-wrap gap-4">
	<SearchInput value={search} placeholder="Cari nama, email, atau NPP admin..." onSearch={handleSearch} />
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
					<div class="flex gap-2">
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
						<div class="h-7 w-16 animate-pulse rounded bg-base-300"></div>
					</div>
				</td>
			</tr>
		{/each}
	{:else if admins.length === 0}
		<tr>
			<td colspan="4" class="py-4 text-center text-base-content/50">Belum ada data admin.</td>
		</tr>
	{:else}
		{#each admins as admin (admin.id)}
			<tr>
				<td>{admin.npp || '-'}</td>
				<td class="font-medium">{admin.nama || '-'}</td>
				<td>{admin.email}</td>
				<td>
					<div class="flex gap-2">
						<button class="btn btn-outline btn-sm btn-secondary" onclick={() => openEdit(admin)}>
							Edit
						</button>
						<button class="btn btn-outline btn-sm" onclick={() => openResetPassword(admin.id)}>
							Reset Password
						</button>
						<button class="btn btn-outline btn-sm btn-error" onclick={() => confirmDelete(admin.id)}>
							Hapus
						</button>
					</div>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={isModalOpen} title={isEditMode ? 'Edit Admin' : 'Tambah Admin Baru'}>
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
			<input id="role" type="text" class="input-bordered input w-full" value="Admin" disabled />
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
	title="Hapus Admin"
	message="Apakah Anda yakin ingin menonaktifkan/menghapus admin ini?"
	confirmText="Hapus"
	type="error"
	onConfirm={executeDelete}
/>

<Modal bind:isOpen={showResetPasswordModal} title="Reset Password Admin">
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
