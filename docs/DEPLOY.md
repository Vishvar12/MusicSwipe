# Déploiement de MusicSwipe

> Vercel ne fait pas tourner PHP + MySQL : MusicSwipe se déploie sur un hébergeur PHP classique.
> Hébergeur recommandé : **alwaysdata** (offre gratuite : PHP 8, MySQL, phpMyAdmin, accès SSH/SFTP, HTTPS sur un sous-domaine `*.alwaysdata.net`, serveur Apache).
> Les noms exacts des menus peuvent évoluer : en cas de doute, se fier à l'interface d'administration et à la documentation d'alwaysdata.

## Ce que fait l'étudiant (compte et identifiants personnels)
La création du compte, les mots de passe et l'envoi des fichiers se font avec **tes** identifiants : ne les colle jamais dans un fichier versionné ni dans une conversation avec une IA.

### 1. Créer le compte et vérifier PHP
1. Créer un compte gratuit sur alwaysdata (le nom de compte, ex. `musicswipe`, donnera l'adresse `https://musicswipe.alwaysdata.net`).
2. Dans l'administration → **Environnement / PHP** : choisir une version **PHP 8.1 ou plus** (le projet utilise `never`, `str_contains`).

### 2. Créer la base MySQL
1. **Bases de données → MySQL → Ajouter une base** : par exemple `<compte>_musicswipe`.
2. Créer (ou utiliser) un **utilisateur MySQL** avec tous les droits sur cette base ; noter son mot de passe.
3. Noter l'**hôte MySQL** affiché par l'interface (de la forme `mysql-<compte>.alwaysdata.net`) et le **port** (3306).
4. Ouvrir **phpMyAdmin** depuis l'administration, sélectionner la base, onglet **Importer** → `database.sql` → **Exécuter**.
   Le fichier `database.sql` à jour contient déjà les colonnes et les 28 morceaux : les fichiers de `migrations/` ne sont pas nécessaires sur une base neuve.

### 3. Envoyer le code
Le site est servi depuis le dossier `www/` du compte (dossier par défaut d'alwaysdata).

**Option A — Git (recommandée, par SSH) :**
```bash
ssh <compte>@ssh-<compte>.alwaysdata.net
```
```bash
cd ~ && rm -rf www && git clone https://github.com/Vishvar12/MusicSwipe.git www
```

**Option B — SFTP** (FileZilla, Cyberduck…) : hôte `ssh-<compte>.alwaysdata.net`, envoyer le contenu du dépôt dans `www/` **sans** `config.local.php` ni le dossier `.git`.

### 4. Créer la configuration de production (sur le serveur uniquement)
Créer `www/config.local.php` sur le serveur (jamais dans Git), à partir de `config.example.php` :
```php
<?php
define('DB_HOST', 'mysql-<compte>.alwaysdata.net');
define('DB_PORT', '3306');
define('DB_NAME', '<compte>_musicswipe');
define('DB_USER', '<utilisateur MySQL>');
define('DB_PASS', '<mot de passe MySQL>');
define('APP_DEBUG', false); // production : aucune erreur PHP à l'écran
```

### 5. Mettre à jour après un nouveau ticket
```bash
ssh <compte>@ssh-<compte>.alwaysdata.net
```
```bash
cd ~/www && git pull
```
Si le ticket ajoute un fichier dans `migrations/`, l'exécuter une fois dans phpMyAdmin (onglet **SQL**).

## Tests en production (à faire avec Claude après la mise en ligne)
| # | Vérification | Attendu |
|---|---|---|
| 1 | Ouvrir `https://<compte>.alwaysdata.net` | Accueil, cadenas HTTPS |
| 2 | Inscription, onboarding, swipe, Mes likes, recherche, profil | Tout fonctionne comme en local |
| 3 | Test à 2 comptes (§3 de `docs/security-audit.md`), B en navigation privée | Isolation complète |
| 4 | `curl -I https://<compte>.alwaysdata.net/login.php` | En-têtes de sécurité présents, cookie avec `Secure` |
| 5 | `/database.sql`, `/AGENTS.md`, `/includes/db.php`, `/config.local.php` | 403 (le `.htaccess` est bien pris en compte) |
| 6 | Provoquer une erreur (ex. mauvais mot de passe MySQL temporaire) | Message générique, aucun détail technique |
| 7 | Console du navigateur | Aucune erreur, aucune violation CSP |

Une fois validée, ajouter l'URL publique dans `README.md` (section « Liens »).
