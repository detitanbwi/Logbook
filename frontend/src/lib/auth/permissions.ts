import type { UserRole } from '$lib/types';

export type NormalizedRole = 'superadmin' | 'admin' | 'manager' | 'staff' | 'guest';

export interface NavLink {
	label: string;
	href: string;
}

const SUPERADMIN_LINKS: NavLink[] = [
	{ label: 'Dashboard', href: '/superadmin/dashboard' },
	{ label: 'Admin Management', href: '/superadmin/admins' },
	{ label: 'User Management', href: '/admin/users' },
	{ label: 'Master KPI', href: '/admin/kpis' },
	{ label: 'KPI Assignments', href: '/admin/kpi-assignments' },
	{ label: 'Logbooks', href: '/admin/logbooks' },
	{ label: 'Staff Performance', href: '/admin/staff-performance' },
	{ label: 'Reports', href: '/admin/reports' },
	{ label: 'Audit Logs', href: '/superadmin/audit-logs' }
];

const ADMIN_LINKS: NavLink[] = [
	{ label: 'Dashboard', href: '/admin/dashboard' },
	{ label: 'User Management', href: '/admin/users' },
	{ label: 'Master KPI', href: '/admin/kpis' },
	{ label: 'KPI Assignments', href: '/admin/kpi-assignments' },
	{ label: 'Logbooks', href: '/admin/logbooks' },
	{ label: 'Staff Performance', href: '/admin/staff-performance' },
	{ label: 'Reports', href: '/admin/reports' }
];

const MANAGER_LINKS: NavLink[] = [
	{ label: 'Dashboard', href: '/manager/dashboard' },
	{ label: 'Tim Saya', href: '/manager/team' },
	{ label: 'Review Logbook', href: '/manager/reviews' }
];

const STAFF_LINKS: NavLink[] = [
	{ label: 'Dashboard', href: '/staff/dashboard' },
	{ label: 'Mulai Kerja', href: '/staff/logbook' },
	{ label: 'Riwayat', href: '/staff/history' }
];

export function normalizeRole(role: UserRole | string | null | undefined): NormalizedRole {
	const normalized = (role ?? '').toString().trim().toLowerCase();

	if (normalized === 'superadmin') return 'superadmin';
	if (normalized === 'admin') return 'admin';
	if (normalized === 'manager') return 'manager';
	if (normalized === 'staff') return 'staff';

	return 'guest';
}

export function defaultDashboard(role: UserRole | string | null | undefined): string {
	switch (normalizeRole(role)) {
		case 'superadmin':
			return '/superadmin/dashboard';
		case 'admin':
			return '/admin/dashboard';
		case 'manager':
			return '/manager/dashboard';
		case 'staff':
			return '/staff/dashboard';
		default:
			return '/login';
	}
}

export function canAccessPath(role: UserRole | string | null | undefined, path: string): boolean {
	const normalizedRole = normalizeRole(role);

	if (!path.startsWith('/')) return false;
	if (path.startsWith('/profile') || path.startsWith('/notifications')) return normalizedRole !== 'guest';

	if (path.startsWith('/superadmin')) return normalizedRole === 'superadmin';

	if (path === '/admin/audit-logs' || path.startsWith('/admin/audit-logs/')) {
		return false;
	}

	if (path.startsWith('/admin')) {
		return normalizedRole === 'admin' || normalizedRole === 'superadmin';
	}

	if (path.startsWith('/manager')) return normalizedRole === 'manager';
	if (path.startsWith('/staff')) return normalizedRole === 'staff';

	return true;
}

export function getLegacyPathRedirect(
	role: UserRole | string | null | undefined,
	path: string
): string | null {
	const normalizedRole = normalizeRole(role);

	if (path === '/admin/audit-logs' || path.startsWith('/admin/audit-logs/')) {
		if (normalizedRole === 'superadmin') {
			return '/superadmin/audit-logs';
		}

		return defaultDashboard(role);
	}

	return null;
}

export function getNavigationLinks(role: UserRole | string | null | undefined): NavLink[] {
	switch (normalizeRole(role)) {
		case 'superadmin':
			return SUPERADMIN_LINKS;
		case 'admin':
			return ADMIN_LINKS;
		case 'manager':
			return MANAGER_LINKS;
		case 'staff':
			return STAFF_LINKS;
		default:
			return [];
	}
}
