# AI Developer Guide: Logbook & KPI Project

This guide provides explicit instructions for future AI sessions working on this Svelte 5 + Laravel Logbook & KPI project. Adhere strictly to these conventions to maintain architectural consistency and high code quality.

---

## 1. Tech Stack Overview

- **Frontend Framework:** Svelte 5 (via SvelteKit)
- **Programming Language:** TypeScript
- **Styling:** Tailwind CSS v4 + DaisyUI
- **State Management:** Svelte 5 Runes (`$state`, `$derived`, `$effect`, `$props`)
- **Maps/Location:** `leaflet` and `@types/leaflet` (via SSR-safe custom wrapper `Map.svelte`)
- **Backend/API:** Laravel 11+ (REST API with Sanctum/JWT)
- **Utilities:** Runed (for reactive Svelte 5 utilities `PersistedState`), Valibot (for API payload schemas)

---

## 2. Svelte 5 Runes Conventions

**CRITICAL:** Svelte 4 reactivity is explicitly deprecated in this project.

- **DO NOT** use Svelte 4 reactivity (e.g., `let foo = ...`, `$: bar = foo * 2`, `export let baz`).
- **ALWAYS** use Svelte 5 Runes:
  - **State (`$state`):** Use for reactive variables.
    ```svelte
    <script lang="ts">
    	let count = $state(0);
    </script>
    ```
  - **Computed (`$derived`):** Use for values derived from state.
    ```svelte
    <script lang="ts">
    	let doubled = $derived(count * 2);
    </script>
    ```
  - **Side Effects (`$effect`):** Use for reactive side effects (DOM manipulation, fetching when state changes).
  - **Props (`$props`):** Use for component inputs.
    ```svelte
    <script lang="ts">
    	let { title, items = [] } = $props<{ title: string; items?: any[] }>();
    </script>
    ```

---

## 3. Global State & Context

- Create centralized state modules in `src/lib/stores/` using Svelte 5 runes and `runed`'s `PersistedState` for things like auth tokens.
- Export classes that encapsulate state, rather than using traditional Svelte stores (`writable`, `readable`).
- **Example:**

  ```ts
  // src/lib/stores/auth.svelte.ts
  import { PersistedState } from 'runed';

  export class AuthStore {
  	user = new PersistedState<any | null>('auth-user', null);
  	token = new PersistedState<string | null>('auth-token', null);

  	get isAuthenticated(): boolean {
  		return !!this.token.current;
  	}
  	// ... methods for login/logout
  }
  export const auth = new AuthStore();
  ```

---

## 4. Styling: Tailwind v4 + DaisyUI

- **Component Classes:** Use DaisyUI component classes (e.g., `btn`, `btn-primary`, `card`, `input`, `modal`) as the primary styling method to ensure consistent UI across the app.
- **Utility Classes:** Use Tailwind utility classes for layout, spacing, typography, and specific overrides.
- **V4 Architecture:** Tailwind v4 uses a CSS-first configuration model. Rely on standard `@theme` configurations in the main CSS file rather than a legacy `tailwind.config.js` unless specifically instructed otherwise by the environment setup.

---

## 5. API & Error Handling (Frontend)

- **Centralized Client:** Use a centralized API client (`src/lib/api/client.ts`) utilizing `fetch` or a lightweight wrapper like `axios`.
- **Authentication:** Attach Bearer tokens (from Laravel) automatically via request interceptors.
- **Data Pagination:** ALL list endpoints (`GET /api/v1/...`) return a standard Laravel paginated response structure (`PaginatedResponse<T>`). **ALWAYS** extract data from `response.data.data` (not `response.data` or `response`) when dealing with lists, and pass `response.data.meta` to the `<Pagination />` component.
- **Error Handling Standardization:**
  - Catch API errors globally.
  - Display user-friendly toasts/alerts for `4xx` (Validation) and `5xx` (Server) errors.
  - Handle `401 Unauthorized` by clearing local state (`token`, `user`) and redirecting to `/login`.
  - Handle `403 Forbidden` by showing an "Access Denied" view or toast.

---

## 6. Strict Directory Structure Conventions

Always place files in their designated locations to maintain order:

- `src/routes/`: SvelteKit file-based routing. Group logic by roles using path groups (e.g., `(app)/(admin)/...`, `(app)/(staff)/...`).
- `src/lib/components/`: Reusable, generic UI components (Buttons, Modals, DataTables).
- `src/lib/components/layout/` and `src/lib/components/navigation/`: Structural components like Header, Sidebar, Navbar.
- `src/lib/stores/`: Svelte 5 global state OOP modules using `runed` (e.g., `auth.svelte.ts`, `logbook.svelte.ts`).
- `src/lib/types/`: TypeScript interfaces mapping directly to Laravel DBML models (e.g., `User`, `Logbook`, `KPIMaster`).
- `src/lib/api/schemas/`: Valibot schemas for robust API payload/response parsing.
- `src/lib/api/services/`: API wrapper, endpoint definitions, and service calls.
- `docs/API_FLOW_TESTING.md`: Refer to this file for exact API Request and Response structures returned by the backend.

---

## 7. Development Workflow for AI

When instructed to build a feature, follow these steps in order:

1. **Understand Context:** Read this `AI_GUIDE.md` and `SYSTEM_DESIGN.md` before generating code to ensure alignment with the architectural vision.
2. **Types First:** Define TypeScript interfaces in `src/lib/types/` corresponding to the Laravel DBML before building components or API calls.
3. **Component Approach:**
   - Build small, pure UI components in `src/lib/components/`.
   - Compose them in `src/routes/+page.svelte` or layout files.
   - Handle data fetching in SvelteKit `+page.ts` / `+page.server.ts` for SSR/hydration, or via client-side API services if highly interactive.
4. **Testing & Resilience:** Write clean, modular code. Anticipate null states, loading states, and error states for all asynchronous operations.

---

## 8. Map & Location Visualizations

- **STRICT REQUIREMENT:** You MUST use `leaflet` and `@types/leaflet` for **all** map, GPS, or location-based features.
- When a user asks to implement a map, display a location, or show GPS coordinates, always import and utilize the reusable `src/lib/components/ui/Map.svelte` component. This custom wrapper correctly handles Leaflet's SSR (Server Side Rendering) constraints in SvelteKit by lazily instantiating the map `onMount`.

---

## 9. Backend (Laravel) Reminders for Context

_If making assumptions about the API behavior, remember:_

- The API uses Form Requests for validation.
- Endpoints return standard API Resources (`JsonResource`).
- Mutations affecting multiple tables (e.g., `LogbookController@submit`) must be wrapped in Database Transactions.
- The system heavily relies on `audit_logs` and `notifications`—frontend must gracefully handle and display these async side-effects.

## 10. Notification & Auth Data-Flow Rules (Important)

### Notification display + navigation

- Always use notification preview precedence:
  1) `preview_message`
  2) `message`
  3) `data.message`
  4) fallback text (`Notifikasi baru`)
- Do not render raw `JSON.stringify` for notification content in UI.
- On notification click (both navbar dropdown and full notifications page):
  1) mark read if unread
  2) navigate to related route.
- Navigation strategy is hybrid:
  - use backend `target_path` + `target_params` first
  - fallback by `type` + `reference_id`.

### Auth revalidation behavior

- `fetchMe()` unauthorized (`401`) must clear local session only.
- Do not call remote `/auth/logout` from unauthorized revalidation path.
- Call `/auth/logout` only for explicit user logout action.
- Deduplicate concurrent `fetchMe()` / `logout()` operations to avoid repeated `/auth/me` and `/auth/logout` requests.
