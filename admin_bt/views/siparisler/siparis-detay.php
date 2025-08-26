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

$siparis_id = isset($p4) ? (int)$p4 : 0;
if ($siparis_id === 0) {
    $_SESSION['hata'] = "Geçersiz sipariş ID'si.";
    header("Location: /{$yonetimurl}/siparisler/liste");
    exit();
}

// 1. Ana Sipariş Bilgilerini Çek
$siparis = $db->fetch(
    "SELECT T1.*, T2.ad_soyad AS musteri_adi, T2.eposta AS musteri_eposta 
     FROM bt_siparisler T1 
     LEFT JOIN bt_musteriler T2 ON T1.musteri_id = T2.id 
     WHERE T1.id = :id",
    ['id' => $siparis_id]
);

if (!$siparis) {
    $_SESSION['hata'] = "Sipariş bulunamadı.";
    header("Location: /{$yonetimurl}/siparisler/liste");
    exit();
}

// 2. Sipariş Detaylarını (Ürünleri) Çek
$siparis_detaylari = $db->fetchAll(
    "SELECT T1.*, (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = T1.urun_id ORDER BY sira ASC LIMIT 1) AS ana_gorsel 
     FROM bt_siparis_detaylari T1 
     WHERE T1.siparis_id = :id",
    ['id' => $siparis_id]
);

// 3. Adres Bilgilerini Çek
$teslimat_adresi = $db->fetch("SELECT * FROM bt_musteri_adresleri WHERE id = :id", ['id' => $siparis['teslimat_adresi_id']]);
$fatura_adresi = $db->fetch("SELECT * FROM bt_musteri_adresleri WHERE id = :id", ['id' => $siparis['fatura_adresi_id']]);

// 4. Sipariş Geçmişini Çek
$siparis_gecmisi = $db->fetchAll(
    "SELECT T1.*, T2.ad_soyad AS kullanici_adi 
     FROM bt_siparis_durum_gecmisi T1 
     LEFT JOIN bt_kullanicilar T2 ON T1.kullanici_id = T2.id 
     WHERE T1.siparis_id = :id ORDER BY T1.olusturma_tarihi DESC",
    ['id' => $siparis_id]
);

// 5. Formlar için Kargo Firmalarını Çek
$kargo_firmalari = $db->fetchAll("SELECT id, firma_adi FROM bt_kargo_firmalari WHERE durum = 'Aktif'");

// Sipariş Durumları
$siparis_durumlari = ['Yeni', 'Hazırlanıyor', 'Kargoda', 'Teslim Edildi', 'İade', 'İptal'];

