<?php
declare(strict_types=1);

/**
 * Échappe une valeur pour un affichage HTML sûr (protection XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * URL d'un fichier statique avec sa date de modification (?v=...) :
 * le navigateur recharge le fichier dès qu'il change au lieu d'utiliser une version en cache.
 */
function asset(string $path): string
{
    $file = __DIR__ . '/../' . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

/**
 * Redirige vers une page interne puis arrête le script.
 */
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/**
 * Envoie une réponse JSON { ok, data|error } avec le code HTTP donné puis arrête le script.
 */
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Champ de formulaire accessible (label, aide, message d'erreur relié par aria-describedby).
 * $attrs : attributs HTML supplémentaires déjà sûrs (ex. 'required minlength="8"').
 */
function form_field(string $name, string $label, string $type, string $value, ?string $error, string $attrs = '', ?string $hint = null): string
{
    $id = 'f-' . $name;
    $describedBy = [];
    $html = '<div class="field' . ($error !== null ? ' is-invalid' : '') . '">';
    $html .= '<label class="field__label" for="' . e($id) . '">' . e($label) . '</label>';

    $extra = '';
    if ($hint !== null) {
        $describedBy[] = $id . '-hint';
    }
    if ($error !== null) {
        $describedBy[] = $id . '-error';
        $extra .= ' aria-invalid="true"';
    }
    if ($describedBy) {
        $extra .= ' aria-describedby="' . e(implode(' ', $describedBy)) . '"';
    }

    $html .= '<input class="field__input" id="' . e($id) . '" name="' . e($name) . '" type="' . e($type) . '"'
        . ($type === 'password' ? '' : ' value="' . e($value) . '"')
        . ' ' . $attrs . $extra . '>';

    if ($hint !== null) {
        $html .= '<p class="field__hint" id="' . e($id) . '-hint">' . e($hint) . '</p>';
    }
    if ($error !== null) {
        $html .= '<p class="field__error" id="' . e($id) . '-error">' . icon('alert', 16) . '<span>' . e($error) . '</span></p>';
    }
    return $html . '</div>';
}

/**
 * Retourne une icône SVG inline (couleur héritée via currentColor).
 * Icônes décoratives : aria-hidden, le texte accessible est porté par l'élément parent.
 */
function icon(string $name, int $size = 24): string
{
    $paths = [
        'heart'  => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'x'      => '<path d="M18 6 6 18M6 6l12 12"/>',
        'play'   => '<path d="M6 3l14 9-14 9V3z"/>',
        'pause'  => '<path d="M6 4h4v16H6zM14 4h4v16h-4z"/>',
        'music'  => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'cards'  => '<rect x="6" y="3" width="12" height="18" rx="2"/>',
        'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'alert'  => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'check'  => '<path d="M20 6 9 17l-5-5"/>',
        'refresh' => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/>',
    ];
    $filled = in_array($name, ['heart', 'play', 'pause'], true);
    $body = $paths[$name] ?? '';

    return sprintf(
        '<svg class="icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" '
        . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
        $size,
        $filled ? 'currentColor' : 'none',
        $body
    );
}
