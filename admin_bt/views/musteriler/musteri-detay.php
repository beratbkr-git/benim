<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Müşteri yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p4, $db;

$musteri_id = isset($p4) ? (int)$p4 : 0;
if ($musteri_id === 0) {
    $_SESSION['hata'] = "Geçersiz müşteri ID'si.";
    header("Location: /{$yonetimurl}/musteriler/liste");
    exit();
}

// 1. Müşteri bilgilerini çek
$musteri = $db->fetch("SELECT * FROM bt_musteriler WHERE id = :id", ['id' => $musteri_id]);

if (!$musteri) {
    $_SESSION['hata'] = "Müşteri bulunamadı.";
    header("Location: /{$yonetimurl}/musteriler/liste");
    exit();
}

// 2. Müşterinin adreslerini çek
$adresler = $db->fetchAll("SELECT * FROM bt_musteri_adresleri WHERE musteri_id = :musteri_id", ['musteri_id' => $musteri_id]);

// 3. Müşterinin son 10 siparişini çek
$siparisler = $db->fetchAll(
    "SELECT siparis_kodu, toplam_tutar, siparis_durumu, olusturma_tarihi
     FROM bt_siparisler
     WHERE musteri_id = :musteri_id
     ORDER BY olusturma_tarihi DESC
     LIMIT 10",
    ['musteri_id' => $musteri_id]
);
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI -->
        <div class="page-title-container">
            <div class="row">
                <div class="col-12 col-md-7">
                    <h1 class="mb-0 pb-0 display-4" id="title"><?= htmlspecialchars($musteri['ad_soyad']) ?></h1>
                    <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                        <ul class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/musteriler/liste">Müşteriler</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($musteri['ad_soyad']) ?></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <!-- MÜŞTERİ ÖZET BİLGİLERİ KARTI -->
                <h2 class="small-title">Müşteri Profili</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-column">
                            <div class="sw-13 position-relative mb-3">
                                <img src="/admin_bt/assets/img/profile/profile-1.webp" class="img-fluid rounded-xl" alt="profil resmi">
                            </div>
                            <h5 class="mb-0"><?= htmlspecialchars($musteri['ad_soyad']) ?></h5>
                            <div class="text-muted"><?= htmlspecialchars($musteri['eposta']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($musteri['telefon'] ?? '-') ?></div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <div class="me-3">
                                <i data-acorn-icon="dollar" class="text-success me-1"></i>
                                <span class="align-middle">Toplam Harcama: <strong>₺<?= number_format($musteri['toplam_harcama'], 2) ?></strong></span>
                            </div>
                            <div>
                                <i data-acorn-icon="user-circle" class="text-primary me-1"></i>
                                <span class="align-middle">Müşteri ID: <strong><?= $musteri['id'] ?></strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ADRES BİLGİLERİ KARTI -->
                <h2 class="small-title">Kayıtlı Adresler</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <?php if (!empty($adresler)): ?>
                            <?php foreach ($adresler as $adres): ?>
                                <div class="mb-3">
                                    <p class="mb-1"><strong><?= htmlspecialchars($adres['adres_baslik']) ?></strong></p>
                                    <p class="mb-1"><?= htmlspecialchars($adres['adres']) ?></p>
                                    <p class="mb-1"><?= htmlspecialchars($adres['ilce']) ?> / <?= htmlspecialchars($adres['il']) ?></p>
                                    <p class="mb-0"><?= htmlspecialchars($adres['telefon'] ?? '-') ?></p>
                                    <?php if ($adres['varsayilan_adres']): ?>
                                        <span class="badge bg-outline-primary mt-1">Varsayılan</span>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Müşteriye ait kayıtlı adres bulunamadı.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <!-- SİPARİŞ GEÇMİŞİ KARTI -->
                <h2 class="small-title">Son Siparişleri</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Sipariş Kodu</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($siparisler)): ?>
                                        <?php foreach ($siparisler as $siparis): ?>
                                            <?php
                                            $durum_class = 'primary';
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
                                                case 'İade':
                                                    $durum_class = 'danger';
                                                    break;
                                                default:
                                                    $durum_class = 'primary';
                                            }
                                            ?>
                                            <tr>
                                                <td><a href="/<?= $yonetimurl ?>/siparisler/detay/<?= $siparis['id'] ?>">#<?= htmlspecialchars($siparis['siparis_kodu']) ?></a></td>
                                                <td>₺<?= number_format($siparis['toplam_tutar'], 2) ?></td>
                                                <td><span class="badge bg-<?= $durum_class ?>"><?= htmlspecialchars($siparis['siparis_durumu']) ?></span></td>
                                                <td><?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Müşteriye ait sipariş bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>