?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI -->
        <div class="page-title-container">
            <div class="row">
                <div class="col-12 col-md-7">
                    <h1 class="mb-0 pb-0 display-4" id="title">Sipariş Detayı</h1>
                    <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                        <ul class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                            <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/siparisler/liste">Siparişler</a></li>
                            <li class="breadcrumb-item active">#<?= htmlspecialchars($siparis['siparis_kodu']) ?></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-xxl-9">
                <!-- SİPARİŞ ÖZETİ KARTI -->
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="row g-0">
                            <div class="col-auto">
                                <div class="sw-5 me-3">
                                    <div class="border border-1 border-primary rounded-xl sw-5 sh-5 d-flex justify-content-center align-items-center">
                                        <i data-acorn-icon="box" class="text-primary"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card-body d-flex flex-column pt-0 pb-0 ps-3 pe-0 h-100 justify-content-center">
                                    <div class="d-flex flex-column">
                                        <div class="text-alternate">Sipariş Kodu: #<?= htmlspecialchars($siparis['siparis_kodu']) ?></div>
                                        <div class="text-muted text-small">Tarih: <?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SİPARİŞ ÜRÜNLERİ KARTI -->
                <h2 class="small-title">Sipariş İçeriği</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <?php foreach ($siparis_detaylari as $item) : ?>
                            <div class="row g-0 sh-9 mb-3">
                                <div class="col-auto">
                                    <img src="<?= htmlspecialchars($item['ana_gorsel'] ?? '/admin_bt/assets/img/placeholder-page.webp') ?>" class="card-img rounded-xl sh-9 sw-9" alt="görsel">
                                </div>
                                <div class="col">
                                    <div class="card-body d-flex flex-column pt-0 pb-0 ps-3 pe-0 h-100 justify-content-center">
                                        <div class="d-flex flex-column">
                                            <a href="/<?= $yonetimurl ?>/urunler/duzenle/<?= $item['urun_id'] ?>" class="mb-1 body-link"><?= htmlspecialchars($item['urun_adi']) ?></a>
                                            <div class="text-muted text-small">Adet: <?= $item['adet'] ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="card-body d-flex flex-column pt-0 pb-0 ps-3 pe-0 h-100 justify-content-center">
                                        <div class="d-flex flex-column">
                                            <div class="text-alternate text-medium"><strong><?= number_format($item['toplam_fiyat'], 2) ?> ₺</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="separator-light mt-4 mb-4"></div>
                        <div class="row g-0">
                            <div class="col text-end">
                                <p class="mb-1"><strong>Ara Toplam:</strong> <?= number_format($siparis['toplam_tutar'], 2) ?> ₺</p>
                                <p class="mb-1"><strong>Kargo Ücreti:</strong> <?= number_format($siparis['kargo_ucreti'], 2) ?> ₺</p>
                                <p class="mb-1"><strong>İndirim:</strong> -<?= number_format($siparis['indirim_tutari'], 2) ?> ₺</p>
                                <h5 class="mb-0"><strong>Genel Toplam: <?= number_format($siparis['odenen_tutar'], 2) ?> ₺</strong></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SİPARİŞ GEÇMİŞİ KARTI -->
                <h2 class="small-title">Sipariş Geçmişi</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <?php foreach ($siparis_gecmisi as $gecmis) : ?>
                            <div class="row g-0">
                                <div class="col-auto">
                                    <div class="sw-3 d-inline-block d-flex justify-content-center align-items-center h-100">
                                        <div class="sh-3 sw-3 rounded-xl bg-gradient-primary"></div>
                                    </div>
                                </div>
                                <div class="col mb-2">
                                    <div class="h-100 d-flex flex-column justify-content-center ps-3">
                                        <div class="d-flex flex-column">
                                            <div class="text-alternate">"<?= htmlspecialchars($gecmis['yeni_durum']) ?>" durumuna geçirildi.</div>
                                            <div class="text-small text-muted"><?= date('d.m.Y H:i', strtotime($gecmis['olusturma_tarihi'])) ?> (<?= htmlspecialchars($gecmis['kullanici_adi'] ?? 'Sistem') ?>)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-xxl-3">
                <!-- YÖNETİCİ PANELİ KARTI -->
                <h2 class="small-title">Yönetim</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Sipariş Durumu</label>
                            <select id="siparisDurumuSelect" class="form-select">
                                <?php foreach ($siparis_durumlari as $durum) : ?>
                                    <option value="<?= $durum ?>" <?= ($siparis['siparis_durumu'] == $durum) ? 'selected' : '' ?>><?= $durum ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button id="durumGuncelleBtn" class="btn btn-outline-primary w-100 mb-3">Durumu Güncelle</button>

                        <div class="mb-3">
                            <label class="form-label">Kargo Firması</label>
                            <select id="kargoFirmasiSelect" class="form-select">
                                <option value="0">Seçiniz...</option>
                                <?php foreach ($kargo_firmalari as $firma) : ?>
                                    <option value="<?= $firma['id'] ?>" <?= ($siparis['kargo_firma_id'] == $firma['id']) ? 'selected' : '' ?>><?= htmlspecialchars($firma['firma_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kargo Takip No</label>
                            <input type="text" class="form-control" id="kargoTakipNoInput" value="<?= htmlspecialchars($siparis['kargo_takip_no'] ?? '') ?>">
                        </div>
                        <button id="kargoGuncelleBtn" class="btn btn-outline-primary w-100">Kargo Bilgilerini Kaydet</button>
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
                            <div><?= htmlspecialchars($siparis['musteri_adi'] ?? 'Misafir Kullanıcı') ?></div>
                            <div class="text-small text-muted"><?= htmlspecialchars($siparis['musteri_eposta'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>

                <!-- TESLİMAT ADRESİ KARTI -->
                <h2 class="small-title">Teslimat Adresi</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <?php if ($teslimat_adresi) : ?>
                            <p class="mb-1"><strong><?= htmlspecialchars($teslimat_adresi['ad_soyad']) ?></strong></p>
                            <p class="mb-1"><?= htmlspecialchars($teslimat_adresi['adres']) ?></p>
                            <p class="mb-1"><?= htmlspecialchars($teslimat_adresi['ilce']) ?> / <?= htmlspecialchars($teslimat_adresi['il']) ?></p>
                            <p class="mb-0"><?= htmlspecialchars($teslimat_adresi['telefon']) ?></p>
                        <?php else : ?>
                            <p class="text-muted">Adres bilgisi bulunamadı.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const siparisId = <?= $siparis_id ?>;
        const yonetimUrl = '<?= $yonetimurl ?>';

        // Durum Güncelleme
        const durumGuncelleBtn = document.getElementById('durumGuncelleBtn');
        durumGuncelleBtn.addEventListener('click', function() {
            this.disabled = true;
            const yeniDurum = document.getElementById('siparisDurumuSelect').value;
            const eskiDurum = '<?= $siparis['siparis_durumu'] ?>';

            const formData = new FormData();
            formData.append('siparis_id', siparisId);
            formData.append('yeni_durum', yeniDurum);
            formData.append('eski_durum', eskiDurum);

            fetch(`/${yonetimUrl}/siparisler/durum-guncelle`, {
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
                }).finally(() => {
                    this.disabled = false;
                });
        });

        // Kargo Bilgisi Güncelleme
        const kargoGuncelleBtn = document.getElementById('kargoGuncelleBtn');
        kargoGuncelleBtn.addEventListener('click', function() {
            this.disabled = true;
            const kargoFirmaId = document.getElementById('kargoFirmasiSelect').value;
            const kargoTakipNo = document.getElementById('kargoTakipNoInput').value;

            const formData = new FormData();
            formData.append('siparis_id', siparisId);
            formData.append('kargo_firma_id', kargoFirmaId);
            formData.append('kargo_takip_no', kargoTakipNo);

            fetch(`/${yonetimUrl}/siparisler/kargo-guncelle`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        beratbabatoast("success", data.message);
                    } else {
                        beratbabatoast("danger", "Hata: " + data.message);
                    }
                }).finally(() => {
                    this.disabled = false;
                });
        });
    });
</script>