/* Admin sidebar navigation + chart demo */
(function () {
  const links = document.querySelectorAll('[data-admin-target]');
  const panes = document.querySelectorAll('[data-admin-pane]');

  function showPane(id) {
    panes.forEach((p) => p.classList.toggle('d-none', p.getAttribute('data-admin-pane') !== id));
    links.forEach((a) => a.classList.toggle('active', a.getAttribute('data-admin-target') === id));
  }

  links.forEach((a) => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const id = a.getAttribute('data-admin-target');
      const url = new URL(window.location.href);
      url.searchParams.set('tab', id);
      window.history.replaceState({}, '', url.toString());
      showPane(id);
    });
  });

  // Default pane: đọc từ ?tab=
  const url = new URL(window.location.href);
  const tab = url.searchParams.get('tab') || 'overview';
  showPane(tab);
})();

(function () {
  const canvas = document.getElementById('adminRevenueChart');
  if (!canvas || typeof Chart === 'undefined') return;

  const ctx = canvas.getContext('2d');

  // Demo data (có thể thay bằng dữ liệu DB sau)
  const labels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'];
  const revenue = [12, 18, 15, 28, 22, 35]; // triệu
  const orders = [45, 62, 53, 84, 79, 102];

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Doanh thu (triệu)',
          data: revenue,
          borderColor: '#fbbf24',
          backgroundColor: 'rgba(251, 191, 36, 0.14)',
          tension: 0.35,
          fill: true,
          pointRadius: 3,
        },
        {
          label: 'Đơn hàng',
          data: orders,
          borderColor: '#38bdf8',
          backgroundColor: 'rgba(56, 189, 248, 0.10)',
          tension: 0.35,
          fill: true,
          pointRadius: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: { color: '#e5e7eb' },
        },
      },
      scales: {
        x: { ticks: { color: '#cbd5f5' }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
        y: { ticks: { color: '#cbd5f5' }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
      },
    },
  });
})();

