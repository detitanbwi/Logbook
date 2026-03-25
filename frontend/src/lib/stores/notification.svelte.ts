import {
	notificationService,
	type NotificationFilters
} from '$lib/api/services/notificationService';
import type { Notification } from '$lib/api/schemas/notification.schema';
import { isNotificationUnread } from '$lib/utils/notification';

function createNotificationStore() {
	let notifications = $state<Notification[]>([]);
	let loading = $state(false);
	let error = $state<string | null>(null);

	async function fetchUnread(limit: number = 5) {
		loading = true;
		try {
			const data = await notificationService.getNotifications({
				unread_only: true,
				per_page: limit
			});
			const allItems = Array.isArray(data) ? data : (data as any).data || [];
			notifications = allItems.filter((notification: Notification) => isNotificationUnread(notification));
			error = null;
		} catch (e: any) {
			error = e.message || 'Failed to fetch notifications';
			console.error('Failed to fetch notifications:', e);
		} finally {
			loading = false;
		}
	}

	async function fetchAll(filters: NotificationFilters = { page: 1, per_page: 15 }) {
		loading = true;
		try {
			const data = await notificationService.getNotifications(filters);
			return data;
		} catch (e: any) {
			error = e.message || 'Failed to fetch notifications';
			console.error('Failed to fetch notifications:', e);
			throw e;
		} finally {
			loading = false;
		}
	}

	async function markAsRead(id: string) {
		try {
			await notificationService.read(id);
			notifications = notifications.filter((n) => n.id !== id);
			error = null;
		} catch (e) {
			error = (e as { message?: string })?.message || 'Failed to mark notification as read';
			console.error('Failed to mark as read', e);
			throw e;
		}
	}

	async function markAllAsRead() {
		try {
			await notificationService.readAll();
			notifications = [];
			error = null;
		} catch (e) {
			error = (e as { message?: string })?.message || 'Failed to mark all notifications as read';
			console.error('Failed to mark all as read', e);
			throw e;
		}
	}

	return {
		get unread() {
			return notifications;
		},
		get unreadCount() {
			return notifications.length;
		},
		get loading() {
			return loading;
		},
		get error() {
			return error;
		},
		fetchUnread,
		fetchAll,
		markAsRead,
		markAllAsRead
	};
}

export const notificationStore = createNotificationStore();
