const fs = require('fs');

const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';

const content = `import { vi, describe, it, expect, beforeEach } from 'vitest';
vi.mock('$env/dynamic/public', () => ({ env: { PUBLIC_API_URL: 'http://localhost:8000/api' } }));

import { AuthStore } from '$lib/stores/auth.svelte';
import { LogbookStore } from '$lib/stores/logbook.svelte';
import { api } from '$lib/api';

describe('Store State Flow', () => {
	let auth: AuthStore;
	let logbookStore: LogbookStore;

	beforeEach(() => {
		vi.clearAllMocks();
        global.fetch = vi.fn();

		if (typeof localStorage !== 'undefined') {
			localStorage.clear();
		}
		api.clearToken();

		auth = new AuthStore();
		logbookStore = new LogbookStore();
	});

	it('should login, set token in store and client, then fetch logbooks', async () => {
		const mockUser = { id: 1, name: 'Admin', role: 'staff' };
		const mockToken = 'new.jwt.token';
		const mockLogbooks = [{ id: '101', status: 'started' }];

        (global.fetch as any)
            .mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ token: mockToken, user: mockUser }) })
            .mockResolvedValueOnce({ ok: true, status: 200, json: async () => mockLogbooks });

		await auth.login({ nip: '12345', password: 'password123' });

		expect(auth.isAuthenticated).toBe(true);
		expect(auth.user.current).toEqual(mockUser);
		expect(auth.token.current).toBe(mockToken);

		if (typeof localStorage !== 'undefined') {
			expect(localStorage.getItem('auth_token')).toBe(mockToken);
		}

		await logbookStore.fetchLogbooks();

		expect(logbookStore.logbooks).toEqual(mockLogbooks);
	});

	it('should handle logbook start and automatically refresh list', async () => {
		const mockLogbooks = [{ id: '101', status: 'started' }];
		const mockStartedResponse = { id: '101', status: 'started' };

        (global.fetch as any)
            .mockResolvedValueOnce({ ok: true, status: 200, json: async () => mockStartedResponse })
            .mockResolvedValueOnce({ ok: true, status: 200, json: async () => mockLogbooks });

		expect(logbookStore.isLoading).toBe(false);

		const result = await logbookStore.startLogbook({ gps_location_start: '-6.2088,106.8456' });

		expect(logbookStore.logbooks).toEqual(mockLogbooks);
		expect(logbookStore.isLoading).toBe(false);
		expect(logbookStore.error).toBeNull();
		expect(result).toEqual(mockStartedResponse);
	});
});
`;

fs.writeFileSync(path, content, 'utf8');

