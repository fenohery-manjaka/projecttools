# Conventions

## Travailler sur un ticket

1. Prendre un ticket assigné sur le tableau GitHub et le passer en « En cours ».
2. Créer une branche depuis `main` : `lot<numéro>/<numéro-du-ticket>-<sujet-court>`, par exemple `lot1/42-sous-taches`.
3. Ne modifier **que les fichiers listés** dans le ticket. Un fichier en plus ? Le signaler dans la PR et expliquer pourquoi.
4. Ouvrir une pull request qui contient `Closes #42` et remplir le gabarit.
5. La CI doit être verte. Ne jamais pousser directement sur `main`.

## Avant d'ouvrir la PR

```bash
composer ci:check
```

Cette commande lance le formatage et le lint front, la vérification des types TypeScript, PHPStan et les tests. Pour corriger le formatage :

```bash
composer lint
npm run check:fix
```

## Code

- **Langue** : code, noms de classes, clés de permission et de route en anglais ; textes affichés, libellés et messages en français.
- **Contrôleurs** fins : la validation va dans une Form Request, une opération métier réutilisée va dans `Actions/`.
- **Autorisation** : sur la route avec `->can(...)`, la logique dans la policy. Jamais de vérification de rôle par son nom (`hasRole('Responsable')`) : on vérifie une permission.
- **Pages Inertia** : `resources/js/pages/<module>/`. Les liens et formulaires passent par Wayfinder (`@/actions/...`, `@/routes/...`), jamais une URL écrite en dur.
- **Requêtes** : charger les relations dont la page a besoin (`with`, `withCount`), pas de requête dans une boucle.
- **Migrations** : dans `app/Modules/<Nom>/Database/Migrations`, clés étrangères avec `constrained()`.

## Tests

- Un fichier de test par écran ou par opération, dans `tests/Feature/Modules/<Nom>/`.
- Toujours tester le refus d'accès (403) en plus du cas qui marche.
- Les rôles de départ sont disponibles avec `$this->seed(RoleSeeder::class)` ; le trait `Tests\Feature\Modules\Core\InteractsWithModules` fournit `userWithRole()` et `userWithPermissions()`.

## Données de test locales

`php artisan migrate:fresh --seed` crée :

- `test@example.com` : super admin ;
- `contributeur@example.com` : rôle Contributeur.

Mot de passe des deux comptes : `password`.

## Assistants IA

Les règles du projet pour les assistants (Claude Code, Cursor…) sont dans `.ai/guidelines/projecttools.md`. Laravel Boost les intègre quand il génère `CLAUDE.md` / `AGENTS.md` chez chacun (`php artisan boost:install` ou `boost:update`).
