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
$page_title = $is_edit ? 'Sayfa Düzenle' : 'Yeni Sayfa Ekle';
$form_action = "/{$yonetimurl}/sayfalar/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$sayfa_id = $is_edit ? (int)$p4 : 0;

// Varsayılan sayfa verisi
$sayfa = [
    'id' => 0,
    'sayfa_adi' => '',
    'slug' => '',
    'meta_title' => '',
    'meta_description' => '',
    'icerik' => '',
    'resim' => '',
    'banner' => '',
    'sira' => 0,
    'durum' => 'Aktif',
    'dosya' => '',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $sayfa_data = $db->fetch("SELECT * FROM bt_sayfa WHERE id = :id", ['id' => $sayfa_id]);
    if ($sayfa_data) {
        $sayfa = array_merge($sayfa, $sayfa_data);
    } else {
        $_SESSION["hata"] = "Sayfa bulunamadı.";
        header("Location: /{$yonetimurl}/sayfalar/liste");
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
                        <a href="/<?= $yonetimurl ?>/sayfalar/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Sayfalar</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="sayfaForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="sayfaForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $sayfa_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- İÇERİK AYARLARI KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">İçerik Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="sayfa_adi">Sayfa Adı</label>
                                    <input type="text" class="form-control" id="sayfa_adi" name="sayfa_adi" value="<?= htmlspecialchars($sayfa['sayfa_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="icerik">İçerik</label>
                                    <div id="editor" style="height: 300px;">
                                        <?= htmlspecialchars_decode($sayfa['icerik']) ?>
                                    </div>
                                    <textarea class="d-none" name="icerik" id="icerik_textarea"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="dosya">Dosya Adı</label>
                                    <input type="text" class="form-control" id="dosya" name="dosya" value="<?= htmlspecialchars($sayfa['dosya']) ?>">
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
                                    <label class="form-label" for="meta_title">Meta Başlığı</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($sayfa['meta_title']) ?>">
                                    <small class="form-text text-muted">Bu alan boş bırakılırsa, sayfa adı otomatik olarak kullanılır. (Örnek: Sayfa Adı | Site Adı)</small>
                                </div>
                                <!-- <div class="mb-3">
                                    <label class="form-label" for="sef_link">Sef Link (URL)</label>
                                    <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($sayfa['slug']) ?>">
                                    <small class="form-text text-muted">Bu alan boş bırakılırsa, sayfa adından otomatik oluşturulur.</small>
                                </div> -->
                                <div class="mb-3">
                                    <label class="form-label" for="meta_description">Meta Açıklaması</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars($sayfa['meta_description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- GÖRSEL AYARLARI KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Görsel Ayarları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Ana Görsel</label>
                                    <?= getSingleImageUpload('resim', $sayfa['resim'], 'Ana Görsel') ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Banner Görseli</label>
                                    <?= getSingleImageUpload('banner', $sayfa['banner'], 'Banner Görseli') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- YAYINLAMA KARTI -->
                    <div class="card">
                        <div class="card-body">
                            <h2 class="small-title">Yayınlama</h2>
                            <div class="mb-3">
                                <label class="form-label" for="durum">Durum</label>
                                <select class="form-select select2Basic" id="durum" name="durum">
                                    <option value="Aktif" <?= ($sayfa['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Pasif" <?= ($sayfa['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="sira">Sıralama</label>
                                <input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($sayfa['sira']) ?>">
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
                    <h5 class="modal-title">Sayfayı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($sayfa['sayfa_adi']) ?>" adlı sayfayı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const sayfaId = '<?= $sayfa_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/sayfalar/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [sayfaId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Sayfa başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimurl}/sayfalar/liste`;
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