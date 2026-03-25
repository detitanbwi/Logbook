import { redirect } from '@sveltejs/kit';
import { auth } from '$lib/stores/auth.svelte';
import type { PageLoad } from './$types';

export const load: PageLoad = async ({ parent }) => {
	await parent();

	const role = auth.role?.toLowerCase();

	if (role === 'admin') throw redirect(302, '/admin/dashboard');
	if (role === 'manager') throw redirect(302, '/manager/dashboard');
	if (role === 'staff') throw redirect(302, '/staff/dashboard');

	if (auth.isAuthenticated) {
		throw redirect(302, '/staff/dashboard');
	}

	throw redirect(302, '/login');
};
