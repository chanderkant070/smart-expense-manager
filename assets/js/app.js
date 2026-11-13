document.addEventListener('DOMContentLoaded', () => {
  /* ---------- Modals ---------- */
  const openModal = id => {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('open'); m.setAttribute('aria-hidden', 'false');
    const first = m.querySelector('input:not([type=hidden]),select');
    if (first) setTimeout(() => first.focus(), 30);
  };
  const closeModals = () => document.querySelectorAll('.modal.open').forEach(m => {
    m.classList.remove('open'); m.setAttribute('aria-hidden', 'true');
  });
  document.querySelectorAll('[data-open]').forEach(b => b.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.remove('open');
    openModal(b.dataset.open);
  }));
  document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', closeModals));
  document.querySelectorAll('.modal').forEach(m => m.addEventListener('mousedown', e => { if (e.target === m) closeModals(); }));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

  /* ---------- Mobile menu ---------- */
  const sidebar = document.getElementById('sidebar');
  document.getElementById('menuBtn')?.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.querySelector('.main')?.addEventListener('click', () => sidebar?.classList.remove('open'));

  /* ---------- Delete confirmations ---------- */
  document.querySelectorAll('form[data-confirm]').forEach(f =>
    f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  /* ---------- Flash auto-hide ---------- */
  const flash = document.querySelector('.main .flash');
  if (flash) setTimeout(() => flash.remove(), 6000);

  /* ---------- Charts ---------- */
  const dataEl = document.getElementById('chart-data');
  if (!dataEl || !window.Chart) return;
  const d = JSON.parse(dataEl.textContent);
  const inr = v => '₹' + Number(v).toLocaleString('en-IN', { maximumFractionDigits: 0 });
  const axisInr = v => v >= 100000 ? '₹' + (v / 100000) + 'L' : v >= 1000 ? '₹' + (v / 1000) + 'k' : '₹' + v;

  Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
  Chart.defaults.color = '#7a8099';
  const tip = { callbacks: { label: c => ' ' + (c.dataset.label ? c.dataset.label + ': ' : '') + inr(c.parsed.y ?? c.parsed) } };
  const yAxis = { beginAtZero: true, grid: { color: '#eef0f7' }, border: { display: false }, ticks: { callback: axisInr } };
  const xAxis = { grid: { display: false }, border: { display: false } };
  const draw = (id, cfg) => { const el = document.getElementById(id); if (el) new Chart(el, cfg); };

  if (d.series) {
    draw('chartMonthly', {
      type: 'bar',
      data: { labels: d.series.labels, datasets: [{ label: 'Expenses', data: d.series.expenses, backgroundColor: '#5b63ff', borderRadius: 10, maxBarThickness: 44 }] },
      options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip }, scales: { y: yAxis, x: xAxis } }
    });
    draw('chartIncomeExpense', {
      type: 'bar',
      data: { labels: d.series.labels, datasets: [
        { label: 'Income', data: d.series.income, backgroundColor: '#16a34a', borderRadius: 8, maxBarThickness: 36 },
        { label: 'Expenses', data: d.series.expenses, backgroundColor: '#f43f5e', borderRadius: 8, maxBarThickness: 36 }
      ] },
      options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: tip }, scales: { y: yAxis, x: xAxis } }
    });
  }
  if (d.cats && d.cats.values.length) {
    draw('chartCategory', {
      type: 'doughnut',
      data: { labels: d.cats.labels, datasets: [{ data: d.cats.values, backgroundColor: d.cats.colors, borderWidth: 3, borderColor: '#fff', borderRadius: 6 }] },
      options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + inr(c.parsed) } } } }
    });
  }
  if (d.daily) {
    draw('chartDaily', {
      type: 'line',
      data: { labels: d.daily.labels, datasets: [{ label: 'Spent', data: d.daily.values, borderColor: '#5b63ff', backgroundColor: '#5b63ff22', fill: true, tension: .35, pointRadius: 2 }] },
      options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip }, scales: { y: yAxis, x: xAxis } }
    });
  }
});
