import { describe, it, expect } from 'vitest';
import * as v from 'valibot';
import {
	LoginRequestSchema,
	LoginResponseSchema,
	ChangePasswordRequestSchema
} from '../schemas/auth.schema';
import {
	StartLogbookRequestSchema,
	ToggleKpiRequestSchema,
	SubmitLogbookRequestSchema,
	RateLogbookRequestSchema
} from '../schemas/logbook.schema';
import { AnalyticsDashboardSchema } from '../schemas/analytics.schema';
import { NotificationSchema } from '../schemas/notification.schema';
import { UserCreateSchema, UserUpdateSchema } from '../schemas/user.schema';
import { MasterKpiCreateSchema, KpiAssignmentSchema } from '../schemas/kpi.schema';

describe('API DTO Schemas', () => {
	it('should validate LoginRequest', () => {
		const valid = { nip: '12345', password: 'password123' };
		expect(v.safeParse(LoginRequestSchema, valid).success).toBe(true);

		const invalid = { nip: 12345, password: 'password123' };
		expect(v.safeParse(LoginRequestSchema, invalid).success).toBe(false);
	});

	it('should validate LoginResponse', () => {
		const valid = { token: 'jwt.token.here', user: { id: '1', nama: 'Admin' } };
		expect(v.safeParse(LoginResponseSchema, valid).success).toBe(true);
	});

	it('should validate ChangePasswordRequest with min length 8', () => {
		const valid = { old_password: 'password123', new_password: 'newpassword123' };
		expect(v.safeParse(ChangePasswordRequestSchema, valid).success).toBe(true);

		const invalidLen = { old_password: 'password123', new_password: 'short' };
		expect(v.safeParse(ChangePasswordRequestSchema, invalidLen).success).toBe(false);
	});

	it('should validate StartLogbookRequest', () => {
		const valid = { gps_location_start: '-6.2088,106.8456' };
		expect(v.safeParse(StartLogbookRequestSchema, valid).success).toBe(true);
	});

	it('should validate ToggleKpiRequest', () => {
		const valid = { is_finished: true };
		expect(v.safeParse(ToggleKpiRequestSchema, valid).success).toBe(true);

		const invalid = { is_finished: 'true' };
		expect(v.safeParse(ToggleKpiRequestSchema, invalid).success).toBe(false);
	});

	it('should validate SubmitLogbookRequest', () => {
		const valid1 = { gps_location_end: '-6.2088,106.8456' };
		const valid2 = { gps_location_end: '-6.2088,106.8456', gambar_bukti: ['base64string'] };
		const valid3 = { gps_location_end: '-6.2088,106.8456', gambar_bukti: null };

		expect(v.safeParse(SubmitLogbookRequestSchema, valid1).success).toBe(true);
		expect(v.safeParse(SubmitLogbookRequestSchema, valid2).success).toBe(true);
		expect(v.safeParse(SubmitLogbookRequestSchema, valid3).success).toBe(true);
	});

	it('should validate RateLogbookRequest (1-5)', () => {
		expect(v.safeParse(RateLogbookRequestSchema, { rating: 1 }).success).toBe(true);
		expect(v.safeParse(RateLogbookRequestSchema, { rating: 5 }).success).toBe(true);
		expect(v.safeParse(RateLogbookRequestSchema, { rating: 3 }).success).toBe(true);

		expect(v.safeParse(RateLogbookRequestSchema, { rating: 0 }).success).toBe(false);
		expect(v.safeParse(RateLogbookRequestSchema, { rating: 6 }).success).toBe(false);
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
				nip: '12345',
				password: 'password123',
				role: 'Staff'
			};
			expect(v.safeParse(UserCreateSchema, valid).success).toBe(true);
			const invalid = { nama: 'Test', email: 'test@example.com' };
			expect(v.safeParse(UserCreateSchema, invalid).success).toBe(false);
		});

		it('should validate UserUpdateSchema', () => {
			expect(v.safeParse(UserUpdateSchema, { nama: 'Test' }).success).toBe(true);
			expect(v.safeParse(UserUpdateSchema, {}).success).toBe(true);
			expect(v.safeParse(UserUpdateSchema, { nama: 123 }).success).toBe(false);
		});
	});

	describe('KPI Schema', () => {
		it('should validate MasterKpiCreateSchema', () => {
			expect(v.safeParse(MasterKpiCreateSchema, { nama: 'KPI 1' }).success).toBe(true);
			expect(
				v.safeParse(MasterKpiCreateSchema, { nama: 'KPI 1', status_aktif: true }).success
			).toBe(true);
			expect(v.safeParse(MasterKpiCreateSchema, {}).success).toBe(false);
		});

		it('should validate KpiAssignmentSchema', () => {
			expect(v.safeParse(KpiAssignmentSchema, { user_id: '1', kpi_id: '2' }).success).toBe(true);
			expect(v.safeParse(KpiAssignmentSchema, { user_id: '1' }).success).toBe(false);
		});
	});
});
