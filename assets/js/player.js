// Lecteur d'extraits (US-04) : un seul élément <audio> pour toute la page,
// donc un seul extrait à la fois, limité à 30 secondes.
// Expose window.MusicSwipePlayer = { load(url), stop(), toggle() } pour swipe.js.
(() => {
    const MAX_SECONDS = 30;
    const audio = document.querySelector('[data-audio]');
    const root = document.querySelector('[data-player]');
    if (!audio || !root) {
        return;
    }

    const toggleButton = root.querySelector('[data-player-toggle]');
    const fill = root.querySelector('[data-player-fill]');
    const bar = root.querySelector('[data-player-bar]');
    const current = root.querySelector('[data-player-current]');
    const errorMessage = root.querySelector('[data-player-error]');
    let source = null;

    const format = (seconds) => '0:' + String(Math.floor(seconds)).padStart(2, '0');

    // États : idle | loading | playing | paused | error
    const setState = (state) => {
        root.dataset.state = state;
        toggleButton.setAttribute('aria-label', state === 'playing' ? "Mettre l'extrait en pause" : "Écouter l'extrait");
        toggleButton.disabled = state === 'error';
        errorMessage.hidden = state !== 'error';
    };

    const render = () => {
        const time = Math.min(audio.currentTime || 0, MAX_SECONDS);
        fill.style.width = (time / MAX_SECONDS) * 100 + '%';
        current.textContent = format(time);
        bar.setAttribute('aria-valuenow', String(Math.floor(time)));
    };

    audio.addEventListener('timeupdate', () => {
        if (audio.currentTime >= MAX_SECONDS) {
            audio.pause(); // fin de l'extrait de 30 s
        }
        render();
    });
    audio.addEventListener('waiting', () => setState('loading'));
    audio.addEventListener('playing', () => setState('playing'));
    audio.addEventListener('pause', () => {
        if (root.dataset.state !== 'error') {
            setState('paused');
        }
    });
    audio.addEventListener('ended', () => setState('paused'));
    audio.addEventListener('error', () => {
        if (audio.getAttribute('src')) {
            setState('error');
        }
    });

    const play = async () => {
        if (!source) {
            setState('error');
            return;
        }
        if (!audio.getAttribute('src')) {
            audio.src = source; // chargement seulement au premier appui (preload="none")
        }
        if (audio.currentTime >= MAX_SECONDS) {
            audio.currentTime = 0;
        }
        setState('loading');
        try {
            await audio.play();
        } catch (error) {
            if (error.name !== 'AbortError') {
                setState('error');
            }
        }
    };

    const stop = () => {
        audio.pause();
        if (audio.getAttribute('src')) {
            audio.currentTime = 0;
        }
        render();
    };

    const load = (url) => {
        stop();
        source = url || null;
        audio.removeAttribute('src');
        audio.load();
        setState('idle');
        render();
    };

    const toggle = () => {
        if (root.dataset.state === 'playing' || root.dataset.state === 'loading') {
            audio.pause();
        } else if (root.dataset.state !== 'error') {
            play();
        }
    };

    toggleButton.addEventListener('click', toggle);
    window.MusicSwipePlayer = { load, stop, toggle };
})();
