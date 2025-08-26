<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Kampanya yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Kampanya Düzenle' : 'Yeni Kampanya Ekle';
$form_action = "/{$yonetimurl}/kampanyalar/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$kampanya_id = $is_edit ? (int)$p4 : 0;

// Varsayılan kampanya verisi
$kampanya = [
    'id' => 0,
    'kampanya_adi' => '',
    'indirim_tipi' => 'Yuzde',
    'indirim_degeri' => 0.00,
    'min_sepet_tutari' => 0.00,
    'durum' => 'Aktif',
    'baslangic_tarihi' => date('Y-m-d'),
    'bitis_tarihi' => null,
    'kriterler' => '[]',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $kampanya_data = $db->fetch("SELECT * FROM bt_kampanyalar WHERE id = :id", ['id' => $kampanya_id]);
    if ($kampanya_data) {
        $kampanya = array_merge($kampanya, $kampanya_data);
    } else {
        $_SESSION["hata"] = "Kampanya bulunamadı.";
        header("Location: /{$yonetimurl}/kampanyalar/liste");
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
                        <a href="/<?= $yonetimurl ?>/kampanyalar/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Kampanyalar</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="kampanyaForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="kampanyaForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $kampanya_id ?>">
            <input type="hidden" name="kriterler" id="kriterlerInput" value='<?= htmlspecialchars($kampanya['kriterler'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>'>
            <div class="row">
                <div class="col-xl-8">
                    <!-- KAMPANYA BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Kampanya Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="kampanya_adi">Kampanya Adı</label>
                                    <input type="text" class="form-control" id="kampanya_adi" name="kampanya_adi" value="<?= htmlspecialchars($kampanya['kampanya_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="indirim_tipi">İndirim Tipi</label>
                                    <select class="form-select select2Basic" id="indirim_tipi" name="indirim_tipi">
                                        <option value="Yuzde" <?= ($kampanya['indirim_tipi'] == 'Yuzde') ? 'selected' : '' ?>>Yüzde (%)</option>
                                        <option value="Sabit" <?= ($kampanya['indirim_tipi'] == 'Sabit') ? 'selected' : '' ?>>Sabit Tutar (₺)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="indirim_degeri">İndirim Değeri</label>
                                    <input type="number" class="form-control" id="indirim_degeri" name="indirim_degeri" value="<?= htmlspecialchars($kampanya['indirim_degeri']) ?>" step="0.01" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="min_sepet_tutari">Minimum Sepet Tutarı (₺)</label>
                                    <input type="number" class="form-control" id="min_sepet_tutari" name="min_sepet_tutari" value="<?= htmlspecialchars($kampanya['min_sepet_tutari']) ?>" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- YAYINLAMA VE GEÇERLİLİK KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Yayınlama</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($kampanya['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($kampanya['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Geçerlilik Tarihi</label>
                                    <div class="input-daterange input-group" id="datePickerRange">
                                        <input type="text" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" value="<?= htmlspecialchars($kampanya['baslangic_tarihi'] ? date('d.m.Y', strtotime($kampanya['baslangic_tarihi'])) : '') ?>" placeholder="Başlangıç Tarihi" required>
                                        <span class="input-group-text"> - </span>
                                        <input type="text" class="form-control" id="bitis_tarihi" name="bitis_tarihi" value="<?= htmlspecialchars($kampanya['bitis_tarihi'] ? date('d.m.Y', strtotime($kampanya['bitis_tarihi'])) : '') ?>" placeholder="Bitiş Tarihi">
                                    </div>
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
                    <h5 class="modal-title">Kampanyayı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($kampanya['kampanya_adi']) ?>" adlı kampanyayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const kampanyaId = '<?= $kampanya_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/kampanyalar/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [kampanyaId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Kampanya başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/kampanyalar/liste`;
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
            // Datepicker'ı başlat
            $('#datePickerRange').datepicker({
                language: 'tr',
                format: 'dd.mm.yyyy',
                weekStart: 1,
                autoclose: true
            });
        });
    </script>
<?php endif; ?>