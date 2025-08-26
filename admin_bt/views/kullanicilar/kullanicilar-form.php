<?php
// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Yetki kontrolü
if (!hasPermission('Admin')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle';
$form_action = "/{$yonetimurl}/kullanicilar/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$kullanici_id = $is_edit ? (int)$p4 : 0;

// Varsayılan kullanıcı verisi
$kullanici = [
    'id' => 0,
    'ad_soyad' => '',
    'eposta' => '',
    'parola' => '',
    'yetki_seviyesi' => 'Editör',
    'aktif_mi' => 1,
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $kullanici_data = $db->fetch("SELECT * FROM bt_kullanicilar WHERE id = :id", ['id' => $kullanici_id]);
    if ($kullanici_data) {
        $kullanici = array_merge($kullanici, $kullanici_data);
    } else {
        $_SESSION["hata"] = "Kullanıcı bulunamadı.";
        header("Location: /{$yonetimurl}/kullanicilar/liste");
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
                        <a href="/<?= $yonetimurl ?>/kullanicilar/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Kullanıcılar</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="kullaniciForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="kullaniciForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $kullanici_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- KULLANICI BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Kullanıcı Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="ad_soyad">Ad Soyad</label>
                                    <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" value="<?= htmlspecialchars($kullanici['ad_soyad']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="eposta">E-posta</label>
                                    <input type="email" class="form-control" id="eposta" name="eposta" value="<?= htmlspecialchars($kullanici['eposta']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="parola">Parola</label>
                                    <input type="password" class="form-control" id="parola" name="parola">
                                    <?php if ($is_edit) : ?>
                                        <small class="text-muted">Parolayı değiştirmek istemiyorsanız boş bırakın.</small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="yetki_seviyesi">Yetki Seviyesi</label>
                                    <select class="form-select select2Basic" id="yetki_seviyesi" name="yetki_seviyesi" required>
                                        <option value="Admin" <?= ($kullanici['yetki_seviyesi'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                        <option value="Yönetici" <?= ($kullanici['yetki_seviyesi'] == 'Yönetici') ? 'selected' : '' ?>>Yönetici</option>
                                        <option value="Editör" <?= ($kullanici['yetki_seviyesi'] == 'Editör') ? 'selected' : '' ?>>Editör</option>
                                        <option value="Bayi" <?= ($kullanici['yetki_seviyesi'] == 'Bayi') ? 'selected' : '' ?>>Bayi</option>
                                        <option value="Müşteri Temsilcisi" <?= ($kullanici['yetki_seviyesi'] == 'Müşteri Temsilcisi') ? 'selected' : '' ?>>Müşteri Temsilcisi</option>
                                    </select>
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
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="aktif_mi" name="aktif_mi" value="1" <?= $kullanici['aktif_mi'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aktif_mi">Aktif</label>
                                </div>
                                <?php if ($is_edit) : ?>
                                    <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- PROFİL RESMİ KARTI -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Profil Resmi</h5>
                            <?= getSingleImageUpload('profil_resmi', $kullanici['profil_resmi'] ?? '', 'Profil Resmi') ?>
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
                    <h5 class="modal-title">Kullanıcıyı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($kullanici['ad_soyad']) ?>" adlı kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
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
                const kullaniciId = '<?= $kullanici_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/kullanicilar/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [kullaniciId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Kullanıcı başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/kullanicilar/liste`;
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