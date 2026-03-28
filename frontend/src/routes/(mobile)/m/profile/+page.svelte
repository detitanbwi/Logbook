<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { authService } from '$lib/api/services/authService';
	import { goto } from '$app/navigation';
	import { slide } from 'svelte/transition';

	let user = $derived(auth.user?.current);

	let oldPassword = $state('');
	let newPassword = $state('');
	let confirmPassword = $state('');
	let isChangingPassword = $state(false);
	let passwordSuccess = $state('');
	let passwordError = $state('');

	function getInitials(name?: string) {
		if (!name) return 'U';
		return name
			.split(' ')
			.map((n) => n[0])
			.join('')
			.substring(0, 2)
			.toUpperCase();
	}

	function formatDate(dateStr?: string | null) {
		if (!dateStr) return 'Belum diisi';
		try {
			const date = new Date(dateStr);
			return new Intl.DateTimeFormat('id-ID', {
				day: 'numeric',
				month: 'long',
				year: 'numeric'
			}).format(date);
		} catch (e) {
			return dateStr;
		}
	}

	async function handleLogout() {
		await auth.logout();
		goto('/login');
	}

	async function handleChangePassword(e: Event) {
		e.preventDefault();
		passwordError = '';
		passwordSuccess = '';

		if (newPassword.length < 8) {
			passwordError = 'Password baru minimal 8 karakter';
			return;
		}
		if (newPassword !== confirmPassword) {
			passwordError = 'Konfirmasi password tidak cocok';
			return;
		}

		isChangingPassword = true;
		try {
			await authService.changePassword({
				old_password: oldPassword,
				new_password: newPassword,
				new_password_confirmation: confirmPassword
			});
			passwordSuccess = 'Password berhasil diubah';
			oldPassword = '';
			newPassword = '';
			confirmPassword = '';
	} catch (error: unknown) {
		const err = error instanceof Error ? error : null;
		passwordError =
			err?.message ||
			'Gagal mengubah password. Periksa kembali password lama Anda.';
		} finally {
			isChangingPassword = false;
		}
	}
</script>

<svelte:head>
	<title>Profile - Mobile</title>
</svelte:head>

