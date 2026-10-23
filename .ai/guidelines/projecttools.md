# ProjectTools — règles du projet

Internal project-management platform. Read `docs/architecture.md` and `docs/conventions.md` before changing code.

## Modules

- The app is split into modules under `app/Modules/<Name>`, listed in `config/modules.php`. `app/Modules/Projects` is the reference module: copy its structure.
- A module class extends `App\Modules\Core\Module` and declares `key()`, `name()`, `dependencies()`, `permissions()` (name => French label) and `navigation()`.
- Module routes live in `app/Modules/<Name>/routes.php`. They are loaded automatically inside `web`, `auth`, `verified` and `module:<key>`. Do not register them elsewhere.
- Migrations go in `app/Modules/<Name>/Database/Migrations`, factories in `Database/Factories`. Bind them to the model with `#[UseFactory]`, and the policy with `#[UsePolicy]`.
- Inertia pages go in `resources/js/pages/<module>/`. Use Wayfinder imports (`@/actions/...`, `@/routes/...`), never hard-coded URLs.

## Authorization

- Every module route MUST call `->can(...)`. `tests/Feature/Architecture/ModuleRoutesAreProtectedTest.php` fails otherwise.
- Check permissions, never role names. In policies use `$user->checkPermissionTo('ideas.create')`.
- The super admin is `users.is_super_admin` (set by `php artisan projecttools:super-admin`), not a role. It passes every gate through `Gate::before`.
- Restrict project lists with `Project::query()->visibleTo($user)`.
- New permissions: declare them in the module class, add them to the default roles in `database/seeders/RoleSeeder.php`, run `php artisan projecttools:sync-permissions`.

## Scope

- Only touch the files listed in the ticket. `app/Modules/Core`, `config/`, `bootstrap/` and `routes/` belong to the lead developer: do not change them without being asked.
- User-facing text is French; code, routes and permission keys are English.
- Run `composer ci:check` before opening a pull request.
