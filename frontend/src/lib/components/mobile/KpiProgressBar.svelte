<script lang="ts">
	let {
		nama,
		capaian,
		target,
		satuan = '',
		class: className = ''
	}: {
		nama: string;
		capaian: number;
		target: number;
		satuan?: string;
		class?: string;
	} = $props();

	const percent = $derived(target > 0 ? Math.min(Math.round((capaian / target) * 100), 100) : 0);
	const isComplete = $derived(percent >= 100);
	const barColor = $derived(
		isComplete ? 'bg-success' : percent >= 75 ? 'bg-info' : percent >= 50 ? 'bg-warning' : 'bg-error'
	);
</script>

<div class="rounded-lg border border-base-300 bg-base-100 p-3 {className}">
	<div class="mb-1 flex items-center justify-between">
		<span class="text-sm font-medium">{nama}</span>
		<span class="text-xs text-base-content/60">
			{isComplete ? '✅' : '⏳'}
		</span>
	</div>

	<div class="mb-1 flex items-baseline justify-between text-xs text-base-content/70">
		<span>
			{capaian}/{target} {satuan}
		</span>
		<span class="font-semibold">{percent}%</span>
	</div>

	<div class="h-2 w-full overflow-hidden rounded-full bg-base-300">
		<div class="h-full rounded-full transition-all {barColor}" style="width: {percent}%"></div>
	</div>
</div>
