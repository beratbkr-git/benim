<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Kategori yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Kategori Düzenle' : 'Yeni Kategori Ekle';
$form_action = "/{$yonetimurl}/kategoriler/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$kategori_id = $is_edit ? (int)$p4 : 0;

// Varsayılan kategori verisi
$kategori = [
    'id' => 0,
    'kategori_adi' => '',
    'ust_kategori_id' => null,
    'aciklama' => '',
    'seo_title' => '',
    'seo_description' => '',
    'seo_keywords' => '',
    'gorsel_url' => '',
    'banner_image' => '',
    'featured' => 0,
    'sira' => 0,
    'durum' => 'Aktif',
];
$tum_kategoriler = [];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $kategori_data = $db->fetch("SELECT * FROM bt_kategoriler WHERE id = :id", ['id' => $kategori_id]);
    if ($kategori_data) {
        $kategori = array_merge($kategori, $kategori_data);
        // Kendisi hariç diğer kategorileri çek
        $tum_kategoriler = $db->fetchAll("SELECT id, kategori_adi FROM bt_kategoriler WHERE id != :id AND durum = 'Aktif' ORDER BY sira ASC", ['id' => $kategori_id]);
    } else {
        $_SESSION["hata"] = "Kategori bulunamadı.";
        header("Location: /{$yonetimurl}/kategoriler/liste");
        exit();
    }
} else {
    // Ekleme modunda tüm kategorileri çek
    $tum_kategoriler = $db->fetchAll("SELECT id, kategori_adi FROM bt_kategoriler WHERE durum = 'Aktif' ORDER BY sira ASC");
}
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/kategoriler/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Kategoriler</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="kategoriForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="kategoriForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $kategori_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- KATEGORİ BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Kategori Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="kategori_adi">Kategori Adı</label>
                                    <input type="text" class="form-control" id="kategori_adi" name="kategori_adi" value="<?= htmlspecialchars($kategori['kategori_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="ust_kategori_id">Üst Kategori</label>
                                    <select class="form-select select2Basic" id="ust_kategori_id" name="ust_kategori_id">
                                        <option value="">Üst Kategori Yok</option>
                                        <?php foreach ($tum_kategoriler as $ust_kategori) : ?>
                                            <option value="<?= $ust_kategori['id'] ?>" <?= ($kategori['ust_kategori_id'] == $ust_kategori['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ust_kategori['kategori_adi']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="aciklama">Açıklama</label>
                                    <textarea class="form-control ckeditor" id="aciklama" name="aciklama" rows="3"><?= htmlspecialchars($kategori['aciklama']) ?></textarea>
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
                                    <input type="text" class="form-control" id="seo_title" name="seo_title" value="<?= htmlspecialchars($kategori['seo_title']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="seo_description">Meta Açıklaması</label>
                                    <textarea class="form-control" id="seo_description" name="seo_description" rows="3"><?= htmlspecialchars($kategori['seo_description']) ?></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="seo_keywords">SEO Anahtar Kelimeler</label>
                                    <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" value="<?= htmlspecialchars($kategori['seo_keywords']) ?>">
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
                                    <?= getSingleImageUpload('gorsel_url', $kategori['gorsel_url'], 'Kategori Görseli') ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Banner Görseli</label>
                                    <?= getSingleImageUpload('banner_image', $kategori['banner_image'], 'Kategori Bannerı') ?>
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
                                    <option value="Aktif" <?= ($kategori['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Pasif" <?= ($kategori['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="sira">Sıralama</label>
                                <input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($kategori['sira']) ?>">
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?= $kategori['featured'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="featured">Öne Çıkan Kategori</label>
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
                    <h5 class="modal-title">Kategoriyi Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($kategori['kategori_adi']) ?>" adlı kategoriyi kalıcı olarak silmek istediğinizden emin misiniz? Alt kategoriler de silinecektir. Bu işlem geri alınamaz.
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
                const kategoriId = '<?= $kategori_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/kategoriler/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [kategoriId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Kategori başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/kategoriler/liste`;
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