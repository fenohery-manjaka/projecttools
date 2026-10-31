# 00 · Pages de connexion en français

**Étiquettes** : `essai` · `module:core` · `traduction` — **Qui** : nouvel arrivant, premier ticket — **Dépend de** : aucun — **Taille** : S

Ticket d'essai : le but est de faire une fois tout le chemin décrit dans `documentation/guide-demarrage.html` (branche, commit, vérification, pull request, relecture) sur un changement simple et sans risque. Branche : `essai/00-connexion-en-francais`.

## Contexte

L'application est en français, mais les pages d'authentification viennent du kit de départ Laravel et sont restées en anglais : « Log in to your account », « Forgot your password? », « Sign in with a passkey », etc.

Traduire **tous les textes visibles** de ces pages en français : titres, descriptions, libellés, textes indicatifs des champs (placeholder), boutons, liens, messages d'état. Ne rien changer d'autre : ni la mise en page, ni les champs, ni le comportement.

Repères de vocabulaire :

| Anglais                     | Français                                 |
| --------------------------- | ---------------------------------------- |
| Log in / Sign in            | Se connecter                             |
| Sign up / Register          | Créer un compte                          |
| Email address               | Adresse e-mail                           |
| Password / Confirm password | Mot de passe / Confirmer le mot de passe |
| Forgot your password?       | Mot de passe oublié ?                    |
| Remember me                 | Rester connecté                          |
| Sign in with a passkey      | Se connecter avec une clé d'accès        |
| Or continue with email      | Ou continuer avec votre e-mail           |
| Two-factor authentication   | Double authentification                  |
| Recovery code               | Code de secours                          |

Ponctuation française : espace avant `?`, `!`, `:` ; apostrophe `’`.

## Écran de référence

Les pages elles-mêmes, en local : `/login`, `/register`, `/forgot-password`, le lien reçu par e-mail pour `/reset-password/…`, `/email/verify`, `/user/confirm-password`, `/two-factor-challenge`. Pour voir l'e-mail de réinitialisation en local, il est écrit dans `storage/logs/laravel.log`.

## Fichiers autorisés

```
~ resources/js/pages/auth/Login.vue
~ resources/js/pages/auth/Register.vue
~ resources/js/pages/auth/ForgotPassword.vue
~ resources/js/pages/auth/ResetPassword.vue
~ resources/js/pages/auth/VerifyEmail.vue
~ resources/js/pages/auth/ConfirmPassword.vue
~ resources/js/pages/auth/TwoFactorChallenge.vue
~ resources/js/components/PasskeyVerify.vue          textes par défaut uniquement
~ tests/Feature/Auth/*.php                           seulement si un test vérifie un texte anglais
```

## Base de données

Aucune.

## Critères d'acceptation

- [ ] Plus aucun texte anglais visible sur les 7 pages (vérifié dans le navigateur, en étant déconnecté).
- [ ] Le titre de l'onglet du navigateur est lui aussi en français sur chaque page.
- [ ] Se connecter, se tromper de mot de passe (le message d'erreur est déjà en français), demander un nouveau mot de passe : tout fonctionne comme avant.
- [ ] Aucun autre fichier que ceux listés n'est modifié (`git status` avant le commit).
- [ ] Un seul commit au format `fix(core): traduire les pages de connexion en français`, ou plusieurs commits au même format.
- [ ] `composer ci:check` vert en local, puis la vérification GitHub verte sur la pull request.
- [ ] Pull request ouverte avec le modèle rempli et une capture de la page de connexion.

## Hors périmètre

- Les pages Paramètres › Profil, Sécurité, Apparence (autre ticket).
- Les e-mails envoyés par l'application.
- Changer l'apparence des pages.
