/**
 * Ketchila E-commerce Dashboard
 * Modern dashboard with Acorn theme charts
 */
class KetchilaDashboard {
  constructor() {
    this._revenueChart = null;
    this._orderStatusChart = null;
    this._inventoryChart = null;
    this._customerChart = null;
    this._initCharts();
    this._initEvents();
    this._initRealTimeUpdates();
  }

  _initEvents() {
    document.documentElement.addEventListener(
      Globals.colorAttributeChange,
      (event) => {
        this._destroyCharts();
        this._initCharts();
      }
    );

    const refreshBtn = document.getElementById("refreshDashboard");
    if (refreshBtn) {
      refreshBtn.addEventListener("click", () => this.refreshDashboard());
    }
  }

  _destroyCharts() {
    this._revenueChart && this._revenueChart.destroy();
    this._orderStatusChart && this._orderStatusChart.destroy();
    this._inventoryChart && this._inventoryChart.destroy();
    this._customerChart && this._customerChart.destroy();
  }

  _initCharts() {
    this._initRevenueChart();
    this._initOrderStatusChart();
    this._initInventoryChart();
    this._initCustomerChart();
  }

  _initRevenueChart() {
    if (document.getElementById("revenueChart")) {
      const ctx = document.getElementById("revenueChart").getContext("2d");
      this._revenueChart = new Chart(ctx, {
        type: "line",
        data: {
          labels: window.dashboardData?.revenue_chart?.labels || [],
          datasets: [
            {
              label: "Günlük Gelir (₺)",
              data: window.dashboardData?.revenue_chart?.revenue || [],
              borderColor: Globals.primary,
              backgroundColor: "rgba(" + Globals.primaryrgb + ",0.1)",
              pointBackgroundColor: Globals.primary,
              pointBorderColor: Globals.primary,
              pointHoverBackgroundColor: Globals.foreground,
              pointHoverBorderColor: Globals.primary,
              borderWidth: 2,
              pointRadius: 3,
              pointBorderWidth: 2,
              pointHoverBorderWidth: 2,
              pointHoverRadius: 6,
              fill: true,
              tension: 0.4,
            },
            {
              label: "Sipariş Sayısı",
              data: window.dashboardData?.revenue_chart?.orders || [],
              borderColor: Globals.secondary,
              backgroundColor: "rgba(" + Globals.secondaryrgb + ",0.1)",
              pointBackgroundColor: Globals.secondary,
              pointBorderColor: Globals.secondary,
              pointHoverBackgroundColor: Globals.foreground,
              pointHoverBorderColor: Globals.secondary,
              borderWidth: 2,
              pointRadius: 3,
              pointBorderWidth: 2,
              pointHoverBorderWidth: 2,
              pointHoverRadius: 6,
              fill: false,
              tension: 0.4,
              yAxisID: "y1",
            },
          ],
        },
        options: {
          plugins: {
            crosshair: {
              line: {
                color: Globals.primary,
                width: 1,
              },
              sync: {
                enabled: false,
              },
              zoom: {
                enabled: false,
              },
            },
            datalabels: { display: false },
          },
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: "index",
            intersect: false,
          },
          scales: {
            y: {
              type: "linear",
              display: true,
              position: "left",
              grid: {
                display: true,
                lineWidth: 1,
                color: Globals.separatorLight,
                drawBorder: false,
              },
              ticks: {
                beginAtZero: true,
                padding: 20,
                callback: function (value) {
                  return value.toLocaleString("tr-TR") + " ₺";
                },
              },
            },
            y1: {
              type: "linear",
              display: true,
              position: "right",
              grid: {
                drawOnChartArea: false,
              },
              ticks: {
                beginAtZero: true,
                padding: 20,
                callback: function (value) {
                  return Math.round(value);
                },
              },
            },
            x: {
              grid: {
                display: false,
              },
              ticks: {
                padding: 20,
              },
            },
          },
          tooltips: ChartsExtend.ChartTooltip(),
          legend: {
            position: "bottom",
            labels: ChartsExtend.LegendLabels(),
          },
        },
      });
    }
  }

  _initOrderStatusChart() {
    if (document.getElementById("orderStatusChart")) {
      this._orderStatusChart = ChartsExtend.SmallDoughnutChart(
        "orderStatusChart",
        window.dashboardData?.order_status_chart?.values || [0],
        "SİPARİŞ DURUMLARI"
      );
    }
  }

  _initInventoryChart() {
    if (document.getElementById("inventoryChart")) {
      this._inventoryChart = ChartsExtend.SmallDoughnutChart(
        "inventoryChart",
        window.dashboardData?.inventory_chart?.values || [0],
        "STOK DURUMU"
      );
    }
  }

  _initCustomerChart() {
    if (document.getElementById("customerChart")) {
      this._customerChart = ChartsExtend.SmallLineChart("customerChart", {
        labels: window.dashboardData?.customer_chart?.labels || [],
        datasets: [
          {
            label: "Yeni Müşteriler",
            data: window.dashboardData?.customer_chart?.data || [],
            borderColor: Globals.tertiary,
            pointBackgroundColor: Globals.tertiary,
            pointBorderColor: Globals.tertiary,
            pointHoverBackgroundColor: Globals.foreground,
            pointHoverBorderColor: Globals.tertiary,
            borderWidth: 2,
            pointRadius: 2,
            pointBorderWidth: 2,
            pointHoverBorderWidth: 2,
            pointHoverRadius: 5,
            fill: false,
          },
        ],
      });
    }
  }

  _initRealTimeUpdates() {
    setInterval(() => {
      this.updateStats();
    }, 30000);
  }

  updateStats() {
    fetch("/admin_bt/ajax/dashboard-stats.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.updateStatCards(data.stats);
        }
      })
      .catch((error) => {
        console.error("Dashboard stats update failed:", error);
      });
  }

  updateStatCards(stats) {
    const statElements = {
      todayOrders: stats.today_orders,
      todayRevenue: stats.today_revenue,
      monthlyOrders: stats.monthly_orders,
      monthlyRevenue: stats.monthly_revenue,
      totalCustomers: stats.total_customers,
      lowStockProducts: stats.low_stock_products,
    };

    Object.keys(statElements).forEach((elementId) => {
      const element = document.getElementById(elementId);
      if (element) {
        element.textContent = statElements[elementId];
        element.classList.add("animate__animated", "animate__pulse");
        setTimeout(() => {
          element.classList.remove("animate__animated", "animate__pulse");
        }, 1000);
      }
    });
  }

  refreshDashboard() {
    this._destroyCharts();

    fetch("/admin_bt/ajax/dashboard-data.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.dashboardData = data.data;
          this._initCharts();
          this.updateStatCards(data.data.stats);

          this.showNotification("Dashboard başarıyla yenilendi", "success");
        }
      })
      .catch((error) => {
        console.error("Dashboard refresh failed:", error);
        this.showNotification("Dashboard yenilenemedi", "error");
      });
  }

  showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `alert alert-${
      type === "error" ? "danger" : type
    } alert-dismissible fade show position-fixed`;
    notification.style.cssText =
      "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 5000);
  }

  exportDashboardData() {
    const data = {
      timestamp: new Date().toISOString(),
      stats: window.dashboardData?.stats || {},
      charts: {
        revenue: window.dashboardData?.revenue_chart || {},
        orders: window.dashboardData?.order_status_chart || {},
        inventory: window.dashboardData?.inventory_chart || {},
        customers: window.dashboardData?.customer_chart || {},
      },
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], {
      type: "application/json",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `dashboard-data-${
      new Date().toISOString().split("T")[0]
    }.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  if (typeof Globals !== "undefined" && typeof ChartsExtend !== "undefined") {
    window.ketchilaDashboard = new KetchilaDashboard();
  }
});
