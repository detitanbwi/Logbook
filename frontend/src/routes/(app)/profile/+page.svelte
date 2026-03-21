<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { authService } from '$lib/api/services/authService';
	import UserAvatar from '$lib/components/ui/UserAvatar.svelte';
	import { compressToWebP } from '$lib/utils/imageCompression';

	let profileNama = $state(auth.user.current?.nama ?? '');
	let profileEmail = $state(auth.user.current?.email ?? '');
	let profileNik = $state(auth.user.current?.nik ?? '');
	let profileNpwp = $state(auth.user.current?.npwp ?? '');
	let profileAlamat = $state(auth.user.current?.alamat ?? '');
	let profileTempatLahir = $state(auth.user.current?.tempat_lahir ?? '');
	let profileTanggalLahir = $state(auth.user.current?.tanggal_lahir ?? '');
	let profileStatusKawin = $state(auth.user.current?.status_kawin ?? '');
	
	let profilePhoto = $state<File | null>(null);
	let photoPreviewUrl = $state<string | null>(null);
	
	let isUpdatingProfile = $state(false);
	let profileMessage = $state<{ type: 'success' | 'error'; text: string } | null>(null);

	let currentPassword = $state('');
	let newPassword = $state('');
	let confirmPassword = $state('');
	let isChangingPassword = $state(false);
	let passwordMessage = $state<{ type: 'success' | 'error'; text: string } | null>(null);

	let user = $derived(auth.user.current);

	async function handlePhotoChange(event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const file = input.files?.[0];
		if (!file) {
			profilePhoto = null;
			photoPreviewUrl = null;
			return;
		}

		try {
			profilePhoto = await compressToWebP(file);
			photoPreviewUrl = URL.createObjectURL(profilePhoto);
		} catch (err) {
			console.error('Compression failed', err);
			profilePhoto = file;
			photoPreviewUrl = URL.createObjectURL(file);
		}
	}

	async function handleUpdateProfile(e: Event) {
		e.preventDefault();

		const nama = profileNama.trim();
		const email = profileEmail.trim();

		if (!nama) {
			profileMessage = { type: 'error', text: 'Nama wajib diisi.' };
			return;
		}

		if (!email) {
			profileMessage = { type: 'error', text: 'Email wajib diisi.' };
			return;
		}

		isUpdatingProfile = true;
		profileMessage = null;

		try {
			if (profilePhoto) {
				const formData = new FormData();
				formData.append('nama', nama);
				formData.append('email', email);
				if (profileNik) formData.append('nik', profileNik);
				if (profileNpwp) formData.append('npwp', profileNpwp);
				if (profileAlamat) formData.append('alamat', profileAlamat);
				if (profileTempatLahir) formData.append('tempat_lahir', profileTempatLahir);
				if (profileTanggalLahir) formData.append('tanggal_lahir', profileTanggalLahir);
				if (profileStatusKawin) formData.append('status_kawin', profileStatusKawin);
				
				formData.append('foto', profilePhoto);
				await authService.updateProfile(formData);
			} else {
				await authService.updateProfile({ 
					nama, 
					email,
					nik: profileNik,
					npwp: profileNpwp,
					alamat: profileAlamat,
					tempat_lahir: profileTempatLahir,
					tanggal_lahir: profileTanggalLahir,
					status_kawin: profileStatusKawin
				} as any);
			}

			const refreshedUser = await auth.fetchMe();
			profileNama = refreshedUser?.nama ?? nama;
			profileEmail = refreshedUser?.email ?? email;
			profileNik = refreshedUser?.nik ?? profileNik;
			profileNpwp = refreshedUser?.npwp ?? profileNpwp;
			profileAlamat = refreshedUser?.alamat ?? profileAlamat;
			profileTempatLahir = refreshedUser?.tempat_lahir ?? profileTempatLahir;
			profileTanggalLahir = refreshedUser?.tanggal_lahir ?? profileTanggalLahir;
			profileStatusKawin = refreshedUser?.status_kawin ?? profileStatusKawin;
			
			profileMessage = { type: 'success', text: 'Profil berhasil diperbarui.' };
			profilePhoto = null;
			photoPreviewUrl = null;
		} catch (error: any) {
			const errorMessage = error.response?.data?.message || error.message || 'Gagal memperbarui profil';
			profileMessage = { type: 'error', text: errorMessage };
		} finally {
			isUpdatingProfile = false;
		}
	}

	async function handleChangePassword(e: Event) {
		e.preventDefault();
		if (newPassword !== confirmPassword) {
			passwordMessage = { type: 'error', text: 'Password baru dan konfirmasi tidak cocok.' };
			return;
		}

		if (newPassword.length < 8) {
			passwordMessage = { type: 'error', text: 'Password baru minimal 8 karakter.' };
			return;
		}

		isChangingPassword = true;
		passwordMessage = null;

		try {
			await authService.changePassword({
				old_password: currentPassword,
				new_password: newPassword,
				new_password_confirmation: confirmPassword
			});

			passwordMessage = { type: 'success', text: 'Password berhasil diubah.' };
			currentPassword = '';
			newPassword = '';
			confirmPassword = '';
		} catch (error: any) {
			const errorMessage = error.response?.data?.message || error.message || 'Gagal mengubah password';
			passwordMessage = { type: 'error', text: errorMessage };
		} finally {
			isChangingPassword = false;
		}
	}
