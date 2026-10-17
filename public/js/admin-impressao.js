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
    const paperRolls = {
        '57x30': { width: 57, diameter: 30 },
        '57x40': { width: 57, diameter: 40 },
        '80x40': { width: 80, diameter: 40 },
        '80x60': { width: 80, diameter: 60 },
        '80x80': { width: 80, diameter: 80 },
    };
    const previewPixelsPerMillimeter = 6.75;
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
        const roll = form.elements.paperRoll.value;
        const dimensions = paperRolls[roll] || paperRolls['80x40'];
        preview.style.setProperty('--preview-width', `${dimensions.width}mm`);
        preview.style.setProperty('--preview-height', `${dimensions.diameter * previewPixelsPerMillimeter}px`);
        preview.style.setProperty('--receipt-width', `${dimensions.width}mm`);
        preview.style.setProperty('--receipt-padding', form.elements.padding.value === 'compact' ? '2mm' : '3mm');
        preview.style.setProperty('--receipt-font-size', `${form.elements.fontSize.value}px`);
        preview.style.setProperty('--receipt-font-family', receiptFonts[form.elements.receiptFont.value] || receiptFonts.monospace);
        preview.dataset.copies = form.elements.copies.value;
        preview.dataset.size = String(dimensions.width);
        preview.dataset.paperRoll = roll;
        const receiptPreview = preview.querySelector('.cupom');
        if (receiptPreview) {
            const fontFamily = receiptFonts[form.elements.receiptFont.value] || receiptFonts.monospace;
            receiptPreview.style.setProperty('font-family', fontFamily, 'important');
            receiptPreview.style.setProperty('font-size', `${form.elements.fontSize.value}px`, 'important');
            receiptPreview.querySelectorAll('*').forEach(element => {
                element.style.setProperty('font-family', fontFamily, 'important');
            });
        }
    };
    form.addEventListener('change', () => {
        render();
        if (fontStatus) fontStatus.textContent = 'Prévia atualizada. Clique em salvar para gravar a fonte da nota.';
    });
    render();
})();
