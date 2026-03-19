import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('$env/dynamic/public', () => ({ env: { PUBLIC_API_URL: 'http://localhost:8000/api' } }));

import {
	authService,
	staffLogbookService,
	api,
	analyticsService,
	notificationService,
	auditService,
	usersService,
	kpiService
} from '../index';

describe('API Services Integration', () => {
	beforeEach(() => {
		// Mock global fetch
		global.fetch = vi.fn();
		api.clearToken();
	});

	describe('authService', () => {
		it('should call login endpoint and set token on success', async () => {
			const mockResponse = { token: 'fake-jwt-token', user: { id: '1', nama: 'Admin' } };
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const credentials = { nip: '12345', password: 'password123' };
			const result = await authService.login(credentials);

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/login'),
				expect.objectContaining({
					method: 'POST',
					body: JSON.stringify(credentials)
				})
			);
			expect(result).toEqual(mockResponse);

			// Test if token is injected in next request
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({})
			});
			await authService.getMe();

			const getMeCall = vi.mocked(global.fetch).mock.calls[1];
			const headers = new Headers(getMeCall[1]?.headers as any);
			expect(headers.get('Authorization')).toBe('Bearer fake-jwt-token');
		});

		it('should call logout and clear token', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 204 // No content
			});

			await authService.logout();

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/logout'),
				expect.objectContaining({ method: 'POST' })
			);

			// Verify token is cleared
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({})
			});
			await authService.getMe();
			const getMeCall = vi.mocked(global.fetch).mock.calls[1];
			const headers = new Headers(getMeCall[1]?.headers as any);
			expect(headers.has('Authorization')).toBe(false);
		});
	});

	describe('staffLogbookService', () => {
		it('should call getLogbooks endpoint', async () => {
			const mockResponse = [{ id: '1' }, { id: '2' }];
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const result = await staffLogbookService.getLogbooks();

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/logbooks'),
				expect.objectContaining({ method: 'GET' })
			);
			expect(result).toEqual(mockResponse);
		});

		it('should parse and submit startLogbook correctly', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({ success: true })
			});

			const payload = { gps_location_start: '-6.2088,106.8456' };
			await staffLogbookService.startLogbook(payload);

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/logbooks/start'),
				expect.objectContaining({
					method: 'POST',
					body: JSON.stringify(payload)
				})
			);
		});

		it('should throw validation error if startLogbook payload is invalid', async () => {
			const invalidPayload = { gps_location_start: 123 } as any; // Invalid type
			await expect(staffLogbookService.startLogbook(invalidPayload)).rejects.toThrow();
		});
	});

	describe('analyticsService', () => {
		it('should call getAdminDashboard', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({})
			});
			await analyticsService.getAdminDashboard();
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/dashboard/admin'),
				expect.objectContaining({ method: 'GET' })
			);
		});
	});

	describe('notificationService', () => {
		it('should call getNotifications', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });
			await notificationService.getNotifications();
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/notifications'),
				expect.objectContaining({ method: 'GET' })
			);
		});
	});

	describe('auditService', () => {
		it('should call getAuditLogs', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });
			await auditService.getAuditLogs();
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/audit-logs'),
				expect.objectContaining({ method: 'GET' })
			);
		});
	});

	describe('usersService', () => {
		it('should call getAll', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });
			await usersService.getAll();
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/users'),
				expect.objectContaining({ method: 'GET' })
			);
		});
	});

	describe('kpiService', () => {
		it('should call getAllMaster', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });
			await kpiService.getAllMaster();
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/master'),
				expect.objectContaining({ method: 'GET' })
			);
		});
	});
});
