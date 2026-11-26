# Audit de sécurité — MusicSwipe

> Ticket T7 (branche `chore/security-deploy`), réalisé le 26/11/2026 sur l'environnement local MAMP (PHP 8.3, MySQL 8).
> Méthode : relecture du code + tests automatisés en ligne de commande (`curl`, deux sessions distinctes) + vérification dans le navigateur.
> Équivalent PHP / MySQL de la RLS de Supabase : **l'identité vient uniquement de `$_SESSION['user_id']`** et **chaque requête sur une donnée personnelle est filtrée par cet identifiant**.

## 1. Synthèse

| Domaine | Mesure en place | État |
|---|---|---|
| Mots de passe | `password_hash()` (bcrypt) / `password_verify()`, 8 à 72 caractères, rehash automatique | ✅ |
| Injection SQL | PDO, requêtes préparées partout, `ATTR_EMULATE_PREPARES = false` ; tri choisi dans une liste fixe ; `LIMIT`/`OFFSET` liés comme entiers ; jokers `%` `_` neutralisés | ✅ |
| XSS | Toute donnée affichée passe par `e()` (`htmlspecialchars`, `ENT_QUOTES`, UTF-8) ; côté JS uniquement `textContent` (aucun `innerHTML`) ; CSP sans script inline | ✅ |
| CSRF | Jeton aléatoire par session, vérifié sur chaque POST (formulaires + en-tête `X-CSRF-Token` des appels `fetch`) ; déconnexion en POST | ✅ |
| Contrôle d'accès | `require_login()` sur chaque page privée et chaque endpoint `api/` ; requêtes filtrées par `user_id` de session (jointure `playlists.user_id` pour `playlist_tracks`) | ✅ |
| Sessions | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS ; `session_regenerate_id(true)` à la connexion ; `use_strict_mode` ; cookies uniquement ; en-têtes no-cache sur les pages privées | ✅ |
| Validation | Double validation : attributs HTML + JavaScript à l'écran, puis contrôle serveur systématique (listes blanches, longueurs, types) | ✅ |
| Secrets | Identifiants BDD dans `config.local.php`, ignoré par Git ; seul le modèle `config.example.php` est versionné | ✅ |
| Erreurs | Message générique à l'utilisateur, détail dans les logs (`error_log`) ; `display_errors` coupé hors `APP_DEBUG` | ✅ (corrigé, voir §2) |
| En-têtes HTTP | CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, sans `X-Powered-By` | ✅ (corrigé, voir §2) |
| Fichiers internes | `includes/`, `migrations/`, `docs/`, `.git`, `*.sql`, `*.md`, `config*.php` refusés (403) ; pas de liste de dossier | ✅ (corrigé, voir §2) |
| Force brute | Blocage 5 minutes après 5 échecs de connexion (par session) | ⚠️ partiel (voir §4) |

## 2. Problèmes trouvés et corrigés

| # | Problème constaté | Risque | Correction | Preuve après correction |
|---|---|---|---|---|
| 1 | Session « orpheline » : un compte supprimé en base restait connecté (constaté en T3) | Accès avec une identité qui n'existe plus ; erreurs de clé étrangère | `require_login()` et `require_guest()` vérifient `user_exists()` ; sinon déconnexion | Compte supprimé → `api/get_tracks.php` = 401, `swipe.php` → `login.php`, `login.php` = 200 (pas de boucle) |
| 2 | Aucun en-tête de sécurité ; `X-Powered-By: PHP/8.3.14` exposé | Clickjacking, injection de ressources externes, empreinte du serveur | `includes/security.php` (CSP stricte, `frame-ancestors 'none'`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, suppression de `X-Powered-By`) | `curl -I` : en-têtes présents, plus de `X-Powered-By` ; aucune violation CSP dans la console sur les 6 écrans |
| 3 | `database.sql`, `*.md`, `migrations/`, `includes/*.php` accessibles par URL ; `includes/genre_picker.php` affichait une erreur fatale avec le chemin du serveur | Fuite du schéma, de la documentation interne et de l'arborescence serveur | `.htaccess` : `RedirectMatch 403` sur les dossiers internes, `Require all denied` sur les fichiers sensibles, `Options -Indexes` | 16 adresses testées → 403 ; pages et `assets/*.css` → 200 |
| 4 | `display_errors` actif (réglage MAMP) | Messages techniques visibles par les visiteurs en production | Constante `APP_DEBUG` (absente = `false`) : `display_errors` coupé, `log_errors` actif | Script de test avec variable inexistante : seule la sortie normale s'affiche, l'avertissement est dans le log |
| 5 | Connexion sans limite d'essais | Devinette de mot de passe par force brute | 5 échecs → blocage 5 minutes, message clair ; succès → compteur remis à zéro | 5e échec → « Trop de tentatives » ; 6e essai avec le bon mot de passe refusé ; 2 échecs puis succès → connecté |

