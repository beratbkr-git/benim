<?php
if (!defined('YONETIM_DIR')) {
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle-deger' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Değer Düzenle' : 'Yeni Değer Ekle';
$form_action = "/{$yonetimurl}/varyantlar/" . ($is_edit ? "duzenle-deger-kontrol" : "ekle-deger-kontrol");

$deger_id = $is_edit ? (int)$p4 : 0;
$ozellik_id = $is_edit ? 0 : (int)($p4 ?? 0);

$deger = ['id' => 0, 'ozellik_id' => $ozellik_id, 'deger' => '', 'sira' => 0];
$ozellik = null;

if ($is_edit) {
    $deger_data = $db->fetch("SELECT * FROM bt_varyant_degerleri WHERE id = :id", ['id' => $deger_id]);
    if ($deger_data) {
        $deger = array_merge($deger, $deger_data);
        $ozellik_id = $deger['ozellik_id'];
    } else {
        header("Location: /{$yonetimurl}/varyantlar/liste");
        exit();
    }
}

if ($ozellik_id > 0) {
    $ozellik = $db->fetch("SELECT ozellik_adi FROM bt_varyant_ozellikleri WHERE id = :id", ['id' => $ozellik_id]);
}

if (!$ozellik) {
    header("Location: /{$yonetimurl}/varyantlar/liste");
    exit();
}
?>
<main>
    <div class="container">
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-40">
                        <a href="/<?= $yonetimurl ?>/varyantlar/degerler-liste/<?= $ozellik_id ?>" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">'<?= htmlspecialchars($ozellik['ozellik_adi']) ?>' Değerleri</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end"><button type="submit" form="degerForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto"><i data-acorn-icon="save"></i><span>Kaydet</span></button></div>
            </div>
        </div>
        <form id="degerForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $deger_id ?>">
            <input type="hidden" name="ozellik_id" value="<?= $ozellik_id ?>">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label" for="deger">Değer</label><input type="text" class="form-control" id="deger" name="deger" value="<?= htmlspecialchars($deger['deger']) ?>" required></div>
                    <div class="mb-0"><label class="form-label" for="sira">Sıralama</label><input type="number" class="form-control" id="sira" name="sira" value="<?= htmlspecialchars($deger['sira']) ?>"></div>
                </div>
            </div>
        </form>
    </div>
</main>