# 06 · Tâches : sous-tâches

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 03 — **Taille** : S

## Contexte

Une tâche se découpe en petites étapes à cocher (« Sous-tâches et vérifications » dans le prototype). Sur la page tâche, une section liste les sous-tâches dans l'ordre, avec :

- une case à cocher par sous-tâche ;
- un champ « Ajouter une sous-tâche » (Entrée pour valider) ;
- modifier le texte et supprimer une sous-tâche (petit bouton) ;
- un compteur « 2/5 » dans le titre de la section.

Quand le ticket 05 est fait, la carte du Kanban affiche aussi « 2/5 ». Sinon, le prévoir dans `TaskCard.vue` dès qu'il existe.

Gérer les sous-tâches demande `->can('update', 'task')`. Routes imbriquées avec `->scopeBindings()` :

```
POST   tasks/{task}/subtasks              tasks.subtasks.store
PATCH  tasks/{task}/subtasks/{subtask}    tasks.subtasks.update     texte ou case cochée
DELETE tasks/{task}/subtasks/{subtask}    tasks.subtasks.destroy
```

Les actions ne rechargent pas toute la page : `preserveScroll`, et la case cochée réagit tout de suite (mise à jour optimiste ou rechargement partiel `only: ['task']`).

## Écran de référence

Prototype › Tâches › tiroir de détail › « Sous-tâches et vérifications ».

## Fichiers autorisés

```
+ app/Modules/Tasks/Models/Subtask.php
+ app/Modules/Tasks/Database/Migrations/…_create_subtasks_table.php
+ app/Modules/Tasks/Database/Factories/SubtaskFactory.php
+ app/Modules/Tasks/Http/Controllers/SubtaskController.php
+ app/Modules/Tasks/Http/Requests/SubtaskRequest.php
~ app/Modules/Tasks/Models/Task.php                     relation subtasks()
~ app/Modules/Tasks/Http/Controllers/TaskController.php  show() : sous-tâches ; index() : compteurs
~ app/Modules/Tasks/routes.php
+ resources/js/components/tasks/SubtaskList.vue
~ resources/js/pages/tasks/Show.vue
~ resources/js/components/tasks/TaskCard.vue            si le ticket 05 est fait
+ tests/Feature/Modules/Tasks/SubtaskTest.php
```

## Base de données

```
+ subtasks
    id
    task_id     FK tasks, cascadeOnDelete
    title       string(200)
    done_at     timestamp, nullable
    position    unsignedInteger, défaut 0
    timestamps
    index (task_id, position)
```

## Critères d'acceptation

- [ ] Ajouter, cocher, décocher, renommer et supprimer une sous-tâche sans rechargement complet.
- [ ] Le compteur « x/y » est juste sur la page tâche (et sur la carte Kanban si elle existe).
- [ ] Une sous-tâche d'une autre tâche répond 404.
- [ ] Observateur : cases et boutons désactivés, et 403 côté serveur.
- [ ] Les compteurs du Kanban et de la liste sont chargés avec `withCount`, sans requête par carte.
- [ ] Tests feature : création, case cochée, renommage, suppression, 404 inter-tâches, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Réordonner les sous-tâches par glisser-déposer.
- Assigner une sous-tâche à quelqu'un.
