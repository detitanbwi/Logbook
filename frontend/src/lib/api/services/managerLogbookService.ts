import { api } from '../core/client';
import { RateLogbookRequestSchema, type RateLogbookRequest } from '../schemas/logbook.schema';
import * as v from 'valibot';
import type { PaginatedResponse } from '../core/types';
import type { Logbook } from '../../types';
import type { LogbookFilters } from './staffLogbookService';

export class ManagerLogbookService {
	async getPendingReviews(params?: LogbookFilters): Promise<PaginatedResponse<Logbook>> {
		return api.get<PaginatedResponse<Logbook>>('/logbooks', {
			params: { status: 'SUBMITTED', ...params }
		});
	}

	async revertLogbook(logbookId: string | number): Promise<any> {
		return api.post<any>(`/logbooks/${logbookId}/revert`);
	}

	async reviewLogbook(logbookId: string | number, data: RateLogbookRequest): Promise<any> {
		const validated = v.parse(RateLogbookRequestSchema, data);
		return api.put<any>(`/logbooks/${logbookId}/review`, validated);
	}

	/** @deprecated use reviewLogbook() */
	async rateLogbook(logbookId: string | number, data: RateLogbookRequest): Promise<any> {
		return this.reviewLogbook(logbookId, data);
	}
}

export const managerLogbookService = new ManagerLogbookService();
