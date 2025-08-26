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
$page_title = $is_edit ? 'Menü Düzenle' : 'Yeni Menü Ekle';
$form_action = "/{$yonetimurl}/menuler/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$menu_id = $is_edit ? (int)$p4 : 0;

// Varsayılan menü verisi
$menu = [
    'id' => 0,
    'menu_adi' => '',
    'url' => '',
    'menuturu' => 'url',
    'ust_menu_id' => null,
    'sira' => 0,
    'durum' => 'Aktif',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $menu_data = $db->fetch("SELECT * FROM bt_menuler WHERE id = :id", ['id' => $menu_id]);
    if ($menu_data) {
        $menu = array_merge($menu, $menu_data);
    } else {
        $_SESSION["hata"] = "Menü bulunamadı.";
        header("Location: /{$yonetimurl}/menuler/liste");
        exit();
    }
}

// Üst menü seçeneklerini çek
$ust_menuler = $db->fetchAll("SELECT id, menu_adi FROM bt_menuler WHERE ust_menu_id IS NULL AND id != :id ORDER BY sira ASC", ['id' => $menu_id]);
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/menuler/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Menüler</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="menuForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="menuForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $menu_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- GENEL BİLGİLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Menü Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="menu_adi">Menü Adı</label>
                                    <input type="text" class="form-control" id="menu_adi" name="menu_adi" value="<?= htmlspecialchars($menu['menu_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="url">URL</label>
                                    <input type="text" class="form-control" id="url" name="url" value="<?= htmlspecialchars($menu['url']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="menuturu">Menü Türü</label>
                                    <select class="form-select select2Basic" id="menuturu" name="menuturu">
                                        <option value="url" <?= ($menu['menuturu'] == 'url') ? 'selected' : '' ?>>URL</option>
                                        <option value="dropdown" <?= ($menu['menuturu'] == 'dropdown') ? 'selected' : '' ?>>Dropdown</option>
                                        <option value="megamenu" <?= ($menu['menuturu'] == 'megamenu') ? 'selected' : '' ?>>Mega Menü</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="ust_menu_id">Üst Menü</label>
                                    <select class="form-select select2Basic" id="ust_menu_id" name="ust_menu_id">
                                        <option value="">Üst menü yok</option>
                                        <?php foreach ($ust_menuler as $ust_menu) : ?>
                                            <option value="<?= $ust_menu['id'] ?>" <?= ($menu['ust_menu_id'] == $ust_menu['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ust_menu['menu_adi']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="sira">Sıralama</label>
                                    <input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($menu['sira']) ?>">
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
                                        <option value="Aktif" <?= ($menu['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($menu['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
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
                    <h5 class="modal-title">Menüyü Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($menu['menu_adi']) ?>" adlı menüyü kalıcı olarak silmek istediğinizden emin misiniz? Alt menüler de silinecektir. Bu işlem geri alınamaz.
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
                const menuId = '<?= $menu_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

                fetch(`/${yonetimUrl}/menuler/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [menuId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Menü başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/menuler/liste`;
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