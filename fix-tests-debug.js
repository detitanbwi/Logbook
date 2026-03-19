const fs = require('fs');

const content = `import { vi, describe, it, expect, beforeEach } from 'vitest';
vi.mock('$env/dynamic/public', () => ({ env: { PUBLIC_API_URL: 'http://localhost:8000/api' } }));

import { AuthStore } from '../auth.svelte';
import { LogbookStore } from '../logbook.svelte';
import { authService, staffLogbookService, api } from '../../api';

describe('Store State Flow', () => {
	let auth: AuthStore;
	let logbookStore: LogbookStore;

	beforeEach(() => {
		vi.clearAllMocks();

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

		vi.spyOn(authService, 'login').mockResolvedValueOnce({
			token: mockToken,
			user: mockUser
		});
		vi.spyOn(staffLogbookService, 'getLogbooks').mockResolvedValueOnce(mockLogbooks);

		await auth.login({ nip: '12345', password: 'password123' });

        console.log('auth.isAuthenticated:', auth.isAuthenticated);
        console.log('auth.token.current:', auth.token.current);

		expect(auth.isAuthenticated).toBe(true);
		expect(auth.user.current).toEqual(mockUser);
		expect(auth.token.current).toBe(mockToken);
	});
});
`;
fs.writeFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', content, 'utf8');
