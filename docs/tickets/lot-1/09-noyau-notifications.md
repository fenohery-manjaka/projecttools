# 09 · Noyau : cloche de notifications

**Étiquettes** : `lot-1` · `module:core` · `fonctionnalité` — **Qui** : exécutant, relecture renforcée (noyau) — **Dépend de** : 07 — **Taille** : M

## Contexte

Afficher les notifications de l'utilisateur (pour l'instant : les mentions du ticket 07) dans l'en-tête de l'application :

- une cloche avec le nombre de notifications non lues (rien si zéro) ;
- au clic, une liste des 10 dernières : texte, auteur, « il y a … », non lues mises en avant ;
- cliquer une notification la marque comme lue et ouvre la page liée ;
- un bouton « Tout marquer comme lu ».

Données partagées : ajouter une prop **différée** `notifications` (`{ unread_count, latest[] }`) dans `HandleInertiaRequests`, chargée seulement si l'utilisateur est connecté. **Ne pas la nommer `can`** (voir `docs/architecture.md`, « Données partagées »).

Routes dans `app/Modules/Core/routes.php` (préfixe `notifications`). Chaque route de module doit avoir un `->can()` : créer une `NotificationPolicy` (une notification n'est accessible qu'à son destinataire) et l'enregistrer dans `CoreServiceProvider::configureGates()` avec `Gate::policy(DatabaseNotification::class, NotificationPolicy::class)`.

```
POST notifications/{notification}/read   notifications.read       ->can('update', 'notification')
POST notifications/read-all              notifications.read-all   ->can('viewAny', DatabaseNotification::class)
```

`viewAny` renvoie `true` pour tout utilisateur connecté ; `update` vérifie que la notification lui appartient.

## Écran de référence

Pas d'écran dédié dans le prototype : une cloche dans la barre du haut, à droite du fil d'Ariane, au style des menus existants (`DropdownMenu`).

## Fichiers autorisés

```
+ app/Modules/Core/Http/Controllers/NotificationController.php
+ app/Modules/Core/Policies/NotificationPolicy.php
~ app/Modules/Core/CoreServiceProvider.php          Gate::policy(...) uniquement
~ app/Modules/Core/routes.php
~ app/Http/Middleware/HandleInertiaRequests.php     prop « notifications »
~ resources/js/types/global.d.ts
+ resources/js/components/NotificationBell.vue
~ resources/js/components/AppSidebarHeader.vue
+ tests/Feature/Modules/Core/NotificationsTest.php
```

## Base de données

Aucune (table `notifications` créée au lot 0).

## Critères d'acceptation

- [ ] La cloche montre le nombre de non lues ; il baisse quand on en lit une.
- [ ] Cliquer une notification la marque lue et ouvre la tâche concernée.
- [ ] « Tout marquer comme lu » met le compteur à zéro.
- [ ] Marquer la notification d'un autre utilisateur répond 403.
- [ ] La prop `notifications` n'ajoute pas de requête sur les pages publiques (connexion…).
- [ ] La cloche est utilisable au clavier et annonce le nombre (texte accessible, pas seulement une pastille).
- [ ] `ModuleRoutesAreProtectedTest` reste vert.
- [ ] Tests feature : compteur, lecture, tout lire, 403 sur la notification d'un autre.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Page « Toutes mes notifications », préférences de notification, e-mails.
- Mise à jour en temps réel (sans rechargement).
