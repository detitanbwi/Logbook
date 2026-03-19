import { api } from '../core/client';
import type {
	AnalyticsDashboardResponse,
	TeamLocationsResponse
} from '../schemas/analytics.schema';

export const analyticsService = {
	getAdminDashboard: () => api.get<AnalyticsDashboardResponse>('/dashboard/admin'),
	getManagerDashboard: () => api.get<AnalyticsDashboardResponse>('/dashboard/manager'),
	getTeamLocations: () => api.get<TeamLocationsResponse>('/dashboard/manager/locations'),
	getStaffDashboard: () => api.get<AnalyticsDashboardResponse>('/dashboard/staff'),
	getUserKpiAchievements: (userId: string) => api.get<any>(`/users/${userId}/kpi-achievements`),
	exportReports: () => api.get<Blob>('/reports/export', { headers: { Accept: 'application/pdf' } })
};
