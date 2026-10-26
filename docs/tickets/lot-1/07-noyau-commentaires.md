# 07 · Noyau : commentaires et mentions

**Étiquettes** : `lot-1` · `module:core` · `fonctionnalité` — **Qui** : **vous (expert)** — **Dépend de** : aucun — **Taille** : M

## Contexte

Les commentaires servent aux tâches (lot 1), puis aux idées, décisions et documents (lot 2). Ils vivent donc dans le noyau, une seule fois, et chaque module les branche sur son modèle.

À construire :

- une table `comments` **polymorphe** (`commentable_type`, `commentable_id`) ;
- le modèle `Comment` et le trait `HasComments` (relation `comments()` triée du plus ancien au plus récent) ;
- l'action `PostComment` : crée le commentaire, inscrit l'activité, puis notifie les personnes mentionnées ;
- la notification `MentionedInComment` (canal `database` ; données : auteur, extrait, titre de l'élément, adresse de la page) ;
- le composant Vue `CommentThread.vue`, réutilisable : liste (auteur, « il y a 3 h », texte), formulaire, et suggestions « @Prénom » à la frappe de `@`.

**Mentions** : le texte reste libre, mais le formulaire envoie aussi `mentioned_user_ids[]`. On ne déduit pas les mentions du texte en cherchant des prénoms : deux collègues peuvent porter le même. Le serveur ne garde que les identifiants autorisés par le module (ex. membres du projet), ne notifie jamais l'auteur lui-même, et une seule fois par personne.

**Pas de route dans le noyau** : chaque module expose sa propre route avec sa propre permission (ticket 08 pour les tâches) et appelle `PostComment`. Le noyau fournit aussi `DeleteComment` : l'auteur peut supprimer son commentaire ; un module peut l'autoriser plus largement.

À documenter dans `docs/architecture.md` : section « Commentaires », avec comment brancher un module en 3 étapes.

## Écran de référence

Prototype › Tâches › tiroir de détail › « Commentaires » (liste, raccourcis « @Prénom », bouton « Commenter »).

## Fichiers autorisés

```
+ app/Modules/Core/Database/Migrations/…_create_comments_table.php
+ app/Modules/Core/Models/Comment.php
+ app/Modules/Core/Concerns/HasComments.php
+ app/Modules/Core/Actions/PostComment.php
+ app/Modules/Core/Actions/DeleteComment.php
+ app/Modules/Core/Notifications/MentionedInComment.php
+ resources/js/components/comments/CommentThread.vue
+ resources/js/types/comments.ts
+ tests/Feature/Modules/Core/CommentsTest.php
+ tests/Fixtures/...                                   modèle commentable de test si besoin
~ docs/architecture.md
```

## Base de données

```
+ comments
    id
    commentable_type, commentable_id   morphs, index
    user_id         FK users, nullable, nullOnDelete
    body            text
    timestamps
```

## Critères d'acceptation

- [ ] Un modèle avec `HasComments` reçoit des commentaires via `PostComment`, qui inscrit aussi l'activité.
- [ ] Une mention notifie la personne (`database`), jamais l'auteur, une seule fois même si elle est citée deux fois.
- [ ] Un identifiant mentionné hors de la liste autorisée est ignoré.
- [ ] La notification contient de quoi l'afficher sans requête supplémentaire (texte, auteur, adresse).
- [ ] `CommentThread.vue` fonctionne sans dépendance nouvelle, se lit au clavier, et affiche « Aucun commentaire » quand la liste est vide.
- [ ] Section « Commentaires » ajoutée à `docs/architecture.md`.
- [ ] Tests : création, mentions filtrées, pas d'auto-notification, suppression par l'auteur, refus pour un autre.
- [ ] `composer ci:check` vert.

## Hors périmètre

- Modifier un commentaire après publication.
- Réactions, fils de réponses.
- Envoi des mentions par e-mail (la cloche du ticket 09 suffit pour le lot 1).
