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
$ayarlar = $db->fetchAll("SELECT * FROM bt_site_ayarlari");
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
                        <h1 class="mb-0 pb-0 display-4" id="title">Genel Ayarlar</h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="ayarlarForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="ayarlarForm" action="/<?= $yonetimurl ?>/ayarlar/kaydet" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Site Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="site_adi">Site Adı</label>
                                    <input type="text" class="form-control" id="site_adi" name="ayarlar[site_adi]" value="<?= htmlspecialchars($ayarlar_map['site_adi'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="site_url">Site URL</label>
                                    <input type="text" class="form-control" id="site_url" name="ayarlar[site_url]" value="<?= htmlspecialchars($ayarlar_map['site_url'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="eposta_iletisim">İletişim E-postası</label>
                                    <input type="email" class="form-control" id="eposta_iletisim" name="ayarlar[eposta_iletisim]" value="<?= htmlspecialchars($ayarlar_map['eposta_iletisim'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="telefon_iletisim">Telefon Numarası</label>
                                    <input type="tel" class="form-control" id="telefon_iletisim" name="ayarlar[telefon_iletisim]" value="<?= htmlspecialchars($ayarlar_map['telefon_iletisim'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="adres_bilgisi">Adres</label>
                                    <textarea class="form-control" id="adres_bilgisi" name="ayarlar[adres_bilgisi]" rows="3"><?= htmlspecialchars($ayarlar_map['adres_bilgisi'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="copyright_yazisi">Copyright Yazısı</label>
                                    <input type="text" class="form-control" id="copyright_yazisi" name="ayarlar[copyright_yazisi]" value="<?= htmlspecialchars($ayarlar_map['copyright_yazisi'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- DİL AYARLARI KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Dil Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="varsayilan_dil_kodu">Varsayılan Dil Kodu</label>
                                    <input type="text" class="form-control" id="varsayilan_dil_kodu" name="ayarlar[varsayilan_dil_kodu]" value="<?= htmlspecialchars($ayarlar_map['varsayilan_dil_kodu'] ?? 'tr') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="varsayilan_dil_adi">Varsayılan Dil Adı</label>
                                    <input type="text" class="form-control" id="varsayilan_dil_adi" name="ayarlar[varsayilan_dil_adi]" value="<?= htmlspecialchars($ayarlar_map['varsayilan_dil_adi'] ?? 'Türkçe') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- SOSYAL MEDYA KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Sosyal Medya</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="facebook_url">Facebook URL</label>
                                    <input type="text" class="form-control" id="facebook_url" name="ayarlar[facebook_url]" value="<?= htmlspecialchars($ayarlar_map['facebook_url'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="twitter_url">Twitter URL</label>
                                    <input type="text" class="form-control" id="twitter_url" name="ayarlar[twitter_url]" value="<?= htmlspecialchars($ayarlar_map['twitter_url'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="instagram_url">Instagram URL</label>
                                    <input type="text" class="form-control" id="instagram_url" name="ayarlar[instagram_url]" value="<?= htmlspecialchars($ayarlar_map['instagram_url'] ?? '') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="youtube_url">YouTube URL</label>
                                    <input type="text" class="form-control" id="youtube_url" name="ayarlar[youtube_url]" value="<?= htmlspecialchars($ayarlar_map['youtube_url'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- LOGO AYARLARI KARTI -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Logo Ayarları</h5>
                            <div class="mb-3">
                                <label class="form-label">Ana Logo</label>
                                <?= getSingleImageUpload('site_logo_url', $ayarlar_map['site_logo_url'] ?? '', 'Ana Logo') ?>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Footer Logo</label>
                                <?= getSingleImageUpload('site_footer_logo_url', $ayarlar_map['site_footer_logo_url'] ?? '', 'Footer Logo') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>