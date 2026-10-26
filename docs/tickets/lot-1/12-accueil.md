# 12 · Accueil

**Étiquettes** : `lot-1` · `module:core` · `fonctionnalité` — **Qui** : exécutant, relecture renforcée (route d'accueil) — **Dépend de** : 10, 11 — **Taille** : M

## Contexte

Remplacer la page d'accueil vide du kit de départ (`Dashboard.vue`) par un vrai tableau de bord, en s'appuyant sur les points d'extension du ticket 11.

Contenu :

- **En-tête** : date du jour en toutes lettres (« lundi 26 octobre »), et une phrase de synthèse : « 3 éléments demandent une intervention. » ou « Rien de bloquant aujourd'hui. » ; boutons « Nouvelle tâche » (si l'utilisateur peut en créer) ;
- **Demande une intervention** : `ModuleRegistry::attentionFor($user)` — tâches bloquées (avec la raison), tâches en retard (« 3 j de retard · Fanja, Tojo ») ; gravité indiquée par une icône **et** un mot ;
- **Mes prochaines tâches** : mes 6 tâches ouvertes les plus proches (même règle que Mes tâches, ticket 10), lien « Tout voir » vers `/my-tasks` ;
- **À venir** : `ModuleRegistry::agendaFor($user, aujourd'hui, +14 jours)`, 7 éléments maximum (échéances, jalons, fins de projet) ;
- **Projets en cours** : nombre de projets au statut « En cours » visibles, lien vers `/projects`.

Chaque bloc vide affiche une phrase simple (« Rien à signaler. »), jamais un cadre vide.

Implémenter dans `TasksModule` :

- `attention()` : tâches bloquées et en retard des projets visibles et non archivés ;
- `agenda()` : échéances des tâches ouvertes dans la période.

La route `dashboard` (`routes/web.php`) passe de `Route::inertia` à un contrôleur du noyau, `HomeController`. Le nom de route `dashboard` ne change pas : Fortify y redirige après la connexion.

## Écran de référence

Prototype › Accueil (en-tête avec synthèse, « Demande une intervention », mes tâches, agenda).

## Fichiers autorisés

```
+ app/Modules/Core/Http/Controllers/HomeController.php
~ routes/web.php                               dashboard → HomeController (même nom de route)
~ app/Modules/Tasks/TasksModule.php            attention(), agenda()
+ app/Modules/Tasks/Support/MyTasks.php        règle « mes tâches ouvertes », partagée avec le ticket 10
~ app/Modules/Tasks/Http/Controllers/MyTasksController.php   utiliser MyTasks
~ resources/js/pages/Dashboard.vue
+ resources/js/components/home/AttentionList.vue
+ resources/js/components/home/AgendaList.vue
~ tests/Feature/DashboardTest.php
+ tests/Feature/Modules/Tasks/TaskAgendaTest.php
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] La phrase de synthèse compte juste les éléments de « Demande une intervention ».
- [ ] Une tâche bloquée apparaît avec sa raison, une tâche en retard avec le nombre de jours et ses responsables.
- [ ] « Mes prochaines tâches » et `/my-tasks` utilisent la même règle (`MyTasks`) et donnent le même ordre.
- [ ] « À venir » mélange échéances, jalons et fins de projet sur 14 jours, triés par date.
- [ ] Rien d'un projet invisible ou archivé n'apparaît.
- [ ] Un utilisateur sans aucun rôle voit un accueil sans erreur, avec les blocs vides expliqués ; un visiteur non connecté est renvoyé vers la connexion.
- [ ] Après connexion, on arrive toujours sur l'accueil.
- [ ] Le nombre de requêtes ne dépend pas du nombre de tâches (test).
- [ ] Tests : synthèse, attention (bloquée, retard), prochaines tâches, à venir, utilisateur sans rôle.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Temps de la semaine, décisions en attente, réunions (lots 2 et 3, via `attention()` / `agenda()` de leurs modules).
- Personnaliser les blocs de l'accueil.
