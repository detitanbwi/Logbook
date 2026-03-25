import { auth } from '$lib/stores/auth.svelte';
import { enforceAppRouteGuard } from '$lib/auth/route-guards';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ url }) => {
	await enforceAppRouteGuard({
		isAuthenticated: auth.isAuthenticated,
		isInitialized: auth.isInitialized,
		role: auth.role,
		path: url.pathname,
		ensureInitialized: async () => {
			await auth.fetchMe();
		}
	});

	return {
		role: auth.role,
		user: auth.user.current
	};
};
