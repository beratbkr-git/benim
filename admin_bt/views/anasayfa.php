<?php
global $yonetimurl, $db;

// Bu kontrolcüler artık ana yönlendiricide (admin.php) dahil edildiği için burada tekrar çağırmaya gerek yok.
// require_once YONETIM_DIR . 'controllers/AnalyticsController.php';
// require_once YONETIM_DIR . 'controllers/InventoryController.php';

$analytics = new AnalyticsController($db);
$inventory = new InventoryController($db);

$dashboard_data = $analytics->dashboard();
$inventory_data = $inventory->dashboard();
?>

<main>
    <div class="container">
        <div class="page-title-container d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0 pb-0 display-4">Dashboard</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                    <i data-acorn-icon="sync" class="me-1"></i>Yenile
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i data-acorn-icon="calendar" class="me-1"></i>Son 30 Gün
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="changePeriod(7)">Son 7 Gün</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changePeriod(30)">Son 30 Gün</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changePeriod(90)">Son 90 Gün</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ana İstatistik Kartları -->
        <div class="row mb-4">
            <div class="col-12 col-lg-3 col-xxl-3 mb-4">
                <div class="card h-100 hover-border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-small text-muted mb-1">BUGÜNKÜ SİPARİŞLER</div>
                                <div class="text-primary h3 mb-0" id="daily-orders">
                                    <?= $dashboard_data['daily_stats']['today']['siparis_sayisi'] ?? 0 ?>
                                </div>
                                <div class="text-small">
                                    <?php
                                    $change = $dashboard_data['daily_stats']['siparis_degisim'] ?? 0;
                                    $color = $change >= 0 ? 'success' : 'danger';
                                    $icon = $change >= 0 ? 'chevron-up' : 'chevron-down';
                                    ?>
                                    <span class="text-<?= $color ?>">
                                        <i data-acorn-icon="<?= $icon ?>" class="me-1"></i><?= abs($change) ?>%
                                    </span>
                                    <span class="text-muted">dünden</span>
                                </div>
                            </div>
                            <div class="sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center bg-gradient-light">
                                <i data-acorn-icon="cart" class="text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-xxl-3 mb-4">
                <div class="card h-100 hover-border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-small text-muted mb-1">BUGÜNKÜ GELİR</div>
                                <div class="text-success h3 mb-0" id="daily-revenue">
                                    ₺<?= number_format($dashboard_data['daily_stats']['today']['toplam_gelir'] ?? 0, 2) ?>
                                </div>
                                <div class="text-small">
                                    <?php
                                    $change = $dashboard_data['daily_stats']['gelir_degisim'] ?? 0;
                                    $color = $change >= 0 ? 'success' : 'danger';
                                    $icon = $change >= 0 ? 'chevron-up' : 'chevron-down';
                                    ?>
                                    <span class="text-<?= $color ?>">
                                        <i data-acorn-icon="<?= $icon ?>" class="me-1"></i><?= abs($change) ?>%
                                    </span>
                                    <span class="text-muted">dünden</span>
                                </div>
                            </div>
                            <div class="sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center bg-gradient-light">
                                <i data-acorn-icon="dollar-sign" class="text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-xxl-3 mb-4">
                <div class="card h-100 hover-border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-small text-muted mb-1">ORTALAMA SEPET</div>
                                <div class="text-warning h3 mb-0" id="avg-basket">
                                    ₺<?= number_format($dashboard_data['monthly_stats']['current']['toplam_gelir'] / max($dashboard_data['monthly_stats']['current']['siparis_sayisi'], 1), 2) ?>
                                </div>
                                <div class="text-small">
                                    <span class="text-muted">Bu ay: </span>
                                    <span class="text-primary">₺<?= number_format($dashboard_data['monthly_stats']['current']['toplam_gelir'], 2) ?></span>
                                </div>
                            </div>
                            <div class="sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center bg-gradient-light">
                                <i data-acorn-icon="shopping-bag" class="text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-xxl-3 mb-4">
                <div class="card h-100 hover-border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-small text-muted mb-1">DÜŞÜK STOK UYARISI</div>
                                <div class="text-info h3 mb-0" id="low-stock-count">
                                    <?= $inventory_data['stock_summary']['dusuk_stok'] ?? 0 ?>
                                </div>
                                <div class="text-small">
                                    <span class="text-danger"><?= $inventory_data['stock_summary']['stokta_yok'] ?? 0 ?></span>
                                    <span class="text-muted">stokta yok</span>
                                </div>
                            </div>
                            <div class="sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center bg-gradient-light">
                                <i data-acorn-icon="alert-triangle" class="text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafikler ve Analitik -->
        <div class="row mb-4">
            <div class="col-12 col-xl-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Gelir Analizi</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Sipariş Durumları</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="orderStatusChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detaylı İstatistikler -->
        <div class="row mb-4">
            <div class="col-12 col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">En Çok Satan Ürünler</h5>
                        <a href="/<?= $yonetimurl ?>/urunler/liste" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Satış</th>
                                        <th>Gelir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($dashboard_data['top_products'] ?? [], 0, 5) as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?= htmlspecialchars($product['urun_adi']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= $product['toplam_satis'] ?></span>
                                            </td>
                                            <td>
                                                <strong>₺<?= number_format($product['toplam_gelir'], 2) ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Düşük Stok Uyarıları</h5>
                        <a href="/<?= $yonetimurl ?>/urunler/liste" class="btn btn-sm btn-outline-warning">Tümünü Gör</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Stok</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($inventory_data['low_stock_products'] ?? [], 0, 5) as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    <?= htmlspecialchars($product['urun_adi']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $product['stok_miktari'] <= 0 ? 'danger' : 'warning' ?>">
                                                    <?= $product['stok_miktari'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($product['stok_miktari'] <= 0): ?>
                                                    <span class="text-danger">Stokta Yok</span>
                                                <?php else: ?>
                                                    <span class="text-warning">Düşük Stok</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Aktiviteler -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Son Siparişler</h5>
                        <a href="/<?= $yonetimurl ?>/siparisler/liste" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sipariş Kodu</th>
                                        <th>Müşteri</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($dashboard_data['recent_orders'] ?? [], 0, 10) as $siparis): ?>
                                        <?php
                                        $durum_class = 'primary';
                                        $durum_text = $siparis['siparis_durumu'];
                                        switch ($siparis['siparis_durumu']) {
                                            case 'Teslim Edildi':
                                                $durum_class = 'success';
                                                break;
                                            case 'Kargoda':
                                                $durum_class = 'warning';
                                                break;
                                            case 'Hazırlanıyor':
                                                $durum_class = 'info';
                                                break;
                                            case 'İptal':
                                                $durum_class = 'danger';
                                                break;
                                            case 'İade':
                                                $durum_class = 'danger';
                                                break;
                                            default:
                                                $durum_class = 'primary'; // Yeni
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <strong>#<?= htmlspecialchars($siparis['siparis_kodu']) ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="text-truncate" style="max-width: 150px;">
                                                        <?= htmlspecialchars($siparis['musteri_adi'] ?? 'Misafir') ?>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($siparis['musteri_eposta'] ?? '') ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>₺<?= number_format($siparis['odenen_tutar'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $durum_class ?>"><?= $durum_text ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <?= date('d.m.Y', strtotime($siparis['olusturma_tarihi'])) ?>
                                                    <br><small class="text-muted"><?= date('H:i', strtotime($siparis['olusturma_tarihi'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/<?= $yonetimurl ?>/siparisler/detay/<?= $siparis['id'] ?>" class="btn btn-outline-primary">
                                                        <i data-acorn-icon="eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    class KetchilaDashboard {
        constructor() {
            this._revenueChart = null;
            this._orderStatusChart = null;
            this._initCharts();
            this._initEvents();
            this._initRealTimeUpdates();
        }

        _initEvents() {
            document.documentElement.addEventListener(Globals.colorAttributeChange, (event) => {
                this._revenueChart && this._revenueChart.destroy();
                this._orderStatusChart && this._orderStatusChart.destroy();
                this._initCharts();
            });
        }

        _initCharts() {
            this._initRevenueChart();
            this._initOrderStatusChart();
        }

        _initRevenueChart() {
            if (document.getElementById('revenueChart')) {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                this._revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode(array_column($dashboard_data['revenue_chart'] ?? [], 'tarih')) ?>,
                        datasets: [{
                            label: 'Günlük Gelir (₺)',
                            data: <?= json_encode(array_column($dashboard_data['revenue_chart'] ?? [], 'revenue')) ?>,
                            borderColor: Globals.primary,
                            backgroundColor: 'rgba(' + Globals.primaryrgb + ',0.1)',
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
                            tension: 0.4
                        }, {
                            label: 'Sipariş Sayısı',
                            data: <?= json_encode(array_column($dashboard_data['revenue_chart'] ?? [], 'orders')) ?>,
                            borderColor: Globals.secondary,
                            backgroundColor: 'rgba(' + Globals.secondaryrgb + ',0.1)',
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
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        plugins: {
                            crosshair: {
                                line: {
                                    color: Globals.primary,
                                    width: 1
                                },
                                sync: {
                                    enabled: false
                                },
                                zoom: {
                                    enabled: false
                                }
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: {
                                    display: true,
                                    lineWidth: 1,
                                    color: Globals.separatorLight,
                                    drawBorder: false
                                },
                                ticks: {
                                    beginAtZero: true,
                                    padding: 20,
                                    callback: function(value) {
                                        return value.toLocaleString('tr-TR') + ' ₺';
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    beginAtZero: true,
                                    padding: 20,
                                    callback: function(value) {
                                        return Math.round(value);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    padding: 20
                                }
                            }
                        },
                        tooltips: ChartsExtend.ChartTooltip(),
                        legend: {
                            position: 'bottom',
                            labels: ChartsExtend.LegendLabels()
                        }
                    }
                });
            }
        }

        _initOrderStatusChart() {
            if (document.getElementById('orderStatusChart')) {
                this._orderStatusChart = ChartsExtend.SmallDoughnutChart(
                    'orderStatusChart',
                    <?= json_encode($dashboard_data['order_status_chart']['data'] ?? [0]) ?>,
                    'SİPARİŞ DURUMLARI'
                );
            }
        }

        _initRealTimeUpdates() {
            setInterval(() => {
                this.updateStats();
            }, 30000);
        }

        updateStats() {
            fetch(`/${yonetimurl}/anasayfa/dashboardData`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data.stats;
                        this.updateStatCards(stats);
                    }
                })
                .catch(error => {
                    console.error('Dashboard stats update failed:', error);
                });
        }

        updateStatCards(stats) {
            const statElements = {
                'daily-orders': stats.today_orders,
                'daily-revenue': '₺' + stats.today_revenue,
            };

            Object.keys(statElements).forEach(elementId => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.textContent = statElements[elementId];
                    element.classList.add('animate__animated', 'animate__pulse');
                    setTimeout(() => {
                        element.classList.remove('animate__animated', 'animate__pulse');
                    }, 1000);
                }
            });
        }

        refreshDashboard() {
            this._revenueChart && this._revenueChart.destroy();
            this._orderStatusChart && this._orderStatusChart.destroy();

            fetch(`/${yonetimurl}/anasayfa/dashboardData`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.dashboardData = data.data;
                        this._initCharts();
                        this.updateStatCards(data.data.stats);
                    }
                })
                .catch(error => {
                    console.error('Dashboard refresh failed:', error);
                    location.reload();
                });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Globals !== 'undefined' && typeof ChartsExtend !== 'undefined') {
            window.ketchilaDashboard = new KetchilaDashboard();
        }
    });

    function refreshDashboard() {
        if (window.ketchilaDashboard) {
            window.ketchilaDashboard.refreshDashboard();
        } else {
            location.reload();
        }
    }

    function changePeriod(days) {
        console.log('Changing period to:', days);
    }

    function viewOrder(orderId) {
        window.location.href = `/${yonetimurl}/siparisler/detay/${orderId}`;
    }
</script>