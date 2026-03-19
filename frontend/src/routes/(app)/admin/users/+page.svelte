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
	let users = $derived(data.users);
	let meta = $derived(data.meta);

	// Read current filter values from URL
	let search = $derived($page.url.searchParams.get('search') || '');
	let role = $derived($page.url.searchParams.get('role') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');

	const roleOptions = [
		{ label: 'Admin', value: 'Admin' },
		{ label: 'Manager', value: 'Manager' },
		{ label: 'Staff', value: 'Staff' }
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

	let isModalOpen = $state(false);
	let isEditMode = $state(false);
	let currentUserId = $state<string | null>(null);

	let formData = $state<UserCreateDto>({
		nama: '',
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
		formData = { nama: '', email: '', nip: '', password: '', role: 'Staff' };
		isModalOpen = true;
	}

	function openEdit(user: any) {
		isEditMode = true;
		currentUserId = user.id;
		formData = {
			nama: user.nama,
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
					nama: formData.nama,
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
			invalidate('users:list');
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
			invalidate('users:list');
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
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#each users as user}
		<tr>
			<td>{user.nip}</td>
			<td class="font-medium">{user.nama}</td>
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
	{:else}
		<tr>
			<td colspan="5" class="py-4 text-center text-base-content/50"> Belum ada data User. </td>
		</tr>
	{/each}
</DataTable>

<Pagination {meta} />

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
