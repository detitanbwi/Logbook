import { api } from '../core/client';
import {
	StartLogbookRequestSchema,
	UpdateLogbookRequestSchema,
	UpdateKpiProgressSchema,
	type StartLogbookRequest,
	type UpdateLogbookRequest,
	type UpdateKpiProgressRequest
} from '../schemas/logbook.schema';
import * as v from 'valibot';
import type { PaginatedResponse, PaginationParams, BaseResponse } from '../core/types';
import type { Logbook, LogbookDuration } from '../../types';
import { normalizeEntityUser } from '../schemas/user-normalization.schema';

export interface LogbookFilters extends PaginationParams {
	status?: string;
	search?: string;
	date_from?: string;
	date_to?: string;
	sort_by?: string;
	sort_dir?: 'asc' | 'desc';
	user_id?: string;
}

export class StaffLogbookService {
	private normalizeLogbook(logbook: Logbook): Logbook {
		return normalizeEntityUser(logbook);
	}

	async getLogbooks(params?: LogbookFilters): Promise<PaginatedResponse<Logbook>> {
		const response = await api.get<PaginatedResponse<Logbook>>('/logbooks', {
			params: params as Record<string, unknown>
		});
		if (!Array.isArray(response?.data)) {
			return response;
		}

		return {
			...response,
			data: response.data.map((logbook) => this.normalizeLogbook(logbook))
		};
	}

	async getLogbookById(logbookId: string): Promise<Logbook> {
		const response = await api.get<BaseResponse<Logbook>>(`/logbooks/${logbookId}`);
		return this.normalizeLogbook(response.data);
	}

	async startLogbook(data: StartLogbookRequest): Promise<Logbook> {
		const validated = v.parse(StartLogbookRequestSchema, data);
		const response = await api.post<BaseResponse<Logbook>>('/logbooks/start', validated);
		return this.normalizeLogbook(response.data);
	}

	async updateLogbook(logbookId: string, data: UpdateLogbookRequest): Promise<Logbook> {
		const validated = v.parse(UpdateLogbookRequestSchema, data);
		const response = await api.patch<BaseResponse<Logbook>>(`/logbooks/${logbookId}`, validated);
		return this.normalizeLogbook(response.data);
	}

	async updateKpiProgress(
		logbookId: string,
		detailId: string,
		data: UpdateKpiProgressRequest
	): Promise<{ id: string; capaian_angka: number; target_angka: number; satuan: string; finished_at: string | null }> {
		const validated = v.parse(UpdateKpiProgressSchema, data);
		return api.patch(`/logbooks/${logbookId}/kpi/${detailId}/progress`, validated);
	}

	async uploadKpiAttachment(
		logbookId: string,
		detailId: string,
		file: File
	): Promise<{ message: string; data: { id: string; lampiran_file: string } }> {
		const formData = new FormData();
		formData.append('lampiran_file', file);
		return api.post(`/logbooks/${logbookId}/kpi/${detailId}/attachment`, formData);
	}

	async deleteKpiAttachment(
		logbookId: string,
		detailId: string
	): Promise<{ message: string; data: { id: string; lampiran_file: null } }> {
		return api.delete(`/logbooks/${logbookId}/kpi/${detailId}/attachment`);
	}

	async submitLogbook(logbookId: string): Promise<Logbook> {
		const response = await api.post<BaseResponse<Logbook>>(`/logbooks/${logbookId}/submit`);
		return this.normalizeLogbook(response.data);
	}

	async deleteLogbook(logbookId: string): Promise<{ message: string }> {
		return api.delete(`/logbooks/${logbookId}`);
	}

	async getLogbookDuration(logbookId: string): Promise<LogbookDuration> {
		return api.get(`/logbooks/${logbookId}/duration`);
	}
}

export const staffLogbookService = new StaffLogbookService();
