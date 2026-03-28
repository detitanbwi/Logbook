import { env } from '$env/dynamic/public';
import type { LaravelErrorResponse } from './types';

// Ambil URL API dari env. Jika belum ada, gunakan default localhost
const API_URL = env.PUBLIC_API_URL || 'http://localhost:8000/api/v1';

// Unified token key for localStorage
const TOKEN_KEY = 'auth-token';

interface FetchOptions extends RequestInit {
	params?: Record<string, any>;
}

class ApiClient {
	private onUnauthorizedCallback: (() => void) | null = null;

	public setOnUnauthorized(callback: () => void): void {
		this.onUnauthorizedCallback = callback;
	}

	private getToken(): string | null {
		if (typeof window === 'undefined') return null;
		const val = localStorage.getItem(TOKEN_KEY);
		if (!val) return null;
		try {
			return JSON.parse(val);
		} catch (e) {
			return val;
		}
	}

	public setToken(token: string | null): void {
		if (typeof window !== 'undefined') {
			if (token) {
				localStorage.setItem(TOKEN_KEY, JSON.stringify(token));
			} else {
				localStorage.removeItem(TOKEN_KEY);
			}
		}
	}

	public clearToken(): void {
		if (typeof window !== 'undefined') {
			localStorage.removeItem(TOKEN_KEY);
		}
	}

	/**
	 * Wrapper utama untuk Fetch API
	 */
	public async request<T>(endpoint: string, options: FetchOptions = {}): Promise<T> {
		// Pastikan endpoint selalu diawali dengan slash
		let url = `${API_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;

		if (options.params) {
			const searchParams = new URLSearchParams();
			Object.entries(options.params).forEach(([key, value]) => {
				if (value !== undefined && value !== null) {
					searchParams.append(key, String(value));
				}
			});
			url += `?${searchParams.toString()}`;
		}

		const headers = new Headers(options.headers);

		// Setup Headers Standar
		headers.set('Accept', 'application/json');

		// Jangan set Content-Type jika body adalah FormData (misal upload file)
		// Browser akan otomatis menset multipart/form-data beserta boundary-nya
		if (!(options.body instanceof FormData)) {
			if (!headers.has('Content-Type')) {
				headers.set('Content-Type', 'application/json');
			}
		}

		// Inject Authorization Token jika ada
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

			// Jika response tidak OK (4xx, 5xx), lempar error yang terstruktur
			if (!response.ok) {
				const errorData: LaravelErrorResponse = await response.json().catch(() => ({
					message: response.statusText
				}));

				// Handle 401 Unauthorized secara global jika diperlukan
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

			// Handle response 204 No Content
			if (response.status === 204) {
				return {} as T;
			}

			return await response.json();
		} catch (error) {
			console.error(`[API Error] ${options.method || 'GET'} ${url}:`, error);
			throw error; // Lempar ke pemanggil fungsi
		}
	}

	// Shorthand methods
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
		// Di Laravel, file upload (FormData) tidak bisa dikirim via PUT secara langsung.
		// Solusi: Gunakan method POST dan append '_method="PUT"' di FormData jika perlu.
		return this.request<T>(endpoint, {
			...options,
			method: 'PUT',
			body: data instanceof FormData ? data : JSON.stringify(data)
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
