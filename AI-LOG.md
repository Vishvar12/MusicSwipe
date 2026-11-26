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
