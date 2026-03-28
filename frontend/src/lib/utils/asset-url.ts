import { env } from '$env/dynamic/public';

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

function getBackendOrigin(): string {
	const apiUrl = normalizeApiUrl(env.PUBLIC_API_URL);
	const fallbackOrigin = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:8000';

	try {
		return new URL(apiUrl, fallbackOrigin).origin;
	} catch {
		return fallbackOrigin;
	}
}

export function resolveStorageUrl(filePath: string): string {
	if (!filePath) {
		return '#';
	}

	if (/^https?:\/\//i.test(filePath)) {
		return filePath;
	}

	const normalizedPath = filePath.replace(/^\/+/, '');
	const backendOrigin = getBackendOrigin();

	if (normalizedPath.startsWith('storage/')) {
		return `${backendOrigin}/${normalizedPath}`;
	}

	return `${backendOrigin}/storage/${normalizedPath}`;
}
