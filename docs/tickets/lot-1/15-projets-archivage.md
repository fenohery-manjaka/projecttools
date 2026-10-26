# 15 · Projets : filtres par statut et archivage

**Étiquettes** : `lot-1` · `module:projects` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 02 — **Taille** : S

## Contexte

**Filtres de la liste des projets** : des pastilles au-dessus des cartes, chacune avec son nombre :

- « Tous » (projets non archivés), « En cours », « Envisagés », « En pause », « Terminés », « Abandonnés » ;
- « Archivés » (seulement les projets archivés).

Le filtre est dans l'adresse (`/projects?status=active`, `/projects?status=archived`). Les nombres ne comptent que les projets visibles par l'utilisateur.

**Archiver un projet** : bouton « Archiver » sur la page projet, et « Désarchiver » sur un projet archivé, avec la permission `projects.update` (`->can('update', 'project')`). Archiver remplit `projects.archived_at` (la colonne existe déjà).

Un projet archivé :

- disparaît de la liste par défaut, de Tâches, Mes tâches, Accueil et Calendrier (le scope `Task::visibleTo` du ticket 02 l'exclut déjà) ;
- reste consultable par son adresse, avec un bandeau « Projet archivé le 26 octobre 2026 » ;
- est en **lecture seule** : plus de modification, de jalon ni de tâche tant qu'il n'est pas désarchivé (règle dans `ProjectPolicy::update` et `TaskPolicy`).

Routes :

```
POST   projects/{project}/archive   projects.archive     ->can('archive', 'project')
DELETE projects/{project}/archive   projects.unarchive   ->can('archive', 'project')
```

Ajouter `ProjectPolicy::archive()` : `projects.update` et projet visible, sans la règle « lecture seule », pour pouvoir désarchiver.

## Écran de référence

Prototype › Projets (pastilles de filtre avec compteurs, pastille « Archivé » sur une carte).

## Fichiers autorisés

```
~ app/Modules/Projects/Http/Controllers/ProjectController.php     index() : filtre et compteurs
+ app/Modules/Projects/Http/Controllers/ProjectArchiveController.php
+ app/Modules/Projects/Http/Requests/ProjectFilterRequest.php
~ app/Modules/Projects/Policies/ProjectPolicy.php                 archive(), lecture seule
~ app/Modules/Tasks/Policies/TaskPolicy.php                       lecture seule si projet archivé
~ app/Modules/Projects/routes.php
~ resources/js/pages/projects/Index.vue
~ resources/js/pages/projects/Show.vue
+ resources/js/components/projects/StatusFilter.vue
+ tests/Feature/Modules/Projects/ProjectArchiveTest.php
```

## Base de données

Aucune (`projects.archived_at` existe depuis le lot 0).

## Critères d'acceptation

- [ ] Chaque pastille filtre correctement, et son nombre est juste pour l'utilisateur connecté.
- [ ] Archiver puis désarchiver depuis la page projet ; l'action est inscrite au journal.
- [ ] Un projet archivé n'apparaît plus dans la liste par défaut ni dans les écrans de tâches.
- [ ] Sur un projet archivé : modifier, ajouter un jalon ou une tâche répond 403, et les boutons sont masqués ; « Désarchiver » reste possible.
- [ ] Sans `projects.update` : pas de bouton, et 403 sur les deux routes.
- [ ] Tests feature : filtres et compteurs, archiver, désarchiver, lecture seule, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Archivage automatique des projets terminés.
- Suppression définitive des projets archivés.
