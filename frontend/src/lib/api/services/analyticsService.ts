import { api } from '../core/client';
import type { AnalyticsDashboardResponse, TeamLocationsResponse } from '../schemas/analytics.schema';
import type { StaffPerformanceSummaryResponse } from '../../types';
import { normalizeUserIdentity } from '../schemas/user-normalization.schema';

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
	async getAdminDashboard(): Promise<AnalyticsDashboardResponse> {
		const response = await api.get<{ data: AnalyticsDashboardResponse }>('/dashboard/admin');
		return (response as any)?.data ?? response;
	},
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
	async getStaffDashboard(): Promise<AnalyticsDashboardResponse> {
		const response = await api.get<{ data: AnalyticsDashboardResponse }>('/dashboard/staff');
		return (response as any)?.data ?? response;
	},
	async getStaffPerformanceSummary(params?: {
		date_from?: string;
		date_to?: string;
	}): Promise<StaffPerformanceSummaryResponse> {
		const response = await api.get<StaffPerformanceSummaryResponse>('/summaries/staff-performance', {
			params
		});

		const normalizeAverageRating = (value: unknown): number | null => {
			if (value === null || value === undefined || value === '') return null;
			const numeric = typeof value === 'number' ? value : Number(value);
			return Number.isFinite(numeric) ? numeric : null;
		};

		return {
			...response,
			items: response.items.map((item) => ({
				...normalizeUserIdentity(item),
				average_rating: normalizeAverageRating(item.average_rating)
			}))
		};
	},
	getUserKpiAchievements: (userId: string) => api.get<any>(`/users/${userId}/kpi-achievements`),
	exportReports: (params: ExportReportParams) =>
		api.get<ExportReportResponse>('/reports/export', { params })
};
