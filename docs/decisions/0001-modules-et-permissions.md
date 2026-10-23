# 0001 — Modules maison et permissions configurables

- **Date** : 23 octobre 2026
- **Statut** : accepté

## Contexte

Le prototype codait 4 rôles et leurs droits en dur. On veut que le super admin règle les rôles, les permissions et les modules actifs depuis les paramètres, et que le travail puisse être découpé en tickets confiés à un développeur exécutant.

## Décisions

1. Rôle global pour les droits ; l'appartenance à un projet limite ce que l'on voit (sauf `projects.view-all`).
2. Modules dans `app/Modules/<Nom>`, structure maison légère (classe `Module`, `ModuleRegistry`, `ModuleLoader`), sans paquet de modules.
3. `spatie/laravel-permission` v8 pour les rôles et permissions.
4. Tickets dans GitHub Issues avec un tableau GitHub Project.
5. Projets et Tâches obligatoires ; les autres modules sont activables.
6. Clés de permission en anglais, libellés en français.
7. Base de production : à trancher (MySQL ou PostgreSQL).

## Conséquences

- Les permissions sont déclarées dans le code et synchronisées en base (`projecttools:sync-permissions`) ; les rôles sont des données.
- Le super admin est une colonne, pas un rôle : il ne peut pas se retirer ses propres droits par erreur.
- Toute route de module doit déclarer un `->can()`, vérifié par un test.
- Les factories et policies des modules sont liées par les attributs `#[UseFactory]` et `#[UsePolicy]`, car elles ne suivent pas les emplacements par défaut de Laravel.
