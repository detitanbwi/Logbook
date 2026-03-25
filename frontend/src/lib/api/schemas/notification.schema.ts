import * as v from 'valibot';

// Re-export Notification type from central types
export type { Notification, NotificationType } from '$lib/types';

export const NotificationSchema = v.object({
	id: v.string(),
	user_id: v.optional(v.string()),
	title: v.optional(v.string()),
	message: v.optional(v.nullable(v.string())),
	preview_message: v.optional(v.nullable(v.string())),
	type: v.string(),
	reference_id: v.optional(v.nullable(v.string())),
	is_read: v.optional(v.boolean()),
	data: v.optional(v.nullable(v.any())),
	target_path: v.optional(v.nullable(v.string())),
	target_params: v.optional(v.nullable(v.record(v.string(), v.any()))),
	read_at: v.nullable(v.string()),
	created_at: v.string()
});
