(() => {
    const form = document.getElementById('print-settings-form');
    if (!form) return;
    const preview = document.getElementById('paper-preview');
    const settings = window.printSettings || {};
    const receiptFonts = {
        monospace: '"Courier New", monospace',
        arial: 'Arial, sans-serif',
        georgia: 'Georgia, serif',
    };
    const fontStatus = document.querySelector('[data-receipt-font-status]');
    Object.entries(settings).forEach(([name, value]) => {
        const field = form.elements.namedItem(name);
        if (!field) return;
        const legacyFontSizes = { small: '10', normal: '12', large: '14' };
        field.value = name === 'fontSize'
            ? (legacyFontSizes[String(value)] || String(value))
            : String(value);
    });
    const render = () => {
        const width = form.elements.paperWidth.value;
        preview.style.setProperty('--preview-width', `${width}mm`);
        preview.style.setProperty('--receipt-width', `${width}mm`);
        preview.style.setProperty('--receipt-padding', form.elements.padding.value === 'compact' ? '2mm' : '3mm');
        preview.style.setProperty('--receipt-font-size', `${form.elements.fontSize.value}px`);
        preview.style.setProperty('--receipt-font-family', receiptFonts[form.elements.receiptFont.value] || receiptFonts.monospace);
        preview.dataset.copies = form.elements.copies.value;
        preview.dataset.size = width;
    };
    form.addEventListener('change', () => {
        render();
        if (fontStatus) fontStatus.textContent = 'Prévia atualizada. Clique em salvar para gravar a fonte da nota.';
    });
    render();
})();
