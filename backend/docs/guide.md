# Project Guidelines & Superpowers

- **Framework**: Laravel 12. Use `Application::configure()->withMiddleware()` in `bootstrap/app.php` instead of `app/Http/Kernel.php`.
- **Typing**: Strict explicit return types and parameter type hints. Use PHP 8 constructor property promotion.
- **Code Style**: Run `vendor/bin/pint --dirty` before finishing. Follow standard conventions.
- **Database**: Use Eloquent relationships properly. Avoid raw queries or `DB::`. Address N+1 problems via eager loading. *AI Warning: Before applying the `SoftDeletes` trait to any Eloquent model, you MUST check its corresponding migration file to ensure `$table->softDeletes()` actually exists. Otherwise, it will cause an immediate SQL Exception.*
- **Testing**: Use Pest for all feature and unit tests.
- **Tools**: Rely on Laravel Boost tools (Artisan, Tinker, Search Docs). Use sub-agents for specialized tasks like writing migrations or building specific controllers.

## Seeded Data & UI Testing
Refer to [Seeded Data](seeded_data.md) for a list of default accounts (Admin, Manager, Staff) and historical mock data created to simplify frontend development.
