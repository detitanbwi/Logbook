<script lang="ts">
	import { resolveStorageUrl } from '$lib/utils/asset-url';

	let {
		foto = null,
		fotoUrl = null,
		name = '',
		size = 'sm'
	}: {
		foto?: string | null;
		fotoUrl?: string | null;
		name?: string;
		size?: 'xs' | 'sm' | 'md' | 'lg';
	} = $props();

	let avatarSrc = $derived.by(() => {
		if (fotoUrl) return fotoUrl;
		if (foto) {
			return resolveStorageUrl(foto);
		}
		return null;
	});

	let initials = $derived.by(() => {
		if (!name) return '?';
		const parts = name.trim().split(' ');
		if (parts.length >= 2) {
			return (parts[0][0] + parts[1][0]).toUpperCase();
		}
		return name.substring(0, 2).toUpperCase();
	});

	const sizeClasses = {
		xs: 'w-6',
		sm: 'w-8',
		md: 'w-12',
		lg: 'w-24'
	};
</script>

{#if avatarSrc}
	<div class="avatar">
		<div class="{sizeClasses[size]} rounded-full">
			<img src={avatarSrc} alt={name} class="object-cover rounded-full" />
		</div>
	</div>
{:else}
	<div class="placeholder avatar">
		<div class="{sizeClasses[size]} rounded-full bg-primary text-primary-content">
			<span class="font-semibold" class:text-xs={size === 'xs'} class:text-sm={size === 'sm'} class:text-lg={size === 'md'} class:text-3xl={size === 'lg'}>
				{initials}
			</span>
		</div>
	</div>
{/if}
