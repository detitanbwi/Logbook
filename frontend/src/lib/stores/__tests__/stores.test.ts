import { vi, describe, it, expect, beforeEach } from 'vitest';
import type { Logbook, User } from '$lib/types';

vi.mock('runed', () => {
	return {
		PersistedState: class {
			current: unknown = null;
			constructor(_key: string, initialValue: unknown) {
				this.current = initialValue;
			}
		}
	};
});
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

	describe('AuthStore', () => {
		it('should login successfully, set token in store and client', async () => {
			const mockUser = {
				id: '1',
				nama: 'Admin',
				npp: '12345',
				role: 'Staff'
			} as unknown as User;
			const mockToken = 'new.jwt.token';

			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({ token: mockToken, user: mockUser })
			});

			expect(auth.isLoading).toBe(false);

			const loginPromise = auth.login({ npp: '12345', password: 'password123' });
			expect(auth.isLoading).toBe(true);

			await loginPromise;

			expect(auth.isAuthenticated).toBe(true);
			expect(auth.user.current).toEqual(mockUser);
			expect(auth.token.current).toBe(mockToken);
			expect(auth.isLoading).toBe(false);
			expect(auth.error).toBeNull();

			if (typeof localStorage !== 'undefined') {
				expect(localStorage.getItem('auth-token')).toBe(JSON.stringify(mockToken));
			}
		});

		it('login fails -> sets error state, isLoading becomes false, does not set token', async () => {
			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: false,
				status: 401,
				json: async () => ({ message: 'Invalid credentials' })
			});

			expect(auth.isLoading).toBe(false);

			const loginPromise = auth.login({ npp: 'wrong', password: 'wrong' });
			expect(auth.isLoading).toBe(true);

			await expect(loginPromise).rejects.toThrow('Invalid credentials');

			expect(auth.isAuthenticated).toBe(false);
			expect(auth.user.current).toBeNull();
			expect(auth.token.current).toBeNull();
			expect(auth.isLoading).toBe(false);
			expect(auth.error).toBe('Invalid credentials');
		});

		it('fetchMe() unauthorized -> clears local session without remote logout call', async () => {
			auth.user.current = {
				id: '1',
				nama: 'Test',
				npp: '111',
				role: 'Staff'
			} as unknown as User;
			auth.token.current = 'expired.token';
			api.setToken('expired.token');

			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: false,
				status: 401,
				json: async () => ({ message: 'Unauthorized' })
			});

			await expect(auth.fetchMe()).rejects.toThrow('Unauthorized');

			expect(auth.isAuthenticated).toBe(false);
			expect(auth.user.current).toBeNull();
			expect(auth.token.current).toBeNull();
			expect(auth.isInitialized).toBe(false);

			expect(global.fetch).toHaveBeenCalledTimes(1);
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/me'),
				expect.any(Object)
			);
		});

		it('fetchMe() dedupes concurrent calls to a single /auth/me request', async () => {
			const mockUser = {
				id: '1',
				nama: 'Staff',
				npp: '222',
				role: 'Staff'
			} as unknown as User;
			auth.token.current = 'valid.token';
			api.setToken('valid.token');

			(global.fetch as ReturnType<typeof vi.fn>).mockImplementationOnce(
				() =>
					new Promise((resolve) => {
						setTimeout(() => {
							resolve({
								ok: true,
								status: 200,
								json: async () => ({ user: mockUser })
							});
						}, 5);
					})
			);

			const [first, second] = await Promise.all([auth.fetchMe(), auth.fetchMe()]);

			expect(first).toEqual(mockUser);
			expect(second).toEqual(mockUser);
			expect(auth.user.current).toEqual(mockUser);
			expect(auth.isInitialized).toBe(true);
			expect(global.fetch).toHaveBeenCalledTimes(1);
		});

		it('logout() -> clears user/token and performs remote call once', async () => {
			auth.user.current = { id: '1', nama: 'Test', npp: '333' } as unknown as User;
			auth.token.current = 'existing.token';
			api.setToken('existing.token');

			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({})
			});

			await Promise.all([auth.logout(), auth.logout()]);

			expect(auth.isAuthenticated).toBe(false);
			expect(auth.user.current).toBeNull();
			expect(auth.token.current).toBeNull();

			expect(global.fetch).toHaveBeenCalledTimes(1);
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/logout'),
				expect.any(Object)
			);
		});
	});

	describe('LogbookStore', () => {
		it('fetchLogbooks() sets isLoading to true then false and updates state', async () => {
			const mockLogbooks = [
				{ id: '101', status: 'SUBMITTED' },
				{ id: '102', status: 'ACCEPTED' }
			] as unknown as Logbook[];
			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({ data: mockLogbooks, meta: null })
			});

			expect(logbookStore.isLoading).toBe(false);

			const fetchPromise = logbookStore.fetchLogbooks();
			expect(logbookStore.isLoading).toBe(true);

			await fetchPromise;

			expect(logbookStore.isLoading).toBe(false);
			expect(logbookStore.error).toBeNull();
			expect(logbookStore.logbooks).toEqual(mockLogbooks);
		});

		it('fetchLogbooks() on error sets the error string', async () => {
			(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
				ok: false,
				status: 500,
				json: async () => ({ message: 'Server Error' })
			});

			const fetchPromise = logbookStore.fetchLogbooks();
			expect(logbookStore.isLoading).toBe(true);

			await expect(fetchPromise).rejects.toThrow('Server Error');

			expect(logbookStore.isLoading).toBe(false);
			expect(logbookStore.error).toBe('Server Error');
			expect(logbookStore.logbooks).toEqual([]);
		});

		it('startLogbook() success updates the state', async () => {
			const mockLogbooks = [{ id: '101', status: 'SUBMITTED' }] as unknown as Logbook[];
			const mockStartedResponse = { id: '101', status: 'SUBMITTED' } as unknown as Logbook;

			(global.fetch as ReturnType<typeof vi.fn>)
				.mockResolvedValueOnce({
					ok: true,
					status: 200,
					json: async () => ({ data: mockStartedResponse })
				}) // startLogbook
				.mockResolvedValueOnce({
					ok: true,
					status: 200,
					json: async () => ({ data: mockLogbooks, meta: null })
				}); // fetchLogbooks

			const startPromise = logbookStore.startLogbook({ tanggal: '2026-03-21', start_kerja: '08:00', lokasi: '-6.2088,106.8456' });
			expect(logbookStore.isLoading).toBe(true);

			const result = await startPromise;

			expect(logbookStore.logbooks).toEqual(mockLogbooks);
			expect(logbookStore.isLoading).toBe(false);
			expect(logbookStore.error).toBeNull();
			expect(result).toEqual(mockStartedResponse);
		});

	it('updateKpiProgress() updates the specific KPI in the nested array optimally without resetting everything', async () => {
		logbookStore.currentLogbook = {
			id: '101',
			status: 'SUBMITTED',
			details: [
				{
					id: 'd1',
					kpi_id: 'k1',
					capaian_angka: 0,
					target_angka: 100,
					satuan: 'unit',
					logbook_id: '101',
					kpi_nama: 'KPI 1',
					created_at: '',
					updated_at: ''
				},
				{
					id: 'd2',
					kpi_id: 'k2',
					capaian_angka: 0,
					target_angka: 100,
					satuan: 'unit',
					logbook_id: '101',
					kpi_nama: 'KPI 2',
					created_at: '',
					updated_at: ''
				}
			]
		} as unknown as Logbook;

		const updateResponse = { id: 'd1', kpi_id: 'k1', capaian_angka: 100 };

		(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
			ok: true,
			status: 200,
			json: async () => updateResponse
		});

		const updatePromise = logbookStore.updateKpiProgress('101', 'd1', { capaian_angka: 100 });
		expect(logbookStore.isLoading).toBe(true);

		await updatePromise;

		expect(logbookStore.isLoading).toBe(false);
		expect(logbookStore.error).toBeNull();

		expect(logbookStore.currentLogbook!.details![0].capaian_angka).toBe(100);
		expect(logbookStore.currentLogbook!.details![1].capaian_angka).toBe(0);
	});

		it('submitLogbook() updates the status of currentLogbook', async () => {
			logbookStore.currentLogbook = {
				id: '101',
				status: 'SUBMITTED'
			} as unknown as Logbook;

			const submitResponse = { message: 'Success' };

			(global.fetch as ReturnType<typeof vi.fn>)
				.mockResolvedValueOnce({
					ok: true,
					status: 200,
					json: async () => ({ data: submitResponse })
				}) // submit
				.mockResolvedValueOnce({
					ok: true,
					status: 200,
					json: async () => ({ data: [], meta: null })
				}); // fetchLogbooks

		const submitPromise = logbookStore.submitLogbook('101');
			expect(logbookStore.isLoading).toBe(true);

			await submitPromise;

			expect(logbookStore.isLoading).toBe(false);
			expect(logbookStore.error).toBeNull();

			// Updates current logbook status correctly
			expect(logbookStore.currentLogbook!.status).toBe('SUBMITTED');
		});
	});
});
