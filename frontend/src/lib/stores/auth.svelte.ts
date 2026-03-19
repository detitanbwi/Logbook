import { PersistedState } from 'runed';
import { authService } from '$lib/api';
import type { LoginRequest, LoginResponse } from '$lib/api';
import { api } from '$lib/api';
import type { User, UserRole } from '$lib/types';

interface ApiError {
	message?: string;
	status?: number;
	errors?: Record<string, string[]>;
}

export class AuthStore {
	user = new PersistedState<User | null>('auth-user', null);
	token = new PersistedState<string | null>('auth-token', null);
	isLoading = $state(false);
	error = $state<string | null>(null);
	
	// Flag to track if we've verified the session with the backend this browser session
	isInitialized = $state(false);

	get isAuthenticated(): boolean {
		return !!this.token.current;
	}

	get role(): UserRole | null {
		return this.user.current?.role ?? null;
	}

	constructor() {
		if (this.token.current) {
			api.setToken(this.token.current);
		}
	}

	async login(credentials: LoginRequest): Promise<LoginResponse> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await authService.login(credentials);
			this.user.current = res.user;
			this.token.current = res.token;
			api.setToken(res.token);
			this.isInitialized = true;
			return res;
		} catch (error: unknown) {
			const apiError = error as ApiError;
			this.error = apiError.message || 'Login failed';
			throw error;
		} finally {
			this.isLoading = false;
		}
	}

	async logout(): Promise<void> {
		try {
			await authService.logout();
		} finally {
			this.user.current = null;
			this.token.current = null;
			this.isInitialized = false;
			api.clearToken();
		}
	}

	async fetchMe(): Promise<User> {
		try {
			const user = await authService.getMe();
			this.user.current = user;
			this.isInitialized = true;
			return user;
		} catch (error) {
			this.logout();
			throw error;
		}
	}
}

export const auth = new AuthStore();
