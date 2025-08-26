<?php
// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Görsel Düzenle' : 'Yeni Görsel Ekle';
$form_action = "/{$yonetimurl}/gorseller/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$gorsel_id = $is_edit ? (int)$p4 : 0;

// Varsayılan görsel verisi
$gorsel = [
    'id' => 0,
    'gorsel_adi' => '',
    'kisa_aciklama' => '',
    'gorsel_url' => '',
    'konum' => 'basibos_resimler',
    'link' => '',
    'sira' => 0,
    'durum' => 'Aktif',
    'mobil_ayri_gorunum' => 0,
    'mobil_gorsel_url' => '',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $gorsel_data = $db->fetch("SELECT * FROM bt_gorseller WHERE id = :id", ['id' => $gorsel_id]);
    if ($gorsel_data) {
        $gorsel = array_merge($gorsel, $gorsel_data);
    } else {
        $_SESSION["hata"] = "Görsel bulunamadı.";
        header("Location: /{$yonetimurl}/gorseller/liste");
        exit();
    }
}
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/gorseller/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Görseller</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="gorselForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="gorselForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $gorsel_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Görsel Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="gorsel_adi">Görsel Adı</label>
                                    <input type="text" class="form-control" id="gorsel_adi" name="gorsel_adi" value="<?= htmlspecialchars($gorsel['gorsel_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="gorsel_aciklama">Görsel Kısa Açıklaması</label>
                                    <textarea type="text" class="form-control" id="kisa_aciklama" name="kisa_aciklama"><?= htmlspecialchars($gorsel['kisa_aciklama']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="konum">Konum</label>
                                    <select class="form-select select2DelandAdd" id="konum" name="konum" data-placeholder="Konum Seçin">
                                        <option label="Konum Seçin"></option>
                                        <option value="slider_anasayfa" <?= ($gorsel['konum'] == 'slider_anasayfa') ? 'selected' : '' ?>>Slider Görseli</option>
                                        <option value="anasayfa_banner" <?= ($gorsel['konum'] == 'anasayfa_banner') ? 'selected' : '' ?>>Anasayfa Banner</option>
                                        <option value="iletisim_banner" <?= ($gorsel['konum'] == 'iletisim_banner') ? 'selected' : '' ?>>İletişim Banner</option>
                                        <option value="hakkimizda_banner" <?= ($gorsel['konum'] == 'hakkimizda_banner') ? 'selected' : '' ?>>Hakkımızda Banner</option>
                                        <option value="basibos_resimler" <?= ($gorsel['konum'] == 'basibos_resimler') ? 'selected' : '' ?>>Başıboş Resimler</option>
                                    </select>
                                    <small class="form-text text-muted">İstediğiniz konumu seçebilir veya yenisini yazıp ekleyebilirsiniz.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="link">Link</label>
                                    <input type="url" class="form-control" id="link" name="link" value="<?= htmlspecialchars($gorsel['link']) ?>">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label" for="sira">Sıralama</label>
                                        <input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($gorsel['sira']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="durum">Durum</label>
                                        <select class="form-select select2Basic" id="durum" name="durum">
                                            <option value="Aktif" <?= ($gorsel['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Pasif" <?= ($gorsel['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- GÖRSEL YÜKLEME KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Görsel</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Ana Görsel (Masaüstü)</label>
                                    <div id="resimYukleme" class="singleImageUpload">
                                        <img src="<?= htmlspecialchars($gorsel['gorsel_url'] ?? '/admin_bt/assets/img/placeholder-page.webp') ?>" class="border border-4 border-separator-light rounded-xl sh-11 sw-11" alt="Görsel" onerror="this.onerror=null;this.src='/admin_bt/assets/img/placeholder-page.webp';" />
                                        <button class="btn btn-icon btn-icon-only btn-separator-light btn-sm position-absolute rounded-xl b-0 e-0" type="button">
                                            <i data-acorn-icon="upload"></i>
                                        </button>
                                        <input class="d-none file-upload" name="gorsel_url" type="file" accept="image/*" />
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="mobil_ayri_gorunum" name="mobil_ayri_gorunum" value="1" <?= $gorsel['mobil_ayri_gorunum'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mobil_ayri_gorunum">Mobil için ayrı görsel kullan</label>
                                    </div>
                                </div>
                                <div id="mobilGorselAlani" class="mb-0" style="display: <?= $gorsel['mobil_ayri_gorunum'] ? 'block' : 'none' ?>;">
                                    <label class="form-label">Mobil Görsel</label>
                                    <div id="mobilResimYukleme" class="singleImageUpload">
                                        <img src="<?= htmlspecialchars($gorsel['mobil_gorsel_url'] ?? '/admin_bt/assets/img/placeholder-page.webp') ?>" class="border border-4 border-separator-light rounded-xl sh-11 sw-11" alt="Mobil Görsel" onerror="this.onerror=null;this.src='/admin_bt/assets/img/placeholder-page.webp';" />
                                        <button class="btn btn-icon btn-icon-only btn-separator-light btn-sm position-absolute rounded-xl b-0 e-0" type="button">
                                            <i data-acorn-icon="upload"></i>
                                        </button>
                                        <input class="d-none file-upload" name="mobil_gorsel_url" type="file" accept="image/*" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($is_edit) : ?>
                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Silme Onay Modalı -->
<?php if ($is_edit) : ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Görseli Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($gorsel['gorsel_adi']) ?>" adlı görseli kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
                this.disabled = true;
                const gorselId = '<?= $gorsel_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/gorseller/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [gorselId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Görsel başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimurl}/gorseller/liste`;
                            }, 1500);
                        } else {
                            beratbabatoast("danger", "Silme işlemi başarısız: " + data.message);
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        beratbabatoast("danger", "Silme işlemi sırasında bir sunucu hatası oluştu.");
                        this.disabled = false;
                    });
            });
        });

        // Mobile ayrı görünüm checkbox'ı için dinleyici
        const mobileCheckbox = document.getElementById('mobil_ayri_gorunum');
        const mobileImageArea = document.getElementById('mobilGorselAlani');

        mobileCheckbox.addEventListener('change', function() {
            if (this.checked) {
                mobileImageArea.style.display = 'block';
            } else {
                mobileImageArea.style.display = 'none';
            }
        });

        // Select2'yi başlat
        $('.select2DelandAdd').select2({
            tags: true,
            tokenSeparators: [',']
        });
        $('.select2Basic').select2({
            minimumResultsForSearch: Infinity
        });
    </script>
<?php endif; ?>