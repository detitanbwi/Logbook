import { api } from '../core/client';
import type {
	DailyStaffSummary,
	DailyKpiSummary,
	StaffPerformanceSummaryResponse,
	PeriodSummary,
	KpiPeriodSummary
} from '../../types';
import type { PaginatedResponse } from '../core/types';
import { normalizeUserIdentity } from '../schemas/user-normalization.schema';

export interface SummaryDateParams {
	date?: string;
	date_from?: string;
	date_to?: string;
	tanggal?: string;
	per_page?: number;
	page?: number;
	user_id?: string;
}

export const summaryService = {
	async daily(params?: SummaryDateParams): Promise<PaginatedResponse<DailyStaffSummary>> {
		return api.get('/summaries/daily', { params: params as Record<string, unknown> });
	},

	async dailyByUser(userId: string, params?: SummaryDateParams): Promise<PaginatedResponse<DailyStaffSummary>> {
		return api.get(`/summaries/daily/${userId}`, { params: params as Record<string, unknown> });
	},

	async period(params?: SummaryDateParams): Promise<PeriodSummary> {
		return api.get('/summaries/period', { params: params as Record<string, unknown> });
	},

	async kpiDaily(params?: SummaryDateParams): Promise<PaginatedResponse<DailyKpiSummary>> {
		return api.get('/summaries/kpi/daily', { params: params as Record<string, unknown> });
	},

	async kpiPeriod(params?: SummaryDateParams): Promise<KpiPeriodSummary> {
		return api.get('/summaries/kpi/period', { params: params as Record<string, unknown> });
	},

	async teamDaily(params?: SummaryDateParams): Promise<PaginatedResponse<DailyStaffSummary>> {
		return api.get('/summaries/team/daily', { params: params as Record<string, unknown> });
	},

	async staffPerformance(params?: {
		date_from?: string;
		date_to?: string;
	}): Promise<StaffPerformanceSummaryResponse> {
		const response = await api.get<StaffPerformanceSummaryResponse>('/summaries/staff-performance', {
			params: params as Record<string, unknown>
		});

		return {
			...response,
			items: response.items.map((item) => normalizeUserIdentity(item))
		};
	}
};
