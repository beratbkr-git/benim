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

$is_edit = ($p3 === 'kargo-yontemi-duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Kargo Yöntemi Düzenle' : 'Yeni Kargo Yöntemi Ekle';
$form_action = "/{$yonetimurl}/kargolar/" . ($is_edit ? "kargo-yontemi-duzenle-kontrol" : "kargo-yontemi-ekle-kontrol");
$yontem_id = $is_edit ? (int)$p4 : 0;

// Varsayılan kargo yöntemi verisi
$kargo_yontemi = [
    'id' => 0,
    'yontem_adi' => '',
    'aciklama' => '',
    'temel_ucret' => 0.00,
    'hesaplama_tipi' => 'sabit',
    'birim_ucret' => 0.00,
    'yuzde_orani' => 0.00,
    'ucretsiz_kargo_limiti' => 0.00,
    'min_teslimat_gun' => 1,
    'max_teslimat_gun' => 7,
    'durum' => 'Aktif',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $yontem_data = $db->fetch("SELECT * FROM bt_kargo_yontemleri WHERE id = :id", ['id' => $yontem_id]);
    if ($yontem_data) {
        $kargo_yontemi = array_merge($kargo_yontemi, $yontem_data);
    } else {
        $_SESSION["hata"] = "Kargo yöntemi bulunamadı.";
        header("Location: /{$yonetimurl}/kargolar/kargo-yontemleri-liste");
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
                        <a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemleri-liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Kargo Ayarları</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="kargoYontemiForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="kargoYontemiForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $yontem_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Kargo Yöntemi Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="yontem_adi">Yöntem Adı</label>
                                    <input type="text" class="form-control" id="yontem_adi" name="yontem_adi" value="<?= htmlspecialchars($kargo_yontemi['yontem_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="aciklama">Açıklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= htmlspecialchars($kargo_yontemi['aciklama']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="hesaplama_tipi">Hesaplama Tipi</label>
                                    <select class="form-select select2Basic" id="hesaplama_tipi" name="hesaplama_tipi">
                                        <option value="sabit" <?= ($kargo_yontemi['hesaplama_tipi'] == 'sabit') ? 'selected' : '' ?>>Sabit Fiyat</option>
                                        <option value="agirlik" <?= ($kargo_yontemi['hesaplama_tipi'] == 'agirlik') ? 'selected' : '' ?>>Ağırlık Bazlı</option>
                                        <option value="deger" <?= ($kargo_yontemi['hesaplama_tipi'] == 'deger') ? 'selected' : '' ?>>Değer Bazlı</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="temel_ucret">Temel Ücret (₺)</label>
                                    <input type="number" class="form-control" id="temel_ucret" name="temel_ucret" value="<?= htmlspecialchars($kargo_yontemi['temel_ucret']) ?>" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="ucretsiz_kargo_limiti">Ücretsiz Kargo Limiti (₺)</label>
                                    <input type="number" class="form-control" id="ucretsiz_kargo_limiti" name="ucretsiz_kargo_limiti" value="<?= htmlspecialchars($kargo_yontemi['ucretsiz_kargo_limiti']) ?>" step="0.01">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label" for="min_teslimat_gun">Min. Teslimat Günü</label>
                                        <input type="number" class="form-control" id="min_teslimat_gun" name="min_teslimat_gun" value="<?= htmlspecialchars($kargo_yontemi['min_teslimat_gun']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="max_teslimat_gun">Max. Teslimat Günü</label>
                                        <input type="number" class="form-control" id="max_teslimat_gun" name="max_teslimat_gun" value="<?= htmlspecialchars($kargo_yontemi['max_teslimat_gun']) ?>">
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
                                        <option value="Aktif" <?= ($kargo_yontemi['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($kargo_yontemi['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
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
                    <h5 class="modal-title">Kargo Yöntemini Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($kargo_yontemi['yontem_adi']) ?>" adlı kargo yöntemini kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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

                fetch(`/${yonetimUrl}/kargolar/kargo-yontemi-sil`, {
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
                            beratbabatoast("success", "Kargo yöntemi başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/kargolar/kargo-yontemleri-liste`;
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