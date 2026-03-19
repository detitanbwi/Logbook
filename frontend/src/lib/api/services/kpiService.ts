import { api } from '../core/client';
import type { MasterKpiCreateDto, KpiAssignmentDto } from '../schemas/kpi.schema';
import type { PaginatedResponse, PaginationParams } from '../core/types';
import type { KpiMaster } from '../../types';

export interface KpiFilters extends PaginationParams {
	status?: string;
}

export const kpiService = {
	// Master KPI CRUD
	getAllMaster: (params?: KpiFilters) =>
		api.get<PaginatedResponse<KpiMaster>>('/kpi/master', { params }),
	getMasterById: (id: string) => api.get<any>(`/kpi/master/${id}`),
	createMaster: (data: MasterKpiCreateDto) => api.post<any>('/kpi/master', data),
	updateMaster: (id: string, data: Partial<MasterKpiCreateDto>) =>
		api.put<any>(`/kpi/master/${id}`, data),
	deleteMaster: (id: string) => api.delete<void>(`/kpi/master/${id}`),

	// KPI Assignments
	getAssignments: (params?: PaginationParams) =>
		api.get<PaginatedResponse<any>>('/kpi/assignments', { params }),
	assignKpi: (data: KpiAssignmentDto) => api.post<any>('/kpi/assignments', data),
	deleteAssignment: (id: string) => api.delete<void>(`/kpi/assignments/${id}`),

	// Staff / Me
	getMyKpis: () => api.get<any[]>('/kpi/me')
};
