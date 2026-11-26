# AI-LOG.md — Journal des décisions IA (MusicSwipe)

> Une entrée par tâche, datée, rédigée juste après la vérification. Rubrique clé : **Décision** (accepté / modifié / refusé, et pourquoi).

## Modèle d'entrée
```markdown
## AAAA-MM-JJ — Ticket TX — Titre
- **Outil :** (ex. Claude Code, mode plan puis approbation manuelle)
- **Branche :** feature/...
- **Prompt / Objectif :** résumé de la demande (ou renvoi vers le prompt de tâche)
- **Résultat :** fichiers créés / modifiés
- **Vérification :** php -l, navigateur 390/1440 px, phpMyAdmin, test 2 comptes, diff relu
- **Décision :** accepté / modifié (quoi, pourquoi) / refusé (pourquoi)
- **Commit :** `feat(...): ...`
```

---

## 2026-11-26 — Initialisation du contexte projet et de la BDD MySQL
- **Outil :** Assistant IA (chat) + phpMyAdmin (MAMP)
- **Prompt / Objectif :** Transformer le cahier des charges MusicSwipe v1.0 en fichiers de contexte (`PROJECT.md`, `DESIGN.md`, `AGENTS.md`, `CLAUDE.md`, `README.md`), générer le schéma MySQL (`database.sql`) et la connexion PDO (`includes/db.php`).
- **Résultat :** 5 fichiers de documentation, 7 tables importées avec 10 genres et 8 morceaux de test, secrets isolés dans `config.local.php` (ignoré par `.gitignore`).
- **Vérification :** Tables et relations vérifiées dans phpMyAdmin ; fond sombre `#161522` ajouté pour compléter la palette officielle `#413f5b` / `#f0dcc5`.
- **Décision :** Accepté ; stack PHP / MySQL assumée à la place de Next.js / Supabase (justification : maîtrise du langage, hébergement MAMP local, sécurité équivalente à la RLS par filtrage systématique sur `$_SESSION['user_id']`).

