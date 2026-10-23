# ProjectTools — Plan de construction (validé)

> Validé le 23 octobre 2026. Diaporama de présentation : https://claude.ai/artifact/Y3YWDQQ2YwKXT9PCMkEDE3 (privé, à partager depuis la page).
> Source fonctionnelle : le prototype HTML (13 écrans, rôles écrits en dur), à reprendre sous forme modulaire.

## Décisions

| #   | Question                    | Décision                                                                                                       |
| --- | --------------------------- | -------------------------------------------------------------------------------------------------------------- |
| 1   | Rôle global ou par projet ? | Rôle global pour les droits ; appartenance au projet pour ce qu'on voit (sauf permission `projects.view-all`). |
| 2   | Où vivent les modules ?     | `app/Modules/<Nom>`, structure maison légère, sans paquet de modules.                                          |
| 3   | Outil de permissions        | `spatie/laravel-permission` v8 (compatible Laravel 13).                                                        |
| 4   | Suivi des tickets           | GitHub Issues + un tableau GitHub Project (`gh` à installer sur le poste de l'expert).                         |
| 5   | Modules obligatoires        | Projets et Tâches ; tous les autres sont activables.                                                           |
| 6   | Langue des permissions      | Clés en anglais (`ideas.create`), libellés affichés en français.                                               |
| 7   | Base de production          | **À trancher** avant la première mise en ligne : MySQL ou PostgreSQL. SQLite en développement.                 |

## Architecture

### Noyau et modules

- **Noyau** (`app/Modules/Core`, jamais désactivé) : membres et invitations, rôles et permissions, paramètres et modules, menu construit depuis les modules, Accueil / Mes tâches / Calendrier, journal d'activité, notifications.
- **Modules** :

| Module    | Statut                               | Dépend de              |
| --------- | ------------------------------------ | ---------------------- |
| Projets   | Toujours actif — module de référence | —                      |
| Tâches    | Toujours actif                       | Projets                |
| Idées     | Activable                            | Projets                |
| Décisions | Activable                            | Projets                |
| Réunions  | Activable                            | Tâches                 |
| Documents | Activable                            | Projets                |
| Temps     | Activable                            | Tâches                 |
| Budgets   | Activable                            | Projets                |
| Rapport   | Activable                            | lit les modules actifs |

Le registre refuse de désactiver un module dont un autre module actif dépend. Un module désactivé disparaît du menu et ses routes répondent 404.

### Forme d'un module

```
app/Modules/Ideas/
├─ IdeasModule.php          déclare : clé, nom, dépendances, permissions, menu, routes
├─ Models/
├─ Http/Controllers/
├─ Http/Requests/
├─ Policies/
├─ Actions/
├─ Database/Migrations/
└─ routes.php               préfixe /ideas, middleware module:ideas
resources/js/pages/ideas/
tests/Feature/Modules/Ideas/
```

Règle de travail : **un ticket = un dossier de module**.

### Contrôles d'accès (dans cet ordre)

1. **Module actif ?** — middleware `module:<clé>` → sinon 404, absent du menu.
2. **Super admin ?** — `Gate::before` → tout est permis (sauf module désactivé).
3. **Permission du rôle ?** — permission spatie, réglée dans Paramètres › Rôles → sinon 403.
4. **Membre du projet ?** — Policy du module → sinon 403, sauf `projects.view-all`.

### Données ajoutées par le socle

```
+ roles · permissions · model_has_roles · role_has_permissions · model_has_permissions   (spatie)
+ modules        — clé, actif, réglages
+ project_user   — membres d'un projet
+ activity_log
+ notifications  — table Laravel
~ users          — + is_super_admin
```

- Permissions : déclarées dans le code de chaque module, écrites en base par `php artisan projecttools:sync-permissions`.
- Rôles et cases cochées : en base, modifiés dans Paramètres › Rôles. Les 4 rôles du prototype sont créés au départ (Administrateur, Responsable, Contributeur, Observateur).
- Super admin : `php artisan projecttools:super-admin <email>`, jamais depuis l'écran.

## Cadre de travail (expert / exécutant)

1. L'expert écrit le ticket (Issue GitHub, gabarit « Tâche », jalon du lot).
2. L'exécutant se l'assigne, colonne « En cours ».
3. Il crée sa branche : `lot1/42-sous-taches`.
4. Il ouvre la PR : « Closes #42 », gabarit de PR rempli.
5. La CI vérifie : `composer ci:check` (Pint, ESLint, types, PHPStan, tests).
6. L'expert relit et fusionne (squash) ; ticket « Fait ».

Tableau : À faire → En cours → En revue → Fait. Un jalon par lot, étiquettes module + type.

Un ticket contient : contexte, écran de référence du prototype, fichiers autorisés, base de données, critères d'acceptation, hors périmètre.

Garde-fous : CI obligatoire (dont un test qui refuse toute route de module sans contrôle d'accès), CODEOWNERS sur le noyau, `docs/architecture.md` et `docs/conventions.md`, consignes IA dans `.ai/guidelines/projecttools.md` (lues par Laravel Boost), module Projets comme exemple à copier.

## Lots

### Lot 0 — Socle (expert, avec Claude)

Ce qu'on verra : Paramètres › Rôles (matrice rôles × permissions), Paramètres › Modules (interrupteurs + dépendances), Paramètres › Membres (inviter, rôle, désactiver), menu latéral construit depuis les modules actifs, couleurs et polices du prototype, module Projets (liste, création, membres).

Fichiers : `app/Modules/Core/`, `app/Modules/Projects/`, `app/Models/User.php`, `app/Providers/AppServiceProvider.php`, `bootstrap/providers.php`, `routes/settings.php`, `config/modules.php`, `config/permission.php`, `resources/js/pages/settings/`, `resources/js/pages/projects/`, `AppSidebar.vue`, `NavMain.vue`, `resources/css/app.css`, `docs/`, `.ai/guidelines/`, `.github/`.

**Critères de fin**

- [ ] `composer ci:check` passe, en local et sur GitHub Actions.
- [ ] Tests : le super admin passe partout ; un rôle sans la permission reçoit 403 ; un module désactivé répond 404 et quitte le menu.
- [ ] Navigateur : décocher une case dans Paramètres › Rôles retire l'accès à un compte de test.
- [ ] GitHub : `docs/architecture.md`, gabarits de ticket et de PR, tickets du lot 1 créés.

### Lot 1 — Travail (exécutant, ≈ 12 à 15 tickets)

Tâches en Kanban et en liste ; tiroir de tâche (sous-tâches, assignés, priorité, échéance) ; commentaires avec @mentions qui notifient ; Mes tâches ; Accueil et Calendrier ; Projets complets (jalons, progression, archivage).
Base : `tasks`, `task_user`, `subtasks`, `milestones`, `comments` (polymorphe).

### Lot 2 — Collaboration (exécutant, ≈ 14 à 18 tickets)

Idées (critères, score, votes, « Transformer en projet ») ; Décisions (options pour/contre, décideurs, verdict) ; Réunions (ordre du jour, chrono, compte rendu, actions → tâches) ; Documents (fichiers, liens, notes ; accès « tous » ou « réservé » via `documents.view-restricted`).
Base : `ideas`, `idea_votes`, `decisions`, `decision_options`, `decision_user`, `meetings`, `meeting_items`, `meeting_user`, `documents`.

### Lot 3 — Pilotage (exécutant, ≈ 10 à 12 tickets)

Chrono (un seul actif par personne, côté serveur), saisie manuelle et semaine ; Budgets (enveloppe par projet, dépenses par nature, reste en Ariary) ; Rapport d'activité ; Équipe (charge, retards).
Base : `time_entries`, `running_timers`, `expenses`, `projects.budget`.

## Risques

| Risque                                                          | Parade                                                                              |
| --------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| Protection de `main` indisponible (dépôt privé, compte gratuit) | À vérifier ; sinon CODEOWNERS et aucun push direct sur `main`.                      |
| Désactiver un module dont un autre dépend                       | Le registre refuse et nomme le module qui en dépend.                                |
| Route de module sans contrôle d'accès                           | Un test parcourt toutes les routes de modules.                                      |
| Super admin perdu ou désactivé                                  | `projecttools:super-admin`, jamais depuis l'écran.                                  |
| Le prototype garde tout dans le navigateur                      | Chrono, notifications, mentions refaits côté serveur ; tickets estimés plus larges. |
| `gh` absent sur le poste                                        | L'installer puis `gh auth login`.                                                   |
