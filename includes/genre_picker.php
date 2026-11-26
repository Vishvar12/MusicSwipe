<?php
/**
 * Sélecteur de genres (pastilles = vraies cases à cocher), partagé par onboarding.php et profil.php.
 * Variables attendues :
 *   $genres      array   tous les genres (get_all_genres)
 *   $selected    int[]   identifiants cochés
 *   $genreError  ?string message d'erreur serveur, ou null
 *   $genreLegend string  légende du groupe (lue par les lecteurs d'écran)
 * Comportement écran (compteur, blocage sans genre) : assets/js/onboarding.js sur un formulaire [data-genre-form].
 */
$genreLegend = $genreLegend ?? 'Genres musicaux';
?>
<fieldset class="genre-picker" aria-describedby="genres-count genres-error">
    <legend class="visually-hidden"><?= e($genreLegend) ?></legend>
    <div class="genre-picker__list">
        <?php foreach ($genres as $genre): ?>
            <label class="genre-pill">
                <input class="genre-pill__input" type="checkbox" name="genres[]"
                       value="<?= (int) $genre['id'] ?>"
                       <?= in_array((int) $genre['id'], $selected, true) ? 'checked' : '' ?>>
                <span class="genre-pill__label">
                    <?= icon('check', 16) ?>
                    <span><?= e($genre['name']) ?></span>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</fieldset>

<p class="field__error" id="genres-error" role="alert" <?= $genreError === null ? 'hidden' : '' ?>>
    <?= icon('alert', 16) ?>
    <span data-genre-error-text><?= e($genreError ?? 'Choisis au moins un genre pour continuer.') ?></span>
</p>

<p class="genre-counter<?= $selected === [] ? '' : ' is-active' ?>" id="genres-count" aria-live="polite">
    <?= e(genre_count_label(count($selected))) ?>
</p>
