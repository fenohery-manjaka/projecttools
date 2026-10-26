# 08 · Tâches : commentaires sur une tâche

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 03, 07 — **Taille** : S

## Contexte

Brancher les commentaires du noyau (ticket 07) sur les tâches, en 3 étapes (voir `docs/architecture.md`, section « Commentaires ») :

1. `Task` utilise le trait `HasComments` ;
2. le module expose ses routes ;
3. la page tâche affiche `CommentThread.vue`.

Règles propres aux tâches :

- commenter demande `tasks.comment` sur une tâche visible (`TaskPolicy::comment`) ;
- on ne peut mentionner que les **membres du projet** de la tâche ;
- un commentaire se supprime par son auteur, ou par quelqu'un qui a `tasks.update` sur la tâche ;
- la notification de mention renvoie vers la page de la tâche.

Routes (`->scopeBindings()`) :

```
POST   tasks/{task}/comments              tasks.comments.store     ->can('comment', 'task')
DELETE tasks/{task}/comments/{comment}    tasks.comments.destroy   ->can('view', 'task') + règle auteur / tasks.update
```

## Écran de référence

Prototype › Tâches › tiroir de détail › « Commentaires ».

## Fichiers autorisés

```
~ app/Modules/Tasks/Models/Task.php                       use HasComments
+ app/Modules/Tasks/Http/Controllers/TaskCommentController.php
+ app/Modules/Tasks/Http/Requests/StoreTaskCommentRequest.php
~ app/Modules/Tasks/Policies/TaskPolicy.php               deleteComment()
~ app/Modules/Tasks/routes.php
~ app/Modules/Tasks/Http/Controllers/TaskController.php   show() : commentaires et membres mentionnables
~ resources/js/pages/tasks/Show.vue
+ tests/Feature/Modules/Tasks/TaskCommentTest.php
```

## Base de données

Aucune (table `comments` du ticket 07).

## Critères d'acceptation

- [ ] Commenter une tâche ; le commentaire apparaît avec l'auteur et « il y a … ».
- [ ] Les suggestions « @ » ne proposent que les membres du projet ; mentionner un non-membre est ignoré côté serveur.
- [ ] La personne mentionnée reçoit une notification qui mène à la tâche ; l'auteur n'en reçoit pas.
- [ ] Un Observateur peut commenter (permission `tasks.comment`), mais ne peut supprimer que ses propres commentaires.
- [ ] Sans `tasks.comment` : pas de formulaire, et 403 sur la route.
- [ ] Les commentaires et leurs auteurs sont chargés en une fois (`with`), pas de requête par commentaire.
- [ ] Tests feature : commenter, mentions filtrées, notification, suppression par l'auteur / par un responsable / refusée, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Affichage des notifications (ticket 09).
