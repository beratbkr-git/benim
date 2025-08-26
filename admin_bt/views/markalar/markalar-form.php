<?php
if (!defined('YONETIM_DIR')) {
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Marka Düzenle' : 'Yeni Marka Ekle';
$form_action = "/{$yonetimurl}/markalar/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$marka_id = $is_edit ? (int)$p4 : 0;

$marka = [
    'id' => 0,
    'marka_adi' => '',
    'aciklama' => '',
    'logo_url' => '',
    'durum' => 'Aktif',
    'seo_title' => '',
    'seo_description' => '',
];

if ($is_edit) {
    $marka_data = $db->fetch("SELECT * FROM bt_markalar WHERE id = :id", ['id' => $marka_id]);
    if ($marka_data) {
        $marka = array_merge($marka, $marka_data);
    } else {
        $_SESSION["hata"] = "Marka bulunamadı.";
        header("Location: /{$yonetimurl}/markalar/liste");
        exit();
    }
}
?>
<main>
    <div class="container">
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/markalar/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Markalar</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="markaForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="markaForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $marka_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Genel Bilgiler</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="marka_adi">Marka Adı</label>
                                    <input type="text" class="form-control" id="marka_adi" name="marka_adi" value="<?= htmlspecialchars($marka['marka_adi']) ?>" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="aciklama">Açıklama</label>
                                    <textarea class="form-control ckeditor" id="aciklama" name="aciklama" rows="5"><?= htmlspecialchars($marka['aciklama']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- SEO AYARLARI KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">SEO Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="seo_title">SEO Başlığı</label>
                                    <input type="text" class="form-control" id="seo_title" name="seo_title" value="<?= htmlspecialchars($marka['seo_title']) ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="seo_description">Meta Açıklaması</label>
                                    <textarea class="form-control" id="seo_description" name="seo_description" rows="3"><?= htmlspecialchars($marka['seo_description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- LOGO VE DURUM KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Logo ve Yayınlama</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Marka Logosu</label>
                                    <div id="markaLogoYukleme" class="singleImageUpload">
                                        <img src="<?= htmlspecialchars($marka['logo_url'] ?? '/admin_bt/assets/img/placeholder-page.webp') ?>" class="border border-4 border-separator-light rounded-xl sh-11 sw-11" alt="Marka Logosu" onerror="this.onerror=null;this.src='/admin_bt/assets/img/placeholder-page.webp';" />
                                        <button class="btn btn-icon btn-icon-only btn-separator-light btn-sm position-absolute rounded-xl b-0 e-0" type="button">
                                            <i data-acorn-icon="upload"></i>
                                        </button>
                                        <input class="d-none file-upload" name="logo_url" type="file" accept="image/*" />
                                    </div>
                                    <?php if ($is_edit && $marka['logo_url']): ?>
                                        <small class="form-text text-muted d-block mt-2">Mevcut Logo: <code><?= htmlspecialchars($marka['logo_url']) ?></code></small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($marka['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($marka['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                    </select>
                                </div>
                                <?php if ($is_edit) : ?>
                                    <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                <?php endif; ?>
                            </div>
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
                    <h5 class="modal-title">Markayı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($marka['marka_adi']) ?>" adlı markayı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const markaId = '<?= $marka_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/markalar/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [markaId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Marka başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/markalar/liste`;
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