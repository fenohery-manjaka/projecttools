# 05 · Tâches : tableau Kanban

**Étiquettes** : `lot-1` · `module:tasks` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : 04 — **Taille** : L

## Contexte

La vue « Tableau » de `/tasks` (`?view=board`) range les tâches en 4 colonnes par statut : À faire, En cours, Bloqué, Terminé. Elle utilise les mêmes filtres que la liste (`TaskFilters`, ticket 04).

Une carte montre : titre, pastille du projet, priorité (Urgente et Haute signalées), échéance (« En retard » si dépassée), initiales des responsables, et « 2/5 » sous-tâches quand le ticket 06 est fait. Un clic ouvre la page de la tâche.

**Déplacer une carte** (glisser-déposer) entre colonnes ou dans une colonne change son statut et sa position :

- `PATCH tasks/{task}/move` (`tasks.move`, `->can('update', 'task')`) avec `{ status, position }` ;
- l'action `MoveTask` renumérote les positions de la colonne dans une transaction ;
- déposer dans « Bloqué » ouvre la fenêtre de raison (`BlockReasonDialog`, ticket 03) ; si on annule, la carte revient à sa place ;
- déposer dans « Terminé » remplit `completed_at`.

Glisser-déposer en **HTML natif** (pas de nouvelle bibliothèque sans accord). Pour le clavier et l'accessibilité, chaque carte a aussi un petit menu « Déplacer vers… ». Sans `tasks.update`, le tableau est en lecture seule (pas de glisser, pas de menu).

## Écran de référence

Prototype › Tâches › vue « Tableau » (colonnes, cartes, glisser entre colonnes).

## Fichiers autorisés

```
~ app/Modules/Tasks/routes.php
~ app/Modules/Tasks/Http/Controllers/TaskController.php   index() : données de la vue tableau
+ app/Modules/Tasks/Http/Controllers/TaskMoveController.php
+ app/Modules/Tasks/Http/Requests/MoveTaskRequest.php
+ app/Modules/Tasks/Actions/MoveTask.php
~ resources/js/pages/tasks/Index.vue                      sélecteur Tableau / Liste
+ resources/js/components/tasks/TaskBoard.vue
+ resources/js/components/tasks/TaskCard.vue
+ tests/Feature/Modules/Tasks/TaskMoveTest.php
```

## Base de données

Aucune (`tasks.position` existe depuis le ticket 02).

## Critères d'acceptation

- [ ] Le sélecteur Tableau / Liste garde les filtres actifs, et le choix est dans l'adresse.
- [ ] Déplacer une carte change son statut et sa position ; après rechargement, l'ordre est conservé.
- [ ] Déposer dans « Bloqué » sans raison est impossible, y compris par un appel direct au serveur (validation).
- [ ] Annuler la fenêtre de raison remet la carte à sa place.
- [ ] Le menu « Déplacer vers… » fait la même chose que le glisser-déposer.
- [ ] Observateur : tableau en lecture seule, et 403 sur `tasks.move`.
- [ ] Sur téléphone, les colonnes défilent horizontalement sans faire déborder la page.
- [ ] Tests feature : changement de statut, renumérotation des positions, blocage, `completed_at`, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Colonnes personnalisables, limite de cartes par colonne.
- Mise à jour en temps réel quand un collègue déplace une carte.
