# 10 · Tâches : Mes tâches

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 03 — **Taille** : M

## Contexte

La page `/my-tasks` montre à chacun **ses** tâches ouvertes (celles dont il est responsable, dans les projets visibles et non archivés), rangées par urgence :

1. En retard
2. Aujourd'hui
3. Cette semaine (dans les 7 prochains jours)
4. Plus tard
5. Sans échéance

Dans chaque groupe, tri par priorité (Urgente d'abord), puis par échéance. Un groupe vide n'est pas affiché. Si tout est vide : « Aucune tâche ouverte. Profitez-en pour faire avancer une idée. »

Chaque ligne : case à cocher pour terminer la tâche (et la rouvrir), titre (lien), pastille du projet, priorité, échéance. Cocher utilise la route de statut du ticket 03 (`tasks.status.update`).

Sous la liste, « Terminées récemment » : les 5 dernières tâches terminées (par `completed_at`).

Encadré « Ma charge » : nombre de tâches ouvertes par priorité, et estimation restante (somme des `estimate_hours` ouvertes, « 12 h »).

Phrase d'en-tête : « 8 tâches ouvertes. Cochez pour terminer. » et bouton « Nouvelle tâche » si l'utilisateur peut en créer.

Entrée de menu : « Mes tâches », groupe Travail, ordre 15 (entre Accueil et Projets), icône `CircleCheck` (à ajouter dans `navigationIcons.ts`), visible avec `viewAny` sur `Task`.

Route : `GET my-tasks` → `tasks.mine`, `->can('viewAny', Task::class)`.

## Écran de référence

Prototype › Mes tâches.

## Fichiers autorisés

```
~ app/Modules/Tasks/TasksModule.php                       navigation()
~ app/Modules/Tasks/routes.php
+ app/Modules/Tasks/Http/Controllers/MyTasksController.php
~ resources/js/lib/navigationIcons.ts                     CircleCheck
+ resources/js/pages/tasks/Mine.vue
+ resources/js/components/tasks/TaskRow.vue
+ tests/Feature/Modules/Tasks/MyTasksTest.php
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] Seules mes tâches ouvertes apparaissent, rangées dans le bon groupe (testé avec des dates fixes : `travel` / `freezeTime`).
- [ ] Le tri dans un groupe suit la priorité puis l'échéance.
- [ ] Cocher termine la tâche et la fait passer dans « Terminées récemment » ; décocher la rouvre.
- [ ] « Ma charge » donne les bons nombres et la bonne estimation restante.
- [ ] Un Observateur voit la page, mais sans cases à cocher.
- [ ] Le menu « Mes tâches » apparaît avec `tasks.view`, sinon 403 sur la page.
- [ ] Tests feature : groupes, tri, terminées récemment, charge, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- « Temps enregistré cette semaine », minuteur, « Où va mon temps » (lot 3).
