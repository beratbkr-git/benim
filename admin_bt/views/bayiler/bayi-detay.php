<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Bayi yönetimi için yetki kontrolü
if (!hasPermission('Yönetici')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p4, $db;

$bayi_id = isset($p4) ? (int)$p4 : 0;
if ($bayi_id === 0) {
    $_SESSION['hata'] = "Geçersiz bayi ID'si.";
    header("Location: /{$yonetimurl}/bayiler/liste");
    exit();
}

// Bayi ve ilgili kullanıcı bilgilerini çek
$bayi = $db->fetch(
    "SELECT T1.*, T2.ad_soyad, T2.eposta, T2.telefon
     FROM bt_bayiler T1
     LEFT JOIN bt_kullanicilar T2 ON T1.kullanici_id = T2.id
     WHERE T1.id = :id",
    ['id' => $bayi_id]
);

if (!$bayi) {
    $_SESSION['hata'] = "Bayi bulunamadı.";
    header("Location: /{$yonetimurl}/bayiler/liste");
    exit();
}

$onay_durumlari = ['Beklemede', 'Onaylı', 'Reddedildi'];

?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI -->
        <div class="page-title-container">
            <div class="row">
                <div class="col-12 col-md-7">
                    <h1 class="mb-0 pb-0 display-4" id="title"><?= htmlspecialchars($bayi['firma_adi']) ?></h1>
                    <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                        <ul class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/bayiler/liste">Bayiler</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($bayi['firma_adi']) ?></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <!-- BAYİ ÖZET BİLGİLERİ KARTI -->
                <h2 class="small-title">Bayi Profili</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-column">
                            <div class="sw-13 position-relative mb-3">
                                <img src="/admin_bt/assets/img/profile/profile-2.webp" class="img-fluid rounded-xl" alt="profil resmi">
                            </div>
                            <h5 class="mb-0"><?= htmlspecialchars($bayi['firma_adi']) ?></h5>
                            <div class="text-muted"><?= htmlspecialchars($bayi['ad_soyad'] ?? '-') ?></div>
                            <div class="text-muted"><?= htmlspecialchars($bayi['eposta'] ?? '-') ?></div>
                            <div class="text-muted"><?= htmlspecialchars($bayi['telefon'] ?? '-') ?></div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <div class="me-3">
                                <i data-acorn-icon="tag" class="text-info me-1"></i>
                                <span class="align-middle">Komisyon: <strong>%<?= number_format($bayi['komisyon_orani'], 2) ?></strong></span>
                            </div>
                            <div>
                                <i data-acorn-icon="user-circle" class="text-primary me-1"></i>
                                <span class="align-middle">Bayi ID: <strong><?= $bayi['id'] ?></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <!-- İŞLEM YÖNETİMİ KARTI -->
                <h2 class="small-title">İşlem Yönetimi</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Onay Durumu</label>
                            <select id="onayDurumuSelect" class="form-select">
                                <?php foreach ($onay_durumlari as $durum) : ?>
                                    <option value="<?= $durum ?>" <?= ($bayi['onay_durumu'] == $durum) ? 'selected' : '' ?>><?= $durum ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button id="durumGuncelleBtn" class="btn btn-outline-primary w-100 mb-3">Durumu Güncelle</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const bayiId = <?= $bayi_id ?>;
        const yonetimUrl = '<?= $yonetimurl ?>';

        // Durum Güncelleme
        const durumGuncelleBtn = document.getElementById('durumGuncelleBtn');
        durumGuncelleBtn?.addEventListener('click', function() {
            this.disabled = true;
            const yeniDurum = document.getElementById('onayDurumuSelect').value;

            const formData = new FormData();
            formData.append('id', bayiId);
            formData.append('onay_durumu', yeniDurum);

            fetch(`/${yonetimUrl}/bayiler/durum-guncelle`, {
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