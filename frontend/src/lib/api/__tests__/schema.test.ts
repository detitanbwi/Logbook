import { describe, it, expect } from 'vitest';
import * as v from 'valibot';
import {
	LoginRequestSchema,
	LoginResponseSchema,
	ChangePasswordRequestSchema
} from '../schemas/auth.schema';
import {
	StartLogbookRequestSchema,
	UpdateKpiProgressSchema,
	SubmitLogbookRequestSchema,
	ReviewLogbookRequestSchema
} from '../schemas/logbook.schema';
import { AnalyticsDashboardSchema } from '../schemas/analytics.schema';
import { NotificationSchema } from '../schemas/notification.schema';
import { UserCreateSchema, UserUpdateSchema } from '../schemas/user.schema';
import { MasterKpiCreateSchema, KpiAssignmentSchema } from '../schemas/kpi.schema';

describe('API DTO Schemas', () => {
	it('should validate LoginRequest', () => {
		const valid = { npp: '12345', password: 'password123' };
		expect(v.safeParse(LoginRequestSchema, valid).success).toBe(true);

		const invalid = { npp: 12345, password: 'password123' };
		expect(v.safeParse(LoginRequestSchema, invalid).success).toBe(false);
	});

	it('should validate LoginResponse', () => {
		const valid = { token: 'jwt.token.here', user: { id: '1', nama: 'Admin' } };
		expect(v.safeParse(LoginResponseSchema, valid).success).toBe(true);
	});

	it('should validate ChangePasswordRequest with min length 8', () => {
		const valid = {
			old_password: 'password123',
			new_password: 'newpassword123',
			new_password_confirmation: 'newpassword123'
		};
		expect(v.safeParse(ChangePasswordRequestSchema, valid).success).toBe(true);

		const invalidLen = {
			old_password: 'password123',
			new_password: 'short',
			new_password_confirmation: 'short'
		};
		expect(v.safeParse(ChangePasswordRequestSchema, invalidLen).success).toBe(false);
	});

	it('should validate StartLogbookRequest', () => {
		const valid = { tanggal: '2026-03-21', start_kerja: '08:00', lokasi: 'Kantor' };
		expect(v.safeParse(StartLogbookRequestSchema, valid).success).toBe(true);
	});

	it('should validate UpdateKpiProgress', () => {
		const valid = { capaian_angka: 50 };
		expect(v.safeParse(UpdateKpiProgressSchema, valid).success).toBe(true);

		const invalid = { capaian_angka: -1 };
		expect(v.safeParse(UpdateKpiProgressSchema, invalid).success).toBe(false);
	});

	it('should validate SubmitLogbookRequest', () => {
		const valid = {};
		expect(v.safeParse(SubmitLogbookRequestSchema, valid).success).toBe(true);
	});

	it('should validate ReviewLogbookRequest', () => {
		const valid = { decision: 'ACCEPTED', rating: 3, reviewer_comment: 'OK' };
		expect(v.safeParse(ReviewLogbookRequestSchema, valid).success).toBe(true);

		const invalid = { rating: 0 };
		expect(v.safeParse(ReviewLogbookRequestSchema, invalid).success).toBe(false);
	});

	describe('Analytics Schema', () => {
		it('should validate AnalyticsDashboardSchema', () => {
			expect(
				v.safeParse(AnalyticsDashboardSchema, {
					total_users: 10,
					total_logbooks: 20,
					kpi_achievements: []
				}).success
			).toBe(true);
			expect(v.safeParse(AnalyticsDashboardSchema, {}).success).toBe(true);
		});
	});

	describe('Notification Schema', () => {
		it('should validate NotificationSchema', () => {
			const valid = {
				id: '1',
				type: 'alert',
				data: {},
				read_at: null,
				created_at: '2023-01-01T00:00:00Z'
			};
			expect(v.safeParse(NotificationSchema, valid).success).toBe(true);
			const invalid = { id: '1', type: 'alert' };
			expect(v.safeParse(NotificationSchema, invalid).success).toBe(false);
		});
	});

		describe('User Schema', () => {
		it('should validate UserCreateSchema', () => {
			const valid = {
				nama: 'Test User',
				email: 'test@example.com',
				npp: '12345',
				password: 'password123',
				role: 'Staff'
			};
			expect(v.safeParse(UserCreateSchema, valid).success).toBe(true);
			expect(
				v.safeParse(UserCreateSchema, {
					...valid,
					role: 'Manager'
				}).success
			).toBe(false);
			const invalid = { nama: 'Test', email: 'test@example.com' };
			expect(v.safeParse(UserCreateSchema, invalid).success).toBe(false);
		});

		it('should validate UserUpdateSchema', () => {
			expect(v.safeParse(UserUpdateSchema, { nama: 'Test' }).success).toBe(true);
			expect(v.safeParse(UserUpdateSchema, {}).success).toBe(true);
			expect(v.safeParse(UserUpdateSchema, { nama: 123 as any }).success).toBe(false);
		});
	});

	describe('KPI Schema', () => {
		it('should validate MasterKpiCreateSchema', () => {
			expect(v.safeParse(MasterKpiCreateSchema, { nama: 'KPI 1', target_angka: 100, satuan: 'unit', status_aktif: true }).success).toBe(true);
			expect(v.safeParse(MasterKpiCreateSchema, { nama: 'KPI 1' }).success).toBe(false);
		});

		it('should validate KpiAssignmentSchema', () => {
			expect(v.safeParse(KpiAssignmentSchema, { user_id: '1', kpi_id: '2' }).success).toBe(true);
			expect(v.safeParse(KpiAssignmentSchema, { user_id: '1' }).success).toBe(false);
		});
	});
});
