# 14 · Projets : page projet complète

**Étiquettes** : `lot-1` · `module:projects` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 01, 04 — **Taille** : M

## Contexte

Compléter la page d'un projet et les cartes de la liste maintenant que tâches et jalons existent.

**Page projet** : des onglets, dont l'onglet actif est dans l'adresse (`?tab=tasks`) :

- **Aperçu** : description et objectifs ; progression (cercle en %, « 8 tâches terminées sur 14 ») ; prochains jalons (3) ; « Éléments en attente » (tâches bloquées avec leur raison, tâches en retard) ; bouton « Nouvelle tâche » ;
- **Tâches** : les tâches du projet, avec le composant `TaskTable` du ticket 04, et un lien « Ouvrir dans Tâches » vers `/tasks?project={id}` ;
- **Jalons** : le composant du ticket 01, et pour chaque jalon le nombre de tâches rattachées ;
- **Équipe** : la gestion des membres existante.

Progression = tâches terminées / total des tâches du projet (0 % s'il n'y en a aucune, 100 % si le projet est « Terminé » sans tâche, comme dans le prototype).

**Cartes de la liste des projets** : barre de progression avec « 8 / 14 tâches · 57 % », nombre de tâches en retard s'il y en a, prochain jalon (« Maquette · 12 nov. ») sinon la date de fin.

Les onglets Réunions, Décisions, Documents et Budget du prototype viendront avec leurs modules (lots 2 et 3).

## Écran de référence

Prototype › Projets (cartes) et Projets › un projet (onglets Aperçu, Tâches, Jalons, Équipe).

## Fichiers autorisés

```
~ app/Modules/Projects/Http/Controllers/ProjectController.php   index() et show() : compteurs, onglets
~ resources/js/pages/projects/Show.vue
~ resources/js/pages/projects/Index.vue
+ resources/js/components/projects/ProjectProgress.vue
+ resources/js/components/projects/ProjectTabs.vue
+ resources/js/components/projects/PendingItems.vue
~ resources/js/components/projects/ProjectMilestones.vue          nombre de tâches par jalon
+ tests/Feature/Modules/Projects/ProjectOverviewTest.php
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] La progression est juste dans tous les cas (aucune tâche, projet terminé sans tâche, mélange de statuts).
- [ ] L'onglet actif est dans l'adresse et survit au rechargement.
- [ ] « Éléments en attente » liste les tâches bloquées (avec raison) et en retard du projet, ou « Rien en attente sur ce projet. ».
- [ ] Les cartes de la liste affichent progression, retards et prochain jalon, **sans requête par carte** (`withCount` avec conditions, test du nombre de requêtes).
- [ ] Un Observateur voit tous les onglets en lecture seule ; un non-membre reçoit 403 sur la page et sur chaque onglet (`?tab=…`).
- [ ] Sur téléphone, les onglets défilent horizontalement sans faire déborder la page.
- [ ] Tests feature : calculs de progression, éléments en attente, compteurs de la liste, onglet par l'adresse.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Onglets Réunions, Décisions, Documents, Budget (lots 2 et 3).
- Temps passé sur le projet (lot 3).
