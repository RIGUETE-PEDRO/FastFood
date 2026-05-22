/**
 * Carrossel de Produtos - Funcionalidade
 * Permite pausar na hover e clicar em produtos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Basic click behavior: open product modal or log
    const carouselProducts = document.querySelectorAll('.produto-card-mini');
    carouselProducts.forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            const titulo = this.querySelector('.mini-titulo')?.textContent?.trim() ?? '';
            const preco = this.querySelector('.mini-preco')?.textContent?.trim() ?? '';
            const imagem = this.querySelector('img')?.src ?? '';

        });
    });

    // JS-driven continuous loop animation to avoid CSS jump
    const wrapper = document.querySelector('.carousel-produtos');
    const track = document.querySelector('.carousel-produtos .carousel-track');
    if (!wrapper || !track) return;

    // Disable any CSS animation to avoid conflict
    track.style.animation = 'none';

    let originalItems = Array.from(track.children);
    if (originalItems.length === 0) return;

    if (originalItems.length % 2 === 0) {
        const half = originalItems.length / 2;
        const firstHalf = originalItems.slice(0, half);
        const secondHalf = originalItems.slice(half);
        const isAlreadyDuplicated = firstHalf.every((node, index) => node.outerHTML === secondHalf[index].outerHTML);

        if (isAlreadyDuplicated) {
            originalItems = firstHalf;
        }
    }

    function restoreOriginalItems() {
        track.replaceChildren(...originalItems.map((node) => node.cloneNode(true)));
    }

    function fillTrackForViewport() {
        restoreOriginalItems();
        const cycleWidth = track.scrollWidth;

        let guard = 0;
        while (track.scrollWidth < wrapper.clientWidth + cycleWidth && guard < 10) {
            const cloneWrap = document.createDocumentFragment();
            originalItems.forEach(node => cloneWrap.appendChild(node.cloneNode(true)));
            track.appendChild(cloneWrap);
            guard++;
        }

        return cycleWidth;
    }

    let rafId = null;
    let paused = false;
    let lastTimestamp = null;
    let offset = 0.0; // pixels moved (float)
    const durationSeconds = 25; // seconds to move half width

    let halfWidth = 0;
    function computeDimensions() {
        halfWidth = fillTrackForViewport();
        // set will-change to hint browser
        track.style.willChange = 'transform';
    }

    function getSpeed() {
        return halfWidth > 0 ? (halfWidth / durationSeconds) : 0; // px per second
    }

    function step(timestamp) {
        if (lastTimestamp === null) lastTimestamp = timestamp;
        const dt = (timestamp - lastTimestamp) / 1000; // seconds
        lastTimestamp = timestamp;

        if (!paused) {
            const speed = getSpeed();
            offset += speed * dt; // incremental
            if (halfWidth > 0) {
                if (offset >= halfWidth) offset = offset % halfWidth;
                track.style.transform = `translate3d(${-offset}px, 0, 0)`;
            }
        }

        rafId = requestAnimationFrame(step);
    }

    function startAnim() {
        if (rafId) cancelAnimationFrame(rafId);
        lastTimestamp = null;
        rafId = requestAnimationFrame(step);
    }

    function stopAnim() {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = null;
        lastTimestamp = null;
    }

    // Pause on hover / touch
    wrapper.addEventListener('mouseenter', () => { paused = true; });
    wrapper.addEventListener('mouseleave', () => { paused = false; lastTimestamp = null; });
    wrapper.addEventListener('touchstart', () => { paused = true; }, { passive: true });
    wrapper.addEventListener('touchend', () => { paused = false; lastTimestamp = null; }, { passive: true });

    // Pause when tab not visible
    document.addEventListener('visibilitychange', () => { paused = document.hidden; });

    // Restart on resize (recompute dimensions)
    window.addEventListener('resize', () => { offset = 0; lastTimestamp = null; computeDimensions(); });

    // Wait images load to avoid layout shifts causing seam
    const imgs = Array.from(track.querySelectorAll('img'));
    const imagePromises = imgs.map(img => {
        return new Promise(resolve => {
            if (img.complete) return resolve();
            img.addEventListener('load', resolve);
            img.addEventListener('error', resolve);
        });
    });

    Promise.all(imagePromises).then(() => {
        // small timeout to ensure layout settled, then compute dims and start
        setTimeout(() => {
            computeDimensions();
            startAnim();
            console.log('🎠 Carrossel JS controller iniciado (images loaded) - halfWidth=', halfWidth);
        }, 80);
    });
});

