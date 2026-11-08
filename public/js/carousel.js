/**
 * Carrossel de produtos em movimento contínuo, com suporte a arrasto no mouse
 * e no toque. Um arrasto não deve ser confundido com a seleção de um produto.
 */

let suppressCarouselClickUntil = 0;

// Executa antes dos outros manipuladores de clique da página, que abrem o modal.
document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target?.closest('.carousel-produtos') || Date.now() > suppressCarouselClickUntil) return;

    event.preventDefault();
    event.stopImmediatePropagation();
}, true);

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.carousel-produtos');
    const track = wrapper?.querySelector('.carousel-track');
    if (!wrapper || !track) return;

    // A largura e a altura dos cards são definidas pelo CSS, então as imagens
    // não precisam terminar de carregar para a animação começar.
    track.style.animation = 'none';
    track.querySelectorAll('img').forEach((image) => {
        image.draggable = false;
    });

    let originalItems = Array.from(track.children);
    if (originalItems.length === 0) return;

    if (originalItems.length % 2 === 0) {
        const half = originalItems.length / 2;
        const firstHalf = originalItems.slice(0, half);
        const secondHalf = originalItems.slice(half);
        const isAlreadyDuplicated = firstHalf.every((node, index) => node.outerHTML === secondHalf[index].outerHTML);

        if (isAlreadyDuplicated) originalItems = firstHalf;
    }

    let animationFrame = null;
    let paused = false;
    let hoverPaused = false;
    let pointerPaused = false;
    let lastTimestamp = null;
    let offset = 0;
    let cycleWidth = 0;
    const durationSeconds = 25;
    const dragThreshold = 8;
    let activePointer = null;

    function normalizeOffset(value) {
        if (cycleWidth <= 0) return 0;
        return ((value % cycleWidth) + cycleWidth) % cycleWidth;
    }

    function renderOffset() {
        track.style.transform = `translate3d(${-offset}px, 0, 0)`;
    }

    function restoreOriginalItems() {
        track.replaceChildren(...originalItems.map((node) => node.cloneNode(true)));
    }

    function fillTrackForViewport() {
        restoreOriginalItems();

        const groupWidth = track.scrollWidth;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        cycleWidth = groupWidth + gap;
        if (cycleWidth <= 0) return;

        let copies = 0;
        while (track.scrollWidth < wrapper.clientWidth + cycleWidth && copies < 100) {
            const cloneWrap = document.createDocumentFragment();
            originalItems.forEach((node) => cloneWrap.appendChild(node.cloneNode(true)));
            track.appendChild(cloneWrap);
            copies += 1;
        }
    }

    function syncPausedState() {
        paused = hoverPaused || pointerPaused || document.hidden;
    }

    function step(timestamp) {
        if (lastTimestamp === null) lastTimestamp = timestamp;
        const deltaSeconds = Math.min((timestamp - lastTimestamp) / 1000, 0.05);
        lastTimestamp = timestamp;

        if (!paused && cycleWidth > 0) {
            offset = normalizeOffset(offset + (cycleWidth / durationSeconds) * deltaSeconds);
            renderOffset();
        }

        animationFrame = requestAnimationFrame(step);
    }

    function startAnimation() {
        if (animationFrame !== null) cancelAnimationFrame(animationFrame);
        lastTimestamp = null;
        animationFrame = requestAnimationFrame(step);
    }

    function recomputeDimensions() {
        fillTrackForViewport();
        offset = normalizeOffset(offset);
        renderOffset();
    }

    function finishPointer(event) {
        if (!activePointer || event.pointerId !== activePointer.id) return;

        if (activePointer.moved) {
            suppressCarouselClickUntil = Date.now() + 400;
        }

        if (activePointer.dragging) wrapper.classList.remove('is-dragging');
        if (wrapper.hasPointerCapture?.(event.pointerId)) {
            wrapper.releasePointerCapture(event.pointerId);
        }

        activePointer = null;
        pointerPaused = false;
        syncPausedState();
        lastTimestamp = null;
    }

    wrapper.addEventListener('pointerdown', (event) => {
        if (!event.isPrimary || (event.pointerType === 'mouse' && event.button !== 0)) return;

        activePointer = {
            id: event.pointerId,
            startX: event.clientX,
            startY: event.clientY,
            startOffset: offset,
            dragging: false,
            moved: false,
        };
        pointerPaused = true;
        syncPausedState();
    });

    wrapper.addEventListener('pointermove', (event) => {
        if (!activePointer || event.pointerId !== activePointer.id) return;

        const deltaX = event.clientX - activePointer.startX;
        const deltaY = event.clientY - activePointer.startY;
        if (Math.hypot(deltaX, deltaY) >= dragThreshold) activePointer.moved = true;

        if (!activePointer.dragging) {
            if (Math.abs(deltaX) < dragThreshold && Math.abs(deltaY) < dragThreshold) return;
            if (Math.abs(deltaY) > Math.abs(deltaX)) return;

            activePointer.dragging = true;
            wrapper.classList.add('is-dragging');
            try {
                wrapper.setPointerCapture(event.pointerId);
            } catch {
                // O arrasto continua funcionando sem captura em navegadores antigos.
            }
        }

        event.preventDefault();
        offset = normalizeOffset(activePointer.startOffset - deltaX);
        renderOffset();
    });

    document.addEventListener('pointerup', finishPointer);
    document.addEventListener('pointercancel', finishPointer);

    wrapper.addEventListener('pointerenter', (event) => {
        if (event.pointerType !== 'mouse') return;
        hoverPaused = true;
        syncPausedState();
    });
    wrapper.addEventListener('pointerleave', (event) => {
        if (event.pointerType !== 'mouse') return;
        hoverPaused = false;
        syncPausedState();
        lastTimestamp = null;
    });

    document.addEventListener('visibilitychange', syncPausedState);
    window.addEventListener('resize', recomputeDimensions);
    window.addEventListener('pageshow', () => {
        recomputeDimensions();
        syncPausedState();
        startAnimation();
    });

    if ('ResizeObserver' in window) {
        new ResizeObserver(recomputeDimensions).observe(wrapper);
    }

    recomputeDimensions();
    syncPausedState();
    startAnimation();
});
