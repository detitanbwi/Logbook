import type { Notification } from '$lib/types';

const DEFAULT_NOTIFICATION_PREVIEW = 'Notifikasi baru';

function toNonEmptyString(value: unknown): string | null {
	if (typeof value !== 'string') {
		return null;
	}

	const trimmed = value.trim();
	return trimmed.length > 0 ? trimmed : null;
}

function toRecord(value: unknown): Record<string, unknown> | null {
	if (value && typeof value === 'object' && !Array.isArray(value)) {
		return value as Record<string, unknown>;
	}

	return null;
}

function buildUrlWithParams(path: string, params?: Record<string, unknown> | null): string {
	if (!params || Object.keys(params).length === 0) {
		return path;
	}

	const queryParams = new URLSearchParams();

	for (const [key, value] of Object.entries(params)) {
		if (value === undefined || value === null) {
			continue;
		}
		queryParams.set(key, String(value));
	}

	const query = queryParams.toString();
	if (!query) {
		return path;
	}

	return `${path}${path.includes('?') ? '&' : '?'}${query}`;
}

export function isNotificationUnread(notification: Notification): boolean {
	if (typeof notification.is_read === 'boolean') {
		return !notification.is_read;
	}

	return true;
}

export function getNotificationPreviewText(notification: Notification): string {
	const data = toRecord(notification.data);
	const previewText =
		toNonEmptyString(notification.preview_message) ??
		toNonEmptyString(notification.message) ??
		toNonEmptyString(data?.message);

	return previewText ?? DEFAULT_NOTIFICATION_PREVIEW;
}

export function resolveNotificationDestination(notification: Notification): string {
	const targetPath = toNonEmptyString(notification.target_path);
	const targetParams = toRecord(notification.target_params);

	if (targetPath) {
		return buildUrlWithParams(targetPath, targetParams);
	}

	const referenceId = toNonEmptyString(notification.reference_id);

	if (
		notification.type === 'LOGBOOK_SUBMITTED' ||
		notification.type === 'LOGBOOK_REVERTED' ||
		notification.type === 'LOGBOOK_ACCEPTED' ||
		notification.type === 'LOGBOOK_REJECTED'
	) {
		if (notification.type === 'LOGBOOK_SUBMITTED') {
			return referenceId
				? `/manager/reviews?logbook_id=${encodeURIComponent(referenceId)}`
				: '/manager/reviews';
		}

		return referenceId
			? `/staff/history?logbook_id=${encodeURIComponent(referenceId)}`
			: '/staff/history';
	}

	if (notification.type === 'KPI_ASSIGNMENT') {
		return referenceId
			? `/staff/logbook?assignment_id=${encodeURIComponent(referenceId)}`
			: '/staff/logbook';
	}

	return '/notifications';
}

export async function handleNotificationClick(
	notification: Notification,
	deps: {
		markAsRead: (id: string) => Promise<unknown>;
		navigate: (to: string) => Promise<unknown> | void;
	}
): Promise<string> {
	if (isNotificationUnread(notification)) {
		await deps.markAsRead(notification.id);
	}

	const destination = resolveNotificationDestination(notification);
	await deps.navigate(destination);

	return destination;
}
