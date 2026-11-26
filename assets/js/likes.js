// Playlist « Mes likes » (US-05, US-07, US-08) : écoute, note personnelle, retrait confirmé.
// Les textes venant de la base sont insérés avec textContent (jamais innerHTML).
(() => {
    const root = document.querySelector('[data-likes]');
    if (!root) {
        return;
    }

    const MAX_SECONDS = 30;
    const NOTE_MAX = 255;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const $ = (selector) => root.querySelector(selector);

    const list = $('[data-likes-list]');
    const empty = $('[data-likes-empty]');
    const countBadge = $('[data-likes-count]');
    const toast = $('[data-toast]');
    const audio = $('[data-audio]');
    const dialog = $('[data-delete-dialog]');
    const heading = root.querySelector('h1');
    let toastTimer = null;

    // --- Utilitaires ---------------------------------------------------------
    const showToast = (message, type = 'success') => {
        $('[data-toast-text]').textContent = message;
        toast.classList.toggle('toast--warning', type === 'warning');
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 2500);
    };

    const showError = (element, message) => {
        element.querySelector('span').textContent = message;
        element.hidden = false;
    };

    const api = async (url, body) => {
        let response;
        try {
            response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify(body),
            });
        } catch {
            throw new Error(''); // réseau coupé : l'appelant affiche un message clair
        }
        if (response.status === 401) {
            window.location.href = 'login.php';
            throw new Error('Session expirée.');
        }
        let payload = null;
        try {
            payload = await response.json();
        } catch {
            payload = null;
        }
        if (!response.ok || !payload || !payload.ok) {
            throw new Error(payload?.error || '');
        }
        return payload.data;
    };

    const setButtonBusy = (button, busyText) => {
        button.dataset.label ??= button.textContent;
        button.disabled = busyText !== null;
        button.textContent = busyText ?? button.dataset.label;
    };

    // Pochettes manquantes : l'icône de remplacement reste visible
    root.querySelectorAll('[data-cover]').forEach((img) => {
        const hide = () => { img.hidden = true; };
        if (img.complete && img.naturalWidth === 0) {
            hide();
        }
        img.addEventListener('error', hide);
    });

    // --- Écoute : un seul extrait à la fois, 30 s maximum ---------------------
    let playingRow = null;

    const setPlaying = (row, playing) => {
        row.classList.toggle('is-playing', playing);
        const button = row.querySelector('[data-play]');
        button.setAttribute('aria-pressed', String(playing));
        button.setAttribute('aria-label', (playing ? 'Mettre en pause ' : 'Écouter ') + row.dataset.title);
    };

    const stopAudio = () => {
        audio.pause();
        if (playingRow) {
            setPlaying(playingRow, false);
            playingRow = null;
        }
    };

    audio.addEventListener('timeupdate', () => {
        if (audio.currentTime >= MAX_SECONDS) {
            stopAudio();
        }
    });
    audio.addEventListener('ended', stopAudio);
    audio.addEventListener('error', () => {
        if (playingRow) {
            stopAudio();
            showToast('Extrait indisponible', 'warning');
        }
    });

    const togglePlay = (row) => {
        if (playingRow === row) {
            stopAudio();
            return;
        }
        stopAudio();
        playingRow = row;
        setPlaying(row, true);
        audio.src = row.dataset.preview;
        audio.play().catch((error) => {
            if (error.name !== 'AbortError' && playingRow === row) {
                stopAudio();
                showToast('Extrait indisponible', 'warning');
            }
        });
    };

    // --- Note personnelle (Update) -------------------------------------------
    const renderNote = (row) => {
        const text = row.querySelector('[data-note-text]');
        const note = row.dataset.note;
        text.textContent = note ? `« ${note} »` : 'Ajouter une note…';
        text.classList.toggle('is-empty', !note);
    };

    const updateCounter = (row) => {
        const input = row.querySelector('[data-note-input]');
        row.querySelector('[data-note-count]').textContent = `${input.value.length} / ${NOTE_MAX} caractères`;
    };

    const openEditor = (row) => {
        const input = row.querySelector('[data-note-input]');
        input.value = row.dataset.note;
        row.querySelector('[data-note-view]').hidden = true;
        row.querySelector('[data-note-form]').hidden = false;
        row.querySelector('[data-note-error]').hidden = true;
        row.classList.add('is-editing');
        updateCounter(row);
        input.focus();
    };

    const closeEditor = (row) => {
        row.querySelector('[data-note-form]').hidden = true;
        row.querySelector('[data-note-view]').hidden = false;
        row.classList.remove('is-editing');
        row.querySelector('[data-edit]').focus();
    };

    const saveNote = async (row) => {
        const form = row.querySelector('[data-note-form]');
        const error = row.querySelector('[data-note-error]');
        const submit = form.querySelector('[type="submit"]');
        const note = row.querySelector('[data-note-input]').value.trim();

        if (note.length > NOTE_MAX) {
            showError(error, `La note ne doit pas dépasser ${NOTE_MAX} caractères.`);
            return;
        }
        error.hidden = true;
        setButtonBusy(submit, 'Enregistrement…');
        try {
            const data = await api(root.dataset.apiNote, { track_id: Number(row.dataset.trackId), note });
            row.dataset.note = data.note ?? '';
            renderNote(row);
            closeEditor(row);
            showToast(data.note ? 'Note enregistrée' : 'Note effacée');
        } catch (err) {
            showError(error, err.message || "La note n'a pas pu être enregistrée. Vérifie ta connexion.");
        } finally {
            setButtonBusy(submit, null);
        }
    };

    // --- Retrait confirmé (Delete) -------------------------------------------
    let pendingRow = null;
    const deleteError = dialog.querySelector('[data-delete-error]');
    const confirmButton = dialog.querySelector('[data-delete-confirm]');

    const summary = $('[data-likes-summary]');

    const updateCount = (count) => {
        if (countBadge) {
            countBadge.textContent = count + (count > 1 ? ' morceaux' : ' morceau');
            countBadge.hidden = count === 0;
        }
        if (count === 0) {
            // Plus aucun like : état vide, sans formulaire de recherche ni pagination
            root.querySelectorAll('[data-hide-when-empty]').forEach((el) => { el.hidden = true; });
            list.hidden = true;
            empty.hidden = false;
            return true;
        }
        if (list.querySelectorAll('[data-track-row]').length === 0) {
            // Page vidée mais il reste des morceaux ailleurs : le serveur recalcule la page à afficher
            window.location.reload();
            return false;
        }
        if (summary) {
            const total = Number(summary.dataset.total) - 1;
            summary.dataset.total = String(total);
            summary.textContent = `${total} ${total > 1 ? 'résultats' : 'résultat'} · page ${summary.dataset.page} sur ${summary.dataset.pages}`;
        }
        return true;
    };

    const askDelete = (row) => {
        pendingRow = row;
        dialog.querySelector('[data-delete-name]').textContent = row.dataset.title;
        deleteError.hidden = true;
        dialog.showModal();
        dialog.querySelector('[data-delete-cancel]').focus(); // choix le moins destructif par défaut
    };

    const confirmDelete = async () => {
        const row = pendingRow;
        if (!row) {
            return;
        }
        deleteError.hidden = true;
        setButtonBusy(confirmButton, 'Suppression…');
        try {
            const data = await api(root.dataset.apiDelete, { track_id: Number(row.dataset.trackId) });
            if (playingRow === row) {
                stopAudio();
            }
            const nextFocus = (row.nextElementSibling || row.previousElementSibling)?.querySelector('[data-play]');
            row.remove();
            pendingRow = null;
            dialog.close();
            if (updateCount(data.count)) {
                showToast('Morceau retiré de Mes likes');
                (nextFocus ?? heading).focus();
            }
        } catch (err) {
            showError(deleteError, err.message || "Le morceau n'a pas pu être retiré. Vérifie ta connexion.");
        } finally {
            setButtonBusy(confirmButton, null);
        }
    };

    dialog.querySelector('[data-delete-cancel]').addEventListener('click', () => dialog.close());
    confirmButton.addEventListener('click', confirmDelete);
    dialog.addEventListener('close', () => {
        // Annulation (bouton ou Échap) : on rend le focus au bouton qui a ouvert la modale
        if (pendingRow) {
            pendingRow.querySelector('[data-delete]').focus();
            pendingRow = null;
        }
    });

    // --- Recherche, filtre, tri, pagination (US-06) --------------------------
    const filtersForm = $('[data-likes-filters]');
    const skeleton = $('[data-likes-skeleton]');

    // État chargement pendant que le serveur prépare la nouvelle page
    const showLoading = () => {
        stopAudio();
        if (list && skeleton) {
            list.hidden = true;
            skeleton.hidden = false;
        }
        root.setAttribute('aria-busy', 'true');
    };

    if (filtersForm) {
        // Un changement de genre ou de tri relance la recherche (le bouton reste utile sans JavaScript)
        filtersForm.querySelectorAll('[data-autosubmit]').forEach((select) => {
            select.addEventListener('change', () => filtersForm.requestSubmit());
        });
        filtersForm.addEventListener('submit', () => {
            // URL plus lisible : on n'envoie pas les champs vides (q= et genre=)
            filtersForm.querySelectorAll('input[name="q"], select[name="genre"]').forEach((field) => {
                field.disabled = field.value.trim() === '';
            });
            showLoading();
        });
    }

    $('[data-likes-pagination]')?.addEventListener('click', (event) => {
        if (event.target.closest('a[href]')) {
            showLoading();
        }
    });

    // Retour arrière depuis le cache du navigateur : on réaffiche la liste et les champs
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            root.removeAttribute('aria-busy');
            skeleton && (skeleton.hidden = true);
            list && (list.hidden = list.querySelectorAll('[data-track-row]').length === 0);
            filtersForm?.querySelectorAll('[disabled]').forEach((field) => { field.disabled = false; });
        }
    });

    // --- Délégation des clics sur les lignes ---------------------------------
    list?.addEventListener('click', (event) => {
        const row = event.target.closest('[data-track-row]');
        if (!row) {
            return;
        }
        if (event.target.closest('[data-play]')) {
            togglePlay(row);
        } else if (event.target.closest('[data-edit]')) {
            openEditor(row);
        } else if (event.target.closest('[data-cancel]')) {
            closeEditor(row);
        } else if (event.target.closest('[data-delete]')) {
            askDelete(row);
        }
    });

    list?.addEventListener('submit', (event) => {
        event.preventDefault();
        saveNote(event.target.closest('[data-track-row]'));
    });

    list?.addEventListener('input', (event) => {
        if (event.target.matches('[data-note-input]')) {
            const row = event.target.closest('[data-track-row]');
            updateCounter(row);
            row.querySelector('[data-note-error]').hidden = true;
        }
    });

    list?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && event.target.matches('[data-note-input]')) {
            closeEditor(event.target.closest('[data-track-row]'));
        }
    });
})();