</script>

<div class="max-w-3xl space-y-6">
	<h1 class="mb-6 text-3xl font-bold text-base-content">Profil Pengguna</h1>

	{#if user}
		<div class="card border border-base-300 bg-base-100 shadow-sm">
			<div class="card-body">
				<div class="mb-2 flex items-center gap-6">
					<UserAvatar foto={user.foto} fotoUrl={user.foto_url} name={user.nama} size="lg" />
					<div>
						<h2 class="text-2xl font-bold text-base-content">{user.nama}</h2>
						<p class="mb-2 font-medium text-base-content/70">{user.npp}</p>
						<span class="badge font-medium badge-primary">{user.role}</span>
					</div>
				</div>
			</div>
		</div>
	{/if}

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h3 class="mb-4 card-title text-xl">Informasi Profil</h3>

			{#if profileMessage}
				<div
					class="mb-4 alert rounded-lg p-3 text-sm"
					class:alert-success={profileMessage.type === 'success'}
					class:alert-error={profileMessage.type === 'error'}
				>
					<span>{profileMessage.text}</span>
				</div>
			{/if}

			<form onsubmit={handleUpdateProfile} class="space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div class="form-control w-full">
						<label class="label" for="profile-nama">
							<span class="label-text font-medium">Nama</span>
						</label>
						<input
							id="profile-nama"
							type="text"
							bind:value={profileNama}
							placeholder="Masukkan nama"
							class="input-bordered input w-full"
							required
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-email">
							<span class="label-text font-medium">Email</span>
						</label>
						<input
							id="profile-email"
							type="email"
							bind:value={profileEmail}
							placeholder="Masukkan email"
							class="input-bordered input w-full"
							required
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-nik">
							<span class="label-text font-medium">NIK</span>
						</label>
						<input
							id="profile-nik"
							type="text"
							bind:value={profileNik}
							class="input-bordered input w-full"
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-npwp">
							<span class="label-text font-medium">NPWP</span>
						</label>
						<input
							id="profile-npwp"
							type="text"
							bind:value={profileNpwp}
							class="input-bordered input w-full"
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-tempat-lahir">
							<span class="label-text font-medium">Tempat Lahir</span>
						</label>
						<input
							id="profile-tempat-lahir"
							type="text"
							bind:value={profileTempatLahir}
							class="input-bordered input w-full"
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-tanggal-lahir">
							<span class="label-text font-medium">Tanggal Lahir</span>
						</label>
						<input
							id="profile-tanggal-lahir"
							type="date"
							bind:value={profileTanggalLahir}
							class="input-bordered input w-full"
							disabled={isUpdatingProfile}
						/>
					</div>

					<div class="form-control w-full">
						<label class="label" for="profile-status-kawin">
							<span class="label-text font-medium">Status Kawin</span>
						</label>
						<select
							id="profile-status-kawin"
							class="select select-bordered w-full"
							bind:value={profileStatusKawin}
							disabled={isUpdatingProfile}
						>
							<option value="">Pilih Status</option>
							<option value="Belum Kawin">Belum Kawin</option>
							<option value="Kawin">Kawin</option>
							<option value="Cerai Hidup">Cerai Hidup</option>
							<option value="Cerai Mati">Cerai Mati</option>
						</select>
					</div>

					<div class="form-control w-full md:col-span-2">
						<label class="label" for="profile-alamat">
							<span class="label-text font-medium">Alamat</span>
						</label>
						<textarea
							id="profile-alamat"
							bind:value={profileAlamat}
							class="textarea textarea-bordered w-full"
							rows="2"
							disabled={isUpdatingProfile}
						></textarea>
					</div>

					<div class="form-control w-full md:col-span-2">
						<label class="label" for="profile-photo">
							<span class="label-text font-medium">Foto Profil (WebP/JPG/PNG)</span>
						</label>
						<div class="flex items-center gap-4">
							{#if photoPreviewUrl}
								<div class="avatar">
									<div class="w-16 rounded-full border border-base-300">
										<img src={photoPreviewUrl} alt="Preview" class="object-cover" />
									</div>
								</div>
							{/if}
							<input
								id="profile-photo"
								type="file"
								accept="image/jpeg,image/png,image/webp"
								onchange={handlePhotoChange}
								class="file-input file-input-bordered w-full max-w-xs"
								disabled={isUpdatingProfile}
							/>
						</div>
					</div>
				</div>

				<div class="mt-6">
					<button
						type="submit"
						class="btn btn-primary"
						disabled={isUpdatingProfile || !profileNama.trim() || !profileEmail.trim()}
					>
						{#if isUpdatingProfile}
							<span class="loading loading-sm loading-spinner"></span>
							Menyimpan...
						{:else}
							Simpan Profil
						{/if}
					</button>
				</div>
			</form>
		</div>
	</div>

	{#if user?.riwayat_pendidikan && user.riwayat_pendidikan.length > 0}
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h3 class="mb-4 card-title text-xl">Riwayat Pendidikan</h3>
			<div class="space-y-4">
				{#each user.riwayat_pendidikan as pend}
					<div class="bg-base-200 p-4 rounded-lg">
						<div class="font-semibold text-lg">{pend.institusi}</div>
						<div class="text-sm opacity-80">{pend.jurusan}</div>
						<div class="text-xs opacity-60 mt-1">Lulus Tahun: {pend.tahun_lulus}</div>
					</div>
				{/each}
			</div>
		</div>
	</div>
	{/if}

	{#if user?.riwayat_karir && user.riwayat_karir.length > 0}
	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h3 class="mb-4 card-title text-xl">Riwayat Karir</h3>
			<div class="space-y-4">
				{#each user.riwayat_karir as karir}
					<div class="bg-base-200 p-4 rounded-lg">
						<div class="font-semibold text-lg">{karir.posisi}</div>
						<div class="text-sm opacity-80">{karir.perusahaan}</div>
						<div class="text-xs opacity-60 mt-1">{karir.tahun_mulai} - {karir.tahun_selesai || 'Sekarang'}</div>
					</div>
				{/each}
			</div>
		</div>
	</div>
	{/if}

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h3 class="mb-4 card-title text-xl">Ubah Password</h3>

			{#if passwordMessage}
				<div
					class="mb-4 alert rounded-lg p-3 text-sm"
					class:alert-success={passwordMessage.type === 'success'}
					class:alert-error={passwordMessage.type === 'error'}
				>
					<span>{passwordMessage.text}</span>
				</div>
			{/if}

			<form onsubmit={handleChangePassword} class="space-y-4">
				<div class="form-control w-full max-w-md">
					<label class="label" for="current-password">
						<span class="label-text font-medium">Password Saat Ini</span>
					</label>
					<input
						id="current-password"
						type="password"
						bind:value={currentPassword}
						placeholder="Masukkan password saat ini"
						class="input-bordered input w-full"
						required
						disabled={isChangingPassword}
					/>
				</div>

				<div class="form-control w-full max-w-md">
					<label class="label" for="new-password">
						<span class="label-text font-medium">Password Baru</span>
					</label>
					<input
						id="new-password"
						type="password"
						bind:value={newPassword}
						placeholder="Masukkan password baru"
						class="input-bordered input w-full"
						required
						disabled={isChangingPassword}
					/>
				</div>

				<div class="form-control w-full max-w-md">
					<label class="label" for="confirm-password">
						<span class="label-text font-medium">Konfirmasi Password Baru</span>
					</label>
					<input
						id="confirm-password"
						type="password"
						bind:value={confirmPassword}
						placeholder="Ulangi password baru"
						class="input-bordered input w-full"
						required
						disabled={isChangingPassword}
					/>
				</div>

				<div class="mt-6">
					<button
						type="submit"
						class="btn btn-primary"
						disabled={isChangingPassword || !currentPassword || !newPassword || !confirmPassword}
					>
						{#if isChangingPassword}
							<span class="loading loading-sm loading-spinner"></span>
							Menyimpan...
						{:else}
							Simpan Password
						{/if}
					</button>
				</div>
			</form>
		</div>
	</div>
</div>