import { staffLogbookService } from '$lib/api';
import type { StartLogbookRequest, ToggleKpiRequest, SubmitLogbookRequest } from '$lib/api';
import type { LogbookFilters } from '$lib/api/services/staffLogbookService';
import type { PaginatedResponse } from '$lib/api/core/types';
import type { Logbook, LogbookKpiDetail } from '$lib/types';

/** Pagination meta from Laravel's LengthAwarePaginator */
type PaginationMeta = PaginatedResponse<unknown>['meta'];

interface ApiError {
	message?: string;
	status?: number;
	errors?: Record<string, string[]>;
}

export class LogbookStore {
	logbooks = $state<Logbook[]>([]);
	meta = $state<PaginationMeta | null>(null);
	currentLogbook = $state<Logbook | null>(null);
	isLoading = $state(false);
	error = $state<string | null>(null);

	async fetchLogbooks(params?: LogbookFilters): Promise<PaginatedResponse<Logbook>> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await staffLogbookService.getLogbooks(params);
			// Handle typical Laravel pagination response format
			this.logbooks = res.data;
			this.meta = res.meta;
			return res;
		} catch (err: unknown) {
			const apiError = err as ApiError;
			this.error = apiError.message || 'Failed to fetch logbooks';
			throw err;
		} finally {
			this.isLoading = false;
		}
	}

	async fetchLogbookById(id: string): Promise<Logbook> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await staffLogbookService.getLogbookById(id);
			this.currentLogbook = res;
			return res;
		} catch (err: unknown) {
			const apiError = err as ApiError;
			this.error = apiError.message || 'Failed to fetch logbook';
			throw err;
		} finally {
			this.isLoading = false;
		}
	}

	async startLogbook(data: StartLogbookRequest): Promise<Logbook> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await staffLogbookService.startLogbook(data);
			await this.fetchLogbooks(); // Refresh list after starting
			return res;
		} catch (err: unknown) {
			const apiError = err as ApiError;
			this.error = apiError.message || 'Failed to start logbook';
			throw err;
		} finally {
			this.isLoading = false;
		}
	}

	async toggleKpi(logbookId: string, detailId: string, data: ToggleKpiRequest): Promise<unknown> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await staffLogbookService.toggleKpi(logbookId, detailId, data);

			// Optimistically update the specific KPI in the nested array
			if (this.currentLogbook?.id === logbookId && this.currentLogbook.details) {
				const detailIndex = this.currentLogbook.details.findIndex(
					(d: LogbookKpiDetail) => d.id === detailId
				);
				if (detailIndex !== -1) {
					// Apply the toggled value directly from data
					this.currentLogbook.details[detailIndex] = {
						...this.currentLogbook.details[detailIndex],
						is_finished: data.is_finished
					};
				}
			}

			return res;
		} catch (err: unknown) {
			const apiError = err as ApiError;
			this.error = apiError.message || 'Failed to toggle KPI';
			throw err;
		} finally {
			this.isLoading = false;
		}
	}

	async submitLogbook(logbookId: string, data: SubmitLogbookRequest | FormData): Promise<Logbook> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await staffLogbookService.submitLogbook(logbookId, data);

			// Update current logbook status if viewed
			if (this.currentLogbook?.id === logbookId) {
				this.currentLogbook.status = 'SUBMITTED';
			}

			await this.fetchLogbooks(); // Refresh list
			return res;
		} catch (err: unknown) {
			const apiError = err as ApiError;
			this.error = apiError.message || 'Failed to submit logbook';
			throw err;
		} finally {
			this.isLoading = false;
		}
	}
}

export const logbookStore = new LogbookStore();
