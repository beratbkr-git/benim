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
$ayarlar = $db->fetchAll("SELECT * FROM bt_sistem_ayarlari_gelismis WHERE kategori = 'eticaret'");
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
                        <h1 class="mb-0 pb-0 display-4" id="title">E-ticaret Ayarları</h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="eticaretAyarlarForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="eticaretAyarlarForm" action="/<?= $yonetimurl ?>/ayarlar/kaydet-gelismis" method="POST" class="tooltip-end-bottom" novalidate>
            <div class="row">
                <div class="col-xl-8">
                    <div class="mb-5">
                        <h2 class="small-title">Fiyatlandırma ve Para Birimi</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="varsayilan_para_birimi">Varsayılan Para Birimi</label>
                                    <input type="text" class="form-control" id="varsayilan_para_birimi" name="ayarlar[varsayilan_para_birimi]" value="<?= htmlspecialchars($ayarlar_map['varsayilan_para_birimi'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="kdv_orani">KDV Oranı (%)</label>
                                    <input type="number" class="form-control" id="kdv_orani" name="ayarlar[kdv_orani]" value="<?= htmlspecialchars($ayarlar_map['kdv_orani'] ?? '') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="ucretsiz_kargo_limiti">Ücretsiz Kargo Limiti (₺)</label>
                                    <input type="number" class="form-control" id="ucretsiz_kargo_limiti" name="ayarlar[ucretsiz_kargo_limiti]" value="<?= htmlspecialchars($ayarlar_map['ucretsiz_kargo_limiti'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>