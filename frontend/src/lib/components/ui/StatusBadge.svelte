<script lang="ts">
	type LogbookStatus = 'SUBMITTED' | 'ACCEPTED' | 'REJECTED' | string;
	let { status }: { status: LogbookStatus } = $props();

	let badgeClass = $derived.by(() => {
		switch (status) {
			case 'SUBMITTED':
				return 'badge-warning';
			case 'ACCEPTED':
				return 'badge-success';
			case 'REJECTED':
				return 'badge-error';
			default:
				return 'badge-neutral';
		}
	});
</script>

<span class="badge {badgeClass} gap-1 font-medium">
	{status}
</span>
