<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Sipariş yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p4, $db;

$iade_id = isset($p4) ? (int)$p4 : 0;
if ($iade_id === 0) {
    $_SESSION['hata'] = "Geçersiz iade ID'si.";
    header("Location: /{$yonetimurl}/siparisler/iade");
    exit();
}

// İade işlemini tüm detaylarıyla çek
$iade = $db->fetch(
    "SELECT T1.*, T2.siparis_kodu, T3.ad_soyad AS musteri_adi
     FROM bt_iade_islemleri T1
     LEFT JOIN bt_siparisler T2 ON T1.siparis_id = T2.id
     LEFT JOIN bt_musteriler T3 ON T2.musteri_id = T3.id
     WHERE T1.id = :id",
    ['id' => $iade_id]
);

if (!$iade) {
    $_SESSION['hata'] = "İade işlemi bulunamadı.";
    header("Location: /{$yonetimurl}/siparisler/iade");
    exit();
}

$iade_durumlari = ['Beklemede', 'Onaylandı', 'Reddedildi', 'Tamamlandı'];

?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI -->
        <div class="page-title-container">
            <div class="row">
                <div class="col-12 col-md-7">
                    <h1 class="mb-0 pb-0 display-4" id="title">İade Detayı</h1>
                    <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                        <ul class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/siparisler/iade">İade/İptal</a></li>
                            <li class="breadcrumb-item active">#<?= htmlspecialchars($iade['siparis_kodu']) ?></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-xxl-9">
                <!-- İADE BİLGİLERİ KARTI -->
                <h2 class="small-title">İade Özeti</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="row g-0">
                            <div class="col-auto">
                                <div class="sw-5 me-3">
                                    <div class="border border-1 rounded-xl sw-5 sh-5 d-flex justify-content-center align-items-center">
                                        <i data-acorn-icon="check-circle" class="text-primary"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card-body d-flex flex-column pt-0 pb-0 ps-3 pe-0 h-100 justify-content-center">
                                    <div class="d-flex flex-column">
                                        <div class="text-alternate">Sipariş Kodu: <a href="/<?= $yonetimurl ?>/siparisler/detay/<?= $iade['siparis_id'] ?>">#<?= htmlspecialchars($iade['siparis_kodu']) ?></a></div>
                                        <div class="text-muted text-small">Talep Tarihi: <?= date('d.m.Y H:i', strtotime($iade['olusturma_tarihi'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mb-4">
                        <p class="mb-1"><strong>İade Tutarı:</strong> <?= number_format($iade['iade_tutari'], 2) ?> ₺</p>
                        <p class="mb-1"><strong>İade Durumu:</strong> <span class="badge bg-outline-primary"><?= htmlspecialchars($iade['durum']) ?></span></p>
                        <p class="mb-1"><strong>İade Nedeni:</strong> <?= htmlspecialchars($iade['iade_nedeni'] ?? '-') ?></p>
                        <p class="mb-0"><strong>Müşteri Notu:</strong> <?= htmlspecialchars($iade['admin_notu'] ?? 'Yok') ?></p>
                    </div>
                </div>

            </div>

            <div class="col-xl-4 col-xxl-3">
                <!-- YÖNETİCİ PANELİ KARTI -->
                <h2 class="small-title">İşlem Yönetimi</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">İade Durumu</label>
                            <select id="iadeDurumuSelect" class="form-select">
                                <?php foreach ($iade_durumlari as $durum) : ?>
                                    <option value="<?= $durum ?>" <?= ($iade['durum'] == $durum) ? 'selected' : '' ?>><?= $durum ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yönetici Notu</label>
                            <textarea id="adminNotuTextarea" class="form-control" rows="3"><?= htmlspecialchars($iade['admin_notu'] ?? '') ?></textarea>
                        </div>
                        <button id="durumGuncelleBtn" class="btn btn-outline-primary w-100 mb-3">Durumu Güncelle</button>
                    </div>
                </div>

                <!-- MÜŞTERİ BİLGİLERİ KARTI -->
                <h2 class="small-title">Müşteri Bilgileri</h2>
                <div class="card mb-5">
                    <div class="card-body d-flex flex-row align-items-center">
                        <div class="sw-5 me-4">
                            <div class="sw-5 sh-5 rounded-xl d-flex justify-content-center align-items-center bg-light">
                                <i data-acorn-icon="user" class="text-primary"></i>
                            </div>
                        </div>
                        <div class="info-container">
                            <div><?= htmlspecialchars($iade['musteri_adi'] ?? 'Misafir Kullanıcı') ?></div>
                            <div class="text-small text-muted">Sipariş Kodu: #<?= htmlspecialchars($iade['siparis_kodu']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const iadeId = <?= $iade_id ?>;
        const yonetimUrl = '<?= $yonetimurl ?>';

        // Durum Güncelleme
        const durumGuncelleBtn = document.getElementById('durumGuncelleBtn');
        durumGuncelleBtn.addEventListener('click', function() {
            this.disabled = true;
            const yeniDurum = document.getElementById('iadeDurumuSelect').value;
            const adminNotu = document.getElementById('adminNotuTextarea').value;

            const formData = new FormData();
            formData.append('iade_id', iadeId);
            formData.append('yeni_durum', yeniDurum);
            formData.append('admin_notu', adminNotu);

            fetch(`/${yonetimUrl}/siparisler/iade-durum-guncelle`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        beratbabatoast("success", data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        beratbabatoast("danger", "Hata: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    beratbabatoast("danger", "İşlem sırasında bir sunucu hatası oluştu.");
                }).finally(() => {
                    this.disabled = false;
                });
        });
    });
</script>