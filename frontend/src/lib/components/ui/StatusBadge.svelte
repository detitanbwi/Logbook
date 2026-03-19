<script lang="ts">
	type LogbookStatus = 'DRAFT' | 'SUBMITTED' | 'REVIEWED' | string;
	let { status }: { status: LogbookStatus } = $props();

	let badgeClass = $derived.by(() => {
		switch (status) {
			case 'DRAFT':
				return 'badge-ghost';
			case 'SUBMITTED':
				return 'badge-warning';
			case 'REVIEWED':
				return 'badge-success';
			default:
				return 'badge-neutral';
		}
	});
</script>

<span class="badge {badgeClass} gap-1 font-medium">
	{status}
</span>
