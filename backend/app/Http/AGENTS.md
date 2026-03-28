# HTTP LAYER KNOWLEDGE BASE

## OVERVIEW
`app/Http` is the API edge: request validation, controller orchestration, resource serialization, and middleware aliases.

## STRUCTURE
```text
app/Http/
├── Controllers/Api/V1/      # API endpoint handlers
├── Requests/Api/V1/         # FormRequest validation/authorization
├── Resources/V1/            # JsonResource response shaping
└── Middleware/              # Custom middleware (role alias wired in bootstrap)
```

## WHERE TO LOOK
| Task | Location | Notes |
|---|---|---|
| Add endpoint behavior | `Controllers/Api/V1/*.php` | Keep orchestration thin; defer data logic to models/services |
| Add payload validation | `Requests/Api/V1/*.php` | Prefer dedicated FormRequest over inline validation |
| Change API response shape | `Resources/V1/*.php` | Keep output contracts centralized |
| Route protection concerns | `Middleware/*` + `bootstrap/app.php` | Middleware aliases are registered in bootstrap |

## CONVENTIONS
- API surface is versioned at `/api/v1`; controller namespace mirrors that boundary.
- Prefer one request class per write action (`Store*`, `Update*`, `Review*`, etc.).
- Keep resources responsible for output formatting, not business rules.

## ANTI-PATTERNS
- Do not put heavy business calculations directly in controllers.
- Do not duplicate validation rules inside controllers and requests.
- Do not return ad-hoc arrays when a matching `Resources/V1` class exists.

## NOTES
- Summary recalculation is observer-driven (`app/Observers/*`), not initiated by controllers.
