import * as v from 'valibot';

// Re-export Notification type from central types
export type { Notification, NotificationType } from '$lib/types';

export const NotificationSchema = v.object({
	id: v.string(),
	user_id: v.optional(v.string()),
	title: v.optional(v.string()),
	message: v.optional(v.string()),
	type: v.string(),
	reference_id: v.optional(v.string()),
	is_read: v.optional(v.boolean()),
	data: v.optional(v.any()),
	read_at: v.nullable(v.string()),
	created_at: v.string()
});
