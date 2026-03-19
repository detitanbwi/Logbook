import type { PageLoad } from './$types';
import { kpiService } from '$lib/api/services/kpiService';

export const load: PageLoad = async ({ url, depends }) => {
	depends('kpi:list');
	try {
		const params: Record<string, unknown> = {
			page: Number(url.searchParams.get('page')) || 1,
			per_page: 15
		};

		const search = url.searchParams.get('search');
		if (search) params.search = search;

		const statusAktif = url.searchParams.get('status_aktif');
		if (statusAktif) params.status_aktif = statusAktif === 'true';

		const sortBy = url.searchParams.get('sort_by');
		if (sortBy) params.sort_by = sortBy;

		const sortDir = url.searchParams.get('sort_dir');
		if (sortDir) params.sort_dir = sortDir;

		const response = await kpiService.getAllMaster(params);
		const kpis = Array.isArray(response) ? response : (response as any).data || [];
		const meta = (response as any).meta || null;
		return { kpis, meta };
	} catch (error) {
		return { kpis: [], meta: null };
	}
};
