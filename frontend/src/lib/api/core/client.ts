import { env } from '$env/dynamic/public';
import type { LaravelErrorResponse } from './types';

function normalizeApiUrl(rawUrl?: string): string {
	const fallback = 'http://localhost:8000/api/v1';
	if (!rawUrl) {
		return fallback;
	}

	const trimmed = rawUrl.replace(/\/+$/, '');

	if (trimmed.endsWith('/api/v1')) {
		return trimmed;
	}

	if (trimmed.endsWith('/api')) {
		return `${trimmed}/v1`;
	}

	if (trimmed.endsWith('/v1')) {
		return trimmed.includes('/api/') || trimmed.endsWith('/api/v1')
			? trimmed
			: `${trimmed.replace(/\/v1$/, '')}/api/v1`;
	}

	return `${trimmed}/api/v1`;
}

const API_URL = normalizeApiUrl(env.PUBLIC_API_URL);

const TOKEN_KEY = 'auth-token';

interface FetchOptions extends RequestInit {
	params?: object;
}

type LocalStorageLike = {
	getItem: (key: string) => string | null;
	setItem: (key: string, value: string) => void;
	removeItem?: (key: string) => void;
	clear?: () => void;
};

class ApiClient {
	private onUnauthorizedCallback: (() => void) | null = null;
	private getStorage(): LocalStorageLike | null {
		if (typeof window === 'undefined') {
			return null;
		}

		const candidate = (window as { localStorage?: LocalStorageLike }).localStorage;
		if (!candidate) {
			return null;
		}

		if (typeof candidate.getItem !== 'function' || typeof candidate.setItem !== 'function') {
			return null;
		}

		return candidate;
	}

	public setOnUnauthorized(callback: () => void): void {
		this.onUnauthorizedCallback = callback;
	}

	private getToken(): string | null {
		const storage = this.getStorage();
		if (!storage) return null;
		const val = storage.getItem(TOKEN_KEY);
		if (!val) return null;
		try {
			return JSON.parse(val);
		} catch {
			return val;
		}
	}

	public setToken(token: string | null): void {
		const storage = this.getStorage();
		if (!storage) {
			return;
		}

		if (token) {
			storage.setItem(TOKEN_KEY, JSON.stringify(token));
		} else if (typeof storage.removeItem === 'function') {
			storage.removeItem(TOKEN_KEY);
		} else if (typeof storage.clear === 'function') {
			storage.clear();
		}
	}

	public clearToken(): void {
		const storage = this.getStorage();
		if (!storage) {
			return;
		}

		if (typeof storage.removeItem === 'function') {
			storage.removeItem(TOKEN_KEY);
		} else if (typeof storage.clear === 'function') {
			storage.clear();
		}
	}

	public async request<T>(endpoint: string, options: FetchOptions = {}): Promise<T> {
		let url = `${API_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;

		if (options.params) {
			const searchParams = new URLSearchParams();
			Object.entries(options.params as Record<string, unknown>).forEach(([key, value]) => {
				if (value !== undefined && value !== null) {
					searchParams.append(key, String(value));
				}
			});
			url += `?${searchParams.toString()}`;
		}

		const headers = new Headers(options.headers);

		headers.set('Accept', 'application/json');

		if (!(options.body instanceof FormData)) {
			if (!headers.has('Content-Type')) {
				headers.set('Content-Type', 'application/json');
			}
		}

		const token = this.getToken();
		if (token) {
			headers.set('Authorization', `Bearer ${token}`);
		}

		const config: RequestInit = {
			...options,
			headers
		};

		try {
			const response = await fetch(url, config);

			if (!response.ok) {
				const errorData: LaravelErrorResponse = await response.json().catch(() => ({
					message: response.statusText
				}));

				if (response.status === 401 && typeof window !== 'undefined') {
					this.clearToken();
					this.onUnauthorizedCallback?.();
				}

				return Promise.reject({
					status: response.status,
					message: errorData.message || 'Terjadi kesalahan pada server',
					errors: errorData.errors
				});
			}

			if (response.status === 204) {
				return {} as T;
			}

			return await response.json();
		} catch (error) {
			console.error(`[API Error] ${options.method || 'GET'} ${url}:`, error);
			throw error;
		}
	}

	get<T>(endpoint: string, options?: FetchOptions) {
		return this.request<T>(endpoint, { ...options, method: 'GET' });
	}

	post<T>(endpoint: string, data?: unknown, options?: FetchOptions) {
		return this.request<T>(endpoint, {
			...options,
			method: 'POST',
			body: data instanceof FormData ? data : JSON.stringify(data)
		});
	}

	put<T>(endpoint: string, data?: unknown, options?: FetchOptions) {
		if (data instanceof FormData) {
			data.append('_method', 'PUT');
			return this.request<T>(endpoint, {
				...options,
				method: 'POST',
				body: data
			});
		}
		return this.request<T>(endpoint, {
			...options,
			method: 'PUT',
			body: JSON.stringify(data)
		});
	}

	patch<T>(endpoint: string, data?: unknown, options?: FetchOptions) {
		return this.request<T>(endpoint, {
			...options,
			method: 'PATCH',
			body: data instanceof FormData ? data : JSON.stringify(data)
		});
	}

	delete<T>(endpoint: string, options?: FetchOptions) {
		return this.request<T>(endpoint, { ...options, method: 'DELETE' });
	}
}

export const api = new ApiClient();
