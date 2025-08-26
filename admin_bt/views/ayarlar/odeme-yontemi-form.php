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

$is_edit = ($p3 === 'odeme-yontemi-duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Ödeme Yöntemi Düzenle' : 'Yeni Ödeme Yöntemi Ekle';
$form_action = "/{$yonetimurl}/ayarlar/" . ($is_edit ? "odeme-yontemi-duzenle-kontrol" : "odeme-yontemi-ekle-kontrol");
$yontem_id = $is_edit ? (int)$p4 : 0;

// Varsayılan ödeme yöntemi verisi
$odeme_yontemi = [
    'id' => 0,
    'yontem_adi' => '',
    'yontem_kodu' => '',
    'aciklama' => '',
    'komisyon_orani' => 0.00,
    'min_tutar' => 0.00,
    'max_tutar' => null,
    'durum' => 'Aktif',
    'gateway_ayarlari' => '[]',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $yontem_data = $db->fetch("SELECT * FROM bt_odeme_yontemleri WHERE id = :id", ['id' => $yontem_id]);
    if ($yontem_data) {
        $odeme_yontemi = array_merge($odeme_yontemi, $yontem_data);
    } else {
        $_SESSION["hata"] = "Ödeme yöntemi bulunamadı.";
        header("Location: /{$yonetimurl}/ayarlar/odeme");
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
                        <a href="/<?= $yonetimurl ?>/ayarlar/odeme" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Ödeme Yöntemleri</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="odemeYontemiForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="odemeYontemiForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $yontem_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Ödeme Yöntemi Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="yontem_adi">Yöntem Adı</label>
                                    <input type="text" class="form-control" id="yontem_adi" name="yontem_adi" value="<?= htmlspecialchars($odeme_yontemi['yontem_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="yontem_kodu">Yöntem Kodu</label>
                                    <input type="text" class="form-control" id="yontem_kodu" name="yontem_kodu" value="<?= htmlspecialchars($odeme_yontemi['yontem_kodu']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="aciklama">Açıklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= htmlspecialchars($odeme_yontemi['aciklama']) ?></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label" for="komisyon_orani">Komisyon Oranı (%)</label>
                                        <input type="number" class="form-control" id="komisyon_orani" name="komisyon_orani" value="<?= htmlspecialchars($odeme_yontemi['komisyon_orani']) ?>" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="min_tutar">Min. Tutar (₺)</label>
                                        <input type="number" class="form-control" id="min_tutar" name="min_tutar" value="<?= htmlspecialchars($odeme_yontemi['min_tutar']) ?>" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="max_tutar">Max. Tutar (₺)</label>
                                        <input type="number" class="form-control" id="max_tutar" name="max_tutar" value="<?= htmlspecialchars($odeme_yontemi['max_tutar']) ?>" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- DURUM KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Durum</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($odeme_yontemi['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($odeme_yontemi['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
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
                    <h5 class="modal-title">Ödeme Yöntemini Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($odeme_yontemi['yontem_adi']) ?>" adlı ödeme yöntemini kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const yontemId = '<?= $yontem_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/ayarlar/odeme-yontemi-sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [yontemId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Ödeme yöntemi başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimurl}/ayarlar/odeme`;
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