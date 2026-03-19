import { describe, expect, it } from 'vitest';
import { normalizeAuditLog } from './auditLog';

describe('normalizeAuditLog', () => {
	it('normalizes legacy backend keys safely', () => {
		const normalized = normalizeAuditLog({
			id: '1',
			table_name: 'users',
			record_id: '42',
			action: 'updated',
			old_data: { role: 'STAFF' },
			new_data: { role: 'MANAGER' },
			performed_at: '2026-01-10T10:00:00.000000Z'
		});

		expect(normalized.event).toBe('updated');
		expect(normalized.auditable_type).toBe('users');
		expect(normalized.auditable_id).toBe('42');
		expect(normalized.old_values).toEqual({ role: 'STAFF' });
		expect(normalized.new_values).toEqual({ role: 'MANAGER' });
		expect(normalized.created_at).toBe('2026-01-10T10:00:00.000000Z');
	});

	it('normalizes alias keys directly and keeps user metadata', () => {
		const normalized = normalizeAuditLog({
			id: '2',
			event: 'created',
			auditable_type: 'App\\Models\\User',
			auditable_id: 'u-1',
			old_values: null,
			new_values: { name: 'Jane' },
			created_at: '2026-01-10T12:00:00.000000Z',
			user: { id: 'u-1', name: 'Jane', email: 'jane@example.com' }
		});

		expect(normalized.event).toBe('created');
		expect(normalized.auditable_type).toBe('App\\Models\\User');
		expect(normalized.old_values).toEqual({});
		expect(normalized.user?.email).toBe('jane@example.com');
	});

	it('falls back to safe defaults when values are missing', () => {
		const normalized = normalizeAuditLog({ id: '3' });

		expect(normalized.event).toBe('-');
		expect(normalized.auditable_type).toBe('-');
		expect(normalized.auditable_id).toBe('-');
		expect(normalized.old_values).toEqual({});
		expect(normalized.new_values).toEqual({});
		expect(normalized.created_at).toBe('');
		expect(normalized.user).toBeUndefined();
	});

	it('parses JSON string values from backend', () => {
		const normalized = normalizeAuditLog({
			id: '019d04df-e48d-72fe-95b8-da84f504c8ed',
			event: 'updated',
			auditable_type: 'kpi_masters',
			auditable_id: '019d02f1-5e8d-725c-b0e1-b60614992d49',
			old_values:
				'{"id":"019d02f1-5e8d-725c-b0e1-b60614992d49","nama":"Evaluasi Kinerja Vendor neque","status_aktif":true}',
			new_values: '{"nama":"Evaluasi Kinerja Vendor","updated_at":"2026-03-19 06:54:35"}',
			created_at: '2026-03-19T06:54:35.000000Z',
			user: { id: 'u-1', name: 'Admin System', email: 'admin@logbook.com' }
		});

		expect(normalized.event).toBe('updated');
		expect(normalized.old_values).toEqual({
			id: '019d02f1-5e8d-725c-b0e1-b60614992d49',
			nama: 'Evaluasi Kinerja Vendor neque',
			status_aktif: true
		});
		expect(normalized.new_values).toEqual({
			nama: 'Evaluasi Kinerja Vendor',
			updated_at: '2026-03-19 06:54:35'
		});
	});

	it('handles invalid JSON strings gracefully', () => {
		const normalized = normalizeAuditLog({
			id: 'test',
			event: 'updated',
			auditable_type: 'test_table',
			auditable_id: '1',
			old_values: 'not a valid json{{{',
			new_values: 'also invalid',
			created_at: '2026-03-19T06:54:35.000000Z'
		});

		expect(normalized.old_values).toEqual({});
		expect(normalized.new_values).toEqual({});
	});
});
