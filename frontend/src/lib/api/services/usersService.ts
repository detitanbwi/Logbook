import { api } from '../core/client';
import type { UserCreateDto, UserUpdateDto } from '../schemas/user.schema';
import type { PaginatedResponse, PaginationParams } from '../core/types';
import type { User } from '../../types';

export interface UserFilters extends PaginationParams {
	role?: string;
	search?: string;
	sort_by?: string;
	sort_dir?: 'asc' | 'desc';
	[key: string]: unknown;
}

export const usersService = {
	getAll: (params?: UserFilters) => api.get<PaginatedResponse<User>>('/users', { params }),
	getById: (id: string) => api.get<any>(`/users/${id}`),
	create: (data: UserCreateDto) => api.post<any>('/users', data),
	update: (id: string, data: UserUpdateDto) => api.put<any>(`/users/${id}`, data),
	delete: (id: string) => api.delete<void>(`/users/${id}`),
	resetPassword: (id: string) => api.put<void>(`/users/${id}/reset-password`)
};
