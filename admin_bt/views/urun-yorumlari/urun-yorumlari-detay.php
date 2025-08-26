<?php
// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $db, $yorum;

$onayla_ajax_url = "/{$yonetimurl}/urun-yorumlari/onayla";
$reddet_ajax_url = "/{$yonetimurl}/urun-yorumlari/reddet";
$yanitla_ajax_url = "/{$yonetimurl}/urun-yorumlari/yanitla";
$sil_ajax_url = "/{$yonetimurl}/urun-yorumlari/sil";
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/urun-yorumlari/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Yorumlar</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title">Yorum Detayı</h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <?php if ($yorum['durum'] === 'Beklemede'): ?>
                        <button id="onaylaBtn" class="btn btn-success btn-icon btn-icon-start w-100 w-md-auto me-2">
                            <i data-acorn-icon="check"></i>
                            <span>Onayla</span>
                        </button>
                        <button id="reddetBtn" class="btn btn-danger btn-icon btn-icon-start w-100 w-md-auto">
                            <i data-acorn-icon="close"></i>
                            <span>Reddet</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <!-- YORUM VE YANIT KARTI -->
                <h2 class="small-title">Yorum</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="sw-13 me-3">
                                <img src="/admin_bt/assets/img/profile/profile-1.webp" class="img-fluid rounded-xl" alt="müşteri resmi" />
                            </div>
                            <div class="flex-grow-1">
                                <a href="/<?= $yonetimurl ?>/musteriler/detay/<?= $yorum['musteri_id'] ?>" class="d-block body-link fw-bold">
                                    <?= htmlspecialchars($yorum['ad_soyad'] ?? 'Bilinmiyor') ?>
                                </a>
                                <div class="text-small text-muted">
                                    <?= date('d.m.Y H:i', strtotime($yorum['olusturma_tarihi'])) ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="text-primary me-2"><?= htmlspecialchars($yorum['puan']) ?>/5</div>
                                <div class="rating-star" data-initial-rating="<?= htmlspecialchars($yorum['puan']) ?>" data-readonly="true"></div>
                            </div>
                        </div>
                        <p class="mb-3"><?= htmlspecialchars($yorum['yorum']) ?></p>

                        <?php if (!empty($yorum['avantajlar']) || !empty($yorum['dezavantajlar'])): ?>
                            <div class="row">
                                <?php if (!empty($yorum['avantajlar'])): ?>
                                    <div class="col-md-6">
                                        <div class="text-small text-muted mb-2">Avantajlar</div>
                                        <ul class="list-unstyled">
                                            <li><span class="badge bg-success">
                                                    <?= htmlspecialchars($yorum['avantajlar']) ?>
                                                </span></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($yorum['dezavantajlar'])): ?>
                                    <div class="col-md-6">
                                        <div class="text-small text-muted mb-2">Dezavantajlar</div>
                                        <ul class="list-unstyled">
                                            <li><span class="badge bg-danger">
                                                    <?= htmlspecialchars($yorum['dezavantajlar']) ?>
                                                </span></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($yorum['admin_yaniti'])): ?>
                            <hr class="mb-3 mt-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="sw-13 me-3">
                                    <img src="/admin_bt/assets/img/profile/profile-1.webp" class="img-fluid rounded-xl" alt="yönetici resmi" />
                                </div>
                                <div class="flex-grow-1">
                                    <a href="#" class="d-block body-link fw-bold">Yönetici Yanıtı</a>
                                    <div class="text-small text-muted">
                                        <?= date('d.m.Y H:i', strtotime($yorum['guncelleme_tarihi'])) ?>
                                    </div>
                                </div>
                            </div>
                            <p class="mb-0"><?= htmlspecialchars($yorum['admin_yaniti']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- YANIT FORMU -->
                <h2 class="small-title">Yönetici Yanıtı</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <form id="yanitForm" action="/<?= $yonetimurl ?>/urun-yorumlari/yanitla" method="POST">
                            <input type="hidden" name="id" value="<?= $yorum['id'] ?>">
                            <div class="mb-3">
                                <textarea name="admin_yaniti" class="form-control" rows="5" placeholder="Müşteriye yanıtınızı buraya yazın..."><?= htmlspecialchars($yorum['admin_yaniti'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Yanıtla</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- YORUM BİLGİLERİ KARTI -->
                <h2 class="small-title">Yorum Bilgileri</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <p class="mb-1"><strong>Yorum ID:</strong> #<?= $yorum['id'] ?></p>
                        <p class="mb-1"><strong>Durum:</strong> <span class="badge bg-outline-primary"><?= htmlspecialchars($yorum['durum']) ?></span></p>
                        <p class="mb-1"><strong>Tavsiye Eder Mi:</strong> <?= $yorum['tavsiye_eder_mi'] ? 'Evet' : 'Hayır' ?></p>
                        <p class="mb-0"><strong>Yardımcı Oy Sayısı:</strong> <?= $yorum['yardimci_oy_sayisi'] ?></p>
                    </div>
                </div>

                <!-- ÜRÜN BİLGİLERİ KARTI -->
                <h2 class="small-title">Ürün Bilgileri</h2>
                <div class="card">
                    <div class="card-body">
                        <a href="/<?= $yonetimurl ?>/urunler/duzenle/<?= $yorum['urun_id'] ?>" class="body-link fw-bold">
                            <?= htmlspecialchars($yorum['urun_adi']) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form gönderimi sırasında yorumu yanıtlama
        document.getElementById('yanitForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        beratbabatoast('success', data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        beratbabatoast('danger', data.message);
                    }
                }).catch(error => {
                    console.error('Yanıt gönderme hatası:', error);
                    beratbabatoast('danger', 'Bir hata oluştu.');
                });
        });

        // Onayla butonu için dinleyici
        document.getElementById('onaylaBtn')?.addEventListener('click', function() {
            if (confirm('Bu yorumu onaylamak istediğinize emin misiniz?')) {
                fetch('<?= $onayla_ajax_url ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [<?= $yorum['id'] ?>]
                        })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast('success', 'Yorum başarıyla onaylandı.');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            beratbabatoast('danger', data.message);
                        }
                    }).catch(error => {
                        console.error('Hata:', error);
                        beratbabatoast('danger', 'Bir hata oluştu.');
                    });
            }
        });

        // Reddet butonu için dinleyici
        document.getElementById('reddetBtn')?.addEventListener('click', function() {
            if (confirm('Bu yorumu reddetmek istediğinize emin misiniz?')) {
                fetch('<?= $reddet_ajax_url ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [<?= $yorum['id'] ?>]
                        })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast('success', 'Yorum başarıyla reddedildi.');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            beratbabatoast('danger', data.message);
                        }
                    }).catch(error => {
                        console.error('Hata:', error);
                        beratbabatoast('danger', 'Bir hata oluştu.');
                    });
            }
        });

        // Rating eklentisini başlat
        $('.rating-star').barrating({
            theme: 'bootstrap-stars',
            readonly: true,
            showValues: false,
            showSelectedRating: false
        });
    });
</script>