import { api } from '../core/client';
import type { UserCreateDto, UserUpdateDto } from '../schemas/user.schema';
import type { BaseResponse, PaginatedResponse, PaginationParams } from '../core/types';
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
		const response = await api.get<BaseResponse<User>>(`/users/${id}`);
		return normalizeUserWithManager(response.data);
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
	async create(data: UserCreateDto): Promise<User> {
		const response = await api.post<BaseResponse<User>>('/users', data);
		return normalizeUserWithManager(response.data);
	},
	async update(id: string, data: UserUpdateDto): Promise<User> {
		const response = await api.put<BaseResponse<User>>(`/users/${id}`, data);
		return normalizeUserWithManager(response.data);
	},
	delete: (id: string) => api.delete<void>(`/users/${id}`),
	resetPassword: (id: string, new_password: string) =>
		api.put<void>(`/users/${id}/reset-password`, { new_password })
};
