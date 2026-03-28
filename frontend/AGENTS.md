# FRONTEND KNOWLEDGE BASE

## OVERVIEW
SvelteKit 5 frontend (TypeScript) for role-based web and mobile experiences, using Svelte runes, centralized auth guard logic, and API service modules under `src/lib/api`.

## STRUCTURE
```text
frontend/
├── src/routes/                 # Route groups: (auth), (app), (mobile)
├── src/lib/auth/               # Guard + role/path permission matrix
├── src/lib/stores/             # Stateful app stores (auth, logbook, notifications)
├── src/lib/api/services/       # HTTP endpoint wrappers
├── src/lib/api/schemas/        # Valibot request/response schemas
└── src/lib/components/         # Shared UI, navigation, mobile components
```

## WHERE TO LOOK
| Task | Location | Notes |
|---|---|---|
| Route access failures | `src/lib/auth/route-guards.ts`, `src/lib/auth/permissions.ts` | Centralized gate + redirects |
| Login/session bugs | `src/lib/stores/auth.svelte.ts`, `src/routes/(auth)/login/*` | Auth state and redirect behavior |
| Role navigation/menu issues | `src/lib/auth/permissions.ts`, `src/lib/components/navigation/*` | Links map to normalized role |
| API payload validation | `src/lib/api/schemas/*` | Valibot schemas mirror contracts |
| Service endpoint wiring | `src/lib/api/services/*` | Keep endpoint paths centralized |

## CONVENTIONS
- Route groups are organizational only: `(auth)`, `(app)`, `(mobile)` shape layout/ownership, not URL segments.
- Root layout is static-style (`prerender=true`, `ssr=false`), while protected app/mobile layouts enforce auth guard.
- Always normalize role before route checks; avoid ad-hoc page-level role branching.
- Keep request/response transforms in API services/utilities, not inside page markup blocks.

## ANTI-PATTERNS
- Do not use Svelte 4 reactivity patterns (`$:` and `export let`) in new code.
- Do not call remote `/auth/logout` during unauthorized revalidation flows.
- Do not implement duplicate role checks in pages when guard utilities already enforce access.
- Do not rely on `.svelte-kit/*` outputs for architecture reasoning.

## COMMANDS
```bash
cd frontend && pnpm dev
cd frontend && pnpm test
cd frontend && pnpm check && pnpm lint
```

## NOTES
- Frontend includes role-aware legacy redirect from `/admin/audit-logs` to `/superadmin/audit-logs`.
- Larger route files exist in admin/mobile modules; prefer extracting reusable logic to `src/lib/*` when extending features.
