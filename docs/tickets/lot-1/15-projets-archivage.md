# 15 · Projets : filtres par statut et archivage

**Étiquettes** : `lot-1` · `module:projects` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 02 — **Taille** : S

> Mis à jour le 19 novembre 2026 : les statuts de projet sont maintenant **réglables** (table `project_statuses`, Paramètres › Statuts des projets). Les pastilles de filtre suivent donc la table, et non plus une liste écrite en dur. Lire d'abord `docs/architecture.md`, section « Statuts réglables ».

## Contexte

**Filtres de la liste des projets** : des pastilles au-dessus des cartes, chacune avec son nombre :

- « Tous » : les projets non archivés ;
- une pastille par statut de projet, avec son nom et sa couleur, dans l'ordre choisi dans Paramètres (`ProjectStatus::query()->ordered()`) ; un statut sans projet garde sa pastille, avec 0 ;
- « Archivés » : seulement les projets archivés, quel que soit leur statut.

Le filtre est dans l'adresse : `/projects?status=3` (l'identifiant du statut) ou `/projects?status=archived`. Les nombres ne comptent que les projets visibles par l'utilisateur (`Project::visibleTo`), et sont calculés en une seule requête groupée (`groupBy('status_id')`), pas une requête par pastille.

Le code ne s'appuie jamais sur le **nom** d'un statut : un statut peut être renommé ou supprimé à tout moment dans Paramètres.

**Archiver un projet** : bouton « Archiver » sur la page projet, et « Désarchiver » sur un projet archivé, avec la permission `projects.update` (`->can('archive', 'project')`). Archiver remplit `projects.archived_at` (la colonne existe déjà), désarchiver la vide. Les deux actions sont inscrites au journal (`RecordActivity`).

Un projet archivé :

- disparaît de la liste par défaut, de Tâches et de Mes tâches : c'est déjà le cas, car `Task::visibleTo` et la liste des projets excluent `archived_at` non nul. À vérifier par un test, pas à recoder ;
- reste consultable par son adresse, avec un bandeau « Projet archivé le 26 octobre 2026 » ;
- porte une pastille « Archivé » sur sa carte dans la vue « Archivés » ;
- est en **lecture seule** : plus de modification du projet, de jalon, ni de tâche (création, modification, statut, Kanban, sous-tâches, suppression) tant qu'il n'est pas désarchivé.

Où mettre la règle « lecture seule » :

- `ProjectPolicy::update()` refuse si `archived_at` est rempli : les routes du projet et des jalons, qui vérifient toutes `->can('update', 'project')`, sont couvertes d'un coup ;
- `TaskPolicy` : `create()`, `update()` et `delete()` refusent si le projet de la tâche est archivé : les routes de tâches, de statut, de déplacement et de sous-tâches sont couvertes d'un coup ;
- `ProjectPolicy::archive()` (nouvelle) : `projects.update` et projet visible, **sans** la règle « lecture seule », pour pouvoir désarchiver.

**À savoir** : le super admin passe toutes les vérifications avant même les policies (`Gate::before`, voir `docs/architecture.md`). La lecture seule ne s'applique donc pas à lui côté serveur, et c'est voulu. Mais les boutons (Modifier, Nouvelle tâche, Ajouter un jalon…) sont masqués pour **tout le monde** sur un projet archivé : la page se base sur `archived_at`, pas seulement sur `can`.

Routes :

```
POST   projects/{project}/archive   projects.archive     ->can('archive', 'project')
DELETE projects/{project}/archive   projects.unarchive   ->can('archive', 'project')
```

## Écran de référence

Prototype › Projets (pastilles de filtre avec compteurs, pastille « Archivé » sur une carte).

## Fichiers autorisés

```
~ app/Modules/Projects/Http/Controllers/ProjectController.php     index() : filtre et compteurs ; show() : archived_at
+ app/Modules/Projects/Http/Controllers/ProjectArchiveController.php
+ app/Modules/Projects/Http/Requests/ProjectFilterRequest.php       status : identifiant existant ou « archived »
~ app/Modules/Projects/Policies/ProjectPolicy.php                 archive(), lecture seule dans update()
~ app/Modules/Tasks/Policies/TaskPolicy.php                       lecture seule si projet archivé
~ app/Modules/Projects/routes.php
~ resources/js/pages/projects/Index.vue
~ resources/js/pages/projects/Show.vue
+ resources/js/components/projects/StatusFilter.vue
+ tests/Feature/Modules/Projects/ProjectArchiveTest.php
```

Déjà prêts, à réutiliser : `ProjectFactory::archived()` et `ProjectFactory::withStatus(ProjectStatusCategory::…)` pour les tests, `StatusBadge.vue` pour afficher un statut.

## Base de données

Aucune (`projects.archived_at` existe depuis le lot 0, `project_statuses` depuis le lot 1).

## Critères d'acceptation

- [ ] Une pastille par statut de `project_statuses`, dans l'ordre des Paramètres, plus « Tous » et « Archivés » ; chaque pastille filtre correctement et son nombre est juste pour l'utilisateur connecté.
- [ ] Un statut ajouté dans Paramètres apparaît dans les pastilles sans toucher au code (testé).
- [ ] Une valeur de filtre invalide (`?status=abc`, identifiant inconnu) renvoie une erreur de validation, pas une page blanche.
- [ ] Les nombres sont calculés sans requête par pastille (test du nombre de requêtes).
- [ ] Archiver puis désarchiver depuis la page projet ; les deux actions sont inscrites au journal.
- [ ] Un projet archivé n'apparaît plus dans la liste par défaut, ni dans Tâches, ni dans Mes tâches.
- [ ] Sur un projet archivé : modifier le projet, ajouter un jalon, créer, modifier, déplacer ou supprimer une tâche répond 403 pour un Responsable, et les boutons sont masqués ; « Désarchiver » reste possible.
- [ ] Sans `projects.update` : pas de bouton, et 403 sur les deux routes d'archivage.
- [ ] Sur téléphone, les pastilles passent à la ligne ou défilent sans faire déborder la page.
- [ ] Tests feature : filtres et compteurs, archiver, désarchiver, lecture seule, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Archivage automatique des projets terminés.
- Suppression définitive des projets archivés.
- Lecture seule pour le super admin côté serveur.
