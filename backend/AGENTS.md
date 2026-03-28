# BACKEND KNOWLEDGE BASE

## OVERVIEW
Laravel 12 API backend for Logbook + KPI flows, using Sanctum auth, FormRequest validation, observer-driven summary recalculation, and API v1 route contracts.

## STRUCTURE
```text
backend/
├── app/Http/Controllers/Api/V1/   # API endpoints (auth, users, KPI, logbooks, summaries)
├── app/Http/Requests/Api/V1/      # Validation and authorization per action
├── app/Http/Resources/V1/         # Response shaping
├── app/Observers/                 # Triggers DailySummaryService sync
├── app/Services/                  # Domain services (summary recomputation)
├── routes/api.php                 # /api/v1 contract
└── tests/                         # Pest/PHPUnit feature + unit tests
```

## WHERE TO LOOK
| Task | Location | Notes |
|---|---|---|
| Add/change API endpoint | `app/Http/Controllers/Api/V1/*` | Match existing controller + request + resource pattern |
| Validate request payloads | `app/Http/Requests/Api/V1/*` | Prefer dedicated FormRequest classes |
| Adjust serialized response | `app/Http/Resources/V1/*` | Keep output format centralized |
| Investigate auth behavior | `app/Http/Controllers/Api/V1/AuthController.php`, `routes/api.php` | Login throttled, other auth routes Sanctum-protected |
| Debug summary drift | `app/Observers/*`, `app/Services/DailySummaryService.php` | Recalc happens from model observers |

## CONVENTIONS
- API version boundary is explicit (`Route::prefix('v1')` in `routes/api.php`).
- Most non-login endpoints live in `Route::middleware('auth:sanctum')` groups.
- Controllers orchestrate request→model/resource flow; summary recalculation is not controller-owned.
- Tests use Pest with Unit/Feature suites (`phpunit.xml` points to `tests/Unit` and `tests/Feature`).

## ANTI-PATTERNS
- Do not add ad-hoc unversioned API routes outside `/api/v1`.
- Do not duplicate validation inside controllers when FormRequest already exists.
- Do not move summary synchronization into controllers; keep observer-driven behavior.
- Do not reason from generated/cached artifacts under `storage/framework/*`.

## COMMANDS
```bash
cd backend && composer dev
cd backend && composer test
cd backend && php artisan test --compact
```

## NOTES
- `public/storage` may appear broken in scans; treat as symlink noise, not architecture signal.
- Scribe outputs under `.scribe/` are generated docs artifacts, not domain source.