## 3. Plan de test à 2 comptes (`AGENTS.md`)

Compte A et compte B créés pour l'audit, chacun dans sa propre session. A like 3 morceaux et écrit une note.

| # | Test | Résultat attendu | Résultat obtenu | ✔ |
|---|---|---|---|---|
| 1 | B ouvre `likes.php` | Aucun morceau de A | Liste vide, état « Aucun morceau liké pour l'instant » | ✅ |
| 2 | B ouvre `profil.php` | Compteurs de B uniquement | Swipes 0, Likes 0, Dislikes 0, taux « — » | ✅ |
| 3 | B supprime un like de A (`api/delete_like.php`) | 403/404, ligne intacte | 404 « Ce morceau n'est pas dans ta playlist » ; likes de A en base : 2, 3, 5 | ✅ |
| 4 | B modifie la note d'un like de A | 403/404, note inchangée | 404 ; note de A toujours « note de A » | ✅ |
| 5 | B envoie `user_id` / `playlist_id` de A dans le corps | Ignorés | Like enregistré au nom de B ; suppression chez A → 404 | ✅ |
| 6 | Déconnecté : pages privées et API | Redirection / 401 | `swipe`, `likes`, `profil`, `onboarding` → 302 `login.php` ; `api/get_tracks.php` → 401 | ✅ |
| 7 | Pseudo et note `<script>alert(1)</script>` | Affichés comme texte | `&lt;script&gt;…` dans le titre du profil et dans la note | ✅ |
| 8 | Recherche `' OR 1=1 --` | Aucun résultat en trop, pas d'erreur SQL | « Aucun morceau ne correspond à ta recherche », HTTP 200 | ✅ |
| 9 | POST sans jeton CSRF | 403 | `swipe_action`, `update_note`, `delete_like`, `profil`, `onboarding`, `logout` → 403 | ✅ |
| 10 | `git log -p --all` et `DB_PASS` | Seule la valeur d'exemple | Une seule définition : `define('DB_PASS', 'root')` de `config.example.php` (valeur par défaut publique de MAMP) | ✅ |

**Non-régression après corrections :** inscription → onboarding → chargement des morceaux → like → note → recherche → modification du profil → déconnexion → reconnexion : tout fonctionne.

## 4. Limites connues (non corrigées, assumées)

- **Limite de tentatives par session :** le compteur est stocké dans la session ; un attaquant qui supprime ses cookies à chaque essai repart de zéro. Une protection complète demanderait une table des tentatives par adresse IP et par email (hors périmètre du MVP).
- **Pas de réinitialisation de mot de passe ni de vérification d'email** (hors périmètre dans `PROJECT.md` : nécessite un serveur d'envoi d'e-mails).
- **Données de test externes :** pochettes (picsum.photos) et extraits (soundhelix.com) sont chargés depuis des services tiers autorisés dans la CSP ; picsum renvoie parfois une 404, l'icône de remplacement s'affiche alors.
- **HTTPS :** le cookie `Secure` et le chiffrement dépendent de l'hébergeur (actif sur alwaysdata, voir `docs/DEPLOY.md`) ; en local MAMP, la connexion est en HTTP.
- **`.htaccess` :** les protections du §2 (point 3) supposent Apache avec `AllowOverride` actif (cas de MAMP et d'alwaysdata). À revérifier après déploiement.
