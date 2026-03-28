import { afterEach, describe, expect, it, vi } from 'vitest';

async function loadResolver(publicApiUrl?: string) {
	vi.resetModules();
	vi.doMock('$env/dynamic/public', () => ({
		env: {
			PUBLIC_API_URL: publicApiUrl
		}
	}));

	return import('./asset-url');
}

describe('resolveStorageUrl', () => {
	afterEach(() => {
		vi.unstubAllGlobals();
	});

	it('keeps absolute URLs unchanged', async () => {
		const { resolveStorageUrl } = await loadResolver('https://api.example.com/api/v1');
		expect(resolveStorageUrl('https://cdn.example.com/file.jpg')).toBe(
			'https://cdn.example.com/file.jpg'
		);
	});

	it('builds backend storage URL from relative file path', async () => {
		const { resolveStorageUrl } = await loadResolver('https://api.example.com/api/v1');
		expect(resolveStorageUrl('proofs/file.jpg')).toBe('https://api.example.com/storage/proofs/file.jpg');
	});

	it('does not duplicate storage prefix', async () => {
		const { resolveStorageUrl } = await loadResolver('https://api.example.com/api/v1');
		expect(resolveStorageUrl('storage/proofs/file.jpg')).toBe(
			'https://api.example.com/storage/proofs/file.jpg'
		);
		expect(resolveStorageUrl('/storage/proofs/file.jpg')).toBe(
			'https://api.example.com/storage/proofs/file.jpg'
		);
	});

	it('supports relative PUBLIC_API_URL using browser origin fallback', async () => {
		vi.stubGlobal('window', {
			location: {
				origin: 'https://frontend.example.com'
			}
		});

		const { resolveStorageUrl } = await loadResolver('/api/v1');
		expect(resolveStorageUrl('proofs/file.jpg')).toBe(
			'https://frontend.example.com/storage/proofs/file.jpg'
		);
	});

	it('returns placeholder for empty path', async () => {
		const { resolveStorageUrl } = await loadResolver('https://api.example.com/api/v1');
		expect(resolveStorageUrl('')).toBe('#');
	});
});
