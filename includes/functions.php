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
 * Redirige vers une page interne puis arrête le script.
 */
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
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
        'music'  => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'cards'  => '<rect x="6" y="3" width="12" height="18" rx="2"/>',
        'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    ];
    $filled = in_array($name, ['heart', 'play'], true);
    $body = $paths[$name] ?? '';

    return sprintf(
        '<svg class="icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" '
        . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
        $size,
        $filled ? 'currentColor' : 'none',
        $body
    );
}
