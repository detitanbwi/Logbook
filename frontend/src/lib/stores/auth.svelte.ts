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
	private fetchMeInFlight: Promise<User> | null = null;
	private logoutInFlight: Promise<void> | null = null;
	private sessionVersion = 0;

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
		api.setOnUnauthorized(() => this.handleUnauthorized());
	}

	private handleUnauthorized(): void {
		this.clearLocalSession();
		if (typeof window !== 'undefined') {
			window.location.href = '/login';
		}
	}

	private clearLocalSession(): void {
		this.sessionVersion += 1;
		this.user.current = null;
		this.token.current = null;
		this.isInitialized = false;
		api.clearToken();
	}

	async login(credentials: LoginRequest): Promise<LoginResponse> {
		this.isLoading = true;
		this.error = null;
		try {
			const res = await authService.login(credentials);
			const user = res.user;

			this.user.current = user;
			this.token.current = res.token;
			api.setToken(res.token);
			this.isInitialized = true;
			return {
				...res,
				user
			};
		} catch (error: unknown) {
			const apiError = error as ApiError;
			this.error = apiError.message || 'Login failed';
			throw error;
		} finally {
			this.isLoading = false;
		}
	}

	async logout(): Promise<void> {
		if (this.logoutInFlight) {
			return this.logoutInFlight;
		}

		// Explicit user logout: clear local session immediately, then notify backend once.
		this.clearLocalSession();

		this.logoutInFlight = (async () => {
			try {
				await authService.logout();
			} finally {
				this.logoutInFlight = null;
			}
		})();

		return this.logoutInFlight;
	}

	async fetchMe(): Promise<User> {
		if (this.fetchMeInFlight) {
			return this.fetchMeInFlight;
		}

		const requestSessionVersion = this.sessionVersion;

		this.fetchMeInFlight = (async () => {
			try {
				const user = await authService.getMe();

				// Prevent stale in-flight responses from restoring an invalidated/logged-out session.
				if (requestSessionVersion !== this.sessionVersion || !this.token.current) {
					throw {
						status: 401,
						message: 'Session invalidated'
					} as ApiError;
				}

				this.user.current = user;
				this.isInitialized = true;
				return user;
			} catch (error: unknown) {
				const apiError = error as ApiError;
				if (apiError.status === 401) {
					// Unauthorized revalidation should only clear local session,
					// never call remote logout endpoint.
					this.clearLocalSession();
				}
				throw error;
			} finally {
				this.fetchMeInFlight = null;
			}
		})();

		return this.fetchMeInFlight;
	}
}

export const auth = new AuthStore();
