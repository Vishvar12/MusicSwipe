// Découverte par swipe (US-04).
// Chargement des morceaux via api/get_tracks.php, décision via api/swipe_action.php.
// Les textes venant de la base sont insérés avec textContent (jamais innerHTML) : pas d'injection HTML.
(() => {
    const root = document.querySelector('[data-swipe]');
    const player = window.MusicSwipePlayer;
    if (!root || !player) {
        return;
    }

    const SWIPE_THRESHOLD = 100; // px à parcourir pour valider un glissement
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const $ = (selector) => root.querySelector(selector);
    const card = $('[data-card]');
    const views = root.querySelectorAll('[data-view]');
    const controls = root.querySelectorAll('[data-swipe-controls]');
    const likeButton = $('[data-swipe-like]');
    const dislikeButton = $('[data-swipe-dislike]');
    const remainingText = $('[data-remaining]');
    const announcer = $('[data-announce]');
    const cover = $('[data-track-cover]');
    const swipeError = $('[data-swipe-error]');
    const swipeErrorText = $('[data-swipe-error-text]');
    const toast = $('[data-toast]');

    let queue = [];
    let current = null;
    let remaining = 0;
    let busy = false;
    let pendingAction = null;
    let toastTimer = null;

    // --- Affichage -----------------------------------------------------------
    const showView = (name) => {
        views.forEach((view) => { view.hidden = view.dataset.view !== name; });
        controls.forEach((el) => { el.hidden = name === 'empty' || name === 'error'; });
        setButtons(name === 'ready' && !busy);
    };

    const setButtons = (enabled) => {
        likeButton.disabled = !enabled;
        dislikeButton.disabled = !enabled;
    };

    const setRemaining = (count) => {
        remaining = count;
        remainingText.textContent = count + (count > 1 ? ' morceaux restants' : ' morceau restant');
    };

    const showToast = (message) => {
        $('[data-toast-text]').textContent = message;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 2500);
    };

    const resetCardPosition = () => {
        card.classList.remove('is-dragging', 'is-leaving');
        card.style.transform = '';
        delete card.dataset.intent;
    };

    const renderCard = (track) => {
        resetCardPosition();
        $('[data-track-title]').textContent = track.title;
        $('[data-track-artist]').textContent = track.artist;
        $('[data-track-genre]').textContent = track.genre;
        cover.hidden = true;
        cover.alt = `Pochette de ${track.title} – ${track.artist}`;
        cover.onload = () => { cover.hidden = false; };
        cover.onerror = () => { cover.hidden = true; }; // l'icône de remplacement reste visible
        cover.src = track.cover_url;
        likeButton.setAttribute('aria-label', `J'aime : ${track.title}`);
        dislikeButton.setAttribute('aria-label', `Je passe : ${track.title}`);
        swipeError.hidden = true;
        announcer.textContent = `Nouveau morceau : ${track.title}, de ${track.artist}`;
        player.load(track.preview_url);
    };

    // --- Appels serveur ------------------------------------------------------
    const api = async (url, { method = 'GET', body } = {}) => {
        const headers = { Accept: 'application/json' };
        if (body !== undefined) {
            headers['Content-Type'] = 'application/json';
            headers['X-CSRF-Token'] = csrfToken;
        }
        let response;
        try {
            response = await fetch(url, {
                method,
                headers,
                credentials: 'same-origin',
                body: body === undefined ? undefined : JSON.stringify(body),
            });
        } catch {
            // Réseau coupé : pas de message technique du navigateur, l'appelant affiche un texte clair
            throw new Error('');
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

    const loadTracks = async () => {
        showView('loading');
        remainingText.textContent = 'Chargement…';
        try {
            const data = await api(root.dataset.apiTracks);
            queue = data.tracks;
            setRemaining(data.remaining);
            next();
        } catch {
            remainingText.textContent = '';
            showView('error');
        }
    };

    const next = () => {
        current = queue.shift() ?? null;
        if (current) {
            renderCard(current);
            showView('ready');
        } else if (remaining > 0) {
            loadTracks(); // la file locale est vide mais il reste des morceaux côté serveur
        } else {
            player.stop();
            setRemaining(0);
            showView('empty');
        }
    };

    const leaveAnimation = (action) => new Promise((resolve) => {
        if (reducedMotion) {
            resolve();
            return;
        }
        card.classList.add('is-leaving');
        const direction = action === 'like' ? 1 : -1;
        card.style.transform = `translateX(${direction * 120}%) rotate(${direction * 18}deg)`;
        setTimeout(resolve, 250);
    });

    const decide = async (action) => {
        if (!current || busy) {
            return;
        }
        busy = true;
        pendingAction = action;
        setButtons(false);
        player.stop(); // l'audio s'arrête à chaque swipe
        swipeError.hidden = true;
        card.dataset.intent = action;

        try {
            const data = await api(root.dataset.apiSwipe, {
                method: 'POST',
                body: { track_id: current.id, action },
            });
            setRemaining(data.remaining);
            if (action === 'like') {
                showToast('Ajouté à Mes likes');
            }
            await leaveAnimation(action);
            busy = false;
            pendingAction = null;
            next();
        } catch (error) {
            busy = false;
            resetCardPosition();
            swipeErrorText.textContent = error.message || "Ton choix n'a pas pu être enregistré. Vérifie ta connexion.";
            swipeError.hidden = false;
            setButtons(true);
        }
    };

    // --- Glisser à la souris ou au doigt -------------------------------------
    let startX = null;
    let deltaX = 0;

    card.addEventListener('pointerdown', (event) => {
        if (busy || event.button !== 0 || event.target.closest('button')) {
            return;
        }
        startX = event.clientX;
        deltaX = 0;
        card.setPointerCapture(event.pointerId);
        card.classList.add('is-dragging');
    });

    card.addEventListener('pointermove', (event) => {
        if (startX === null) {
            return;
        }
        deltaX = event.clientX - startX;
        card.style.transform = `translateX(${deltaX}px) rotate(${deltaX / 20}deg)`;
        if (deltaX > 40) {
            card.dataset.intent = 'like';
        } else if (deltaX < -40) {
            card.dataset.intent = 'dislike';
        } else {
            delete card.dataset.intent;
        }
    });

    const endDrag = () => {
        if (startX === null) {
            return;
        }
        startX = null;
        card.classList.remove('is-dragging');
        if (deltaX > SWIPE_THRESHOLD) {
            decide('like');
        } else if (deltaX < -SWIPE_THRESHOLD) {
            decide('dislike');
        } else {
            resetCardPosition();
        }
    };
    card.addEventListener('pointerup', endDrag);
    card.addEventListener('pointercancel', endDrag);

    // --- Boutons et clavier --------------------------------------------------
    likeButton.addEventListener('click', () => decide('like'));
    dislikeButton.addEventListener('click', () => decide('dislike'));
    $('[data-swipe-retry]').addEventListener('click', () => decide(pendingAction));
    $('[data-reload]').addEventListener('click', loadTracks);

    document.addEventListener('keydown', (event) => {
        if (card.hidden || event.altKey || event.ctrlKey || event.metaKey
            || event.target.closest('input, textarea, select')) {
            return;
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            decide('like');
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            decide('dislike');
        } else if (event.key === ' ' && !event.target.closest('a, button')) {
            // Sur un bouton ou un lien, Espace garde son comportement natif (activer l'élément)
            event.preventDefault();
            player.toggle();
        }
    });

    loadTracks();
})();
