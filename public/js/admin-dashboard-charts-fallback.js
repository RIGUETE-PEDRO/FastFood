(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    function readDashboardData() {
        const dataElement = document.getElementById('dashboard-data');
        if (!dataElement) return null;

        try {
            return JSON.parse(dataElement.textContent || '{}');
        } catch (error) {
            console.error('Nao foi possivel ler os dados do dashboard.', error);
            return null;
        }
    }

    function setupCanvas(canvas) {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        const width = Math.max(320, Math.round(rect.width || canvas.clientWidth || 320));
        const height = Math.max(240, Math.round(rect.height || canvas.clientHeight || 280));
        const ctx = canvas.getContext('2d');

        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.clearRect(0, 0, width, height);

        return { ctx, width, height };
    }

    function drawEmptyState(ctx, width, height, message) {
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = '#667085';
        ctx.font = '700 14px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(message, width / 2, height / 2);
    }

    function drawAxis(ctx, left, top, right, bottom) {
        ctx.strokeStyle = '#e8ded1';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(left, top);
        ctx.lineTo(left, bottom);
        ctx.lineTo(right, bottom);
        ctx.stroke();
    }

    function roundedRect(ctx, x, y, width, height, radius) {
        const safeRadius = Math.min(radius, width / 2, height / 2);

        ctx.beginPath();
        ctx.moveTo(x + safeRadius, y);
        ctx.lineTo(x + width - safeRadius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
        ctx.lineTo(x + width, y + height - safeRadius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
        ctx.lineTo(x + safeRadius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
        ctx.lineTo(x, y + safeRadius);
        ctx.quadraticCurveTo(x, y, x + safeRadius, y);
        ctx.closePath();
    }

    function drawVerticalBars(canvas, labels, values) {
        if (!canvas) return;

        const { ctx, width, height } = setupCanvas(canvas);
        const numericValues = values.map((value) => Number(value || 0));
        const maxValue = Math.max(...numericValues, 0);

        if (!labels.length || maxValue === 0) {
            drawEmptyState(ctx, width, height, 'Sem dados para exibir');
            return;
        }

        const left = 44;
        const right = width - 18;
        const top = 16;
        const bottom = height - 54;
        const chartWidth = right - left;
        const chartHeight = bottom - top;
        const slot = chartWidth / labels.length;
        const barWidth = Math.min(46, slot * 0.58);
        const colors = ['#d62828', '#f97316', '#0b2e5b', '#16a34a', '#6b7280'];

        drawAxis(ctx, left, top, right, bottom);

        labels.forEach((label, index) => {
            const value = numericValues[index] || 0;
            const barHeight = (value / maxValue) * chartHeight;
            const x = left + slot * index + (slot - barWidth) / 2;
            const y = bottom - barHeight;

            ctx.fillStyle = colors[index % colors.length];
            roundedRect(ctx, x, y, barWidth, barHeight, 8);
            ctx.fill();

            ctx.fillStyle = '#101828';
            ctx.font = '800 12px Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(String(value), x + barWidth / 2, Math.max(top + 12, y - 7));

            ctx.fillStyle = '#667085';
            ctx.font = '700 11px Arial, sans-serif';
            ctx.fillText(String(label).slice(0, 12), x + barWidth / 2, bottom + 20);
        });
    }

    function drawHorizontalBars(canvas, labels, values) {
        if (!canvas) return;

        const { ctx, width, height } = setupCanvas(canvas);
        const numericValues = values.map((value) => Number(value || 0));
        const maxValue = Math.max(...numericValues, 0);

        if (!labels.length || maxValue === 0) {
            drawEmptyState(ctx, width, height, 'Sem dados para exibir');
            return;
        }

        const left = 132;
        const right = width - 42;
        const top = 20;
        const barHeight = Math.min(30, (height - 54) / labels.length - 8);
        const gap = 12;
        const chartWidth = right - left;

        labels.forEach((label, index) => {
            const value = numericValues[index] || 0;
            const y = top + index * (barHeight + gap);
            const barWidth = (value / maxValue) * chartWidth;

            ctx.fillStyle = '#667085';
            ctx.font = '800 12px Arial, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillText(String(label).slice(0, 18), left - 12, y + barHeight / 2);

            ctx.fillStyle = '#fff7ed';
            roundedRect(ctx, left, y, chartWidth, barHeight, 8);
            ctx.fill();

            ctx.fillStyle = '#f97316';
            roundedRect(ctx, left, y, Math.max(4, barWidth), barHeight, 8);
            ctx.fill();

            ctx.fillStyle = '#101828';
            ctx.font = '900 12px Arial, sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(String(value), left + Math.max(8, barWidth + 8), y + barHeight / 2);
        });
    }

    function drawDashboardCharts() {
        const data = readDashboardData();
        if (!data) return;

        const existingCharts = window.flashFoodDashboardCharts || {};

        if (!existingCharts.status) {
            drawVerticalBars(
                document.getElementById('pedidosStatusChart'),
                data.statusLabels || [],
                data.statusValores || []
            );
        }

        if (!existingCharts.topProdutos) {
            drawHorizontalBars(
                document.getElementById('topProdutosChart'),
                data.topProdutosLabels || [],
                data.topProdutosValores || []
            );
        }
    }

    ready(() => {
        drawDashboardCharts();
        window.addEventListener('resize', drawDashboardCharts);
    });
})();
