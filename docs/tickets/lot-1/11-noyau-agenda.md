# 11 · Noyau : points d'extension pour l'Accueil et le Calendrier

**Étiquettes** : `lot-1` · `module:core` · `architecture` — **Qui** : **vous (expert)** — **Dépend de** : 01, 02 — **Taille** : M

## Contexte

L'Accueil et le Calendrier rassemblent des éléments venant de plusieurs modules : échéances de tâches, jalons et fins de projet (lot 1), puis réunions et décisions (lot 2). Pour qu'ils ne dépendent d'aucun module en particulier, chaque module **apporte** ses éléments au noyau, qui les rassemble pour les modules actifs.

À ajouter à la classe `Module`, avec une valeur par défaut vide :

- `agenda(User $user, CarbonImmutable $from, CarbonImmutable $to): list<AgendaItem>` : éléments datés de la période (pour le calendrier et « À venir ») ;
- `attention(User $user): list<AttentionItem>` : ce qui demande une intervention (bloqué, en retard, en attente de décision…).

Objets valeur (`final readonly`) dans `app/Modules/Core/Agenda/` :

- `AgendaItem` : `date`, `time` (nullable), `type` (enum `AgendaItemType` : `TaskDue`, `Milestone`, `ProjectEnd`, extensible au lot 2), `label`, `url`, `color` (couleur du projet), `isLate`, `isDone`, `projectName` ;
- `AttentionItem` : `severity` (enum `Severity` : `Critical`, `Warning`, `Info`), `label`, `detail`, `url`, `projectName`.

`ModuleRegistry` reçoit `agendaFor(User, $from, $to)` et `attentionFor(User)`, qui interrogent uniquement les modules **actifs** et trient le résultat (par date pour l'agenda ; par gravité puis date pour l'attention).

Implémentation de référence dans ce ticket : `ProjectsModule::agenda()` (jalons non atteints et fins de projet dans la période, projets visibles et non archivés). Celle de `TasksModule` (échéances, tâches bloquées ou en retard) est faite par l'exécutant aux tickets 12 et 13, sur ce modèle.

À documenter dans `docs/architecture.md` : section « Accueil et calendrier ».

## Écran de référence

Aucun écran. Données : prototype, fonctions `calItems()` (calendrier) et `attention` / `agenda` dans `V.accueil`.

## Fichiers autorisés

```
~ app/Modules/Core/Module.php                   agenda(), attention() par défaut vides
+ app/Modules/Core/Agenda/AgendaItem.php
+ app/Modules/Core/Agenda/AgendaItemType.php
+ app/Modules/Core/Agenda/AttentionItem.php
+ app/Modules/Core/Agenda/Severity.php
~ app/Modules/Core/ModuleRegistry.php           agendaFor(), attentionFor()
~ app/Modules/Projects/ProjectsModule.php       agenda()
+ tests/Feature/Modules/Core/AgendaTest.php
~ docs/architecture.md
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] Un module qui ne redéfinit pas `agenda()` / `attention()` n'apporte rien et ne casse rien.
- [ ] `agendaFor()` ignore les modules désactivés (testé avec le module de démonstration `tests/Fixtures/Modules/Demo`).
- [ ] `ProjectsModule::agenda()` renvoie les jalons non atteints et les fins de projet de la période, seulement pour les projets visibles et non archivés.
- [ ] Le tri est stable : date puis heure pour l'agenda ; gravité puis date pour l'attention.
- [ ] Nombre de requêtes borné : une requête par module et par type d'élément, quel que soit le nombre de projets.
- [ ] Section « Accueil et calendrier » ajoutée à `docs/architecture.md`, avec un exemple d'implémentation.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Les pages Accueil et Calendrier (tickets 12 et 13).
- Les réunions et décisions (lot 2, elles implémenteront `agenda()` et `attention()`).
