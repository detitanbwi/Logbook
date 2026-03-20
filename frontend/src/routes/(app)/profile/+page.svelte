<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { authService } from '$lib/api/services/authService';

	let profileNama = $state(auth.user.current?.nama ?? '');
	let profileEmail = $state(auth.user.current?.email ?? '');
	let profilePhoto = $state<File | null>(null);
	let isUpdatingProfile = $state(false);
	let profileMessage = $state<{ type: 'success' | 'error'; text: string } | null>(null);

	let currentPassword = $state('');
	let newPassword = $state('');
	let confirmPassword = $state('');
	let isChangingPassword = $state(false);
	let passwordMessage = $state<{ type: 'success' | 'error'; text: string } | null>(null);

	let user = $derived(auth.user.current);

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
				formData.append('foto', profilePhoto);
				await authService.updateProfile(formData);
			} else {
				await authService.updateProfile({ nama, email } as any);
			}

			const refreshedUser = await auth.fetchMe();
			profileNama = refreshedUser?.nama ?? nama;
			profileEmail = refreshedUser?.email ?? email;
			profileMessage = { type: 'success', text: 'Profil berhasil diperbarui.' };
			profilePhoto = null;
		} catch (error: any) {
			const errorMessage =
				error.response?.data?.message || error.message || 'Gagal memperbarui profil';
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
			const errorMessage =
				error.response?.data?.message || error.message || 'Gagal mengubah password';
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
					<div class="placeholder avatar">
						<div
							class="w-24 rounded-full bg-primary text-primary-content ring ring-primary ring-offset-2 ring-offset-base-100"
						>
							<span class="text-3xl font-bold"
								>{(user.nama?.charAt(0) || 'U').toUpperCase()}</span
							>
						</div>
					</div>
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
				<div class="form-control w-full max-w-md">
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

				<div class="form-control w-full max-w-md">
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

				<div class="form-control w-full max-w-md">
					<label class="label" for="profile-photo">
						<span class="label-text font-medium">Foto Profil (opsional)</span>
					</label>
					<input
						id="profile-photo"
						type="file"
						accept="image/*"
						onchange={(event) => {
							const input = event.currentTarget as HTMLInputElement;
							profilePhoto = input.files?.[0] ?? null;
						}}
						class="file-input file-input-bordered w-full"
						disabled={isUpdatingProfile}
					/>
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
