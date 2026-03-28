import { describe, it, expect, beforeAll, vi, afterAll } from 'vitest';
import { authService } from '../services/authService';
import { usersService } from '../services/usersService';
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

describe('E2E API Tests', () => {
	beforeAll(() => {
		localStorageMock.clear();
	});

		describe('Admin Flow', () => {
		it('should login as admin, fetch users and logout', async () => {
			// Login
			const loginRes = await authService.login({
				npp: '198001012000011001',
				password: 'password123'
			});
			const token = loginRes.token || (loginRes as any).access_token;
			expect(token).toBeDefined();

			// Fetch users
			const usersRes = await usersService.getAll();
			const usersData = Array.isArray(usersRes) ? usersRes : (usersRes as any).data;
			expect(Array.isArray(usersData)).toBe(true);
			expect(usersData.length).toBeGreaterThan(0);

			// Logout
			await authService.logout();
			expect(localStorage.getItem('auth-token')).toBeNull();
		});
	});

	describe('Staff and Manager Flow', () => {
		let logbookId: string;
		let kpiDetailId: string;
		const dateStr = new Date().toISOString().split('T')[0];

		it('Staff Flow: Login, start logbook, toggle KPI, and submit', async () => {
			// Login Staff
			await authService.login({
				npp: '199003032010012003',
				password: 'password123'
			});

			// Cleanup existing logbook for today if needed (Optional: E2E might be fresh or we might reuse)
			// But startLogbook returns an error if already started. We can try to fetch it first.
			const existingRes = await staffLogbookService.getLogbooks();
			// existingRes could be array or have { data: [...] }
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
			expect(logbookId).toBeDefined();

			// Fetch logbook details to get KPIs
			const detailsRes = await staffLogbookService.getLogbookById(logbookId);
			const logbook = (detailsRes as any).data || detailsRes;
			const kpiDetails = logbook.kpi_details || [];

		if (kpiDetails.length > 0) {
			kpiDetailId = kpiDetails[0].id;

			const updateRes = await staffLogbookService.updateKpiProgress(logbookId, kpiDetailId, {
				capaian_angka: 100
			});
			expect(updateRes).toBeDefined();
		}

			// Submit Logbook
		if (logbook.status !== 'SUBMITTED') {
			const submitRes = await staffLogbookService.submitLogbook(logbookId);
			expect(submitRes).toBeDefined();
		}

			await authService.logout();
		}, 15000);

		it('Manager Flow: Login, fetch submitted logbook, rate it', async () => {
			// Login Manager
			await authService.login({
				npp: '198502022005011002',
				password: 'password123'
			});

		if (!logbookId) {
			throw new Error('No logbookId available from Staff flow');
		}

		const reviewRes = await managerLogbookService.reviewLogbook(logbookId, {
			decision: 'ACCEPTED',
			rating: 4,
			reviewer_comment: 'Good'
		});
		expect(reviewRes).toBeDefined();

			await authService.logout();
		});
	});
});
