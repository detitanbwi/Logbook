import { redirect } from '@sveltejs/kit';
import { auth } from '$lib/stores/auth.svelte';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ url }) => {
	if (!auth.isAuthenticated) {
		throw redirect(302, '/login');
	}

	if (!auth.isInitialized) {
		try {
			await auth.fetchMe();
		} catch (error) {
			// If fetchMe fails (e.g., token is invalid/expired), redirect to login
			throw redirect(302, '/login');
		}
	}

	const role = auth.role?.toLowerCase();
	const path = url.pathname;

	// Simple route guarding - redirect unauthorized users to their proper dashboard
	if (path.startsWith('/admin') && role !== 'admin') {
		if (role === 'manager') throw redirect(302, '/manager/dashboard');
		if (role === 'staff') throw redirect(302, '/staff/dashboard');
		throw redirect(302, '/login');
	}
	if (path.startsWith('/manager') && role !== 'manager') {
		if (role === 'admin') throw redirect(302, '/admin/dashboard');
		if (role === 'staff') throw redirect(302, '/staff/dashboard');
		throw redirect(302, '/login');
	}
	if (path.startsWith('/staff') && role !== 'staff') {
		if (role === 'admin') throw redirect(302, '/admin/dashboard');
		if (role === 'manager') throw redirect(302, '/manager/dashboard');
		throw redirect(302, '/login');
	}

	return {
		role: auth.role,
		user: auth.user.current
	};
};
