<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Yetki kontrolü
if (!hasPermission('Yönetici')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $db;

// Ayarları veritabanından çek
$ayarlar = $db->fetchAll("SELECT * FROM bt_sistem_ayarlari_gelismis WHERE kategori = 'eposta'");
$ayarlar_map = array_column($ayarlar, 'ayar_degeri', 'ayar_anahtari');
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/anasayfa" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Panel Anasayfa</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title">E-posta Ayarları</h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="epostaAyarlarForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="epostaAyarlarForm" action="/<?= $yonetimurl ?>/ayarlar/kaydet-gelismis" method="POST" class="tooltip-end-bottom" novalidate>
            <div class="row">
                <div class="col-xl-8">
                    <div class="mb-5">
                        <h2 class="small-title">SMTP Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="smtp_sunucu">SMTP Sunucu</label>
                                    <input type="text" class="form-control" id="smtp_sunucu" name="ayarlar[smtp_sunucu]" value="<?= htmlspecialchars($ayarlar_map['smtp_sunucu'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="smtp_port">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtp_port" name="ayarlar[smtp_port]" value="<?= htmlspecialchars($ayarlar_map['smtp_port'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="smtp_kullanici_adi">SMTP Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="smtp_kullanici_adi" name="ayarlar[smtp_kullanici_adi]" value="<?= htmlspecialchars($ayarlar_map['smtp_kullanici_adi'] ?? '') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="smtp_sifre">SMTP Şifre</label>
                                    <input type="password" class="form-control" id="smtp_sifre" name="ayarlar[smtp_sifre]" value="<?= htmlspecialchars($ayarlar_map['smtp_sifre'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>