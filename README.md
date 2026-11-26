# 🎵 MusicSwipe

**Swipe, découvre, crée ta playlist.**

MusicSwipe est une application web interactive de découverte musicale inspirée du swipe. Écoutez des extraits de 30 secondes, swipez à droite pour aimer (ajout automatique à la playlist « Mes likes ») ou à gauche pour passer.

> 🚧 **Statut :** phase de conception terminée (contexte + user flow), wireframes en cours. Développement par tickets T0 → T7 (voir `AGENTS.md`).

## Liens
- **Application déployée :** _à venir (ticket T7)_
- **Maquettes Figma :** [MusicSwipe (Figma)](https://www.figma.com/design/6Li5ySI7w7VZokzkZHBZIb/MusicSwipe) — exports dans [`docs/screens/`](docs/screens/)
- **Dépôt GitHub :** https://github.com/Vishvar12/MusicSwipe

## Fonctionnalités (MVP)
- Inscription, connexion, déconnexion ; pages privées protégées
- Onboarding : choix des genres musicaux préférés
- Swipe like / dislike (glisser, boutons ou clavier ← →) avec extrait audio de 30 s
- Playlist « Mes likes » : réécoute, note personnelle modifiable, suppression confirmée
- Recherche, filtre par genre, tri et pagination (critères conservés dans l'URL)
- Dashboard : swipes, likes, dislikes, taux de like, genre favori ; modification du pseudo et des genres
- Responsive mobile-first, Design System accessible (WCAG AA)

## Stack technique
- **Front-end :** HTML5, CSS3 (Design System en variables CSS), JavaScript natif (Vanilla JS)
- **Back-end :** PHP 8+ (PDO, sessions natives), sans framework
- **Base de données :** MySQL 8 (phpMyAdmin)
- **Catalogue audio :** base MySQL ; API gratuite Deezer / iTunes en bonus après validation du MVP

## Prérequis
- MAMP, XAMPP, WAMP ou Laragon (PHP 8+ et MySQL 8), ou PHP 8 en ligne de commande + un serveur MySQL
- Git

## Installation en local (MAMP / XAMPP)

1. **Cloner le dépôt** dans le dossier web du serveur (`/Applications/MAMP/htdocs/` pour MAMP, `C:\xampp\htdocs\` pour XAMPP) :
   ```bash
   git clone https://github.com/Vishvar12/MusicSwipe.git
   cd MusicSwipe
   ```

2. **Créer la base de données :** ouvrir phpMyAdmin, créer une base `musicswipe` (interclassement `utf8mb4_general_ci`), puis onglet **Importer** → sélectionner `database.sql` → **Exécuter**.

   **Base déjà créée avant une mise à jour ?** Exécuter dans l'onglet **SQL** de phpMyAdmin, dans l'ordre, les fichiers du dossier [`migrations/`](migrations/) qui n'ont pas encore été appliqués (ex. `001_playlist_tracks_personal_note.sql`, ajouté au ticket T4). Une base importée depuis le `database.sql` à jour n'en a pas besoin.

3. **Configurer la connexion :** dupliquer le modèle puis renseigner vos identifiants locaux.
   ```bash
   cp config.example.php config.local.php
   ```
   | Serveur | Port MySQL | Utilisateur | Mot de passe |
   |---|---|---|---|
   | MAMP (macOS) | `8889` | `root` | `root` |
   | XAMPP / WAMP | `3306` | `root` | _(vide)_ |

   `config.local.php` est ignoré par Git : ne le committez jamais.

4. **Lancer l'application :**
   - MAMP : démarrer les serveurs puis ouvrir http://localhost:8888/MusicSwipe/
   - XAMPP : http://localhost/MusicSwipe/
   - Sans MAMP :
     ```bash
     php -S localhost:8000
     ```
     puis ouvrir http://localhost:8000

5. **Tester :** créer deux comptes (dont un en navigation privée) pour vérifier l'isolation des données — voir le plan de test dans [`AGENTS.md`](AGENTS.md).

## Structure du projet
```
index.php, register.php, login.php, logout.php
onboarding.php, swipe.php, likes.php, profil.php
api/            endpoints JSON (get_tracks, swipe_action, update_note, delete_like)
includes/       db.php, auth.php, csrf.php, functions.php, header/footer/nav
assets/css/     tokens.css (Design System), base.css, components.css
assets/js/      swipe.js, player.js, likes.js
docs/screens/   maquettes Figma exportées
database.sql    schéma + données de test
```

## Base de données
7 tables reliées par clés étrangères : `users`, `genres`, `user_genres`, `tracks`, `swipes`, `playlists`, `playlist_tracks`. Détail des colonnes et règles métier dans [`PROJECT.md`](PROJECT.md#6-entités-métier-et-relations-mysql).

## Sécurité
- Mots de passe hachés (`password_hash` / `password_verify`), limite de 5 tentatives de connexion puis blocage 5 minutes
- Requêtes préparées PDO uniquement (anti-injection SQL)
- Échappement des sorties `htmlspecialchars` (anti-XSS), en-têtes HTTP de sécurité (CSP, anti-clickjacking) et jeton CSRF sur tous les POST
- Chaque lecture / modification / suppression filtrée par `$_SESSION['user_id']` côté serveur (équivalent de la RLS)
- Fichiers internes (`includes/`, `migrations/`, `*.sql`, `*.md`, `config*.php`) inaccessibles par URL (`.htaccess`)
- Aucun secret versionné (`config.local.php` dans `.gitignore`) ; erreurs PHP masquées en production (`APP_DEBUG`)
- Audit complet et plan de test à 2 comptes : [`docs/security-audit.md`](docs/security-audit.md)

## Déploiement
Vercel n'exécutant pas PHP + MySQL, l'application se déploie sur un hébergeur PHP 8 / MySQL (recommandé : alwaysdata, offre gratuite avec HTTPS). Procédure pas à pas et tests en production : [`docs/DEPLOY.md`](docs/DEPLOY.md).

En local, ajouter `define('APP_DEBUG', true);` dans `config.local.php` pour voir les erreurs PHP à l'écran (sinon elles vont uniquement dans les logs).

## Documentation du projet
| Fichier | Contenu |
|---|---|
| [`PROJECT.md`](PROJECT.md) | Problème, persona, MVP MoSCoW, hors périmètre, entités, user flow, user stories |
| [`DESIGN.md`](DESIGN.md) | Tokens CSS, composants, états d'écran, accessibilité, maquettes |
| [`AGENTS.md`](AGENTS.md) / [`CLAUDE.md`](CLAUDE.md) | Règles techniques, sécurité, workflow Git et agent, tickets |
| [`AI-LOG.md`](AI-LOG.md) | Journal des décisions prises avec l'IA |
| [`docs/security-audit.md`](docs/security-audit.md) | Audit de sécurité et plan de test à 2 comptes |
| [`docs/DEPLOY.md`](docs/DEPLOY.md) | Procédure de déploiement |

## Contexte
Projet réalisé dans le cadre du module « NoCode AI / Vibe Coding — De l'idée au MVP avec l'IA », avec Claude Code comme agent de développement.
