<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { authService } from '$lib/api/services/authService';

	let currentPassword = $state('');
	let newPassword = $state('');
	let confirmPassword = $state('');
	let isSubmitting = $state(false);
	let message = $state<{ type: 'success' | 'error'; text: string } | null>(null);

	let user = $derived(auth.user.current);

	async function handleChangePassword(e: Event) {
		e.preventDefault();
		if (newPassword !== confirmPassword) {
			message = { type: 'error', text: 'Password baru dan konfirmasi tidak cocok.' };
			return;
		}

		if (newPassword.length < 8) {
			message = { type: 'error', text: 'Password baru minimal 8 karakter.' };
			return;
		}

		isSubmitting = true;
		message = null;

		try {
			await authService.changePassword({
				old_password: currentPassword,
				new_password: newPassword,
				new_password_confirmation: confirmPassword
			});

			message = { type: 'success', text: 'Password berhasil diubah.' };
			currentPassword = '';
			newPassword = '';
			confirmPassword = '';
		} catch (error: any) {
			const errorMessage =
				error.response?.data?.message || error.message || 'Gagal mengubah password';
			message = { type: 'error', text: errorMessage };
		} finally {
			isSubmitting = false;
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
							<span class="text-3xl font-bold">{(user.name?.charAt(0) || 'U').toUpperCase()}</span>
						</div>
					</div>
					<div>
						<h2 class="text-2xl font-bold text-base-content">{user.name}</h2>
						<p class="mb-2 font-medium text-base-content/70">{user.nip}</p>
						<span class="badge font-medium badge-primary">{user.role}</span>
					</div>
				</div>
			</div>
		</div>
	{/if}

	<div class="card border border-base-300 bg-base-100 shadow-sm">
		<div class="card-body">
			<h3 class="mb-4 card-title text-xl">Ubah Password</h3>

			{#if message}
				<div
					class="mb-4 alert rounded-lg p-3 text-sm"
					class:alert-success={message.type === 'success'}
					class:alert-error={message.type === 'error'}
				>
					<span>{message.text}</span>
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
						disabled={isSubmitting}
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
						disabled={isSubmitting}
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
						disabled={isSubmitting}
					/>
				</div>

				<div class="mt-6">
					<button
						type="submit"
						class="btn btn-primary"
						disabled={isSubmitting || !currentPassword || !newPassword || !confirmPassword}
					>
						{#if isSubmitting}
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
