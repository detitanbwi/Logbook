import { api } from '../core/client';
import type { MasterKpiCreateDto, KpiAssignmentDto } from '../schemas/kpi.schema';
import type { BaseResponse, PaginatedResponse, PaginationParams } from '../core/types';
import type { KpiMaster } from '../../types';

export interface KpiFilters extends PaginationParams {
	status?: string;
}

export const kpiService = {
	// Master KPI CRUD
	getAllMaster: (params?: KpiFilters) =>
		api.get<PaginatedResponse<KpiMaster>>('/kpi/master', { params }),
	async getMasterById(id: string): Promise<KpiMaster> {
		const response = await api.get<BaseResponse<KpiMaster>>(`/kpi/master/${id}`);
		return response.data;
	},
	async createMaster(data: MasterKpiCreateDto): Promise<KpiMaster> {
		const response = await api.post<BaseResponse<KpiMaster>>('/kpi/master', data);
		return response.data;
	},
	async updateMaster(id: string, data: Partial<MasterKpiCreateDto>): Promise<KpiMaster> {
		const response = await api.put<BaseResponse<KpiMaster>>(`/kpi/master/${id}`, data);
		return response.data;
	},
	deleteMaster: (id: string) => api.delete<void>(`/kpi/master/${id}`),

	// KPI Assignments
	getAssignments: (params?: PaginationParams) =>
		api.get<PaginatedResponse<any>>('/kpi/assignments', { params }),
	assignKpi: (data: KpiAssignmentDto) => api.post<any>('/kpi/assignments', data),
	deleteAssignment: (id: string) => api.delete<void>(`/kpi/assignments/${id}`),

	// Staff / Me
	getMyKpis: () => api.get<any[]>('/kpi/me')
};
