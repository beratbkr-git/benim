<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
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

$is_edit = ($p3 === 'tedarikciler-duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Tedarikçi Düzenle' : 'Yeni Tedarikçi Ekle';
$form_action = "/{$yonetimurl}/envanter/" . ($is_edit ? "tedarikciler-duzenle-kontrol" : "tedarikciler-ekle-kontrol");
$tedarikci_id = $is_edit ? (int)$p4 : 0;

// Varsayılan tedarikçi verisi
$tedarikci = [
    'id' => 0,
    'firma_adi' => '',
    'iletisim_kisi' => '',
    'telefon' => '',
    'eposta' => '',
    'adres' => '',
    'vergi_no' => '',
    'odeme_kosullari' => '',
    'teslimat_suresi' => 7,
    'durum' => 'Aktif',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $tedarikci_data = $db->fetch("SELECT * FROM bt_tedarikciler WHERE id = :id", ['id' => $tedarikci_id]);
    if ($tedarikci_data) {
        $tedarikci = array_merge($tedarikci, $tedarikci_data);
    } else {
        $_SESSION["hata"] = "Tedarikçi bulunamadı.";
        header("Location: /{$yonetimurl}/envanter/tedarikciler-liste");
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
                        <a href="/<?= $yonetimurl ?>/envanter/tedarikciler-liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Tedarikçiler</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="tedarikciForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="tedarikciForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $tedarikci_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- TEDARİKÇİ BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Tedarikçi Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="firma_adi">Firma Adı</label>
                                    <input type="text" class="form-control" id="firma_adi" name="firma_adi" value="<?= htmlspecialchars($tedarikci['firma_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="iletisim_kisi">İletişim Kişisi</label>
                                    <input type="text" class="form-control" id="iletisim_kisi" name="iletisim_kisi" value="<?= htmlspecialchars($tedarikci['iletisim_kisi']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="telefon">Telefon</label>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" value="<?= htmlspecialchars($tedarikci['telefon']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="eposta">E-posta</label>
                                    <input type="email" class="form-control" id="eposta" name="eposta" value="<?= htmlspecialchars($tedarikci['eposta']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="vergi_no">Vergi Numarası</label>
                                    <input type="text" class="form-control" id="vergi_no" name="vergi_no" value="<?= htmlspecialchars($tedarikci['vergi_no']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="adres">Adres</label>
                                    <textarea class="form-control" id="adres" name="adres" rows="3"><?= htmlspecialchars($tedarikci['adres']) ?></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="odeme_kosullari">Ödeme Koşulları</label>
                                    <textarea class="form-control" id="odeme_kosullari" name="odeme_kosullari" rows="3"><?= htmlspecialchars($tedarikci['odeme_kosullari']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- YAYINLAMA KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Yayınlama</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($tedarikci['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($tedarikci['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="teslimat_suresi">Teslimat Süresi (Gün)</label>
                                    <input type="number" class="form-control" id="teslimat_suresi" name="teslimat_suresi" value="<?= htmlspecialchars($tedarikci['teslimat_suresi']) ?>">
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
                    <h5 class="modal-title">Tedarikçiyi Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($tedarikci['firma_adi']) ?>" adlı tedarikçiyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const tedarikciId = '<?= $tedarikci_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/envanter/sil-tedarikciler`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [tedarikciId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Tedarikçi başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/envanter/tedarikciler-liste`;
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