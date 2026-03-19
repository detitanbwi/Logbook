import { api } from '../core/client';
import {
	StartLogbookRequestSchema,
	ToggleKpiRequestSchema,
	SubmitLogbookRequestSchema,
	type StartLogbookRequest,
	type ToggleKpiRequest,
	type SubmitLogbookRequest
} from '../schemas/logbook.schema';
import * as v from 'valibot';
import type { PaginatedResponse, PaginationParams, BaseResponse } from '../core/types';
import type { Logbook } from '../../types';

export interface LogbookFilters extends PaginationParams {
	status?: string;
	search?: string;
	date_from?: string;
	date_to?: string;
	sort_by?: string;
	sort_dir?: 'asc' | 'desc';
}

export class StaffLogbookService {
	async getLogbooks(params?: LogbookFilters): Promise<PaginatedResponse<Logbook>> {
		return api.get<PaginatedResponse<Logbook>>('/logbooks', {
			params: params as Record<string, unknown>
		});
	}

	async getLogbookById(logbookId: string | number): Promise<Logbook> {
		const response = await api.get<BaseResponse<Logbook>>(`/logbooks/${logbookId}`);
		return response.data; // Mengikuti struktur dari api.get return if wrapped in BaseResponse or directly returning it.
		// Catatan: Jika API Laravel langsung return objek Logbook (misalnya dari resource tanpa 'data' wrapper untuk get 1 item),
		// mungkin ini perlu disesuaikan, tapi sementara kita asumsikan menggunakan BaseResponse.
	}

	async startLogbook(data: StartLogbookRequest): Promise<Logbook> {
		const validated = v.parse(StartLogbookRequestSchema, data);
		const response = await api.post<BaseResponse<Logbook>>('/logbooks/start', validated);
		return response.data;
	}

	async toggleKpi(
		logbookId: string | number,
		detailId: string | number,
		data: ToggleKpiRequest
	): Promise<any> {
		const validated = v.parse(ToggleKpiRequestSchema, data);
		return api.patch<any>(`/logbooks/${logbookId}/kpi/${detailId}/toggle`, validated);
	}

	async submitLogbook(
		logbookId: string | number,
		data: SubmitLogbookRequest | FormData
	): Promise<Logbook> {
		// If FormData is passed, send it directly (for file uploads)
		if (data instanceof FormData) {
			const response = await api.post<BaseResponse<Logbook>>(`/logbooks/${logbookId}/submit`, data);
			return response.data;
		}

		// Otherwise, validate and send as JSON
		const validated = v.parse(SubmitLogbookRequestSchema, data);
		const response = await api.post<BaseResponse<Logbook>>(
			`/logbooks/${logbookId}/submit`,
			validated
		);
		return response.data;
	}
}

export const staffLogbookService = new StaffLogbookService();
