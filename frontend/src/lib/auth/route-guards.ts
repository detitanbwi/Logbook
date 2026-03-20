import { redirect } from '@sveltejs/kit';
import type { UserRole } from '$lib/types';
import { canAccessPath, defaultDashboard, getLegacyPathRedirect, normalizeRole } from './permissions';

export interface GuardContext {
	isAuthenticated: boolean;
	isInitialized: boolean;
	role: UserRole | string | null | undefined;
	path: string;
	ensureInitialized: () => Promise<void>;
}

export async function enforceAppRouteGuard(ctx: GuardContext): Promise<void> {
	if (!ctx.isAuthenticated) {
		throw redirect(302, '/login');
	}

	if (!ctx.isInitialized) {
		try {
			await ctx.ensureInitialized();
		} catch {
			throw redirect(302, '/login');
		}
	}

	if (!canAccessPath(ctx.role, ctx.path)) {
		const legacyRedirect = getLegacyPathRedirect(ctx.role, ctx.path);
		if (legacyRedirect) {
			throw redirect(302, legacyRedirect);
		}

		const normalizedRole = normalizeRole(ctx.role);

		if (normalizedRole === 'guest') {
			throw redirect(302, '/login');
		}

		throw redirect(302, defaultDashboard(ctx.role));
	}
}
