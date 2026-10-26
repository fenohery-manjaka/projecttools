# 01 · Projets : jalons d'un projet

**Étiquettes** : `lot-1` · `module:projects` · `fonctionnalité` — **Qui** : exécutant — **Dépend de** : aucun — **Taille** : M

## Contexte

Un projet avance par étapes datées : les jalons (ex. « Maquette validée · 12 novembre »). Sur la page d'un projet, une section « Jalons » liste les jalons triés par date et permet de les gérer :

- ajouter un jalon (nom + date) ;
- le cocher « atteint » ou le décocher ;
- modifier son nom ou sa date ;
- le supprimer (avec confirmation).

Un jalon non atteint dont la date est passée est marqué « Dépassé ». Un compteur indique « 2 / 5 atteints ».

Gérer les jalons demande la permission `projects.update` et de voir le projet (même règle que modifier le projet : `ProjectPolicy::update`). Voir les jalons demande seulement de voir le projet.

Routes imbriquées, avec `->scopeBindings()` pour qu'un jalon d'un autre projet réponde 404 :

```
POST   projects/{project}/milestones              projects.milestones.store    ->can('update', 'project')
PATCH  projects/{project}/milestones/{milestone}  projects.milestones.update   ->can('update', 'project')
DELETE projects/{project}/milestones/{milestone}  projects.milestones.destroy  ->can('update', 'project')
```

## Écran de référence

Prototype › Projets › un projet › onglet « Jalons » (liste, case « atteint », compteur, mention « dépassé »).

## Fichiers autorisés

```
+ app/Modules/Projects/Models/Milestone.php
+ app/Modules/Projects/Database/Migrations/…_create_milestones_table.php
+ app/Modules/Projects/Database/Factories/MilestoneFactory.php
+ app/Modules/Projects/Http/Controllers/MilestoneController.php
+ app/Modules/Projects/Http/Requests/MilestoneRequest.php
~ app/Modules/Projects/Models/Project.php            relation milestones()
~ app/Modules/Projects/Http/Controllers/ProjectController.php   show() : passer les jalons
~ app/Modules/Projects/routes.php
+ resources/js/components/projects/ProjectMilestones.vue
~ resources/js/pages/projects/Show.vue
+ tests/Feature/Modules/Projects/MilestoneTest.php
```

## Base de données

```
+ milestones
    id
    project_id      FK projects, cascadeOnDelete
    name            string(120)
    due_on          date
    reached_at      timestamp, nullable
    timestamps
    index (project_id, due_on)
```

## Critères d'acceptation

- [ ] Ajouter, modifier, supprimer un jalon depuis la page projet ; la liste est triée par date.
- [ ] Cocher « atteint » remplit `reached_at`, décocher le vide ; le compteur « x / y atteints » se met à jour.
- [ ] Un jalon non atteint dont la date est passée affiche « Dépassé » (texte, pas seulement une couleur).
- [ ] Validation : nom obligatoire (120 caractères max), date obligatoire ; messages en français.
- [ ] Un jalon d'un autre projet répond 404 (scopeBindings).
- [ ] Un utilisateur sans `projects.update`, ou qui ne voit pas le projet, reçoit 403 ; un Observateur voit la liste sans les boutons.
- [ ] La création et le passage à « atteint » sont inscrits au journal (`RecordActivity`).
- [ ] La factory lie le modèle avec `#[UseFactory]`.
- [ ] Tests feature : création, validation, modification, atteint/non atteint, suppression, 403, 404 inter-projets.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Rattacher des tâches à un jalon (ticket 03).
- Afficher les jalons dans le calendrier ou l'accueil (tickets 12 et 13).
- Réordonner les jalons à la main : l'ordre est toujours la date.
