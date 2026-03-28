import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('$env/dynamic/public', () => ({ env: { PUBLIC_API_URL: 'http://localhost:8000/api' } }));

import {
	authService,
	staffLogbookService,
	managerLogbookService,
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
			const mockResponse = { token: 'fake-jwt-token', user: { id: '1', name: 'Admin Legacy', nip: '12345' } };
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const credentials = { npp: '12345', password: 'password123' };
			const result = await authService.login(credentials);

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/login'),
				expect.objectContaining({
					method: 'POST',
					body: expect.stringContaining('"npp":"12345"')
				})
			);
			expect(result).toEqual({
				token: 'fake-jwt-token',
				user: {
					id: '1',
					name: 'Admin Legacy',
					nip: '12345',
					nama: 'Admin Legacy',
					npp: '12345',
					manager: null
				}
			});

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

		it('should call updateProfile JSON path with canonical nama and email', async () => {
			const mockResponse = { id: 'user-1', name: 'Updated User', nip: '198001', email: 'updated@example.com' };
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const payload = {
				nama: 'Updated User',
				email: 'updated@example.com'
			};

			const result = await authService.updateProfile(payload as any);

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/auth/profile'),
				expect.objectContaining({
					method: 'PUT',
					body: JSON.stringify(payload)
				})
			);
			expect(result).toEqual({
				id: 'user-1',
				name: 'Updated User',
				nip: '198001',
				email: 'updated@example.com',
				nama: 'Updated User',
				npp: '198001',
				manager: null
			});
		});

		it('should call updateProfile FormData path without forcing JSON content-type', async () => {
			const mockResponse = { id: 'user-1', name: 'Updated User', nip: '198001', foto: '/uploads/foto.jpg' };
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const formData = new FormData();
			formData.append('nama', 'Updated User');
			formData.append('email', 'updated@example.com');

			const result = await authService.updateProfile(formData);

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain('/auth/profile');
			expect(call[1]).toEqual(
				expect.objectContaining({
					method: 'PUT',
					body: formData
				})
			);

			const headers = new Headers((call[1] as any)?.headers);
			expect(headers.has('Content-Type')).toBe(false);
			expect(result).toEqual({
				id: 'user-1',
				name: 'Updated User',
				nip: '198001',
				foto: '/uploads/foto.jpg',
				nama: 'Updated User',
				npp: '198001',
				manager: null
			});
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

		const payload = { tanggal: '2026-03-21', start_kerja: '08:00', lokasi: '-6.2088,106.8456' };
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
		const invalidPayload = { tanggal: 123 } as any; // Invalid type
		await expect(staffLogbookService.startLogbook(invalidPayload)).rejects.toThrow();
	});
	});

	describe('managerLogbookService', () => {
	it('should call canonical review endpoint', async () => {
		(global.fetch as any).mockResolvedValueOnce({
			ok: true,
			status: 200,
			json: async () => ({ success: true })
		});

		await managerLogbookService.reviewLogbook('logbook-1', { decision: 'ACCEPTED', rating: 4, reviewer_comment: 'Good work' });

		expect(global.fetch).toHaveBeenCalledWith(
			expect.stringContaining('/logbooks/logbook-1/review'),
			expect.objectContaining({
				method: 'PUT',
				body: JSON.stringify({ decision: 'ACCEPTED', rating: 4, reviewer_comment: 'Good work' })
			})
		);
	});

	it('should call revert endpoint', async () => {
		(global.fetch as any).mockResolvedValueOnce({
			ok: true,
			status: 200,
			json: async () => ({ success: true })
		});

		await managerLogbookService.revertLogbook('logbook-2', { reason: 'Needs corrections' });

		expect(global.fetch).toHaveBeenCalledWith(
			expect.stringContaining('/logbooks/logbook-2/revert'),
			expect.objectContaining({ method: 'POST' })
		);
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

		it('should call getStaffPerformanceSummary with date query params', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({ date_from: '2026-03-01', date_to: '2026-03-20', items: [] })
			});

			await analyticsService.getStaffPerformanceSummary({
				date_from: '2026-03-01',
				date_to: '2026-03-20'
			});

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain(
				'/summaries/staff-performance?date_from=2026-03-01&date_to=2026-03-20'
			);
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
		});

		it('should call exportReports with report_type and date params and return JSON payload', async () => {
			const mockResponse = {
				message: 'Report generated successfully',
				download_url: 'https://example.com/reports/staff-performance-2026-03.pdf'
			};

			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => mockResponse
			});

			const result = await analyticsService.exportReports({
				report_type: 'staff_performance',
				date_from: '2026-03-01',
				date_to: '2026-03-20'
			});

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain(
				'/reports/export?report_type=staff_performance&date_from=2026-03-01&date_to=2026-03-20'
			);
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
			expect(result).toEqual(mockResponse);
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

		it('should call getNotifications with filter query params', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({}) });

			await notificationService.getNotifications({
				is_read: false,
				type: 'KPI_ASSIGNMENT',
				page: 2,
				per_page: 10
			});

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain(
				'/notifications?is_read=false&type=KPI_ASSIGNMENT&page=2&per_page=10'
			);
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
		});

		it('should call read notification endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 204 });

			await notificationService.read('notif-123');

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/notifications/notif-123/read'),
				expect.objectContaining({ method: 'PUT' })
			);
		});

		it('should call readAll notification endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 204 });

			await notificationService.readAll();

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/notifications/read-all'),
				expect.objectContaining({ method: 'PUT' })
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

		it('should call getAuditLogs with filter and sort query params', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({}) });

			await auditService.getAuditLogs({
				search: 'admin',
				action: 'updated',
				date_from: '2026-03-01',
				date_to: '2026-03-20',
				sort_by: 'performed_at',
				sort_dir: 'desc',
				per_page: 25,
				page: 2
			});

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain(
				'/audit-logs?search=admin&action=updated&date_from=2026-03-01&date_to=2026-03-20&sort_by=performed_at&sort_dir=desc&per_page=25&page=2'
			);
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
		});

		it('should normalize audit user actor from legacy fields', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({
					data: [
						{
							id: 'audit-1',
							table_name: 'users',
							record_id: '1',
							action: 'updated',
							old_data: null,
							new_data: null,
							performed_by: '1',
							performed_at: '2026-03-20T00:00:00Z',
							ip_address: null,
							user_agent: null,
							created_at: '2026-03-20T00:00:00Z',
							updated_at: '2026-03-20T00:00:00Z',
							user: { id: '1', name: 'Legacy Name', nip: '198001', role: 'Admin' }
						}
					],
					meta: {}
				})
			});

			const result = await auditService.getAuditLogs();
			expect(result.data[0]?.user).toEqual({
				id: '1',
				name: 'Legacy Name',
				nip: '198001',
				role: 'Admin',
				nama: 'Legacy Name',
				npp: '198001'
			});
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

		it('should call getSubordinates endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });
			await usersService.getSubordinates('user-1');
			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/users/user-1/subordinates'),
				expect.objectContaining({ method: 'GET' })
			);
		});

		it('should call getAll with role filter query params', async () => {
			(global.fetch as any).mockResolvedValue({
				ok: true,
				status: 200,
				json: async () => ({
					data: [
						{
							id: 'u-1',
							name: 'Legacy User',
							nip: '198001',
							email: 'legacy@example.com',
							role: 'Staff',
							manager_id: null,
							created_at: '2026-03-20T00:00:00Z',
							updated_at: '2026-03-20T00:00:00Z'
						}
					],
					meta: {}
				})
			});

			await usersService.getAll({
				page: 1,
				per_page: 15,
				search: 'admin',
				role: 'Admin',
				sort_by: 'created_at',
				sort_dir: 'desc'
			});

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain(
				'/users?page=1&per_page=15&search=admin&role=Admin&sort_by=created_at&sort_dir=desc'
			);
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
			const result = await usersService.getAll({
				page: 1,
				per_page: 15,
				search: 'admin',
				role: 'Admin',
				sort_by: 'created_at',
				sort_dir: 'desc'
			});
			expect(result.data[0]?.nama).toBe('Legacy User');
			expect(result.data[0]?.npp).toBe('198001');
		});

		it('should call create endpoint with Admin role payload', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 201, json: async () => ({ id: 'admin-1' }) });

			await usersService.create({
				nama: 'Admin Satu',
				email: 'admin1@example.com',
				npp: '198001010001',
				password: 'password123',
				role: 'Admin',
				manager_id: null
			});

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/users'),
				expect.objectContaining({
					method: 'POST',
					body: expect.stringContaining('"role":"Admin"')
				})
			);
		});

		it('should call update endpoint with Admin role payload', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ id: 'admin-1' }) });

			await usersService.update('admin-1', {
				nama: 'Admin Updated',
				email: 'admin-updated@example.com',
				npp: '198001010002',
				role: 'Admin'
			});

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/users/admin-1'),
				expect.objectContaining({
					method: 'PUT',
					body: expect.stringContaining('"role":"Admin"')
				})
			);
		});

		it('should call resetPassword endpoint with new_password payload', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({}) });

			await usersService.resetPassword('admin-1', 'newpassword123');

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/users/admin-1/reset-password'),
				expect.objectContaining({
					method: 'PUT',
					body: JSON.stringify({ new_password: 'newpassword123' })
				})
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

		it('should call getAllMaster with URL query params', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({}) });

			await kpiService.getAllMaster({
				page: 2,
				per_page: 15,
				sort_by: 'created_at',
				sort_dir: 'desc',
				// Compatibility with current backend filter usage from /admin/kpis
				status_aktif: true
			} as any);

			const call = vi.mocked(global.fetch).mock.calls[0];
			expect(call[0] as string).toContain('/kpi/master?page=2&per_page=15&sort_by=created_at&sort_dir=desc&status_aktif=true');
			expect(call[1]).toEqual(expect.objectContaining({ method: 'GET' }));
		});

	it('should call createMaster endpoint', async () => {
		(global.fetch as any).mockResolvedValueOnce({
			ok: true,
			status: 201,
			json: async () => ({ id: 'kpi-1' })
		});

		await kpiService.createMaster({ nama: 'KPI Baru', target_angka: 100, satuan: 'unit', status_aktif: true });

		expect(global.fetch).toHaveBeenCalledWith(
			expect.stringContaining('/kpi/master'),
			expect.objectContaining({
				method: 'POST',
				body: expect.stringContaining('"nama":"KPI Baru"')
			})
		);
	});

		it('should call updateMaster endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 200,
				json: async () => ({ id: 'kpi-1' })
			});

			await kpiService.updateMaster('kpi-1', { nama: 'KPI Updated', status_aktif: false });

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/master/kpi-1'),
				expect.objectContaining({
					method: 'PUT',
					body: expect.stringContaining('"status_aktif":false')
				})
			);
		});

		it('should call deleteMaster endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 204 });

			await kpiService.deleteMaster('kpi-1');

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/master/kpi-1'),
				expect.objectContaining({ method: 'DELETE' })
			);
		});

		it('should call getAssignments endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 200, json: async () => [] });

			await kpiService.getAssignments({ page: 1, per_page: 20 });

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/assignments?page=1&per_page=20'),
				expect.objectContaining({ method: 'GET' })
			);
		});

		it('should call assignKpi endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({
				ok: true,
				status: 201,
				json: async () => ({ id: 'assign-1' })
			});

			await kpiService.assignKpi({ user_id: 'user-1', kpi_id: 'kpi-1' });

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/assignments'),
				expect.objectContaining({
					method: 'POST',
					body: JSON.stringify({ user_id: 'user-1', kpi_id: 'kpi-1' })
				})
			);
		});

		it('should call deleteAssignment endpoint', async () => {
			(global.fetch as any).mockResolvedValueOnce({ ok: true, status: 204 });

			await kpiService.deleteAssignment('assign-1');

			expect(global.fetch).toHaveBeenCalledWith(
				expect.stringContaining('/kpi/assignments/assign-1'),
				expect.objectContaining({ method: 'DELETE' })
			);
		});
	});
});
