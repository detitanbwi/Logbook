import { api } from '../core/client';
import { ReviewLogbookRequestSchema, type ReviewLogbookRequest } from '../schemas/logbook.schema';
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

	async reviewLogbook(
		logbookId: string,
		data: ReviewLogbookRequest
	): Promise<{ message: string; data: Logbook }> {
		const validated = v.parse(ReviewLogbookRequestSchema, data);
		return api.put(`/logbooks/${logbookId}/review`, validated);
	}

}

export const managerLogbookService = new ManagerLogbookService();
