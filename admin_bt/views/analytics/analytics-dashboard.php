<?php
global $yonetimurl, $db;
require_once 'admin_bt/controllers/AnalyticsController.php';

$analyticsController = new AnalyticsController($db);
$daily_stats = $analyticsController->getDailyStats();
$monthly_stats = $analyticsController->getMonthlyStats();
$revenue_chart = $analyticsController->getRevenueChart(30);
$top_products = $analyticsController->getTopProducts(10);
$customer_stats = $analyticsController->getCustomerStats();
?>

<main class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">Satış Analizi</h1>
                <p class="text-muted">Detaylı satış raporları ve analizler</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Bugün Gelir</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($daily_stats['toplam_gelir'] ?? 0, 2) ?> ₺
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-lira-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Bugün Sipariş</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $daily_stats['siparis_sayisi'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Aylık Gelir</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($monthly_stats['toplam_gelir'] ?? 0, 2) ?> ₺
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Ortalama Sepet</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($daily_stats['ortalama_sepet_tutari'] ?? 0, 2) ?> ₺
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">30 Günlük Gelir Trendi</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueAnalyticsChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">En Çok Satan Ürünler</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($top_products as $index => $product): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <span class="badge badge-primary badge-pill"><?= $index + 1 ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($product['urun_adi']) ?></h6>
                                    <small class="text-muted"><?= $product['satis_adedi'] ?> adet satıldı</small>
                                </div>
                                <div>
                                    <span class="font-weight-bold text-success">
                                        <?= number_format($product['toplam_gelir'], 2) ?> ₺
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Müşteri İstatistikleri</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-primary"><?= $customer_stats['yeni_musteri_sayisi'] ?? 0 ?></h4>
                                    <p class="text-muted mb-0">Yeni Müşteri</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success"><?= $customer_stats['geri_donen_musteri_sayisi'] ?? 0 ?></h4>
                                    <p class="text-muted mb-0">Geri Dönen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Hızlı İşlemler</h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary btn-block mb-2" onclick="exportSalesReport()">
                            <i class="fas fa-download"></i> Satış Raporu İndir
                        </button>
                        <button class="btn btn-success btn-block mb-2" onclick="exportProductReport()">
                            <i class="fas fa-download"></i> Ürün Raporu İndir
                        </button>
                        <button class="btn btn-info btn-block" onclick="exportCustomerReport()">
                            <i class="fas fa-download"></i> Müşteri Raporu İndir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const analyticsCtx = document.getElementById('revenueAnalyticsChart').getContext('2d');
    const analyticsChart = new Chart(analyticsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($revenue_chart, 'tarih')) ?>,
            datasets: [{
                label: 'Günlük Gelir (₺)',
                data: <?= json_encode(array_column($revenue_chart, 'gelir')) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('tr-TR') + ' ₺';
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        }
    });

    function exportSalesReport() {
        window.location.href = '/admin_bt/ajax/export-sales.php';
    }

    function exportProductReport() {
        window.location.href = '/admin_bt/ajax/export-products.php';
    }

    function exportCustomerReport() {
        window.location.href = '/admin_bt/ajax/export-customers.php';
    }
</script>