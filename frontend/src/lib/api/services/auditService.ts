import { api } from '../core/client';
import type { PaginatedResponse, PaginationParams } from '../core/types';
import type { AuditLog } from '$lib/types';
import { normalizeAuditLogActors } from '../schemas/user-normalization.schema';

export interface AuditLogQueryParams extends PaginationParams {
	action?: 'created' | 'updated' | 'deleted';
	date_from?: string;
	date_to?: string;
	sort_by?: 'performed_at' | 'created_at';
}

export const auditService = {
	async getAuditLogs(params?: AuditLogQueryParams): Promise<PaginatedResponse<AuditLog>> {
		const response = await api.get<PaginatedResponse<AuditLog>>('/audit-logs', { params });
		if (!Array.isArray(response?.data)) {
			return response;
		}

		return {
			...response,
			data: response.data.map((log) => normalizeAuditLogActors(log))
		};
	}
};
