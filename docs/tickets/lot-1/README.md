# Lot 1 — Travail : les tickets

Le quotidien de l'équipe : tâches (liste, tableau, détail, sous-tâches, commentaires), Mes tâches, Accueil, Calendrier, et la page projet complète (jalons, progression, archivage).

**Avant le premier ticket de ce lot** : suivre `documentation/guide-demarrage.html`, puis faire le ticket d'essai [00 · Pages de connexion en français](../essai/00-connexion-en-francais.md), pour parcourir une fois tout le chemin jusqu'à la pull request.

Chaque fichier ci-dessous suit le gabarit GitHub « Tâche » (`.github/ISSUE_TEMPLATE/tache.yml`) : on copie chaque rubrique dans le champ du même nom. Tant que `gh` n'est pas installé, les tickets vivent ici ; ensuite ils seront créés sur GitHub avec le jalon « Lot 1 ».

## Ordre et dépendances

| #   | Ticket                                                                 | Qui                            | Dépend de | Taille |
| --- | ---------------------------------------------------------------------- | ------------------------------ | --------- | ------ |
| 01  | [Projets · Jalons d'un projet](01-projets-jalons.md)                   | Exécutant                      | —         | M      |
| 02  | [Tâches · Module, modèle et permissions](02-taches-module.md)          | Exécutant, relecture renforcée | 01        | M      |
| 03  | [Tâches · Créer, voir, modifier](03-taches-detail.md)                  | Exécutant                      | 02        | L      |
| 04  | [Tâches · Liste et filtres](04-taches-liste.md)                        | Exécutant                      | 03        | M      |
| 05  | [Tâches · Tableau Kanban](05-taches-kanban.md)                         | Exécutant                      | 04        | L      |
| 06  | [Tâches · Sous-tâches](06-taches-sous-taches.md)                       | Exécutant                      | 03        | S      |
| 07  | [Noyau · Commentaires et mentions](07-noyau-commentaires.md)           | **Vous (expert)**              | —         | M      |
| 08  | [Tâches · Commentaires](08-taches-commentaires.md)                     | Exécutant                      | 03, 07    | S      |
| 09  | [Noyau · Cloche de notifications](09-noyau-notifications.md)           | Exécutant, relecture renforcée | 07        | M      |
| 10  | [Tâches · Mes tâches](10-mes-taches.md)                                | Exécutant                      | 03        | M      |
| 11  | [Noyau · Points d'extension Accueil et Calendrier](11-noyau-agenda.md) | **Vous (expert)**              | 01, 02    | M      |
| 12  | [Accueil](12-accueil.md)                                               | Exécutant, relecture renforcée | 10, 11    | M      |
| 13  | [Calendrier](13-calendrier.md)                                         | Exécutant, relecture renforcée | 11        | L      |
| 14  | [Projets · Page projet complète](14-projets-page-complete.md)          | Exécutant                      | 01, 04    | M      |
| 15  | [Projets · Filtres par statut et archivage](15-projets-archivage.md)   | Exécutant                      | 02        | S      |

Taille : S ≈ une demi-journée, M ≈ une journée, L ≈ deux jours.

**Chemin conseillé** : 01 → 02 → 03, puis en parallèle 04 → 05, 06, 10, 15. Pendant ce temps, vous faites 07 et 11, qui débloquent 08, 09, 12 et 13. Le 14 vient en dernier.

« Relecture renforcée » : le ticket touche des fichiers du noyau ou du câblage (CODEOWNERS vous demande de relire), parce que la fonctionnalité l'exige. Les fichiers concernés sont listés dans le ticket ; rien d'autre du noyau ne doit bouger.

## Choix déjà faits pour ce lot

- **Détail d'une tâche sur une page dédiée** (`/tasks/{id}`) plutôt que dans un tiroir latéral comme dans le prototype : l'adresse se partage, se teste et suit le modèle du module Projets. Un tiroir pourra venir plus tard par-dessus.
- **Projets peut connaître Tâches** (relation `Project::tasks()`) : les deux modules sont obligatoires (décision 5), il n'y a donc pas de cas où Tâches serait absent.
- **Pas de nouvelle dépendance npm ou Composer** sans votre accord (le Kanban se fait en glisser-déposer HTML natif).
- **Hors lot 1** : minuteur et temps passé (lot 3), pièces jointes (lot 2, Documents), liens avec réunions et décisions (lot 2).

## Permissions ajoutées par ce lot

| Permission      | Libellé dans la matrice              | Administrateur | Responsable | Contributeur | Observateur |
| --------------- | ------------------------------------ | :------------: | :---------: | :----------: | :---------: |
| `tasks.view`    | Voir les tâches des projets visibles |      oui       |     oui     |     oui      |     oui     |
| `tasks.create`  | Créer une tâche                      |      oui       |     oui     |     oui      |     non     |
| `tasks.update`  | Modifier une tâche                   |      oui       |     oui     |     oui      |     non     |
| `tasks.delete`  | Supprimer une tâche                  |      oui       |     oui     |     non      |     non     |
| `tasks.comment` | Commenter une tâche                  |      oui       |     oui     |     oui      |     oui     |

Les jalons et l'archivage d'un projet utilisent la permission existante `projects.update`.

**Attention, base déjà en service** : `RoleSeeder` ne remplit que les rôles qui n'existent pas encore. Sur une base existante, après déploiement : lancer `php artisan projecttools:sync-permissions`, puis cocher les nouvelles permissions dans Paramètres › Rôles selon le tableau ci-dessus.
