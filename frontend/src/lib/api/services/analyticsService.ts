import { api } from '../core/client';
import type {
	AnalyticsDashboardResponse,
	TeamLocationsResponse
} from '../schemas/analytics.schema';
import { normalizeUserIdentity } from '../schemas/user-normalization.schema';

export interface StaffPerformanceSummaryItem {
	user_id: string | number;
	nama: string;
	npp: string;
	total_logbooks: number;
	accepted_logbooks: number;
	rejected_logbooks: number;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
}

export interface StaffPerformanceSummaryResponse {
	date_from: string;
	date_to: string;
	items: StaffPerformanceSummaryItem[];
}

export interface ExportReportParams {
	report_type: string;
	date_from?: string;
	date_to?: string;
}

export interface ExportReportResponse {
	message: string;
	download_url: string;
}

export const analyticsService = {
	getAdminDashboard: () => api.get<AnalyticsDashboardResponse>('/dashboard/admin'),
	async getManagerDashboard(): Promise<AnalyticsDashboardResponse> {
		const response = await api.get<AnalyticsDashboardResponse>('/dashboard/manager');
		const payload = (response as any)?.data ?? response;
		const subordinates = Array.isArray(payload?.subordinates)
			? payload.subordinates.map((staff: Record<string, unknown>) => normalizeUserIdentity(staff))
			: payload?.subordinates;

		if ((response as any)?.data) {
			return {
				...(response as any),
				data: {
					...(response as any).data,
					subordinates
				}
			} as AnalyticsDashboardResponse;
		}

		return {
			...(response as any),
			subordinates
		} as AnalyticsDashboardResponse;
	},
	getTeamLocations: () => api.get<TeamLocationsResponse>('/dashboard/manager/locations'),
	getStaffDashboard: () => api.get<AnalyticsDashboardResponse>('/dashboard/staff'),
	async getStaffPerformanceSummary(params?: {
		date_from?: string;
		date_to?: string;
	}): Promise<StaffPerformanceSummaryResponse> {
		const response = await api.get<StaffPerformanceSummaryResponse>('/summaries/staff-performance', {
			params
		});

		return {
			...response,
			items: response.items.map((item) => normalizeUserIdentity(item))
		};
	},
	getUserKpiAchievements: (userId: string) => api.get<any>(`/users/${userId}/kpi-achievements`),
	exportReports: (params: ExportReportParams) =>
		api.get<ExportReportResponse>('/reports/export', { params })
};
