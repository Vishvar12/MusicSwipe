# AGENTS.md — MusicSwipe

## Contexte
Lis `PROJECT.md` (périmètre, entités, user stories, user flow) et `DESIGN.md` (tokens, composants, états, maquettes `docs/screens/`) avant toute tâche. Si une maquette existe pour l'écran concerné dans `docs/screens/`, lis-la avant de coder.

## Stack technique
- **Front-end :** HTML5 sémantique, CSS3 (variables dans `assets/css/tokens.css`, mobile-first), JavaScript natif (swipe, lecteur audio 30 s, `fetch`).
- **Back-end :** PHP 8+ sans framework, sessions PHP natives.
- **Base de données :** MySQL 8 (phpMyAdmin / MAMP), accès **uniquement via PDO et requêtes préparées**.
- **API externe (BONUS, ticket T7 uniquement) :** Deezer (`https://api.deezer.com/search?q=...`) ou iTunes Search (`https://itunes.apple.com/search?term=...&media=music`), gratuites et sans clé.

## Structure du projet
```
index.php, register.php, login.php, logout.php
onboarding.php, swipe.php, likes.php, profil.php
api/                 endpoints JSON : get_tracks.php, swipe_action.php,
                     update_note.php, delete_like.php
includes/
  db.php             connexion PDO (lit config.local.php)
  auth.php           session_start + require_login() + redirection login.php
  csrf.php           csrf_token() / csrf_check()
  functions.php      e() = htmlspecialchars(..., ENT_QUOTES, 'UTF-8'), helpers
  header.php, footer.php, nav.php
assets/css/          tokens.css, base.css, components.css
assets/js/           swipe.js, player.js, likes.js
docs/screens/        exports Figma (référence visuelle)
database.sql         schéma + données de test (importable phpMyAdmin)
config.example.php   modèle de config (versionné) ; config.local.php (ignoré)
```

## Commandes & vérifications
- Serveur local : MAMP (`http://localhost:8888/MusicSwipe/`) ou `php -S localhost:8000`.
- Avant de conclure un ticket : `php -l` sur **chaque** fichier PHP modifié, et montrer la sortie.
- Console navigateur (F12) sans erreur ; logs PHP MAMP (`/Applications/MAMP/logs/php_error.log`) sans nouvelle erreur.
- Vérifier le rendu à 390 px et 1440 px (DevTools, mode appareil).

