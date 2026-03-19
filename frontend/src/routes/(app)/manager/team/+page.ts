// This file provides initial empty state - actual data is loaded client-side
// to enable skeleton loading during navigation

export const load = async () => {
	return {
		team: [],
		meta: null,
		initialLoad: true
	};
};