## 2026-11-26 — Audit du contexte et enrichissement des fichiers .md (conception)
- **Outil :** Claude Code (lecture de tous les fichiers, puis modification des fichiers de documentation uniquement, aucun code applicatif)
- **Prompt / Objectif :** Auditer `PROJECT.md`, `DESIGN.md`, `AGENTS.md`, `database.sql`, `includes/db.php`, `.gitignore` au regard du barème (socle, 4 états, sécurité, Git) et détailler les fichiers de contexte.
- **Problèmes relevés :**
  - Aucune entité n'avait un CRUD complet (pas d'Update sur « Mes likes ») → ajout d'une **note personnelle** (`playlist_tracks.personal_note`, migration prévue en T4) + modification du profil (pseudo, genres).
  - `--danger: #f87171` sur `--bg-surface` = 3,6:1 → échec WCAG AA → remplacé par `#fca5a5` (5,3:1). Focus en hex dur remplacé par `--focus-ring`.
  - Recherche décrite comme « immédiate » alors que le barème exige des critères dans l'URL → formulaire GET `likes.php?q=&genre=&sort=&page=`.
  - `includes/db.php` affiche `$e->getMessage()` à l'utilisateur (fuite d'informations, contredit son propre commentaire) → correction planifiée en T0.
  - Pas de protection CSRF ni de `session_regenerate_id` prévue → ajoutées aux règles de sécurité.
  - Vercel ne fait pas tourner PHP + MySQL → hébergeur PHP/MySQL gratuit à choisir (ex. alwaysdata).
  - Le dossier n'est pas encore un dépôt Git → `git init` en T0.
  - `AI-LOG.md` contenait un bloc de code non fermé et `README.md` est tronqué (bloc d'installation non fermé).
- **Résultat :** `PROJECT.md` (tableau de couverture du socle, user flow Mermaid, arborescence, 10 user stories avec cas d'erreur et de sécurité), `DESIGN.md` (tokens complets avec contrastes calculés, composants, tableau des états par écran, guide Figma et nommage des exports), `AGENTS.md` (sécurité, workflow Git, plan de tickets T0–T7, plan de test 2 comptes, définition de « terminé »), `AI-LOG.md` (format corrigé + modèle).
- **Vérification :** contrastes recalculés avec la formule WCAG 2.x (script) ; relecture de cohérence entre `database.sql` et `PROJECT.md`.
- **Décision :** à valider par moi avant tout code. Le hors périmètre a été renforcé (pas de reset de mot de passe par e-mail, pas de playlists multiples).

## 2026-11-26 — Complétion du README.md
- **Outil :** Claude Code
- **Prompt / Objectif :** Compléter le `README.md` tronqué (bloc d'installation non fermé).
- **Résultat :** `README.md` complet : statut, liens (déploiement et Figma à compléter), fonctionnalités MVP, prérequis, installation MAMP/XAMPP en 5 étapes, structure, BDD, sécurité, déploiement, index de la documentation.
- **Vérification :** relecture ; blocs de code fermés ; liens relatifs vers les fichiers .md.
- **Décision :** accepté ; liens Figma et URL publique laissés en « à venir » (pas d'information inventée).

## 2026-11-26 — Wireframes Figma (Design System + écrans mobile)
- **Outil :** Claude Code + serveur MCP Figma (`use_figma`)
- **Prompt / Objectif :** Réaliser les wireframes dans le fichier Figma MusicSwipe à partir de `DESIGN.md` §6 et exporter les écrans dans `docs/screens/`.
- **Résultat :** page « 00 · Design System » (variables liées aux tokens, styles de texte, ombres, 18 icônes, 18 composants avec variantes) ; page « 01 · Mobile » avec 25 écrans couvrant les états rempli / chargement / vide / erreur / aucun résultat / succès ; 25 PNG + 2 vues d'ensemble dans `docs/screens/`.
- **Vérification :** captures relues écran par écran ; contrastes conformes à `DESIGN.md`.
- **Problèmes :** l'opacité appliquée sur une peinture liée à une variable ne s'affichait pas → ajout de 4 tokens translucides (`--primary-soft`, `--skeleton`, `--border-subtle`, `--veil`) documentés dans `DESIGN.md`. Quota d'appels MCP du plan Figma Starter atteint → écrans desktop non réalisés et PNG découpés depuis une capture d'ensemble (basse résolution).
- **Décision :** accepté pour le mobile ; desktop et exports haute résolution à faire (à la main ou quand le quota sera réinitialisé). Une tâche planifiée vérifie le quota chaque lundi à 9 h.

## 2026-11-26 — Ticket T0 — Structure, design tokens et layout de base
- **Outil :** Claude Code (plan T0 validé, puis exécution)
- **Branche :** `chore/setup` (après `git init` et un commit `docs:` sur `main`)
- **Prompt / Objectif :** Poser la structure du projet sans logique métier : tokens CSS, base, composants, gabarits PHP communs, page d'accueil statique, correction de `db.php`.
- **Résultat :** `assets/css/tokens.css`, `base.css`, `components.css` ; `includes/functions.php` (`e()`, `redirect()`, `icon()` en SVG `currentColor`), `header.php`, `footer.php`, `nav.php` (barre d'onglets mobile + en-tête desktop, onglet actif via `aria-current`) ; `index.php` conforme à `01-accueil-mobile` ; `db.php` n'affiche plus l'erreur technique (log + HTTP 500) ; `.gitignore` complété.
- **Vérification :** `php -l` OK sur les 6 fichiers PHP ; aucune couleur en dur hors `tokens.css` (grep) ; rendu vérifié à 390×844 et 1440×900 dans le navigateur ; console sans erreur ; `config.local.php` absent de `git status`.
- **Limites :** les liens « Créer un compte » / « J'ai déjà un compte » pointent vers `register.php` / `login.php` créés en T1 ; `nav.php` n'est pas encore affiché (aucune page privée avant T1) ; le bouton de déconnexion recevra son jeton CSRF en T1.
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T1 — Authentification (US-01, US-02)
- **Outil :** Claude Code (plan T1 validé, puis exécution)
- **Branche :** `feature/auth`
- **Prompt / Objectif :** Inscription, connexion, déconnexion, protection des pages privées, CSRF, sessions sécurisées.
- **Résultat :** `includes/auth.php` (cookie `HttpOnly`/`SameSite=Lax`, `require_login()`, `require_guest()`, `login_user()` avec `session_regenerate_id`, `logout_user()`, en-têtes no-cache), `includes/csrf.php`, `register.php` (transaction compte + playlist « Mes likes »), `login.php` (message générique, rehash), `logout.php` (POST + CSRF), pages provisoires protégées `onboarding/swipe/likes/profil.php`, `assets/js/forms.js` (état chargement des boutons), styles Input / Alert / Auth ; `form_field()` et `json_response()` ajoutés à `functions.php`.
- **Vérification (curl + navigateur + BDD) :** inscription → 302 onboarding, hash `$2y$`, playlist créée ; email existant refusé sans doublon ; champs invalides refusés côté serveur (validation HTML contournée) ; POST sans CSRF → 403 ; mauvais mot de passe → message générique ; 4 pages privées → 302 `login.php` sans session ; `Cache-Control: no-store` ; comptes A et B en parallèle avec sessions distinctes ; déconnexion de A sans effet sur B ; « Précédent » après déconnexion → `login.php` ; pseudo `<script>` affiché échappé ; `php -l` OK ; console sans erreur ; rendu 390 / 1440 px conforme aux maquettes `02-*`. Comptes de test supprimés ensuite.
- **Écarts au plan :** ajout de `assets/js/forms.js` (état de chargement exigé par `DESIGN.md` §4) et d'un bouton de déconnexion sur `profil.php` (seul accès à la déconnexion sur mobile).
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T2 — Onboarding des genres (US-03)
- **Outil :** Claude Code (plan T2 validé, puis exécution)
- **Branche :** `feature/onboarding-genres`
- **Prompt / Objectif :** Choix d'au moins un genre préféré, enregistré dans `user_genres`, avec double validation.
- **Résultat :** `includes/genres.php` (`get_all_genres`, `get_user_genre_ids`, `filter_valid_genre_ids`, `save_user_genres` en transaction — réutilisables en T6) ; `onboarding.php` (pastilles = vraies cases à cocher dans un `fieldset`, pré-cochage, état vide si aucun genre en base) ; `assets/js/onboarding.js` (compteur `aria-live`, blocage de l'envoi sans genre) ; styles GenrePill ; garde-fou dans `swipe.php` (sans genre → onboarding).
- **Vérification :** envoi vide → message serveur, 0 ligne ; `genres[]=999&genres[]=abc` seuls → refusé ; 999 et doublon ignorés dans un envoi valide → 3 lignes exactes ; pré-cochage ; remplacement sans doublon ; sans CSRF → 403 ; sans session → `login.php` ; redirections après connexion (sans genre → onboarding, avec → swipe) ; 2 comptes : `user_id=1` injecté par B ignoré, genres de A intacts ; navigateur : validation JS, clavier (Tab + Espace) avec focus visible, rendu 390 / 1440 px, console sans erreur. Comptes de test supprimés.
- **Bug trouvé et corrigé :** `.field__error { display: flex }` écrasait l'attribut `hidden` (message d'erreur toujours visible) → règle globale `[hidden] { display: none !important; }` dans `base.css`. Le navigateur gardait l'ancienne feuille en cache → ajout de `asset()` (URL versionnée `?v=date`) pour les CSS et JS.
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T3 — Swipe et lecteur audio 30 s (US-04)
- **Outil :** Claude Code (plan T3 validé, puis exécution)
- **Branche :** `feature/swipe-player`
- **Prompt / Objectif :** Découverte par swipe (glisser, boutons, clavier), lecteur d'extraits 30 s, enregistrement des likes / dislikes, 6 états d'écran.
- **Résultat :** `includes/tracks.php` (playlist de l'utilisateur, morceaux restants, prochains morceaux avec genres préférés en premier) ; `api/get_tracks.php` (GET) et `api/swipe_action.php` (POST JSON + `X-CSRF-Token`, transaction swipe + playlist, doublon → `already: true`) ; `swipe.php` (structure + états) ; `assets/js/player.js` (un seul `<audio>`, arrêt à 30 s, états chargement / lecture / pause / erreur) ; `assets/js/swipe.js` (pointer events avec seuil 100 px, ← → Espace, file de morceaux, textes via `textContent`) ; styles TrackCard, AudioPlayer, squelettes, StateBlock, Toast.
- **Vérification :** API (curl) : 401 sans session, 403 sans CSRF et avec le jeton d'un autre compte, 405, 400 action / id invalides, 404 morceau inconnu, like → `swipes` + `playlist_tracks`, dislike → `swipes` seul, doublon → 1 seule ligne, `user_id` / `playlist_id` envoyés par B ignorés (ligne au nom de B), morceaux déjà swipés exclus, ordre par genres préférés. Navigateur : lecture puis arrêt exact à 0:30, arrêt de l'audio au swipe, like clavier + message « Ajouté à Mes likes », glisser à gauche = dislike, glisser court = retour en place, extrait cassé → « Extrait indisponible » et swipe toujours possible, réseau coupé → carte conservée + Réessayer qui enregistre, erreur de chargement + Réessayer, pile vide → « Tu as tout découvert ! » (et toujours vide après rechargement), rendu 390 / 1440 px conforme aux maquettes `04-*`. Comptes de test supprimés.
- **Bug trouvé et corrigé :** en cas de coupure réseau, le message technique du navigateur (« Failed to fetch ») s'affichait → remplacé par « Ton choix n'a pas pu être enregistré. Vérifie ta connexion. ».
- **Point noté pour T7 :** une session dont l'utilisateur a été supprimé en base reste ouverte (`require_login()` ne revérifie pas l'existence du compte) ; à traiter lors de l'audit sécurité.
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T4 — Playlist « Mes likes » : lecture, note, suppression (US-05, US-07, US-08)
- **Outil :** Claude Code (plan T4 validé, puis exécution ; session reprise après une interruption — la branche, la migration et `database.sql` déjà préparés ont été vérifiés puis conservés)
- **Branche :** `feature/likes-crud`
- **Prompt / Objectif :** Compléter le CRUD de l'entité « Mes likes » (`playlist_tracks`) : Read (liste), Update (note personnelle), Delete (retrait confirmé) ; Create = like au swipe (T3).
- **Résultat :** migration `migrations/001_playlist_tracks_personal_note.sql` (appliquée en local) + colonne dans `database.sql` + étape dans `README.md` ; `includes/likes.php` (toutes les requêtes passent par `playlists.user_id = utilisateur connecté`) ; `api/update_note.php` (trim, 255 caractères max, vide = `NULL`, 404 si le morceau n'est pas dans SA playlist) ; `api/delete_like.php` (404 si 0 ligne, historique `swipes` conservé) ; `likes.php` (liste serveur, états rempli / vide / erreur) ; `assets/js/likes.js` (écoute un extrait à la fois et 30 s max, éditeur de note avec compteur, `<dialog>` de confirmation, messages de succès) ; styles TrackRow, IconButton, éditeur, Modal.
- **Vérification :** API (curl, 2 comptes) : note enregistrée avec trim, même note → 200 (vérification préalable car un UPDATE inchangé renvoie 0 ligne), note vide → `NULL`, 256 caractères → 400 (255 caractères accentués acceptés), note non texte → 400, B qui modifie ou supprime chez A → 404 et données de A intactes, jeton de A utilisé par B → 403, sans session → 401, sans CSRF → 403, GET → 405, double suppression → 404, swipe conservé et morceau non reproposé, note `<script>` affichée échappée. Navigateur : liste conforme à `05-likes-mobile`, édition + « Note enregistrée » + focus rendu au bouton ✎, erreurs 255 caractères et réseau (texte conservé), Annuler, modale (focus sur Annuler, Échap sans suppression, focus rendu), erreur réseau dans la modale, retrait pendant la lecture (audio coupé), compteur, état vide, un seul extrait à la fois et arrêt à 30 s, rendu 1440 px en 2 colonnes. Comptes de test supprimés.
- **Bugs trouvés et corrigés :** (1) la modale s'affichait en haut de l'écran : le reset `* { margin: 0 }` annulait le centrage natif du `<dialog>` → `margin: auto` ; (2) l'icône du message de succès faisait 0 px (SVG `max-width: 100 %` dans une ligne flex) → `flex-shrink: 0` sur les icônes de toast, y compris sur la page Swipe.
- **Non testé en direct :** l'état « erreur de lecture de la base » de `likes.php` (il faudrait casser la base locale) ; vérifié par relecture du code (try/catch → bloc d'erreur + Réessayer).
- **À noter :** le service d'images de test (picsum) renvoie parfois une 404 ; l'icône de remplacement s'affiche alors comme prévu.
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T5 — Recherche, filtre, tri et pagination de « Mes likes » (US-06)
- **Outil :** Claude Code (plan T5 validé, puis exécution)
- **Branche :** `feature/likes-search`
- **Prompt / Objectif :** Rechercher (titre / artiste), filtrer par genre, trier et paginer « Mes likes », critères conservés dans l'URL (`$_GET`) ; ajouter 20 morceaux de test.
- **Résultat :** `migrations/002_seed_more_tracks.sql` (20 morceaux, 2 par genre, `INSERT IGNORE`, appliquée en local) + mêmes lignes dans `database.sql` ; `includes/likes.php` : `read_like_filters()` (liste blanche, valeurs invalides → défaut), `search_user_likes()` (requête préparée, jokers `%` / `_` neutralisés avec `ESCAPE`, `ORDER BY` choisi dans `LIKE_SORTS`, `LIMIT`/`OFFSET` entiers, page trop grande → dernière page), `count_user_likes()`, `likes_url()` ; `likes.php` : formulaire GET (recherche, genre, tri, Rechercher, Réinitialiser), résumé « N résultats · page X sur Y », pagination accessible, états distincts vide / aucun résultat / chargement ; `likes.js` : envoi automatique au changement de genre ou de tri, URL sans champs vides, squelettes pendant le chargement, rechargement si la page devient vide après une suppression, retour arrière propre ; styles filtres, pagination, squelettes de lignes.
- **Vérification :** `q=weeknd&genre=1&sort=artist` → les 2 titres de The Weeknd dans l'ordre ; insensible à la casse et aux accents ; recherche sur l'artiste ; 4 tris ; page 2 et `page=99` → dernière page ; liens de pagination qui gardent les critères ; critères pré-remplis après rechargement ; `sort=hack&genre=999&page=abc` et paramètres en tableau → valeurs par défaut, HTTP 200 ; « Aucun résultat » + Réinitialiser ; `' OR 1=1 --`, `%` et `_` → aucun résultat en trop ; `"><script>` réaffiché échappé ; recherche tronquée à 100 caractères ; 2 comptes : B ne voit jamais les likes de A ; note et retrait (T4) toujours fonctionnels ; navigateur : saisie + Entrée, envoi automatique du genre avec squelette, pagination, bouton Précédent, rendu 390 / 1440 px. Comptes de test supprimés.
- **Corrigé pendant les tests :** l'option « Tous les genres » était coupée à 390 px → « Genre : tous ».
- **Limite connue (cosmétique) :** après avoir vidé une page par suppressions, la page se recharge sur la dernière page valide mais l'URL garde l'ancien `page=`.
- **Décision :** à relire (`git diff`) puis committer.

## 2026-11-26 — Ticket T6 — Dashboard et modification du profil (US-09, US-10)
- **Outil :** Claude Code (plan T6 validé, puis exécution)
- **Branche :** `feature/dashboard-profile`
- **Prompt / Objectif :** Tableau de bord avec compteurs réels (swipes, likes, dislikes, taux de like, genre le plus liké, genres préférés) et modification du pseudo et des genres (Update sur `users` et `user_genres`).
- **Décision de conception :** likes = morceaux actuellement dans « Mes likes » (règle de `PROJECT.md`) ; taux de like = swipes « like » ÷ swipes, calculé sur l'historique pour ne pas changer quand on retire un morceau ; « — » sans swipe. Règle ajoutée dans `PROJECT.md` §6.
- **Résultat :** `includes/stats.php` (`get_user_stats()`, requêtes filtrées par l'utilisateur) ; `profil.php` (dashboard + formulaire, validation serveur, transaction pseudo + genres, Post/Redirect/Get avec message de succès unique, états rempli / vide / erreur / chargement / succès) ; `includes/genre_picker.php` (pastilles sorties de `onboarding.php` et partagées) ; `save_user_genres()` peut s'intégrer à une transaction déjà ouverte ; styles StatCard, avatar, profil.
- **Vérification :** nouveau compte → compteurs 0, taux « — », « Commencer à swiper » ; 3 likes + 2 dislikes → 5 / 3 / 2 / 60 %, identiques aux `COUNT(*)` SQL ; après retrait d'un like → likes 2, taux toujours 60 % ; genre le plus liké exact ; compte B à 0 ; B qui envoie `user_id` = A → seul B est modifié ; modification pseudo + 2 genres → `users` et `user_genres` à jour, message affiché une seule fois (rien au rechargement) ; pseudo vide, 1 caractère, 31 caractères, espaces seuls, aucun genre, genre 999 → refusés avec message par champ et rien d'enregistré ; sans CSRF → 403 ; pseudo `<script>` affiché échappé ; onboarding inchangé après le déplacement des pastilles ; navigateur : blocage à l'écran sans genre, rendu 390 / 1440 px conforme aux maquettes `06-*`. Comptes de test supprimés.
- **Écart au plan :** la barre de navigation n'affiche pas le pseudo (elle ne l'a jamais affiché) ; le nouveau pseudo apparaît dans le titre et l'avatar du profil.
- **Décision :** à relire (`git diff`) puis committer.
