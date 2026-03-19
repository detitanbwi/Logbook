import { usersService } from '$lib/api/services/usersService';

export const load = async ({ url, depends }: { url: URL; depends: (arg0: string) => void }) => {
	depends('team:list');
	try {
		const page = Number(url.searchParams.get('page')) || 1;
		const search = url.searchParams.get('search') || undefined;
		const sort_by = url.searchParams.get('sort_by') || undefined;
		const sort_dir = (url.searchParams.get('sort_dir') as 'asc' | 'desc') || undefined;

		// Asumsi backend bisa filter query param
		const response = await usersService.getAll({
			page,
			role: 'Staff',
			search,
			sort_by,
			sort_dir
		});
		const team = Array.isArray(response) ? response : (response as any).data || [];
		const meta = (response as any).meta || null;

		// If backend didn't filter, we'd need to handle it, but pagination makes front-end filtering incorrect.
		// For now assume backend supports ?role=Staff
		return { team, meta };
	} catch (error) {
		return { team: [], meta: null };
	}
};
