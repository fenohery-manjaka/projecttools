# 13 · Calendrier

**Étiquettes** : `lot-1` · `module:core` · `fonctionnalité` — **Qui** : exécutant, relecture renforcée (noyau) — **Dépend de** : 11 — **Taille** : L

## Contexte

Une page `/calendar` réunit, par mois, tout ce qui est daté : échéances de tâches, jalons, fins de projet (et les réunions au lot 2, sans rien changer à cette page). Les données viennent de `ModuleRegistry::agendaFor()` (ticket 11).

**Grille du mois**, du lundi au dimanche :

- le jour courant mis en avant ;
- jusqu'à 3 éléments par jour, puis « +2 autres » (qui ouvre la liste du jour) ;
- chaque élément montre une icône selon son type (échéance, jalon, fin de projet), la couleur de son projet et son libellé ; en retard en rouge **et** avec la mention « en retard » ; terminé barré ;
- une journée avec 4 éléments ouverts ou plus est marquée « chargée » (point orange et texte pour les lecteurs d'écran) ;
- un clic sur un élément ouvre sa page.

**Navigation** : mois précédent, mois suivant, « Aujourd'hui ». Le mois est dans l'adresse (`/calendar?month=2026-11`).

**À côté de la grille** : « En retard » (tous les éléments en retard, du plus ancien au plus récent) et « 14 prochains jours ».

**Sur téléphone** (moins de 640 px) : la grille laisse la place à une liste par jour du mois.

Entrée de menu : « Calendrier », groupe Travail, ordre 40, icône `Calendar` (dans `navigationIcons.ts`), déclarée dans `CoreModule::navigation()`.

Route : `GET calendar` → `calendar.index`, `->can('viewAny', Project::class)`.

Implémenter `TasksModule::agenda()` si le ticket 12 ne l'a pas déjà fait.

## Écran de référence

Prototype › Calendrier (grille mensuelle, « journée chargée », listes en retard et à venir).

## Fichiers autorisés

```
+ app/Modules/Core/Http/Controllers/CalendarController.php
+ app/Modules/Core/Http/Requests/CalendarRequest.php       month au format AAAA-MM
~ app/Modules/Core/routes.php
~ app/Modules/Core/CoreModule.php                         navigation() : Calendrier
~ app/Modules/Tasks/TasksModule.php                       agenda() si absent
~ resources/js/lib/navigationIcons.ts                     Calendar
+ resources/js/pages/calendar/Index.vue
+ resources/js/components/calendar/MonthGrid.vue
+ resources/js/components/calendar/DayList.vue
+ tests/Feature/Modules/Core/CalendarTest.php
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] Le mois affiché commence un lundi et contient les jours du mois précédent et suivant pour compléter les semaines.
- [ ] Échéances, jalons et fins de projet apparaissent le bon jour, avec la bonne icône et la couleur du projet.
- [ ] « +N autres » apparaît au-delà de 3 éléments et ouvre la liste complète du jour.
- [ ] Une journée de 4 éléments ouverts ou plus est marquée « chargée » (visible et lisible par un lecteur d'écran).
- [ ] Mois précédent / suivant / Aujourd'hui fonctionnent, et le mois est dans l'adresse ; une valeur invalide renvoie une erreur de validation.
- [ ] Rien d'un projet invisible ou archivé n'apparaît ; un module désactivé n'apporte rien.
- [ ] Sur téléphone, la vue liste remplace la grille, sans débordement horizontal.
- [ ] Sans permission sur les projets : 403 et pas d'entrée de menu.
- [ ] Tests feature : mois et bornes, types d'éléments, retard, journée chargée, visibilité, 403.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Réunions (lot 2), glisser une échéance vers un autre jour, vue semaine, export iCal.
