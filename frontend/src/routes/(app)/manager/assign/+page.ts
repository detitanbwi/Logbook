import { usersService } from '$lib/api/services/usersService';
import { kpiService } from '$lib/api/services/kpiService';

export const load = async ({ url, depends }: { url: URL; depends: (arg0: string) => void }) => {
	depends('assign:list');
	try {
		const page = Number(url.searchParams.get('page')) || 1;
		const search = url.searchParams.get('search') || undefined;

		const [usersRes, kpisRes, assignRes] = await Promise.all([
			usersService.getAll({ per_page: 100 }), // large limit for dropdowns
			kpiService.getAllMaster({ per_page: 100 }), // large limit for dropdowns
			kpiService.getAssignments({ page, search })
		]);

		const allUsers = Array.isArray(usersRes) ? usersRes : (usersRes as any).data || [];
		const team = allUsers.filter((u: any) => u.role === 'Staff');

		const kpis = Array.isArray(kpisRes) ? kpisRes : (kpisRes as any).data || [];
		const assignments = Array.isArray(assignRes) ? assignRes : (assignRes as any).data || [];
		const assignMeta = (assignRes as any).meta || null;

		// Ambil query param 'user' kalau ada, untuk auto-select
		const selectedUserId = url.searchParams.get('user') || '';

		return { team, kpis, assignments, assignMeta, selectedUserId };
	} catch (error) {
		return { team: [], kpis: [], assignments: [], assignMeta: null, selectedUserId: '' };
	}
};
