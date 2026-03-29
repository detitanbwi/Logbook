<script lang="ts">
	import { goto } from '$app/navigation';
	import TeamPerformanceTab from '$lib/components/mobile/TeamPerformanceTab.svelte';
	import type { DailyStaffSummary } from '$lib/types';

	function handleMemberClick(member: DailyStaffSummary) {
		const userId = member.user?.id || member.user_id;
		if (!userId) return;
		goto(`/m/team/performance/${userId}`);
	}
</script>

<div class="min-h-screen bg-base-200 pb-24">
	<div class="navbar sticky top-0 z-10 bg-base-100 shadow-sm">
		<div class="flex-none">
			<button class="btn btn-square btn-ghost" aria-label="Kembali" onclick={() => goto('/m/team')}>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					stroke-width="1.5"
					stroke="currentColor"
					class="h-6 w-6"
				>
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			</button>
		</div>
		<div class="flex-1">
			<h1 class="text-lg font-bold">Team Performance</h1>
		</div>
	</div>

	<main class="space-y-4 p-4">
		<div
			class="rounded-2xl border border-primary/15 bg-gradient-to-br from-primary/10 via-base-100 to-base-100 p-4 shadow-sm"
		>
			<div class="text-xs font-semibold tracking-[0.12em] text-primary/80 uppercase">
				Performance Monitor
			</div>
			<h2 class="mt-1 text-xl font-bold">Performa Tim</h2>
			<p class="mt-1 text-sm text-base-content/70">
				Pilih mode harian/periode, lalu ketuk kartu staff untuk lihat detail KPI dan logbook.
			</p>
		</div>

		<TeamPerformanceTab onMemberClick={handleMemberClick} />
	</main>
</div>
