// État de chargement des formulaires (DESIGN.md §4) :
// au moment de l'envoi, le bouton est désactivé et affiche le texte de data-loading-text.
// L'événement « submit » n'est déclenché qu'une fois la validation HTML réussie.
document.querySelectorAll('form[data-loading-text]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button) {
            return;
        }
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = form.dataset.loadingText;
    });
});

// Si la page revient du cache (bouton Précédent), on réactive les boutons.
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});
