<script lang="ts">
	import { CheckCircle2, Circle } from 'lucide-svelte';
	import type { Notification } from '$lib/api/schemas/notification.schema';
	import { getNotificationPreviewText, isNotificationUnread } from '$lib/utils/notification';

	interface Props {
		notification: Notification;
		pendingReadId?: string | null;
		markingAllAsRead?: boolean;
		formatDate: (value: string) => string;
		onOpen: (notification: Notification) => void;
		onMarkAsRead: (id: string) => void;
	}

	let {
		notification,
		pendingReadId = null,
		markingAllAsRead = false,
		formatDate,
		onOpen,
		onMarkAsRead
	}: Props = $props();

	let unread = $derived(isNotificationUnread(notification));
	let isMarkingCurrent = $derived(pendingReadId === notification.id);
</script>

<div
	class="flex w-full cursor-pointer items-start gap-4 p-4 text-left transition-colors hover:bg-base-200/50 {unread
		? ''
		: 'opacity-70'}"
	onclick={() => onOpen(notification)}
	onkeydown={(event) => {
		if (event.key === 'Enter' || event.key === ' ') {
			event.preventDefault();
			onOpen(notification);
		}
	}}
	role="button"
	tabindex="0"
>
	<div class="mt-1 flex-shrink-0">
		{#if !unread}
			<CheckCircle2 class="h-5 w-5 text-base-content/40" />
		{:else}
			<Circle class="h-5 w-5 fill-primary/20 text-primary" />
		{/if}
	</div>

	<div class="min-w-0 flex-1">
		<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
			<p class="truncate text-sm font-semibold text-base-content">
				{notification.type}
			</p>
			<span class="text-xs whitespace-nowrap text-base-content/50">
				{formatDate(notification.created_at)}
			</span>
		</div>

		<p class="mt-1 text-sm text-base-content/80">{getNotificationPreviewText(notification)}</p>
	</div>

	{#if unread}
		<div class="ml-4 flex-shrink-0">
			<button
				class="btn text-primary btn-ghost btn-xs"
				disabled={isMarkingCurrent || markingAllAsRead}
				onclick={(event) => {
					event.stopPropagation();
					onMarkAsRead(notification.id);
				}}
			>
				{isMarkingCurrent ? 'Memproses...' : 'Tandai dibaca'}
			</button>
		</div>
	{/if}
</div>
