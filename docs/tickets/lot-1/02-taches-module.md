# 02 · Tâches : module, modèle et permissions

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant, relecture renforcée (config, seeder) — **Dépend de** : 01 — **Taille** : M

## Contexte

Créer le module `app/Modules/Tasks` en copiant la forme du module Projets (voir `docs/architecture.md`, « Créer un module »). Ce ticket ne crée **aucun écran** : il pose le modèle, les droits et les règles de visibilité sur lesquels s'appuient les tickets 03 à 15.

Déclaration `TasksModule` :

- clé `tasks`, nom « Tâches », description « Tâches, responsables, échéances et sous-tâches. » ;
- obligatoire (`isRequired()` → `true`, décision 5) ;
- dépend de `projects` ;
- permissions (clé → libellé) :
    - `tasks.view` → « Voir les tâches des projets visibles »
    - `tasks.create` → « Créer une tâche »
    - `tasks.update` → « Modifier une tâche »
    - `tasks.delete` → « Supprimer une tâche »
    - `tasks.comment` → « Commenter une tâche »
- pas encore d'entrée de menu (elle arrive au ticket 04).

Énumérations, sur le modèle de `ProjectStatus` (méthodes `label()` et `options()`) :

- `TaskStatus` : `todo` « À faire », `doing` « En cours », `blocked` « Bloqué », `done` « Terminé » ;
- `TaskPriority` : `urgent` « Urgente », `high` « Haute », `normal` « Normale », `low` « Basse », avec une méthode `rank()` (0 = urgente) pour trier.

Modèle `Task` :

- relations `project()`, `milestone()`, `assignees()` (table `task_user`), `creator()` ;
- accesseur `is_late` : échéance passée et statut différent de « Terminé » ;
- scope `visibleTo(User $user)` : tâches dont le projet est visible (`Project::visibleTo`) **et non archivé** (`projects.archived_at` nul).

`TaskPolicy` (vérifie toujours une permission, puis la visibilité du projet via `$user->can('view', $task->project)`) :

- `viewAny` : `tasks.view` ;
- `view` : `tasks.view` et projet visible ;
- `create(User, Project)` : `tasks.create` et projet visible ;
- `update`, `delete`, `comment` : la permission correspondante et projet visible.

Ajouter la relation `Project::tasks()` (les deux modules sont obligatoires : Projets peut connaître Tâches).

## Écran de référence

Aucun écran. Données : prototype, fonction `T(...)` du jeu de données (titre, statut, responsables, échéance, priorité, description, jalon, raison du blocage, estimation, ordre, date de fin).

## Fichiers autorisés

```
+ app/Modules/Tasks/TasksModule.php
+ app/Modules/Tasks/Enums/TaskStatus.php
+ app/Modules/Tasks/Enums/TaskPriority.php
+ app/Modules/Tasks/Models/Task.php
+ app/Modules/Tasks/Policies/TaskPolicy.php
+ app/Modules/Tasks/Database/Migrations/…_create_tasks_table.php
+ app/Modules/Tasks/Database/Factories/TaskFactory.php
~ app/Modules/Projects/Models/Project.php        relation tasks()
~ config/modules.php                             ajouter TasksModule après ProjectsModule
~ database/seeders/RoleSeeder.php                permissions des rôles de départ (tableau du README)
+ tests/Feature/Modules/Tasks/TaskPolicyTest.php
+ tests/Feature/Modules/Tasks/TaskVisibilityTest.php
```

## Base de données

```
+ tasks
    id
    project_id      FK projects, cascadeOnDelete
    milestone_id    FK milestones, nullable, nullOnDelete
    title           string(200)
    description     text, nullable
    status          string, défaut 'todo', index
    priority        string, défaut 'normal'
    due_on          date, nullable, index
    estimate_hours  decimal(5,1), nullable
    blocked_reason  text, nullable
    position        unsignedInteger, défaut 0      ordre dans une colonne du Kanban
    completed_at    timestamp, nullable
    created_by      FK users, nullable, nullOnDelete
    timestamps

+ task_user                                       responsables d'une tâche
    task_id         FK tasks, cascadeOnDelete
    user_id         FK users, cascadeOnDelete
    timestamps
    primary (task_id, user_id), index user_id
```

## Critères d'acceptation

- [ ] Paramètres › Modules affiche « Tâches · Toujours actif · Requiert : Projets ».
- [ ] Après `php artisan projecttools:sync-permissions`, les 5 permissions apparaissent dans la matrice sous « Tâches », avec leurs libellés français.
- [ ] Sur une base neuve (`migrate:fresh --seed`), les rôles de départ ont les permissions du tableau du README.
- [ ] `Task::visibleTo($user)` exclut les tâches des projets non visibles et des projets archivés.
- [ ] `TaskPolicy` testée : permission absente → refus ; permission présente mais projet invisible → refus ; les deux → accord ; super admin → accord.
- [ ] `is_late` testé (échéance passée / aujourd'hui / terminée).
- [ ] Policy et factory liées par `#[UsePolicy]` et `#[UseFactory]`.
- [ ] La PR rappelle, pour une base en service : lancer la synchronisation des permissions et cocher les nouvelles cases dans la matrice.
- [ ] `composer ci:check` vert (dont `ModuleRoutesAreProtectedTest`).

## Hors périmètre

- Tout écran et toute route (tickets 03 et suivants).
- Sous-tâches (06), commentaires (08).
