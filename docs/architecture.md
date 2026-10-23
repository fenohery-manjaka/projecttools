# Architecture de ProjectTools

À lire avant tout ticket. Le plan d'ensemble et les décisions sont dans [plan.md](plan.md).

## Pile technique

Laravel 13 · Inertia v3 + Vue 3 · Tailwind v4 · Fortify (connexion, 2FA, passkeys) · Wayfinder (routes typées côté front) · `spatie/laravel-permission` v8 · PHPUnit · PHPStan niveau 7 · SQLite en développement.

## Les modules

L'application est découpée en modules. Chaque module vit dans `app/Modules/<Nom>` et est déclaré dans [`config/modules.php`](../config/modules.php).

```
app/Modules/
├─ Core/          noyau, toujours actif : membres, rôles, paramètres, registre des modules
└─ Projects/      module de référence : à copier pour créer un module
```

### Forme d'un module

```
app/Modules/Ideas/
├─ IdeasModule.php                 la déclaration (voir ci-dessous)
├─ routes.php                      routes du module
├─ Models/
├─ Enums/
├─ Http/Controllers/
├─ Http/Requests/
├─ Policies/
├─ Actions/                        opérations métier réutilisables (une classe = une opération)
└─ Database/
   ├─ Migrations/                  chargées automatiquement
   └─ Factories/
resources/js/pages/ideas/          pages Inertia (Index.vue, Show.vue…)
resources/js/components/ideas/     composants propres au module
tests/Feature/Modules/Ideas/       tests du module
```

### La déclaration

Un module étend [`App\Modules\Core\Module`](../app/Modules/Core/Module.php) :

| Méthode          | Rôle                                                 | Exemple                                 |
| ---------------- | ---------------------------------------------------- | --------------------------------------- |
| `key()`          | clé technique : routes, permissions, table `modules` | `ideas`                                 |
| `name()`         | nom affiché                                          | `Idées`                                 |
| `isRequired()`   | jamais désactivable                                  | `false`                                 |
| `dependencies()` | clés des modules nécessaires                         | `['projects']`                          |
| `permissions()`  | `nom => libellé` affichés dans la matrice            | `'ideas.create' => 'Proposer une idée'` |
| `navigation()`   | entrées du menu latéral (`NavigationItem`)           | groupe Collaboration                    |
| `boot()`         | réglages propres au module (observers…)              | —                                       |

Le [`ModuleLoader`](../app/Modules/Core/ModuleLoader.php) charge pour chaque module ses migrations (`Database/Migrations`) puis ses routes (`routes.php`) dans les middlewares `web`, `auth`, `verified` et `module:<clé>`.

### Activer, désactiver

Le super admin le fait dans **Paramètres › Modules**. L'état est stocké dans la table `modules` (pas de ligne = activé). Le [`ModuleRegistry`](../app/Modules/Core/ModuleRegistry.php) refuse :

- de désactiver un module obligatoire ;
- de désactiver un module dont un autre module actif dépend ;
- d'activer un module dont une dépendance est désactivée.

Un module désactivé répond 404 sur toutes ses routes (même pour le super admin) et disparaît du menu.

## Contrôles d'accès

Chaque requête vers un module passe, dans l'ordre :

1. **Module actif ?** — middleware `module:<clé>` ([`EnsureModuleEnabled`](../app/Modules/Core/Http/Middleware/EnsureModuleEnabled.php)) → sinon 404.
2. **Super admin ?** — `Gate::before` dans [`CoreServiceProvider`](../app/Modules/Core/CoreServiceProvider.php) → tout est permis.
3. **Permission du rôle ?** — permission spatie, cochée dans **Paramètres › Rôles** → sinon 403.
4. **Membre du projet ?** — la policy du module → sinon 403, sauf permission `projects.view-all`.

Règles :

- **Toute route de module déclare `->can(...)`.** Le test [`ModuleRoutesAreProtectedTest`](../tests/Feature/Architecture/ModuleRoutesAreProtectedTest.php) fait échouer la CI sinon.
- Dans une policy, utiliser `$user->checkPermissionTo('ideas.create')` (renvoie `false` si la permission n'existe pas) plutôt que `hasPermissionTo` (qui lève une exception).
- Lier la policy au modèle avec l'attribut `#[UsePolicy(IdeaPolicy::class)]`, la factory avec `#[UseFactory(IdeaFactory::class)]`.
- Pour limiter une liste aux projets visibles : `Project::query()->visibleTo($user)`.
- Le gate `administer` est réservé au super admin (Paramètres › Rôles et Modules).

### Le super admin

Ce n'est pas un rôle : c'est la colonne `users.is_super_admin`, posée uniquement en ligne de commande :

```bash
php artisan projecttools:super-admin vous@exemple.mg
php artisan projecttools:super-admin vous@exemple.mg --revoke
```

Il ne peut être ni modifié ni désactivé depuis l'écran Membres.

### Permissions et rôles

- Les permissions sont **déclarées dans le code** (méthode `permissions()` du module). `php artisan projecttools:sync-permissions` les écrit en base et supprime les obsolètes. À lancer à chaque déploiement (le seeder le fait aussi).
- Les rôles et les cases cochées vivent **en base** et se règlent dans Paramètres › Rôles.
- Les 4 rôles de départ (Administrateur, Responsable, Contributeur, Observateur) sont créés par [`RoleSeeder`](../database/seeders/RoleSeeder.php), qui ne touche pas un rôle existant. Un nouveau module ajoute ses permissions aux listes de ce seeder.
- Clés en anglais (`ideas.create`, `projects.view-all`), libellés en français.

## Menu latéral

Construit côté serveur dans [`HandleInertiaRequests`](../app/Http/Middleware/HandleInertiaRequests.php) à partir des modules actifs, filtré par les droits de l'utilisateur, groupé en Travail / Collaboration / Pilotage. Côté Vue, [`NavMain.vue`](../resources/js/components/NavMain.vue) l'affiche ; l'icône est un nom Lucide résolu par [`navigationIcons.ts`](../resources/js/lib/navigationIcons.ts) (y ajouter l'icône d'un nouveau module).

## Données du socle

| Table                                                                                      | Contenu                                                                                        |
| ------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------- |
| `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` | spatie                                                                                         |
| `modules`                                                                                  | état activé / désactivé de chaque module                                                       |
| `project_user`                                                                             | membres d'un projet                                                                            |
| `activity_log`                                                                             | journal d'activité (action [`RecordActivity`](../app/Modules/Core/Actions/RecordActivity.php)) |
| `notifications`                                                                            | notifications Laravel                                                                          |
| `users`                                                                                    | + `is_super_admin`, `deactivated_at`                                                           |

Un membre désactivé est déconnecté à sa requête suivante ([`EnsureUserIsActive`](../app/Modules/Core/Http/Middleware/EnsureUserIsActive.php)).

## Créer un module (pas à pas)

1. Copier la forme de `app/Modules/Projects` dans `app/Modules/<Nom>`.
2. Écrire `<Nom>Module.php` : clé, nom, dépendances, permissions, menu.
3. L'ajouter dans `config/modules.php`.
4. Ajouter ses permissions aux rôles de départ dans `RoleSeeder`, et son icône dans `navigationIcons.ts`.
5. Routes dans `routes.php`, chacune avec `->can(...)`.
6. Tests dans `tests/Feature/Modules/<Nom>/`.
7. `composer ci:check` doit passer.
