function pad(value) {
  return String(value).padStart(2, '0');
}

function todayDate() {
  const today = new Date();
  return `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
}

function currentMonth() {
  const today = new Date();
  return `${today.getFullYear()}-${pad(today.getMonth() + 1)}`;
}

function currentYear() {
  return String(new Date().getFullYear());
}

function toDateValue(value) {
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;
  if (/^\d{4}-\d{2}$/.test(value)) return `${value}-01`;
  if (/^\d{4}$/.test(value)) return `${value}-01-01`;
  return todayDate();
}

function toMonthValue(value) {
  if (/^\d{4}-\d{2}$/.test(value)) return value;
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value.slice(0, 7);
  if (/^\d{4}$/.test(value)) return `${value}-${pad(new Date().getMonth() + 1)}`;
  return currentMonth();
}

function toYearValue(value) {
  if (/^\d{4}/.test(value)) return value.slice(0, 4);
  return currentYear();
}

function configureReferenceInput(input, label, period) {
  const previousValue = input.value;

  input.value = '';
  input.removeAttribute('min');
  input.removeAttribute('max');
  input.removeAttribute('step');

  if (period === 'dia') {
    input.type = 'date';
    input.value = toDateValue(previousValue);
    label.textContent = 'Filtrar pelo dia';
    return;
  }

  if (period === 'ano') {
    input.type = 'number';
    input.min = '2000';
    input.max = '2100';
    input.step = '1';
    input.value = toYearValue(previousValue);
    label.textContent = 'Filtrar pelo ano';
    return;
  }

  input.type = 'month';
  input.value = toMonthValue(previousValue);
  label.textContent = 'Filtrar pelo mes';
}

function initDashboardFilter() {
  document.querySelectorAll('[data-dashboard-filter-form]').forEach((form) => {
    const period = form.querySelector('[data-dashboard-periodo]');
    const reference = form.querySelector('[data-dashboard-referencia]');
    const referenceLabel = form.querySelector('[data-dashboard-referencia-label]');

    if (!period || !reference || !referenceLabel) return;

    period.addEventListener('change', () => {
      configureReferenceInput(reference, referenceLabel, period.value);
      reference.focus();
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboardFilter, { once: true });
} else {
  initDashboardFilter();
}
