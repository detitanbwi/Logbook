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
	import UserAvatar from '$lib/components/ui/UserAvatar.svelte';
	import { toastStore } from '$lib/stores/toast.svelte';
	import { auth } from '$lib/stores/auth.svelte';
	import { usersService } from '$lib/api/services/usersService';
	import type { UserCreateDto, UserUpdateDto } from '$lib/api/schemas/user.schema';
	import { compressToWebP } from '$lib/utils/imageCompression';
	import { resolveStorageUrl } from '$lib/utils/asset-url';

	let { data } = $props();
	let initialLoad = $derived(data?.initialLoad ?? false);

	let users = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let currentPage = $derived.by(() => Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived.by(() => Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived.by(() => $page.url.searchParams.get('search') || '');
	let role = $derived.by(() => $page.url.searchParams.get('role') || '');
	let sortBy = $derived.by(() => $page.url.searchParams.get('sort_by') || 'created_at');
	let sortDir = $derived.by(() => ($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'desc');
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

	let activeTab = $state<'basic' | 'personal' | 'riwayat' | 'foto'>('basic');

	type ComplexUserFormData = UserCreateDto & {
		nik?: string | null;
		npwp?: string | null;
		alamat?: string | null;
		tempat_lahir?: string | null;
		tanggal_lahir?: string | null;
		status_kawin?: string | null;
		riwayat_pendidikan?: Record<string, any>[];
		riwayat_karir?: Record<string, any>[];
	};

	let formData = $state<ComplexUserFormData>({
		nama: '',
		email: '',
		npp: '',
		password: '',
		role: 'Staff',
		manager_id: null,
		nik: null,
		npwp: null,
		alamat: null,
		tempat_lahir: null,
		tanggal_lahir: null,
		status_kawin: null,
		riwayat_pendidikan: [],
		riwayat_karir: []
	});

	let photoFile = $state<File | null>(null);
	let photoPreviewUrl = $state<string | null>(null);

	let roles = $derived(
		currentRole === 'SuperAdmin' ? (['Admin', 'Staff'] as const) : (['Staff'] as const)
	);
	let managerCandidates = $state<any[]>([]);
	let loadingManagers = $state(false);
	let subordinateIds = $state<Set<string>>(new Set());

	const managerOptions = $derived.by(() => {
		const selectedUserId = isEditMode ? currentUserId : null;
		return managerCandidates.filter((candidate) => {
			if (selectedUserId && candidate.id === selectedUserId) return false;
			if (subordinateIds.has(candidate.id)) return false;
			return true;
		});
	});

	const canAssignManager = $derived(formData.role === 'Staff');

	function canManageUser(user: any): boolean {
		if (currentRole === 'SuperAdmin') return true;
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
			console.error('Failed to load subordinates', subordinateError);
			subordinateIds = new Set();
		}
	}

	function pendidikanFieldId(index: number, field: 'institusi' | 'jurusan' | 'tahun_lulus'): string {
		return `pendidikan-${index}-${field}`;
	}

	function karirFieldId(index: number, field: 'perusahaan' | 'posisi' | 'tahun_mulai' | 'tahun_selesai'): string {
		return `karir-${index}-${field}`;
	}

	$effect(() => {
		loading = true;
		error = null;

		const params: Record<string, unknown> = { page: currentPage, per_page: perPage };

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
		goto(url.toString(), { replaceState: true, noScroll: true, keepFocus: true });
	}

	function handleSearch(value: string) {
		updateUrl({ search: value });
	}
	function handleRoleFilter(value: string) {
		updateUrl({ role: value });
	}
	function handleFormRoleChange(value: UserCreateDto['role']) {
		formData.role = value;
		if (value !== 'Staff') formData.manager_id = null;
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
		subordinateIds = new Set();
		formData = {
			nama: '', email: '', npp: '', password: '', role: 'Staff', manager_id: null,
			nik: null, npwp: null, alamat: null, tempat_lahir: null, tanggal_lahir: null, status_kawin: null,
			riwayat_pendidikan: [], riwayat_karir: []
		};
		photoFile = null;
		photoPreviewUrl = null;
		activeTab = 'basic';
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
			manager_id: user.manager_id ?? null,
			nik: user.nik ?? null,
			npwp: user.npwp ?? null,
			alamat: user.alamat ?? null,
			tempat_lahir: user.tempat_lahir ?? null,
			tanggal_lahir: user.tanggal_lahir ?? null,
			status_kawin: user.status_kawin ?? null,
			riwayat_pendidikan: user.riwayat_pendidikan ?? [],
			riwayat_karir: user.riwayat_karir ?? []
		};
		photoFile = null;
		photoPreviewUrl = user.foto_url || (user.foto ? resolveStorageUrl(user.foto) : null);
		activeTab = 'basic';
		isModalOpen = true;
	}

	async function handlePhotoChange(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const file = input.files?.[0];
		if (!file) {
			photoFile = null;
			photoPreviewUrl = null;
			return;
		}

		try {
			photoFile = await compressToWebP(file);
			photoPreviewUrl = URL.createObjectURL(photoFile);
		} catch (err) {
			console.error('Image compression failed', err);
			photoFile = file;
			photoPreviewUrl = URL.createObjectURL(file);
		}
	}

	async function handleSubmit() {
		isSubmitting = true;
		try {
			const normalizedManagerId = canAssignManager ? formData.manager_id || null : null;

			if (isEditMode && currentUserId) {
				if (photoFile) {
					const payloadData = new FormData();
					payloadData.append('nama', formData.nama);
					payloadData.append('email', formData.email);
					payloadData.append('npp', formData.npp);
					payloadData.append('role', formData.role);
					if (normalizedManagerId) payloadData.append('manager_id', normalizedManagerId);
					if (formData.nik) payloadData.append('nik', formData.nik);
					if (formData.npwp) payloadData.append('npwp', formData.npwp);
					if (formData.alamat) payloadData.append('alamat', formData.alamat);
					if (formData.tempat_lahir) payloadData.append('tempat_lahir', formData.tempat_lahir);
					if (formData.tanggal_lahir) payloadData.append('tanggal_lahir', formData.tanggal_lahir);
					if (formData.status_kawin) payloadData.append('status_kawin', formData.status_kawin);

					const pendidikan = formData.riwayat_pendidikan || [];
					pendidikan.forEach((item, i) => {
						Object.entries(item).forEach(([key, val]) => {
							payloadData.append(`riwayat_pendidikan[${i}][${key}]`, String(val ?? ''));
						});
					});

					const karir = formData.riwayat_karir || [];
					karir.forEach((item, i) => {
						Object.entries(item).forEach(([key, val]) => {
							payloadData.append(`riwayat_karir[${i}][${key}]`, String(val ?? ''));
						});
					});

					payloadData.append('foto', photoFile);
					await usersService.update(currentUserId, payloadData as any);
				} else {
					const jsonPayload: Record<string, unknown> = {
						nama: formData.nama,
						email: formData.email,
						npp: formData.npp,
						role: formData.role,
						manager_id: normalizedManagerId,
						nik: formData.nik,
						npwp: formData.npwp,
						alamat: formData.alamat,
						tempat_lahir: formData.tempat_lahir,
						tanggal_lahir: formData.tanggal_lahir,
						status_kawin: formData.status_kawin,
						riwayat_pendidikan: formData.riwayat_pendidikan || [],
						riwayat_karir: formData.riwayat_karir || []
					};
					await usersService.update(currentUserId, jsonPayload as any);
				}
				toastStore.success('User berhasil diperbarui.');
			} else {
				// Create user doesn't strictly need all fields via form-data in our current API
				// But we can fallback to JSON create for now (since create doesn't include foto)
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
		if (!userToDelete) return;
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
		if (!userToResetPassword) return;
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

	function addPendidikan() {
		formData.riwayat_pendidikan = [...(formData.riwayat_pendidikan || []), { institusi: '', jurusan: '', tahun_lulus: '' }];
	}
	function removePendidikan(index: number) {
		formData.riwayat_pendidikan = (formData.riwayat_pendidikan || []).filter((_, i) => i !== index);
	}

	function addKarir() {
		formData.riwayat_karir = [...(formData.riwayat_karir || []), { perusahaan: '', posisi: '', tahun_mulai: '', tahun_selesai: '' }];
	}
	function removeKarir(index: number) {
		formData.riwayat_karir = (formData.riwayat_karir || []).filter((_, i) => i !== index);
	}
</script>

<svelte:head>
	<title>User Management | Admin</title>
</svelte:head>

<div class="mb-6 flex items-center justify-between">
	<h1 class="text-2xl font-bold">User Management</h1>
	<button class="btn btn-primary" onclick={openCreate}> + Tambah User </button>
</div>

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
			<SortableHeader column="npp" label="NPP" currentSort={sortBy} currentDir={sortDir} onSort={handleSort} />
			<SortableHeader column="nama" label="User" currentSort={sortBy} currentDir={sortDir} onSort={handleSort} />
			<SortableHeader column="role" label="Role" currentSort={sortBy} currentDir={sortDir} onSort={handleSort} />
			<th>Manager</th>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#if loading}
		{#each Array(5) as _, index (index)}
			<tr>
				<td><div class="h-4 w-24 animate-pulse rounded bg-base-300"></div></td>
				<td>
					<div class="flex items-center gap-3">
						<div class="h-8 w-8 animate-pulse rounded-full bg-base-300"></div>
						<div>
							<div class="mb-2 h-4 w-32 animate-pulse rounded bg-base-300"></div>
							<div class="h-3 w-40 animate-pulse rounded bg-base-300"></div>
						</div>
					</div>
				</td>
				<td><div class="h-5 w-16 animate-pulse rounded bg-base-300"></div></td>
				<td><div class="h-4 w-32 animate-pulse rounded bg-base-300"></div></td>
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
		{#each users as user (user.id)}
			<tr>
				<td>{user.npp || '-'}</td>
				<td>
					<div class="flex items-center gap-3">
						<UserAvatar foto={user.foto} fotoUrl={user.foto_url} name={user.nama} size="sm" />
						<div>
							<div class="font-medium">{user.nama || '-'}</div>
							<div class="text-sm opacity-50">{user.email}</div>
						</div>
					</div>
				</td>
				<td>
					<span class="badge badge-outline">{user.role}</span>
				</td>
				<td>{user.manager?.nama || '-'}</td>
				<td>
					<div class="flex gap-2">
						<button class="btn btn-outline btn-sm btn-secondary" onclick={() => openEdit(user)} disabled={!canManageUser(user)}>
							Detail / Edit
						</button>
						<button class="btn btn-outline btn-sm" onclick={() => openResetPassword(user.id)} disabled={!canManageUser(user)}>
							Reset Password
						</button>
						<button class="btn btn-outline btn-sm btn-error" onclick={() => confirmDelete(user.id)} disabled={!canManageUser(user)}>
							Hapus
						</button>
					</div>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />

<Modal bind:isOpen={isModalOpen} title={isEditMode ? 'Detail & Edit User' : 'Tambah User Baru'}>
	{#if isEditMode}
		<div class="tabs tabs-boxed mb-4">
			<button class="tab" class:tab-active={activeTab === 'basic'} onclick={(e) => { e.preventDefault(); activeTab = 'basic'; }}>Dasar</button>
			<button class="tab" class:tab-active={activeTab === 'personal'} onclick={(e) => { e.preventDefault(); activeTab = 'personal'; }}>Personal</button>
			<button class="tab" class:tab-active={activeTab === 'riwayat'} onclick={(e) => { e.preventDefault(); activeTab = 'riwayat'; }}>Riwayat</button>
			<button class="tab" class:tab-active={activeTab === 'foto'} onclick={(e) => { e.preventDefault(); activeTab = 'foto'; }}>Foto</button>
		</div>
	{/if}

	<form class="flex flex-col gap-4">
		{#if !isEditMode || activeTab === 'basic'}
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div class="form-control">
					<label class="label" for="npp"><span class="label-text">NPP</span></label>
					<input id="npp" type="text" class="input-bordered input w-full" bind:value={formData.npp} required />
				</div>
				<div class="form-control">
					<label class="label" for="nama"><span class="label-text">Nama Lengkap</span></label>
					<input id="nama" type="text" class="input-bordered input w-full" bind:value={formData.nama} required />
				</div>
				<div class="form-control md:col-span-2">
					<label class="label" for="email"><span class="label-text">Email</span></label>
					<input id="email" type="email" class="input-bordered input w-full" bind:value={formData.email} required />
				</div>
				{#if !isEditMode}
					<div class="form-control md:col-span-2">
						<label class="label" for="password"><span class="label-text">Password</span></label>
						<input id="password" type="password" class="input-bordered input w-full" bind:value={formData.password} required />
					</div>
				{/if}
				<div class="form-control">
					<label class="label" for="role"><span class="label-text">Role</span></label>
					<select id="role" class="select-bordered select w-full" value={formData.role} onchange={(e) => handleFormRoleChange((e.currentTarget as HTMLSelectElement).value as UserCreateDto['role'])} disabled={currentRole === 'Admin'}>
						{#each roles as role (role)}
							<option value={role}>{role}</option>
						{/each}
					</select>
				</div>
				<div class="form-control">
					<label class="label" for="manager_id"><span class="label-text">Atasan (Manager)</span></label>
					<select id="manager_id" class="select-bordered select w-full" bind:value={formData.manager_id} disabled={!canAssignManager || loadingManagers}>
						<option value="">Tanpa Atasan</option>
						{#each managerOptions as manager (manager.id)}
							<option value={manager.id}>{manager.nama || '-'} ({manager.npp || '-'})</option>
						{/each}
					</select>
				</div>
			</div>
		{/if}

		{#if isEditMode && activeTab === 'personal'}
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div class="form-control">
					<label class="label" for="nik"><span class="label-text">NIK</span></label>
					<input id="nik" type="text" class="input-bordered input w-full" bind:value={formData.nik} />
				</div>
				<div class="form-control">
					<label class="label" for="npwp"><span class="label-text">NPWP</span></label>
					<input id="npwp" type="text" class="input-bordered input w-full" bind:value={formData.npwp} />
				</div>
				<div class="form-control">
					<label class="label" for="tempat_lahir"><span class="label-text">Tempat Lahir</span></label>
					<input id="tempat_lahir" type="text" class="input-bordered input w-full" bind:value={formData.tempat_lahir} />
				</div>
				<div class="form-control">
					<label class="label" for="tanggal_lahir"><span class="label-text">Tanggal Lahir</span></label>
					<input id="tanggal_lahir" type="date" class="input-bordered input w-full" bind:value={formData.tanggal_lahir} />
				</div>
				<div class="form-control">
					<label class="label" for="status_kawin"><span class="label-text">Status Kawin</span></label>
					<select id="status_kawin" class="select-bordered select w-full" bind:value={formData.status_kawin}>
						<option value={null}>Pilih Status</option>
						<option value="Belum Kawin">Belum Kawin</option>
						<option value="Kawin">Kawin</option>
						<option value="Cerai Hidup">Cerai Hidup</option>
						<option value="Cerai Mati">Cerai Mati</option>
					</select>
				</div>
				<div class="form-control md:col-span-2">
					<label class="label" for="alamat"><span class="label-text">Alamat</span></label>
					<textarea id="alamat" class="textarea textarea-bordered w-full" rows="3" bind:value={formData.alamat}></textarea>
				</div>
			</div>
		{/if}

		{#if isEditMode && activeTab === 'riwayat'}
			<div class="space-y-6">
				<!-- Riwayat Pendidikan -->
				<div>
					<div class="flex items-center justify-between mb-2">
						<h3 class="font-semibold text-lg">Riwayat Pendidikan</h3>
						<button type="button" class="btn btn-sm btn-outline btn-primary" onclick={addPendidikan}>+ Tambah</button>
					</div>
					{#if formData.riwayat_pendidikan?.length === 0}
						<div class="text-sm text-base-content/60 italic">Belum ada data riwayat pendidikan.</div>
					{:else}
						<div class="space-y-4">
							{#each formData.riwayat_pendidikan || [] as pend, idx}
								<div class="card bg-base-200 p-4 relative">
									<button type="button" class="btn btn-circle btn-xs btn-error absolute top-2 right-2" onclick={() => removePendidikan(idx)}>✕</button>
									<div class="grid grid-cols-1 md:grid-cols-3 gap-2">
										<div class="form-control">
											<label class="label py-1" for={pendidikanFieldId(idx, 'institusi')}><span class="label-text text-xs">Institusi</span></label>
											<input id={pendidikanFieldId(idx, 'institusi')} type="text" class="input input-sm input-bordered w-full" bind:value={pend.institusi} />
										</div>
										<div class="form-control">
											<label class="label py-1" for={pendidikanFieldId(idx, 'jurusan')}><span class="label-text text-xs">Jurusan</span></label>
											<input id={pendidikanFieldId(idx, 'jurusan')} type="text" class="input input-sm input-bordered w-full" bind:value={pend.jurusan} />
										</div>
										<div class="form-control">
											<label class="label py-1" for={pendidikanFieldId(idx, 'tahun_lulus')}><span class="label-text text-xs">Tahun Lulus</span></label>
											<input id={pendidikanFieldId(idx, 'tahun_lulus')} type="text" class="input input-sm input-bordered w-full" bind:value={pend.tahun_lulus} />
										</div>
									</div>
								</div>
							{/each}
						</div>
					{/if}
				</div>

				<div class="divider"></div>

				<!-- Riwayat Karir -->
				<div>
					<div class="flex items-center justify-between mb-2">
						<h3 class="font-semibold text-lg">Riwayat Karir</h3>
						<button type="button" class="btn btn-sm btn-outline btn-primary" onclick={addKarir}>+ Tambah</button>
					</div>
					{#if formData.riwayat_karir?.length === 0}
						<div class="text-sm text-base-content/60 italic">Belum ada data riwayat karir.</div>
					{:else}
						<div class="space-y-4">
							{#each formData.riwayat_karir || [] as karir, idx}
								<div class="card bg-base-200 p-4 relative">
									<button type="button" class="btn btn-circle btn-xs btn-error absolute top-2 right-2" onclick={() => removeKarir(idx)}>✕</button>
									<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
										<div class="form-control">
											<label class="label py-1" for={karirFieldId(idx, 'perusahaan')}><span class="label-text text-xs">Perusahaan</span></label>
											<input id={karirFieldId(idx, 'perusahaan')} type="text" class="input input-sm input-bordered w-full" bind:value={karir.perusahaan} />
										</div>
										<div class="form-control">
											<label class="label py-1" for={karirFieldId(idx, 'posisi')}><span class="label-text text-xs">Posisi</span></label>
											<input id={karirFieldId(idx, 'posisi')} type="text" class="input input-sm input-bordered w-full" bind:value={karir.posisi} />
										</div>
										<div class="form-control">
											<label class="label py-1" for={karirFieldId(idx, 'tahun_mulai')}><span class="label-text text-xs">Mulai</span></label>
											<input id={karirFieldId(idx, 'tahun_mulai')} type="text" class="input input-sm input-bordered w-full" bind:value={karir.tahun_mulai} placeholder="YYYY" />
										</div>
										<div class="form-control">
											<label class="label py-1" for={karirFieldId(idx, 'tahun_selesai')}><span class="label-text text-xs">Selesai</span></label>
											<input id={karirFieldId(idx, 'tahun_selesai')} type="text" class="input input-sm input-bordered w-full" bind:value={karir.tahun_selesai} placeholder="YYYY / Sekarang" />
										</div>
									</div>
								</div>
							{/each}
						</div>
					{/if}
				</div>
			</div>
		{/if}

		{#if isEditMode && activeTab === 'foto'}
			<div class="flex flex-col items-center justify-center space-y-4">
				<div class="avatar">
					<div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
						{#if photoPreviewUrl}
							<img src={photoPreviewUrl} alt="Preview" class="object-cover" />
						{:else}
							<div class="flex h-full w-full items-center justify-center bg-base-300 text-3xl font-bold text-base-content/50">
								{formData.nama ? formData.nama.charAt(0).toUpperCase() : '?'}
							</div>
						{/if}
					</div>
				</div>
				
				<div class="form-control w-full max-w-xs mt-4">
					<label class="label" for="user-photo-upload"><span class="label-text">Upload Foto Baru (WebP/JPG/PNG)</span></label>
					<input id="user-photo-upload" type="file" class="file-input file-input-bordered w-full" accept="image/jpeg,image/png,image/webp" onchange={handlePhotoChange} />
					<p class="label-text-alt text-base-content/60 mt-1">Foto akan otomatis dikompres ke WebP.</p>
				</div>
			</div>
		{/if}
	</form>

	{#snippet actions()}
		<button class="btn btn-primary" onclick={handleSubmit} disabled={isSubmitting}>
			{isSubmitting ? 'Menyimpan...' : 'Simpan'}
		</button>
	{/snippet}
</Modal>

<ConfirmDialog bind:open={showDeleteConfirm} title="Hapus User" message="Apakah Anda yakin ingin menonaktifkan/menghapus user ini?" confirmText="Hapus" type="error" onConfirm={executeDelete} />

<Modal bind:isOpen={showResetPasswordModal} title="Reset Password User">
	<form class="flex flex-col gap-4">
		<div class="form-control">
			<label class="label" for="new-password"><span class="label-text">Password Baru</span></label>
			<input id="new-password" type="password" class="input-bordered input w-full" bind:value={newPassword} placeholder="Masukkan password baru (min. 8 karakter)" required />
		</div>
	</form>
	{#snippet actions()}
		<button class="btn btn-primary" onclick={handleResetPassword} disabled={isResettingPassword || newPassword.length < 8}>
			{isResettingPassword ? 'Memproses...' : 'Reset Password'}
		</button>
	{/snippet}
</Modal>