## Conventions
- Tables et colonnes MySQL en anglais `snake_case` ; fichiers PHP en minuscules.
- Mots de passe : `password_hash($pwd, PASSWORD_DEFAULT)` / `password_verify()`.
- Toute sortie HTML de données passe par `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
- CSS : uniquement les variables de `tokens.css` (jamais de couleur hexadécimale en dur hors de ce fichier). Réutiliser les composants de `DESIGN.md` avant d'en créer un nouveau.
- Les endpoints `api/` répondent en JSON `{ "ok": bool, "data"|"error": ... }` avec un code HTTP adapté (200, 400, 401, 403, 404, 500).
- Recherche / filtre / tri / pagination via formulaire **GET** (`likes.php?q=&genre=&sort=&page=`) ; valeurs de `sort` et `genre` validées par liste blanche côté serveur.
- Un seul extrait audio joue à la fois, limité à 30 s ; l'audio s'arrête à chaque swipe.
- Chaque écran dynamique implémente les états de `DESIGN.md` §4 (chargement, vide, erreur + Réessayer, succès, aucun résultat).
- Messages utilisateur en français, sans jargon technique.

## Sécurité (équivalent RLS en PHP / MySQL)
1. **Identité côté serveur uniquement :** l'utilisateur courant vient toujours de `$_SESSION['user_id']`, jamais d'un champ de formulaire, d'un paramètre d'URL ou du corps JSON.
2. **Chaque requête SQL sur une donnée personnelle filtre par propriétaire** (`WHERE user_id = :uid`, ou jointure `playlists.user_id = :uid` pour `playlist_tracks`). Après un `UPDATE`/`DELETE`, vérifier `rowCount()` et renvoyer 403/404 si 0.
3. **Requêtes préparées PDO** pour toute valeur variable ; aucune concaténation SQL (y compris `ORDER BY` : liste blanche).
4. **Double validation :** attributs HTML (`required`, `minlength`, `maxlength`, `type="email"`) + validation PHP qui refuse et réaffiche l'erreur.
5. **CSRF :** jeton de session vérifié sur tout POST (formulaires et `fetch` via en-tête `X-CSRF-Token`). Déconnexion en POST.
6. **Sessions :** `session_regenerate_id(true)` après connexion/inscription ; cookie `HttpOnly`, `SameSite=Lax`.
7. **Erreurs :** jamais de `$e->getMessage()` affiché à l'utilisateur ; journaliser avec `error_log()` et afficher un message générique.
8. **Secrets :** identifiants BDD seulement dans `config.local.php` (ignoré par Git). Vérifier `git status` avant chaque commit.

## Interdits
- Aucun secret ni mot de passe réel dans un fichier versionné.
- Aucune concaténation SQL.
- Aucune confiance au client pour l'identité ou la propriété d'une donnée.
- Aucune fonctionnalité de la section « Won't have » de `PROJECT.md` (social, profils publics, admin, paiement, blind test, humeur…).
- Aucun framework ni librairie (pas de React, Bootstrap, Tailwind, jQuery, Composer) sans validation explicite.
- Pas de réécriture complète d'un fichier existant : modifications minimales.
- Pas d'API externe avant que T1 → T6 soient terminés et déployés.

## Workflow Git & agent (obligatoire)
1. **Explorer & planifier :** lire le contexte, proposer un plan court (fichiers créés / modifiés, critères de fin) et **attendre ma validation**.
2. **Brancher :** `git switch main && git pull` puis `git switch -c feature/<ticket>` (1 ticket = 1 branche).
3. **Coder :** un seul ticket à la fois, modifications minimales.
4. **Vérifier :** `php -l`, test navigateur (mobile + desktop), test phpMyAdmin, **test 2 comptes** si le ticket touche des données personnelles. Montrer les preuves.
5. **Relire :** je relis `git diff` ; tout ajout non demandé est refusé.
6. **Committer :** proposer un message au format Conventional Commits (`feat(likes): ...`, `fix(auth): ...`, `docs: ...`), puis `git merge` dans `main` après validation.
7. **Journaliser :** ajouter une entrée dans `AI-LOG.md` (objectif, résultat, vérification, décision accepté / modifié / refusé).
8. Après deux échecs sur le même problème : s'arrêter, revenir au dernier commit (`git restore`), reformuler plus petit.

## Plan de tickets
| Ticket | Branche | Contenu | Critères de fin |
|---|---|---|---|
| T0 | `chore/setup` | `git init`, `.gitignore`, structure de dossiers, `tokens.css`, header/footer/nav, `functions.php`, correction de `db.php` (pas de message d'erreur brut) | Page vide stylée à 390 / 1440 px, aucun hex hors `tokens.css`, `config.local.php` absent de `git status` |
| T1 | `feature/auth` | US-01, US-02 : register, login, logout, `auth.php`, `csrf.php`, création de la playlist « Mes likes » | Les 4 critères US-01/02 passent ; mot de passe haché en base ; pages privées redirigent en navigation privée |
| T2 | `feature/onboarding-genres` | US-03 : `onboarding.php`, redirection si aucun genre | `user_genres` rempli ; erreur si 0 genre (écran + serveur) |
| T3 | `feature/swipe-player` | US-04 : `swipe.php`, `api/get_tracks.php`, `api/swipe_action.php`, `swipe.js`, `player.js` | Like/dislike en base ; pas de morceau reproposé ; audio stoppé au swipe et limité à 30 s ; 5 états OK ; clavier ← → |
| T4 | `feature/likes-crud` | US-05, US-07, US-08 : migration `ALTER TABLE playlist_tracks ADD personal_note VARCHAR(255) NULL` (ajoutée à `database.sql`), `likes.php`, `api/update_note.php`, `api/delete_like.php`, modale | Read / Update / Delete fonctionnels ; B ne peut ni modifier ni supprimer chez A (testé) |
| T5 | `feature/likes-search` | US-06 : recherche, filtre genre, tri, pagination 10/page, critères dans l'URL ; +20 morceaux de test | URL partageable, survit au rechargement ; « Aucun résultat » ; valeurs invalides ignorées |
| T6 | `feature/dashboard-profile` | US-09, US-10 : `profil.php` compteurs + modification pseudo / genres | Compteurs = `COUNT(*)` phpMyAdmin ; compte B à 0 ; Update visible en base |
| T7 | `chore/security-deploy` puis `feature/bonus-api` | Audit sécurité complet (checklist ci-dessous), déploiement public, README final ; puis bonus API Deezer/iTunes | Checklist 2 comptes cochée et notée dans `AI-LOG.md` ; URL publique fonctionnelle |

## Plan de test à 2 comptes (Compte A vs Compte B)
Compte A dans une fenêtre normale, compte B dans une fenêtre de navigation privée.
| # | Test | Résultat attendu |
|---|---|---|
| 1 | A like 3 morceaux ; B ouvre `likes.php` | B ne voit aucun morceau de A |
| 2 | B ouvre `profil.php` | Compteurs de B uniquement (0 au départ) |
| 3 | B envoie (DevTools → Console, `fetch`) une suppression avec le `track_id` d'un morceau de A | Réponse 403/404, ligne de A intacte dans phpMyAdmin |
| 4 | B envoie une modification de note sur un morceau de A | Réponse 403/404, note de A inchangée |
| 5 | B modifie un `user_id` ou `playlist_id` dans le corps de la requête | Ignoré : le serveur utilise `$_SESSION['user_id']` |
| 6 | Déconnecté, ouvrir `swipe.php`, `likes.php`, `profil.php`, `api/get_tracks.php` | Redirection `login.php` / JSON 401 |
| 7 | Saisir `<script>alert(1)</script>` comme pseudo et comme note | Texte affiché tel quel, aucun script exécuté |
| 8 | Saisir `' OR 1=1 --` dans la recherche | Aucun résultat inattendu, aucune erreur SQL |
| 9 | POST sans jeton CSRF | Refusé (403) |
| 10 | `git log -p | grep -i "DB_PASS"` | Seule la valeur d'exemple de `config.example.php` apparaît |

## Définition de « terminé » pour un ticket
Critères d'acceptation des user stories concernées passés · 4 états d'écran présents · `php -l` OK · console sans erreur · responsive vérifié · test 2 comptes si données personnelles · diff relu · commit sur sa branche · entrée `AI-LOG.md`.
