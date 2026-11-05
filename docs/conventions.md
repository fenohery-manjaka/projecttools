# Conventions

Nouveau sur le projet ? Commencez par le guide pas à pas `documentation/guide-demarrage.html` (installation, lancement, chemin complet jusqu'à la pull request), à ouvrir dans un navigateur, puis par le ticket d'essai `docs/tickets/essai/00-connexion-en-francais.md`.

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
- **Statuts** : jamais écrits en dur. Les statuts de projet et de tâche sont des lignes en base (`project_statuses`, `task_statuses`), réglées dans Paramètres. Le code s'appuie sur leur **comportement** (`category` : terminé, bloqué…), jamais sur leur nom ni leur identifiant. Voir `docs/architecture.md`, « Statuts réglables ».

## Créer dans une modale

**Règle** : créer un élément (projet, tâche, et tout nouvel élément des lots suivants) se fait dans une **modale ouverte par-dessus l'écran courant**, jamais sur une page `…/create`. On ne perd ni la liste, ni les filtres, ni le tableau qu'on regardait. La **modification** garde sa page (`…/{id}/edit`).

Comment faire :

1. Pas de route `GET …/create`. La page qui porte le bouton « Nouveau… » envoie les options du formulaire (listes de projets, de membres…) dans une prop **optionnelle**, chargée seulement à l'ouverture :

    ```php
    'taskForm' => Inertia::optional(fn (): array => $taskFormOptions->for($user)),
    ```

2. Côté Vue, la modale s'appuie sur [`FormDialog.vue`](../resources/js/components/FormDialog.vue) (titre, chargement des options, défilement sur petit écran) et contient un `<Form>` Wayfinder. Exemples : [`CreateProjectDialog.vue`](../resources/js/components/projects/CreateProjectDialog.vue), `CreateTaskDialog.vue`.
3. Une erreur de validation garde la modale ouverte avec la saisie (le `<Form>` préserve l'état de la page).
4. Après succès : rester sur l'écran courant (`back()` + message), sauf si l'élément créé demande d'être configuré tout de suite (un projet ouvre sa page).
5. Test : la route `…/create` répond 404, et la prop optionnelle n'est envoyée qu'à la demande (voir `ProjectTest`).

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
