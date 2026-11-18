# Rapport d’intervention — Noyau commentaire

**Date d’intervention :** 13 novembre 2026  
**Tâche :** noyau commentaire  
**Projet :** ProjectTools

## Objet

Mettre en place le socle de commentaires afin de permettre l’ajout de commentaires sur les éléments métier, avec prise en charge des auteurs, des personnes mentionnables et du contrôle des permissions.

## Intervention réalisée

- Raccordement du modèle `Task` au contrat `Commentable` et au trait `HasComments`.
- Ajout du titre et de l’URL de référence de la tâche commentée.
- Chargement des commentaires avec leur auteur sur la page de détail d’une tâche.
- Détermination des personnes mentionnables à partir des membres actifs et du propriétaire du projet.
- Ajout de la route protégée de publication d’un commentaire.
- Ajout de la validation du contenu et des identifiants mentionnés.
- Intégration du composant d’affichage et de publication des commentaires dans la page de détail d’une tâche.
- Utilisation de Wayfinder pour générer l’adresse d’envoi côté Vue.

## Contrôles appliqués

- La route de commentaire vérifie la permission `comment` sur la tâche.
- Les membres désactivés ne sont pas proposés comme personnes mentionnables.
- Le contenu est obligatoire et limité à 5 000 caractères.
- Les identifiants mentionnés doivent être distincts et au format entier.

## Référence architecture

Cette intervention suit la section **Commentaires** de [`docs/architecture.md`](../architecture.md) : relation polymorphe, modèle commentable, route protégée par permission, personnes mentionnables et publication via l’action `PostComment`.

## État

Intervention réalisée le 13 novembre 2026. La vérification complète de la suite de tests et de la CI reste à effectuer avant intégration.
