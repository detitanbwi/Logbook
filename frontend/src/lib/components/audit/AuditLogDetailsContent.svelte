<script lang="ts">
	import type { AuditLog } from '$lib/types';

	interface Props {
		log: AuditLog;
		getUserName: (log: AuditLog) => string;
		formatDateTime: (value: string | null | undefined) => string;
		toPrettyJson: (value: unknown) => string;
	}

	let { log, getUserName, formatDateTime, toPrettyJson }: Props = $props();
</script>

<div class="space-y-4">
	<div class="grid gap-3 text-sm sm:grid-cols-2">
		<div>
			<div class="text-base-content/60">User</div>
			<div class="font-medium">{getUserName(log)}</div>
		</div>
		<div>
			<div class="text-base-content/60">Action</div>
			<div class="font-medium uppercase">{log.action ?? '-'}</div>
		</div>
		<div>
			<div class="text-base-content/60">Table</div>
			<div class="font-mono text-xs">{log.table_name ?? '-'}</div>
		</div>
		<div>
			<div class="text-base-content/60">Record ID</div>
			<div class="font-mono text-xs">{log.record_id ?? '-'}</div>
		</div>
	</div>

	<div class="divider my-1"></div>

	<div class="grid gap-3 text-sm sm:grid-cols-2">
		<div>
			<div class="text-base-content/60">IP Address</div>
			<div class="font-medium">{log.ip_address ?? '-'}</div>
		</div>
		<div>
			<div class="text-base-content/60">Performed At</div>
			<div class="font-medium">{formatDateTime(log.performed_at ?? log.created_at)}</div>
		</div>
	</div>

	<div>
		<div class="mb-1 text-sm text-base-content/60">User Agent</div>
		<pre class="max-h-28 overflow-auto rounded-box bg-base-200 p-3 text-xs whitespace-pre-wrap">{log.user_agent ?? '-'}</pre>
	</div>

	<div class="grid gap-3 lg:grid-cols-2">
		<div>
			<div class="mb-1 text-sm font-medium">old_data</div>
			<pre class="max-h-72 overflow-auto rounded-box bg-base-200 p-3 text-xs">{toPrettyJson(log.old_data)}</pre>
		</div>
		<div>
			<div class="mb-1 text-sm font-medium">new_data</div>
			<pre class="max-h-72 overflow-auto rounded-box bg-base-200 p-3 text-xs">{toPrettyJson(log.new_data)}</pre>
		</div>
	</div>
</div>
