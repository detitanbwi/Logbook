<script lang="ts">
	import type { DailyStaffSummary } from '$lib/types';

	let {
		member,
		onclick
	}: {
		member: DailyStaffSummary;
		onclick?: (member: DailyStaffSummary) => void;
	} = $props();

	const progressPercent = $derived.by(() => {
		if (typeof member.progress_percent === 'number' && Number.isFinite(member.progress_percent)) {
			return Math.round(member.progress_percent);
		}

		return member.target_angka_total > 0
			? Math.round((member.capaian_angka_total / member.target_angka_total) * 100)
			: 0;
	});

	const durationFormatted = $derived(() => {
		const totalMinutes =
			typeof member.total_work_minutes === 'number'
				? member.total_work_minutes
				: Math.round((Number((member as any).total_work_hours ?? 0) || 0) * 60);
		const hours = Math.floor(totalMinutes / 60);
		const mins = totalMinutes % 60;
		return `${hours}h ${mins}m`;
	});
</script>

<button
	class="w-full rounded-lg border border-base-300 bg-base-100 p-3 text-left transition-colors hover:bg-base-200"
	onclick={() => onclick?.(member)}
>
	<div class="mb-2 flex items-center justify-between">
		<span class="font-medium">{member.user?.nama ?? (member as any).nama ?? '—'}</span>
		<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/40" viewBox="0 0 20 20" fill="currentColor">
			<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
		</svg>
	</div>

	<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/70">
		<span>Logbooks: {member.total_logbooks}</span>
		<span>Durasi: {durationFormatted()}</span>
		<span>KPI: {progressPercent}%</span>
	</div>

	<div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-base-300">
		<div
			class="h-full rounded-full transition-all {progressPercent >= 100
				? 'bg-success'
				: progressPercent >= 75
					? 'bg-info'
					: progressPercent >= 50
						? 'bg-warning'
						: 'bg-error'}"
			style="width: {Math.min(progressPercent, 100)}%"
		></div>
	</div>
</button>
