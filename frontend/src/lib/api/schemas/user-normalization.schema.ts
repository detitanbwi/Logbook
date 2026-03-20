import type { AuditLog } from '$lib/types';

type UnknownRecord = Record<string, unknown>;

type LegacyUserIdentity = {
	nama?: unknown;
	name?: unknown;
	npp?: unknown;
	nip?: unknown;
};

function toTrimmedString(value: unknown): string | undefined {
	if (typeof value !== 'string') return undefined;
	const normalized = value.trim();
	return normalized.length > 0 ? normalized : undefined;
}

export function normalizeUserIdentity<T extends LegacyUserIdentity>(
	user: T
	): T {
	const safeUser = (user ?? {}) as T;

	return {
		...safeUser,
		nama: toTrimmedString(safeUser.nama) ?? toTrimmedString(safeUser.name) ?? '',
		npp: toTrimmedString(safeUser.npp) ?? toTrimmedString(safeUser.nip) ?? ''
	} as T;
}

export function normalizeUserWithManager<T extends LegacyUserIdentity & { manager?: unknown }>(
	user: T
): T {
	const normalizedUser = normalizeUserIdentity(user);
	const rawManager = user.manager;

	if (!rawManager || typeof rawManager !== 'object') {
		return {
			...normalizedUser,
			manager: (rawManager as null | undefined) ?? null
		} as T;
	}

	return {
		...normalizedUser,
		manager: normalizeUserIdentity(rawManager as UnknownRecord)
	} as T;
}

export function normalizeAuditLogActors(log: AuditLog): AuditLog {
	if (!log.user) return log;

	return {
		...log,
		user: normalizeUserIdentity(log.user)
	};
}

export function normalizeEntityUser<T extends { user?: unknown }>(entity: T): T {
	if (!entity?.user || typeof entity.user !== 'object') return entity;

	return {
		...entity,
		user: normalizeUserWithManager(entity.user as Record<string, unknown>)
	} as T;
}
