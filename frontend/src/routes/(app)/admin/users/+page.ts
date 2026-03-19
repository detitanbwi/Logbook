import { usersService } from '$lib/api/services/usersService';

export const load = async ({ url, depends }: { url: URL; depends: (arg0: string) => void }) => {
	depends('users:list');
	try {
		const params: Record<string, unknown> = {
			page: Number(url.searchParams.get('page')) || 1,
			per_page: 15
		};

		// Add search parameter if present
		const search = url.searchParams.get('search');
		if (search) params.search = search;

		// Add role filter if present
		const role = url.searchParams.get('role');
		if (role) params.role = role;

		// Add sort parameters if present
		const sortBy = url.searchParams.get('sort_by');
		if (sortBy) params.sort_by = sortBy;

		const sortDir = url.searchParams.get('sort_dir');
		if (sortDir) params.sort_dir = sortDir;

		const response = await usersService.getAll(params);
		const users = Array.isArray(response) ? response : (response as any).data || [];
		const meta = (response as any).meta || null;
		return { users, meta };
	} catch (error) {
		return { users: [], meta: null };
	}
};
