<script lang="ts">
	let { size = 'md', class: className = '' }: { size?: 'xs' | 'sm' | 'md' | 'lg', class?: string } = $props();

	let sizeClass = $derived(
		size === 'xs' ? 'loading-xs' :
		size === 'sm' ? 'loading-sm' :
		size === 'lg' ? 'loading-lg' :
		'loading-md'
	);
</script>

<span class="loading loading-spinner {sizeClass} {className}"></span>
