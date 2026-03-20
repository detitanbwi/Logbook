import { api } from '../core/client';
import type { UserCreateDto, UserUpdateDto } from '../schemas/user.schema';
import type { PaginatedResponse, PaginationParams } from '../core/types';
import type { User } from '../../types';
import { normalizeUserWithManager } from '../schemas/user-normalization.schema';

export interface UserFilters extends PaginationParams {
	role?: string;
	search?: string;
	sort_by?: string;
	sort_dir?: 'asc' | 'desc';
	[key: string]: unknown;
}

export const usersService = {
	async getAll(params?: UserFilters): Promise<PaginatedResponse<User>> {
		const response = await api.get<PaginatedResponse<User>>('/users', { params });
		if (!Array.isArray(response?.data)) {
			return response;
		}

		return {
			...response,
			data: response.data.map((user) => normalizeUserWithManager(user))
		};
	},
	async getById(id: string): Promise<User> {
		const response = await api.get<User>(`/users/${id}`);
		return normalizeUserWithManager(response);
	},
	async getSubordinates(id: string, params?: PaginationParams): Promise<PaginatedResponse<User>> {
		const response = await api.get<PaginatedResponse<User>>(`/users/${id}/subordinates`, { params });
		if (!Array.isArray(response?.data)) {
			return response;
		}

		return {
			...response,
			data: response.data.map((user) => normalizeUserWithManager(user))
		};
	},
	create: (data: UserCreateDto) => api.post<any>('/users', data),
	update: (id: string, data: UserUpdateDto) => api.put<any>(`/users/${id}`, data),
	delete: (id: string) => api.delete<void>(`/users/${id}`),
	resetPassword: (id: string, new_password: string) =>
		api.put<void>(`/users/${id}/reset-password`, { new_password })
};
