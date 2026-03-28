<script lang="ts">
	import { auth } from '$lib/stores/auth.svelte';
	import { goto } from '$app/navigation';
	import TeamPerformanceTab from '$lib/components/mobile/TeamPerformanceTab.svelte';
	import type { DailyStaffSummary } from '$lib/types';

	let hasSubordinates = $derived(auth.user.current?.has_subordinates ?? false);

	function handleMemberClick(member: DailyStaffSummary) {
		console.log('Member clicked:', member.user?.nama);
	}

	$effect(() => {
		if (!hasSubordinates) {
			goto('/m/overview');
		}
	});
</script>

{#if hasSubordinates}
	<TeamPerformanceTab onMemberClick={handleMemberClick} />
{/if}
