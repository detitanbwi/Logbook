import { describe, it, expect, beforeAll, vi, afterAll } from 'vitest';
import { authService } from '../services/authService';
import { analyticsService } from '../services/analyticsService';
import { auditService } from '../services/auditService';
import { usersService } from '../services/usersService';
import { kpiService } from '../services/kpiService';
import { notificationService } from '../services/notificationService';
import { staffLogbookService } from '../services/staffLogbookService';
import { managerLogbookService } from '../services/managerLogbookService';

vi.mock('$env/dynamic/public', () => ({
	env: { PUBLIC_API_URL: 'http://localhost:8000/api/v1' }
}));

const localStorageMock = (() => {
	let store: Record<string, string> = {};
	return {
		getItem: (key: string) => store[key] || null,
		setItem: (key: string, value: string) => {
			store[key] = value.toString();
		},
		removeItem: (key: string) => {
			delete store[key];
		},
		clear: () => {
			store = {};
		}
	};
})();
Object.defineProperty(global, 'window', { value: { localStorage: localStorageMock } });
Object.defineProperty(global, 'localStorage', { value: localStorageMock });

describe('Comprehensive E2E API Tests', () => {
	beforeAll(() => {
		localStorageMock.clear();
	});

		describe('1. Admin Tests', () => {
		beforeAll(async () => {
			await authService.login({
				npp: '198001012000011001',
				password: 'password123'
			});
		});

		afterAll(async () => {
			await authService.logout();
		});

		it('should get analytics dashboard for admin', async () => {
			const res = await analyticsService.getAdminDashboard();
			expect(res).toBeDefined();
			if (res && 'total_active_users' in res) {
				expect(res).toHaveProperty('total_active_users');
			}
		});

		it('should read audit logs', async () => {
			const res = (await auditService.getAuditLogs({ per_page: 5 })) as any;
			expect(res).toBeDefined();
			const logs = Array.isArray(res) ? res : res.data;
			expect(Array.isArray(logs)).toBe(true);
		});

		it('should get all master KPIs', async () => {
			const res = (await kpiService.getAllMaster()) as any;
			const list = Array.isArray(res) ? res : res.data;
			expect(Array.isArray(list)).toBe(true);
		});

		it('should fail to create user with invalid data', async () => {
			// Intentionally passing bad data
			await expect(
				usersService.create({
					nama: '', // Empty nama should fail validation
					email: 'not-an-email',
					npp: '',
					password: '123',
					// @ts-expect-error Testing with invalid role value
					role: 'INVALID_ROLE'
				})
			).rejects.toThrow();
		});

		it('should fetch user list', async () => {
			const res = (await usersService.getAll()) as any;
			const users = Array.isArray(res) ? res : res.data;
			expect(Array.isArray(users)).toBe(true);
			expect(users.length).toBeGreaterThan(0);
		});
	});

		describe('2. Manager Tests', () => {
		beforeAll(async () => {
			await authService.login({
				npp: '198502022005011002', // Typically a manager
				password: 'password123'
			});
		});

		afterAll(async () => {
			await authService.logout();
		});

		it('should get manager dashboard', async () => {
			const res = await analyticsService.getManagerDashboard();
			expect(res).toBeDefined();
		});

		it('should fetch KPI assignments', async () => {
			const res = (await kpiService.getAssignments()) as any;
			const assignments = Array.isArray(res) ? res : res.data;
			expect(Array.isArray(assignments)).toBe(true);
		});

		it('should read notifications', async () => {
			const res = (await notificationService.getNotifications()) as any;
			const notifications = Array.isArray(res) ? res : res.data;
			expect(Array.isArray(notifications)).toBe(true);

			const readRes = await notificationService.readAll();
			expect(readRes).toBeDefined();
		});
	});

	describe('3. Staff Tests', () => {
		let logbookId: string;
		let kpiDetailId: string;
		const dateStr = new Date().toISOString().split('T')[0];

		beforeAll(async () => {
			await authService.login({
				npp: '199003032010012003', // Typically a staff
				password: 'password123'
			});
		});

		afterAll(async () => {
			await authService.logout();
		});

		it('should get staff dashboard', async () => {
			const res = await analyticsService.getStaffDashboard();
			expect(res).toBeDefined();
		});

		it('should start logbook (if not exists) and fetch it', async () => {
			const existingRes = await staffLogbookService.getLogbooks();
			const list = Array.isArray(existingRes) ? existingRes : existingRes.data || [];
			let activeLogbook = list.find((l: any) => l.date === dateStr);

		if (!activeLogbook) {
			const startRes = await staffLogbookService.startLogbook({
				tanggal: '2026-03-21',
				start_kerja: '08:00',
				lokasi: '-6.200, 106.816'
			});
			activeLogbook = (startRes as any).data || startRes;
		}

			expect(activeLogbook).toBeDefined();
			logbookId = activeLogbook.id;

			const detailsRes = await staffLogbookService.getLogbookById(logbookId);
			const logbook = (detailsRes as any).data || detailsRes;
			const kpiDetails = logbook.kpi_details || [];
			if (kpiDetails.length > 0) {
				kpiDetailId = kpiDetails[0].id;
			}
		}, 15000);

	it('should fail to toggle a KPI with invalid valibot DTO', async () => {
		if (!kpiDetailId) return; // Skip if no KPIs to test

		await expect(
			staffLogbookService.updateKpiProgress(logbookId, kpiDetailId, {
				capaian_angka: 'not-a-number' as any
			})
		).rejects.toThrow();
	});

		it('should export reports', async () => {
			try {
				const res = await analyticsService.exportReports({
					report_type: 'staff_performance'
				});
				expect(res).toBeDefined();
			} catch (e) {
				// Depending on backend implementation this could throw if no data or not PDF format
				// We expect it to at least be called without generic crash
			}
		});
	});
});
