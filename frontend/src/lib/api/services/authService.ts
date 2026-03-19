import { api } from '../core/client';
import {
	LoginRequestSchema,
	type LoginRequest,
	type LoginResponse,
	type ChangePasswordRequest
} from '../schemas/auth.schema';
import * as v from 'valibot';

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
			user: response.user
		};
	}

	async logout(): Promise<void> {
		await api.post<void>('/auth/logout');
		api.clearToken();
	}

	async getMe(): Promise<any> {
		const response = await api.get<any>('/auth/me');
		return response.user;
	}

	async changePassword(data: ChangePasswordRequest): Promise<void> {
		await api.put<void>('/auth/change-password', data);
	}
}

export const authService = new AuthService();
