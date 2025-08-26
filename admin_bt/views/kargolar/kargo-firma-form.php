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

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'kargo-firma-duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Kargo Firması Düzenle' : 'Yeni Kargo Firması Ekle';
$form_action = "/{$yonetimurl}/kargolar/" . ($is_edit ? "kargo-firma-duzenle-kontrol" : "kargo-firma-ekle-kontrol");
$firma_id = $is_edit ? (int)$p4 : 0;

// Varsayılan kargo firması verisi
$kargo_firmasi = [
    'id' => 0,
    'firma_adi' => '',
    'takip_url_sablonu' => '',
    'logo_url' => '',
    'durum' => 'Aktif',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $firma_data = $db->fetch("SELECT * FROM bt_kargo_firmalari WHERE id = :id", ['id' => $firma_id]);
    if ($firma_data) {
        $kargo_firmasi = array_merge($kargo_firmasi, $firma_data);
    } else {
        $_SESSION["hata"] = "Kargo firması bulunamadı.";
        header("Location: /{$yonetimurl}/kargolar/kargo");
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
                        <a href="/<?= $yonetimurl ?>/kargolar/kargo-firmalari-liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Kargo Ayarları</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="kargoFirmaForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="kargoFirmaForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $firma_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Kargo Firması Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="firma_adi">Firma Adı</label>
                                    <input type="text" class="form-control" id="firma_adi" name="firma_adi" value="<?= htmlspecialchars($kargo_firmasi['firma_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="takip_url_sablonu">Takip URL Şablonu</label>
                                    <input type="text" class="form-control" id="takip_url_sablonu" name="takip_url_sablonu" value="<?= htmlspecialchars($kargo_firmasi['takip_url_sablonu']) ?>">
                                    <small class="form-text text-muted">Örn: `https://www.yurticikargo.com/gonderi-sorgula?no={takip_no}`</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- LOGO KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Firma Logosu</h2>
                        <div class="card">
                            <div class="card-body">
                                <div id="firmaLogoYukleme" class="singleImageUpload">
                                    <img src="<?= htmlspecialchars($kargo_firmasi['logo_url'] ?? '/admin_bt/assets/img/default-logo.webp') ?>" class="border border-4 border-separator-light rounded-xl sh-11 sw-11" alt="Firma Logosu" onerror="this.onerror=null;this.src='/admin_bt/assets/img/default-logo.webp';" />
                                    <button class="btn btn-icon btn-icon-only btn-separator-light btn-sm position-absolute rounded-xl b-0 e-0" type="button">
                                        <i data-acorn-icon="upload"></i>
                                    </button>
                                    <input class="d-none file-upload" name="logo_url" type="file" accept="image/*" />
                                </div>
                                <?php if ($is_edit && $kargo_firmasi['logo_url']): ?>
                                    <small class="form-text text-muted d-block mt-2">Mevcut Logo: <code><?= htmlspecialchars($kargo_firmasi['logo_url']) ?></code></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- DURUM KARTI -->
                    <div class="card">
                        <div class="card-body">
                            <h2 class="small-title">Durum</h2>
                            <div class="mb-3">
                                <label class="form-label" for="durum">Durum</label>
                                <select class="form-select select2Basic" id="durum" name="durum">
                                    <option value="Aktif" <?= ($kargo_firmasi['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Pasif" <?= ($kargo_firmasi['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <?php if ($is_edit) : ?>
                                <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                            <?php endif; ?>
                        </div>
                    </div>
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
                    <h5 class="modal-title">Kargo Firmasını Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($kargo_firmasi['firma_adi']) ?>" adlı kargo firmasını kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const firmaId = '<?= $firma_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/kargolar/kargo-firma-sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [firmaId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Kargo firması başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/kargolar/kargo-firmalari-liste`;
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
    </script>
<?php endif; ?>