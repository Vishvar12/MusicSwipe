<?php
/**
 * Pied de page commun.
 * Variable attendue (optionnelle) : $pageScripts (string[]) chemins de scripts JS à charger.
 */
$pageScripts = $pageScripts ?? [];
?>
    </main>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= e($script) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
