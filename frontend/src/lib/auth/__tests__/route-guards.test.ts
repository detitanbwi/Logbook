import { describe, expect, it, vi } from 'vitest';
import { enforceAppRouteGuard } from '$lib/auth/route-guards';

interface GuardInput {
	isAuthenticated?: boolean;
	isInitialized?: boolean;
	role?: string | null;
	path?: string;
	ensureInitialized?: () => Promise<void>;
}

const expectRedirect = async (
	runGuard: Promise<void>,
	location: string,
	status = 302
) => {
	await expect(runGuard).rejects.toMatchObject({ status, location });
};

const runGuard = (input: GuardInput = {}) => {
	return enforceAppRouteGuard({
		isAuthenticated: input.isAuthenticated ?? true,
		isInitialized: input.isInitialized ?? true,
		role: input.role ?? 'staff',
		path: input.path ?? '/staff/dashboard',
		ensureInitialized:
			input.ensureInitialized ??
			(async () => {
				return;
			})
	});
};

describe('enforceAppRouteGuard', () => {
	it('rejects unauthenticated users with login redirect', async () => {
		const ensureInitialized = vi.fn(async () => {
			return;
		});

		await expectRedirect(
			runGuard({
				isAuthenticated: false,
				ensureInitialized,
				role: null,
				path: '/admin/dashboard'
			}),
			'/login'
		);

		expect(ensureInitialized).not.toHaveBeenCalled();
	});

	it('redirects to login when initialization fails', async () => {
		await expectRedirect(
			runGuard({
				isAuthenticated: true,
				isInitialized: false,
				role: 'admin',
				path: '/admin/dashboard',
				ensureInitialized: async () => {
					throw new Error('network down');
				}
			}),
			'/login'
		);
	});

	it('superadmin-only routes reject admin and staff', async () => {
		await expectRedirect(
			runGuard({
				role: 'admin',
				path: '/superadmin/dashboard'
			}),
			'/admin/dashboard'
		);

		await expectRedirect(
			runGuard({
				role: 'staff',
				path: '/superadmin/audit-logs'
			}),
			'/staff/dashboard'
		);
	});

	it('admin routes allow admin and superadmin but reject staff', async () => {
		await expect(
			runGuard({
				role: 'admin',
				path: '/admin/users'
			})
		).resolves.toBeUndefined();

		await expect(
			runGuard({
				role: 'superadmin',
				path: '/admin/users'
			})
		).resolves.toBeUndefined();

		await expectRedirect(
			runGuard({
				role: 'staff',
				path: '/admin/users'
			}),
			'/staff/dashboard'
		);
	});

	it('shared authenticated routes allow all authenticated roles', async () => {
		const roles = ['superadmin', 'admin', 'manager', 'staff'];

		for (const role of roles) {
			await expect(
				runGuard({
					role,
					path: '/profile'
				})
			).resolves.toBeUndefined();

			await expect(
				runGuard({
					role,
					path: '/notifications'
				})
			).resolves.toBeUndefined();
		}
	});

	it('covers legacy /admin/audit-logs redirect behavior', async () => {
		await expectRedirect(
			runGuard({
				role: 'superadmin',
				path: '/admin/audit-logs'
			}),
			'/superadmin/audit-logs'
		);

		await expectRedirect(
			runGuard({
				role: 'admin',
				path: '/admin/audit-logs'
			}),
			'/admin/dashboard'
		);

		await expectRedirect(
			runGuard({
				role: 'staff',
				path: '/admin/audit-logs'
			}),
			'/staff/dashboard'
		);
	});
});
