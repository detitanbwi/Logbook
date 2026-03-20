# API SDK & Types Guide

This document provides a comprehensive guide on how to interact with the Laravel Backend using the newly scaffolded SDK in the Svelte 5 frontend. It covers the core architecture, data validation (Valibot), state management (Svelte 5 Runes), error handling, and usage examples.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Validation & Schemas (`Valibot`)](#validation--schemas)
3. [SDK Client (`src/lib/api/client.ts`)](#sdk-client)
4. [State Management (`Svelte 5 Runes`)](#state-management)
5. [Usage Examples](#usage-examples)
6. [Error Handling](#error-handling)

---

## Architecture Overview

The API SDK is a lightweight abstraction over the native browser `fetch` API, tailored specifically to communicate with a standard Laravel backend API. The directory structure is strictly modularized:

- `src/lib/api/core/client.ts`: The central Fetch wrapper.
- `src/lib/api/schemas/*.schema.ts`: Valibot v1.x schemas for Request/Response DTO validation.
- `src/lib/api/services/*.ts`: Endpoint wrappers perfectly matching the backend Laravel Controllers.
- `src/lib/stores/*.svelte.ts`: Global reactive state handlers using Svelte 5 runes.

The SDK automatically handles:

- **JSON serialization/deserialization**.
- **Bearer Token injection** via `localStorage`.
- **Laravel specific error wrapping** (e.g., Validation Errors).
- **FormData detection** for file uploads without overriding `Content-Type`.

---

## Validation & Schemas

We use **Valibot v1.x** for lightweight, robust DTO (Data Transfer Object) validation. The schemas define the exact shape expected by the Laravel backend, ensuring type-safety before network requests are even made.

### Example: `auth.schema.ts`

```typescript
import * as v from 'valibot';

export const LoginSchema = v.object({
	nip: v.string([v.minLength(1, 'NIP is required')]),
	password: v.string([v.minLength(1, 'Password is required')])
});

// Infer TypeScript types directly from Valibot schemas
export type LoginDTO = v.InferOutput<typeof LoginSchema>;
```

---

## SDK Client

The API client is an instance of `ApiClient` exported as `api` from `src/lib/api/core/client.ts`.

### Authentication Management

The `Authorization: Bearer <token>` header is automatically appended to every outgoing request if the token exists.

- `api.setToken(token: string)`: Saves the Sanctum bearer token in `localStorage`.
- `api.clearToken()`: Removes the token.
- `api.getToken()`: Internal method to retrieve the token.

---

## State Management

Instead of components fetching data directly, we use **Svelte 5 Runed Stores** (`src/lib/stores/`) to hold global application state, handling loading states, errors, and optimistic UI updates automatically.

### `AuthStore` (`src/lib/stores/auth.svelte.ts`)

Manages the current user session.

```typescript
import { authStore } from '$lib/stores/auth.svelte';

// Checking role
if (authStore.user?.role === 'STAFF') { ... }

// Triggering login (which internally calls authService.login)
await authStore.login({ nip: '123', password: 'password' });
```

### `LogbookStore` (`src/lib/stores/logbook.svelte.ts`)

Handles the complex logbook flow (Draft -> Add KPIs -> Submit). Includes optimistic updates so the UI doesn't stutter while waiting for the Laravel API.

---

## Usage Examples

### 1. Fetching Paginated Data (GET)

The backend now returns a standardized `PaginatedResponse<T>` for list endpoints. This includes the actual data array alongside Laravel's meta-information. You can pass a `PaginationParams` object to append query strings like `?page=x&per_page=y`.

```typescript
import { usersService } from '$lib/api/services/usersService';

// Fetch paginated list
async function loadUsers() {
	const response = await usersService.getAll({ page: 1, per_page: 10 });

	// The actual list is nested inside the 'data' property
	console.log(response.data.data); // typed as User[]

	// Laravel pagination info is inside the 'meta' property
	console.log(response.data.meta.total); // Total records available in the DB
	console.log(response.data.meta.current_page); // 1
}
```

To render this data in Svelte, you can iterate over the `.data` array and pass the `.meta` object to our reusable `<Pagination />` component.

```svelte
<script lang="ts">
	import Pagination from '$lib/components/ui/Pagination.svelte';
	let { data } = $props(); // data returned from +page.ts loader
</script>

{#each data.items as user}
	<div>{user.name}</div>
{/each}

<!-- Auto-handles prev/next links using the ?page=x URL param -->
<Pagination meta={data.meta} />
```

### 2. File Uploads (FormData)

When sending `FormData`, the SDK automatically omits the `Content-Type` header so the browser can accurately set the `multipart/form-data` boundary.

```typescript
import { staffLogbookService } from '$lib/api/services/staffLogbookService';

async function uploadProof(file: File) {
	const response = await staffLogbookService.submitLogbook({
		end_latitude: -6.2,
		end_longitude: 106.8,
		gambar_bukti: [file] // The service converts this to FormData automatically
	});
}
```

> **Note on File Exports:** For generating PDFs/Excels, the `ApiClient` passes headers directly. e.g., `Accept: application/pdf` will instruct native `fetch` to handle the blob correctly.

---

## Error Handling

The SDK catches non-2xx responses and formats them using a standardized `LaravelErrorResponse`. You can easily catch validation errors or unauthenticated errors.

```typescript
try {
	await authService.login({ nip: '', password: 'bad' });
} catch (error: any) {
	// Check if it's a 422 Validation Error
	if (error.status === 422 && error.errors) {
		console.error('Validation Errors:', error.errors);
		// error.errors = { nip: ['The NIP field is required.'] }
	} else {
		console.error('Server Error:', error.message);
	}
}
```

### Unauthorized (401) Handling

Currently, the SDK handles `401 Unauthorized` responses globally by clearing the token from local storage, allowing the SvelteKit frontend to redirect back to the `/login` page.

In addition, `AuthStore` now applies a safer session strategy:

- `fetchMe()` on `401` only clears local session state (no remote `/auth/logout` call).
- remote `/auth/logout` is only called for explicit user-initiated logout.
- in-flight `fetchMe()` and `logout()` calls are deduplicated to avoid request storms.

## Notification UX Contract (Hybrid Routing)

Notification responses may include optional navigation metadata from backend:

- `preview_message?: string | null`
- `target_path?: string | null`
- `target_params?: Record<string, unknown> | null`

Preview rendering precedence in frontend:

1. `preview_message`
2. `message`
3. `data.message`
4. fallback: `Notifikasi baru`

Click behavior in Navbar dropdown and `/notifications` page:

1. mark as read when unread
2. navigate to destination using hybrid strategy:
   - backend `target_path` (+ `target_params`) first
   - fallback from `type` + `reference_id`

Current fallback destinations:

- `KPI_ASSIGNMENT` → `/staff/logbook?assignment_id=<id>`
- `LOGBOOK_SUBMITTED` → `/manager/reviews?logbook_id=<id>`
- `LOGBOOK_REVERTED` / `LOGBOOK_REVIEWED` → `/staff/history?logbook_id=<id>`
