import { describe, expect, it, vi } from 'vitest';
import {
	getNotificationPreviewText,
	handleNotificationClick,
	resolveNotificationDestination
} from './notification';
import type { Notification } from '$lib/types';

const baseNotification: Notification = {
	id: 'notif-1',
	message: null,
	type: 'LOGBOOK_SUBMITTED',
	is_read: false,
	created_at: '2026-03-19T10:00:00.000Z',
	read_at: null
};

describe('notification utils', () => {
	it('uses preview text precedence: preview_message -> message -> data.message -> fallback', () => {
		expect(
			getNotificationPreviewText({
				...baseNotification,
				preview_message: 'From preview',
				message: 'From message',
				data: { message: 'From data' }
			})
		).toBe('From preview');

		expect(
			getNotificationPreviewText({
				...baseNotification,
				preview_message: '   ',
				message: 'From message',
				data: { message: 'From data' }
			})
		).toBe('From message');

		expect(
			getNotificationPreviewText({
				...baseNotification,
				preview_message: null,
				message: null,
				data: { message: 'From data' }
			})
		).toBe('From data');

		expect(
			getNotificationPreviewText({
				...baseNotification,
				preview_message: null,
				message: null,
				data: { anything: 'else' }
			})
		).toBe('Notifikasi baru');
	});

	it('resolves destination with backend target_path first, then type/reference fallback', () => {
		expect(
			resolveNotificationDestination({
				...baseNotification,
				target_path: '/logbooks/abc',
				target_params: { source: 'notification' }
			})
		).toBe('/logbooks/abc?source=notification');

		expect(
			resolveNotificationDestination({
				...baseNotification,
				target_path: null,
				reference_id: 'logbook-1'
			})
		).toBe('/manager/reviews?logbook_id=logbook-1');

		expect(
			resolveNotificationDestination({
				...baseNotification,
				type: 'KPI_ASSIGNMENT',
				reference_id: 'assign-1',
				target_path: undefined
			})
		).toBe('/staff/logbook?assignment_id=assign-1');

		expect(
			resolveNotificationDestination({
				...baseNotification,
				type: 'UNKNOWN_TYPE',
				target_path: undefined,
				reference_id: undefined
			})
		).toBe('/notifications');
	});

	it('click handler marks unread then navigates', async () => {
		const markAsRead = vi.fn(async () => undefined);
		const navigate = vi.fn(async () => undefined);

		const destination = await handleNotificationClick(
			{
				...baseNotification,
				target_path: '/logbooks/abc'
			},
			{ markAsRead, navigate }
		);

		expect(markAsRead).toHaveBeenCalledWith('notif-1');
		expect(navigate).toHaveBeenCalledWith('/logbooks/abc');
		expect(destination).toBe('/logbooks/abc');
	});

	it('click handler skips markAsRead when already read and still navigates', async () => {
		const markAsRead = vi.fn(async () => undefined);
		const navigate = vi.fn(async () => undefined);

		await handleNotificationClick(
			{
				...baseNotification,
				is_read: true,
				read_at: '2026-03-19T10:10:00.000Z',
				target_path: '/kpi/me'
			},
			{ markAsRead, navigate }
		);

		expect(markAsRead).not.toHaveBeenCalled();
		expect(navigate).toHaveBeenCalledWith('/kpi/me');
	});
});
