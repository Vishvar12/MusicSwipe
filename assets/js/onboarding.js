// Onboarding (US-03) : compteur de genres et validation « au moins un genre » côté écran.
// Le serveur refait la même vérification (onboarding.php).
// Chargé AVANT forms.js : en cas d'erreur, on bloque l'envoi avant l'état de chargement.
const form = document.querySelector('[data-genre-form]');

if (form) {
    const inputs = form.querySelectorAll('input[name="genres[]"]');
    const counter = document.getElementById('genres-count');
    const error = document.getElementById('genres-error');

    const countChecked = () => [...inputs].filter((input) => input.checked).length;

    const updateCounter = () => {
        const count = countChecked();
        counter.textContent = count + (count > 1 ? ' genres choisis' : ' genre choisi');
        counter.classList.toggle('is-active', count > 0);
        if (count > 0) {
            error.hidden = true;
        }
    };

    inputs.forEach((input) => input.addEventListener('change', updateCounter));

    form.addEventListener('submit', (event) => {
        if (countChecked() === 0) {
            event.preventDefault();
            event.stopImmediatePropagation(); // pas d'état « Enregistrement… » si l'envoi est bloqué
            error.hidden = false;
            inputs[0].focus();
        }
    });
}
