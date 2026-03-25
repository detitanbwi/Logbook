/**
 * Standar format respon error dari Laravel (Validation Exception, dll)
 */
export interface LaravelErrorResponse {
	message: string;
	errors?: Record<string, string[]>;
}

export interface PaginationParams {
	page?: number;
	per_page?: number;
	search?: string;
	sort_by?: string;
	sort_dir?: 'asc' | 'desc';
}

/**
 * Paginasi standar dari resource Laravel (LengthAwarePaginator)
 */
export interface PaginatedResponse<T> {
	data: T[];
	links: {
		first: string | null;
		last: string | null;
		prev: string | null;
		next: string | null;
	};
	meta: {
		current_page: number;
		from: number | null;
		last_page: number;
		links: Array<{
			url: string | null;
			label: string;
			active: boolean;
		}>;
		path: string;
		per_page: number;
		to: number | null;
		total: number;
	};
}

/**
 * Response dasar untuk endpoint yang tidak mengembalikan array/pagination
 */
export interface BaseResponse<T> {
	data: T;
	message?: string;
}
