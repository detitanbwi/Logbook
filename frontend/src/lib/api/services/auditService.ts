import { api } from '../core/client';
import type { PaginatedResponse, PaginationParams } from '../core/types';
import type { AuditLog } from '$lib/types';

export const auditService = {
	getAuditLogs: (params?: PaginationParams) =>
		api.get<PaginatedResponse<AuditLog>>('/audit-logs', { params })
};