<div class="min-h-screen bg-base-200 pb-24">
	{#if user}
		<div class="bg-base-100 px-4 py-8 shadow-sm rounded-b-3xl mb-6">
			<div class="flex flex-col items-center text-center space-y-4">
				<div class="avatar placeholder">
					<div class="bg-primary text-primary-content rounded-full w-24 shadow-md ring ring-primary ring-offset-base-100 ring-offset-2">
						<span class="text-3xl font-bold">{getInitials(user.nama)}</span>
					</div>
				</div>
				
				<div class="space-y-1">
					<h2 class="text-xl font-bold text-base-content">{user.nama}</h2>
					<p class="text-sm font-medium text-base-content/70">{user.npp || 'NPP Belum diisi'}</p>
					<div class="mt-2">
						<span class="badge badge-primary badge-outline font-medium">{user.role || 'User'}</span>
					</div>
				</div>

				{#if user.manager}
					<div class="mt-4 px-4 py-2 bg-base-200 rounded-2xl flex items-center gap-2 text-sm">
						<span class="text-base-content/60">Manager:</span>
						<span class="font-medium text-base-content">{user.manager.nama}</span>
					</div>
				{/if}
			</div>
		</div>

		<div class="px-4 space-y-6">
			<section class="card bg-base-100 shadow-sm border border-base-200/50">
				<div class="card-body p-5 space-y-4">
					<h3 class="font-bold text-base-content/80 uppercase tracking-wider text-xs border-b border-base-200 pb-2">Informasi Pribadi</h3>
					
					<div class="grid grid-cols-1 gap-4">
						<div class="flex flex-col gap-1">
							<span class="text-xs text-base-content/50 font-medium">Email</span>
							<span class="text-sm font-medium">{user.email || 'Belum diisi'}</span>
						</div>
						
						<div class="flex flex-col gap-1">
							<span class="text-xs text-base-content/50 font-medium">Tempat, Tanggal Lahir</span>
							<span class="text-sm font-medium">
								{user.tempat_lahir || 'Belum diisi'}, {formatDate(user.tanggal_lahir)}
							</span>
						</div>

						<div class="grid grid-cols-2 gap-4">
							<div class="flex flex-col gap-1">
								<span class="text-xs text-base-content/50 font-medium">NIK</span>
								<span class="text-sm font-medium">{user.nik || 'Belum diisi'}</span>
							</div>
							<div class="flex flex-col gap-1">
								<span class="text-xs text-base-content/50 font-medium">NPWP</span>
								<span class="text-sm font-medium">{user.npwp || 'Belum diisi'}</span>
							</div>
						</div>

						<div class="flex flex-col gap-1">
							<span class="text-xs text-base-content/50 font-medium">Status Kawin</span>
							<span class="text-sm font-medium">{user.status_kawin || 'Belum diisi'}</span>
						</div>

						<div class="flex flex-col gap-1">
							<span class="text-xs text-base-content/50 font-medium">Alamat</span>
							<span class="text-sm font-medium leading-relaxed">{user.alamat || 'Belum diisi'}</span>
						</div>
					</div>
				</div>
			</section>

			<section class="card bg-base-100 shadow-sm border border-base-200/50">
				<div class="card-body p-5 space-y-4">
					<h3 class="font-bold text-base-content/80 uppercase tracking-wider text-xs border-b border-base-200 pb-2">Riwayat Pendidikan</h3>
					
					{#if user.riwayat_pendidikan && user.riwayat_pendidikan.length > 0}
						<div class="space-y-4">
							{#each user.riwayat_pendidikan as edu}
								<div class="flex flex-col gap-1 relative pl-4 before:absolute before:left-0 before:top-1.5 before:w-2 before:h-2 before:bg-primary/40 before:rounded-full">
									<span class="text-sm font-bold text-base-content">{edu.institusi}</span>
									<span class="text-xs font-medium text-base-content/70">{edu.jenjang} - {edu.jurusan}</span>
									<span class="text-xs font-medium text-primary">{edu.tahun}</span>
								</div>
							{/each}
						</div>
					{:else}
						<p class="text-sm text-base-content/50 italic text-center py-2">Belum ada data</p>
					{/if}
				</div>
			</section>

			<section class="card bg-base-100 shadow-sm border border-base-200/50">
				<div class="card-body p-5 space-y-4">
					<h3 class="font-bold text-base-content/80 uppercase tracking-wider text-xs border-b border-base-200 pb-2">Riwayat Karir</h3>
					
					{#if user.riwayat_karir && user.riwayat_karir.length > 0}
						<div class="space-y-4">
							{#each user.riwayat_karir as career}
								<div class="flex flex-col gap-1 relative pl-4 before:absolute before:left-0 before:top-1.5 before:w-2 before:h-2 before:bg-secondary/40 before:rounded-full">
									<span class="text-sm font-bold text-base-content">{career.jabatan}</span>
									<span class="text-xs font-medium text-base-content/70">{career.perusahaan}</span>
									<span class="text-xs font-medium text-secondary">{career.periode}</span>
								</div>
							{/each}
						</div>
					{:else}
						<p class="text-sm text-base-content/50 italic text-center py-2">Belum ada data</p>
					{/if}
				</div>
			</section>

			<section class="card bg-base-100 shadow-sm border border-base-200/50">
				<div class="card-body p-5 space-y-4">
					<h3 class="font-bold text-base-content/80 uppercase tracking-wider text-xs border-b border-base-200 pb-2">Ubah Password</h3>
					
					<form onsubmit={handleChangePassword} class="space-y-4 mt-2">
						{#if passwordError}
							<div transition:slide class="alert alert-error text-sm rounded-xl py-3">
								<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
								<span>{passwordError}</span>
							</div>
						{/if}

						{#if passwordSuccess}
							<div transition:slide class="alert alert-success text-sm rounded-xl py-3">
								<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
								<span>{passwordSuccess}</span>
							</div>
						{/if}

						<div class="form-control w-full">
							<label class="label pt-0"><span class="label-text font-medium text-xs text-base-content/60">Password Lama</span></label>
							<input 
								type="password" 
								bind:value={oldPassword} 
								required 
								placeholder="Masukkan password lama"
								class="input input-bordered input-sm h-10 rounded-xl bg-base-200/50 focus:bg-base-100" />
						</div>

						<div class="form-control w-full">
							<label class="label pt-0"><span class="label-text font-medium text-xs text-base-content/60">Password Baru</span></label>
							<input 
								type="password" 
								bind:value={newPassword} 
								required 
								placeholder="Minimal 8 karakter"
								class="input input-bordered input-sm h-10 rounded-xl bg-base-200/50 focus:bg-base-100" />
						</div>

						<div class="form-control w-full mb-2">
							<label class="label pt-0"><span class="label-text font-medium text-xs text-base-content/60">Konfirmasi Password Baru</span></label>
							<input 
								type="password" 
								bind:value={confirmPassword} 
								required 
								placeholder="Ulangi password baru"
								class="input input-bordered input-sm h-10 rounded-xl bg-base-200/50 focus:bg-base-100" />
						</div>

						<button 
							type="submit" 
							class="btn btn-primary w-full rounded-xl" 
							disabled={isChangingPassword}>
							{#if isChangingPassword}
								<span class="loading loading-spinner loading-sm"></span>
							{:else}
								Simpan Password
							{/if}
						</button>
					</form>
				</div>
			</section>

			<div class="pt-4 pb-8">
				<button 
					onclick={handleLogout}
					class="btn btn-error btn-outline w-full rounded-xl font-bold bg-base-100">
					Keluar Aplikasi
				</button>
			</div>
		</div>
	{:else}
		<div class="flex items-center justify-center min-h-[60vh]">
			<span class="loading loading-spinner loading-lg text-primary"></span>
		</div>
	{/if}
</div>