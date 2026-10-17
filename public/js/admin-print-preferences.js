(() => {
    let settings = {};
    try {
        settings = JSON.parse(document.body.dataset.printSettings || '{}');
    } catch (_) {}
    const paperRolls = {
        '57x30': 57,
        '57x40': 57,
        '80x40': 80,
        '80x60': 80,
        '80x80': 80,
    };
    const legacyWidth = ['58', '80'].includes(String(settings.paperWidth)) ? Number(settings.paperWidth) : 80;
    const width = paperRolls[settings.paperRoll] || legacyWidth;
    const fontSizes = { small: '10px', normal: '12px', large: '14px', '10': '10px', '12': '12px', '14': '14px' };
    const receiptFonts = {
        monospace: '"Courier New", monospace',
        arial: 'Arial, sans-serif',
        georgia: 'Georgia, serif',
    };
    const padding = settings.padding === 'compact' ? '2mm' : '3mm';
    document.documentElement.style.setProperty('--receipt-width', `${width}mm`);
    document.documentElement.style.setProperty('--receipt-font-size', fontSizes[settings.fontSize] || fontSizes.normal);
    document.documentElement.style.setProperty('--receipt-font-family', receiptFonts[settings.receiptFont] || receiptFonts.monospace);
    document.documentElement.style.setProperty('--receipt-padding', padding);
    document.querySelector('.receipt-preview-label')?.remove();

    // Apply the saved typography directly to the receipt so print engines honor it.
    const receipt = document.querySelector('.cupom');
    if (receipt) {
        const fontFamily = receiptFonts[settings.receiptFont] || receiptFonts.monospace;
        receipt.style.setProperty('font-family', fontFamily, 'important');
        receipt.style.setProperty('font-size', fontSizes[settings.fontSize] || fontSizes.normal, 'important');
        receipt.querySelectorAll('*').forEach(element => {
            element.style.setProperty('font-family', fontFamily, 'important');
        });
    }

    const ticket = document.querySelector('.cupom-container');
    const button = document.querySelector('[data-print-receipt]');
    if (!ticket || !button) return;
    let copies = [];
    button.addEventListener('click', () => window.print());
    if (document.body.dataset.autoPrint === '1') {
        window.addEventListener('load', () => window.setTimeout(() => window.print(), 250));
    }
    window.addEventListener('beforeprint', () => {
        copies.forEach(copy => copy.remove());
        copies = [];
        const count = Math.max(1, Math.min(5, Number.parseInt(settings.copies, 10) || 1));
        for (let i = 1; i < count; i += 1) {
            const copy = ticket.cloneNode(true);
            copy.classList.add('receipt-extra-copy');
            copy.setAttribute('aria-hidden', 'true');
            ticket.parentNode.appendChild(copy);
            copies.push(copy);
        }
    });
    window.addEventListener('afterprint', () => {
        copies.forEach(copy => copy.remove());
        copies = [];
    });
})();
