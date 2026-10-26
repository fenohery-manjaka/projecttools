# 03 · Tâches : créer, voir et modifier une tâche

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 02 — **Taille** : L

## Contexte

Les écrans de base d'une tâche, sur le modèle des pages Projets (`Create`, `Show`, `Edit`).

**Création** (`/tasks/create`, avec `?project_id=` pour présélectionner un projet) et **modification** (`/tasks/{task}/edit`), mêmes champs :

- titre (obligatoire, 200 caractères max) ;
- projet : uniquement les projets où l'utilisateur peut créer une tâche ;
- jalon : facultatif, uniquement parmi les jalons du projet choisi ;
- statut, priorité ;
- responsables : uniquement des membres du projet ;
- échéance, estimation en heures (facultatives) ;
- description ;
- raison du blocage : **obligatoire si le statut est « Bloqué »**, vidée sinon.

**Page tâche** (`/tasks/{task}`) : titre, pastille du projet, statut, priorité, responsables, échéance (« En retard » si dépassée), jalon, estimation, description, raison du blocage mise en avant si bloquée. Boutons de statut rapides (À faire / En cours / Bloqué / Terminé) : passer à « Bloqué » ouvre une petite fenêtre qui demande la raison. Boutons « Modifier » et « Supprimer » (avec confirmation) selon les droits.

Règles :

- passer à « Terminé » remplit `completed_at`, en sortir le vide ;
- `created_by` = l'auteur ;
- création, changement de statut et suppression sont inscrits au journal (`RecordActivity`) ;
- la page projet reçoit un bouton « Nouvelle tâche » (projet présélectionné) si l'utilisateur peut en créer.

Routes :

```
GET    tasks/create          tasks.create          ->can('viewAny', Task::class)   création vérifiée par projet dans la requête
POST   tasks                 tasks.store           ->can('viewAny', Task::class)   + autorisation create(projet) dans TaskRequest
GET    tasks/{task}          tasks.show            ->can('view', 'task')
GET    tasks/{task}/edit     tasks.edit            ->can('update', 'task')
PUT    tasks/{task}          tasks.update          ->can('update', 'task')
PATCH  tasks/{task}/status   tasks.status.update   ->can('update', 'task')
DELETE tasks/{task}          tasks.destroy         ->can('delete', 'task')
```

Pour `create`/`store`, le projet vient de la requête : `TaskRequest::authorize()` vérifie `$this->user()->can('create', [Task::class, $project])`.

## Écran de référence

Prototype › Tâches › tiroir de détail d'une tâche (et la fenêtre « Nouvelle tâche »). Ici en page dédiée, voir les choix du README.

## Fichiers autorisés

```
+ app/Modules/Tasks/routes.php
+ app/Modules/Tasks/Http/Controllers/TaskController.php          create, store, show, edit, update, destroy
+ app/Modules/Tasks/Http/Controllers/TaskStatusController.php    update
+ app/Modules/Tasks/Http/Requests/TaskRequest.php
+ app/Modules/Tasks/Http/Requests/UpdateTaskStatusRequest.php
+ app/Modules/Tasks/Actions/CreateTask.php                       transaction, responsables, journal
+ resources/js/pages/tasks/Create.vue
+ resources/js/pages/tasks/Edit.vue
+ resources/js/pages/tasks/Show.vue
+ resources/js/components/tasks/TaskFields.vue
+ resources/js/components/tasks/TaskStatusButtons.vue
+ resources/js/components/tasks/BlockReasonDialog.vue
~ resources/js/pages/projects/Show.vue                           bouton « Nouvelle tâche »
~ app/Modules/Projects/Http/Controllers/ProjectController.php    show() : can.createTask
+ tests/Feature/Modules/Tasks/TaskTest.php
+ tests/Feature/Modules/Tasks/TaskStatusTest.php
```

## Base de données

Aucune (tables créées au ticket 02).

## Critères d'acceptation

- [ ] Créer une tâche depuis la page projet (projet présélectionné) et depuis `/tasks/create`.
- [ ] Un responsable qui n'est pas membre du projet, ou un jalon d'un autre projet, est refusé avec un message en français.
- [ ] Statut « Bloqué » sans raison : refusé côté serveur (pas seulement dans le formulaire).
- [ ] « Terminé » remplit `completed_at`, revenir à un autre statut le vide.
- [ ] Les boutons de statut rapides fonctionnent, et « Bloqué » demande la raison.
- [ ] Après une erreur de validation, les textes tapés (description, raison) sont conservés : utiliser `:default-value`, comme dans `ProjectFields.vue`.
- [ ] Observateur : pas de bouton Modifier/Supprimer ni de statut rapide, et 403 sur les routes correspondantes.
- [ ] Non-membre du projet : 403 sur la page tâche.
- [ ] Création, statut et suppression apparaissent dans `activity_log`.
- [ ] Tests feature : création (dont autorisation par projet), validation, mise à jour, statut, blocage, suppression, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Liste et tableau des tâches (04, 05), sous-tâches (06), commentaires (08).
- Minuteur et temps passé (lot 3), pièces jointes (lot 2).
