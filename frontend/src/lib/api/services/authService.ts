import { api } from '../core/client';
import {
	LoginRequestSchema,
	type LoginRequest,
	type LoginResponse,
	type ChangePasswordRequest,
	UpdateProfileSchema,
	type UpdateProfileRequest
} from '../schemas/auth.schema';
import * as v from 'valibot';
import { normalizeUserWithManager } from '../schemas/user-normalization.schema';
import type { User } from '$lib/types';

export class AuthService {
	async login(data: LoginRequest): Promise<LoginResponse> {
		const validated = v.parse(LoginRequestSchema, data);

		const response = await api.post<any>('/auth/login', validated);
		const token = response.access_token || response.token;
		if (token) {
			api.setToken(token);
		}
		return {
			token: token,
			user: response.user ? normalizeUserWithManager(response.user) : null
		};
	}

	async logout(): Promise<void> {
		await api.post<void>('/auth/logout');
		api.clearToken();
	}

	async getMe(): Promise<User> {
		const response = await api.get<any>('/auth/me');
		return normalizeUserWithManager(response?.user ?? response);
	}

	async changePassword(data: ChangePasswordRequest): Promise<void> {
		await api.put<void>('/auth/change-password', data);
	}

	async updateProfile(data: UpdateProfileRequest | FormData): Promise<User> {
		if (data instanceof FormData) {
			const response = await api.put<{ message: string; data: User }>('/auth/profile', data);
			return normalizeUserWithManager(response.data);
		}

		const validated = v.parse(UpdateProfileSchema, data);

		const response = await api.put<{ message: string; data: User }>('/auth/profile', validated);
		return normalizeUserWithManager(response.data);
	}
}

export const authService = new AuthService();
