<?php
if (!defined('YONETIM_DIR')) {
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle-ozellik' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Özellik Düzenle' : 'Yeni Özellik Ekle';
$form_action = "/{$yonetimurl}/varyantlar/" . ($is_edit ? "duzenle-ozellik-kontrol" : "ekle-ozellik-kontrol");
$ozellik_id = $is_edit ? (int)$p4 : 0;
$ozellik = ['id' => 0, 'ozellik_adi' => '', 'sira' => 0];

if ($is_edit) {
    $ozellik_data = $db->fetch("SELECT * FROM bt_varyant_ozellikleri WHERE id = :id", ['id' => $ozellik_id]);
    if ($ozellik_data) $ozellik = array_merge($ozellik, $ozellik_data);
    else {
        header("Location: /{$yonetimurl}/varyantlar/liste");
        exit();
    }
}
?>
<main>
    <div class="container">
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30"><a href="/<?= $yonetimurl ?>/varyantlar/liste" class="muted-link pb-1 d-inline-block breadcrumb-back"><i data-acorn-icon="chevron-left" data-acorn-size="13"></i><span class="text-small align-middle">Özellikler</span></a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end"><button type="submit" form="ozellikForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto"><i data-acorn-icon="save"></i><span>Kaydet</span></button></div>
            </div>
        </div>
        <form id="ozellikForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $ozellik_id ?>">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label" for="ozellik_adi">Özellik Adı</label><input type="text" class="form-control" id="ozellik_adi" name="ozellik_adi" value="<?= htmlspecialchars($ozellik['ozellik_adi']) ?>" required></div>
                    <div class="mb-0"><label class="form-label" for="sira">Sıralama</label><input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($ozellik['sira']) ?>"></div>
                </div>
            </div>
        </form>
    </div>
</main>