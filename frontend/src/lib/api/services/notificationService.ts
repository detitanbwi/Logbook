import { api } from '../core/client';
import type { Notification } from '../schemas/notification.schema';
import type { PaginatedResponse, PaginationParams } from '../core/types';

export interface NotificationFilters extends PaginationParams {
	unread_only?: boolean;
	is_read?: boolean;
	type?: string;
}

export const notificationService = {
	getNotifications: (params?: NotificationFilters) =>
		api.get<PaginatedResponse<Notification>>('/notifications', { params }),
	readAll: () => api.put<void>('/notifications/read-all'),
	read: (id: string | number) => api.put<void>(`/notifications/${id}/read`)
};
