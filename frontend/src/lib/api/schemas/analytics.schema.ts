import * as v from 'valibot';

export const AnalyticsDashboardSchema = v.object({
	total_active_users: v.optional(v.number()),
	total_logbooks_this_month: v.optional(v.number()),
	total_logbooks_last_month: v.optional(v.number()),
	pending_logbooks_count: v.optional(v.number()),
	logbook_trend: v.optional(v.number()),
	logbooks_by_day: v.optional(v.array(v.object({ date: v.string(), count: v.number() }))),
	logbooks_by_status: v.optional(v.array(v.object({ status: v.string(), count: v.number() }))),
	users_by_role: v.optional(v.array(v.object({ role: v.string(), count: v.number() }))),
	personal_kpi_completion_rate: v.optional(v.number()),
	missed_logbooks_count: v.optional(v.number()),
	total_users: v.optional(v.number()),
	total_logbooks: v.optional(v.number()),
	active_kpis: v.optional(v.number()),
	team_size: v.optional(v.number()),
	pending_reviews: v.optional(v.number()),
	team_completion_rate: v.optional(v.number()),
	average_rating: v.optional(v.number()),
	kpi_completion_rate: v.optional(v.number()),
	kpi_achievements: v.optional(v.array(v.any())),
	subordinates: v.optional(v.array(v.any()))
});

export type AnalyticsDashboardResponse = v.InferOutput<typeof AnalyticsDashboardSchema>;

export const TeamLocationSchema = v.object({
	lat: v.number(),
	lng: v.number(),
	title: v.string(),
	status: v.string()
});

export type TeamLocation = v.InferOutput<typeof TeamLocationSchema>;

export const TeamLocationsResponseSchema = v.object({
	data: v.array(TeamLocationSchema)
});

export type TeamLocationsResponse = v.InferOutput<typeof TeamLocationsResponseSchema>;
