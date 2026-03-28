# ROUTES KNOWLEDGE BASE

## OVERVIEW
`src/routes` is organized by route groups: auth entry, protected app routes, and mobile shell routes.

## STRUCTURE
```text
src/routes/
├── (auth)/                # login and unauthenticated entry flow
├── (app)/                 # protected desktop/web role routes
│   ├── admin/
│   ├── manager/
│   ├── staff/
│   └── superadmin/
├── (mobile)/              # /m/* mobile-first shell
└── +layout.ts             # global static behavior (prerender + no SSR)
```

## WHERE TO LOOK
| Task | Location | Notes |
|---|---|---|
| Root rendering mode | `+layout.ts` | `prerender=true`, `ssr=false` |
| Auth gate entry | `(auth)/login/*` | Login flow and redirect decisions |
| Protected web pages | `(app)/*` + `(app)/+layout.ts` | Guarded via `enforceAppRouteGuard()` |
| Protected mobile pages | `(mobile)/*` + `(mobile)/+layout.ts` | Same guard, mobile navigation shell |
| Role route mismatches | `(app)/admin|manager|staff|superadmin/*` | Verify against permission matrix |

## CONVENTIONS
- Keep role/path authorization in shared guard utilities, not per-page ad-hoc checks.
- Use `+page.ts` for initial data bootstrapping where already patterned.
- Keep route groups semantic (auth/app/mobile) and avoid flattening role routes.

## ANTI-PATTERNS
- Do not bypass group layout guards with manual redirect logic inside every page.
- Do not mix mobile and desktop route concerns in the same route subtree.
- Do not treat group folder names as URL path segments.

## NOTES
- Superadmin has explicit legacy redirect behavior for old admin audit-log path mappings.
