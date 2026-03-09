document.addEventListener('DOMContentLoaded', function () {
  if (typeof Chart === 'undefined') {
    return;
  }

  var dashboardRoot = document.getElementById('adminDashboardData');
  if (!dashboardRoot) {
    return;
  }

  var userCount = Number.parseInt(dashboardRoot.dataset.userCount || '0', 10);
  var productCount = Number.parseInt(dashboardRoot.dataset.productCount || '0', 10);
  var categoryCount = Number.parseInt(dashboardRoot.dataset.categoryCount || '0', 10);
  var cartCount = Number.parseInt(dashboardRoot.dataset.cartCount || '0', 10);
  var orderCount = Number.parseInt(dashboardRoot.dataset.orderCount || '0', 10);
  var promotionCount = Number.parseInt(dashboardRoot.dataset.promotionCount || '0', 10);

  var overviewEl = document.getElementById('overviewChart');
  if (overviewEl) {
    new Chart(overviewEl, {
      type: 'doughnut',
      data: {
        labels: ['Utilisateurs', 'Produits', 'Categories', 'Paniers', 'Commandes', 'Promotions'],
        datasets: [
          {
            data: [userCount, productCount, categoryCount, cartCount, orderCount, promotionCount],
            backgroundColor: ['#3b82f6', '#16a34a', '#f59e0b', '#ef4444', '#6366f1', '#06b6d4'],
            borderWidth: 0,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
        },
        cutout: '62%',
      },
    });
  }

  var funnelEl = document.getElementById('funnelChart');
  if (funnelEl) {
    new Chart(funnelEl, {
      type: 'bar',
      data: {
        labels: ['Produits', 'Paniers', 'Commandes'],
        datasets: [
          {
            label: 'Parcours commercial',
            data: [productCount, cartCount, orderCount],
            backgroundColor: ['#22c55e', '#f97316', '#6366f1'],
            borderRadius: 10,
            maxBarThickness: 48,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 },
          },
        },
        plugins: {
          legend: { display: false },
        },
      },
    });
  }
});
