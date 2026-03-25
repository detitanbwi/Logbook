// This file provides initial empty state - actual data is loaded client-side
// to enable skeleton loading during navigation

export const load = async ({ url }: { url: URL }) => {
	const selectedUserId = url.searchParams.get('user') || '';
	return {
		team: [],
		kpis: [],
		assignments: [],
		assignMeta: null,
		selectedUserId,
		initialLoad: true
	};
};
