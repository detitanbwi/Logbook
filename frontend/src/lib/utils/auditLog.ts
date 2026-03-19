type UnknownRecord = Record<string, unknown>;

export interface NormalizedAuditLog {
	id: string;
	event: string;
	auditable_type: string;
	auditable_id: string;
	old_values: Record<string, unknown>;
	new_values: Record<string, unknown>;
	created_at: string;
	ip_address: string | null;
	user_agent: string | null;
	user?: {
		id?: string;
		name?: string;
		email?: string;
		nip?: string;
		role?: string;
	};
}

function toRecord(value: unknown): Record<string, unknown> {
	// Handle JSON strings - backend sends values as JSON strings
	if (typeof value === 'string' && value.trim().length > 0) {
		try {
			const parsed = JSON.parse(value);
			if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
				return parsed as Record<string, unknown>;
			}
		} catch {
			// If JSON parse fails, return empty object
			return {};
		}
	}

	// Handle already parsed objects
	if (value && typeof value === 'object' && !Array.isArray(value)) {
		return value as Record<string, unknown>;
	}

	return {};
}

function toString(value: unknown, fallback = ''): string {
	return typeof value === 'string' && value.trim().length > 0 ? value : fallback;
}

function toNullableString(value: unknown): string | null {
	return typeof value === 'string' && value.trim().length > 0 ? value : null;
}

export function normalizeAuditLog(input: unknown): NormalizedAuditLog {
	const raw = (input ?? {}) as UnknownRecord;
	const rawUser = raw.user as UnknownRecord | undefined;

	const createdAt =
		toString(raw.created_at) || toString(raw.performed_at) || toString(raw.updated_at) || '';

	return {
		id: toString(raw.id, ''),
		event: toString(raw.event) || toString(raw.action, '-'),
		auditable_type: toString(raw.auditable_type) || toString(raw.table_name, '-'),
		auditable_id: toString(raw.auditable_id) || toString(raw.record_id, '-'),
		old_values: toRecord(raw.old_values ?? raw.old_data),
		new_values: toRecord(raw.new_values ?? raw.new_data),
		created_at: createdAt,
		ip_address: toNullableString(raw.ip_address),
		user_agent: toNullableString(raw.user_agent),
		user: rawUser
			? {
					id: toString(rawUser.id),
					name: toString(rawUser.name),
					email: toString(rawUser.email),
					nip: toString(rawUser.nip),
					role: toString(rawUser.role)
				}
			: undefined
	};
}
