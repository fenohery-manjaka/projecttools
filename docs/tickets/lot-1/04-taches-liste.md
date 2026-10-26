# 04 · Tâches : liste et filtres

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 03 — **Taille** : M

## Contexte

La page `/tasks` liste toutes les tâches que l'utilisateur peut voir (`Task::visibleTo`), avec des filtres combinables :

- projet (projets visibles) ;
- membre (responsable) ;
- priorité ;
- échéance : « En retard », « 7 prochains jours », « Bloquées », « Sans échéance » ;
- recherche dans le titre et la description.

Les filtres sont dans l'adresse (`?project=3&priority=high&q=logo`) : on peut partager ou recharger une vue filtrée. Un bouton « Réinitialiser » apparaît dès qu'un filtre est actif. Une phrase résume : « 12 tâches correspondent aux filtres » / « 34 tâches dans les projets actifs ».

Colonnes : titre (lien vers la tâche), projet (pastille de couleur), statut, priorité, responsables (initiales), échéance (« En retard » en texte et en couleur si dépassée). Tri : échéance croissante (sans échéance à la fin), puis priorité. Pagination par 50.

Le filtrage est écrit **une seule fois** dans une classe `TaskFilters` réutilisée par le Kanban (ticket 05). Un sélecteur « Tableau / Liste » est prévu dans l'en-tête ; tant que le ticket 05 n'est pas fait, seule « Liste » existe.

Entrée de menu : « Tâches », groupe Travail, ordre 30, icône `ListChecks` (à ajouter dans `navigationIcons.ts`), visible avec `viewAny` sur `Task`.

Route : `GET tasks` → `tasks.index`, `->can('viewAny', Task::class)`.

## Écran de référence

Prototype › Tâches (filtres en haut, vue « Liste »).

## Fichiers autorisés

```
~ app/Modules/Tasks/TasksModule.php                  navigation()
~ app/Modules/Tasks/routes.php
~ app/Modules/Tasks/Http/Controllers/TaskController.php   index()
+ app/Modules/Tasks/Http/Requests/TaskFilterRequest.php
+ app/Modules/Tasks/Support/TaskFilters.php
~ resources/js/lib/navigationIcons.ts                ListChecks
+ resources/js/pages/tasks/Index.vue
+ resources/js/components/tasks/TaskFiltersBar.vue
+ resources/js/components/tasks/TaskTable.vue
+ tests/Feature/Modules/Tasks/TaskIndexTest.php
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] Le menu affiche « Tâches » seulement avec la permission `tasks.view`.
- [ ] Seules les tâches des projets visibles et non archivés apparaissent.
- [ ] Chaque filtre fonctionne seul et combiné ; une valeur invalide renvoie une erreur de validation (pas une page blanche).
- [ ] Les filtres survivent au rechargement (ils sont dans l'adresse).
- [ ] Le nombre de requêtes SQL ne grandit pas avec le nombre de tâches (relations chargées avec `with`), vérifié dans un test.
- [ ] Sans `tasks.view` : 403 sur `/tasks`.
- [ ] Sur téléphone, le tableau défile horizontalement dans son cadre, sans faire déborder la page.
- [ ] Tests feature : visibilité, chaque filtre, combinaison, recherche, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Vue Tableau / Kanban (05).
- Sélection multiple et actions groupées.
