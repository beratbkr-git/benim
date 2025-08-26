<?php
if (!defined('YONETIM_DIR')) {
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p4, $db;
$ozellik_id = isset($p4) ? (int)$p4 : 0;
if ($ozellik_id === 0) {
    header("Location: /{$yonetimurl}/varyantlar/liste");
    exit();
}

$ozellik = $db->fetch("SELECT ozellik_adi FROM bt_varyant_ozellikleri WHERE id = :id", ['id' => $ozellik_id]);
if (!$ozellik) {
    header("Location: /{$yonetimurl}/varyantlar/liste");
    exit();
}

$liste_ajax_url = "/{$yonetimurl}/varyantlar/listele-deger/{$ozellik_id}";
$sil_ajax_url = "/{$yonetimurl}/varyantlar/sil-deger";
?>
<main>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="page-title-container">
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <h1 class="mb-0 pb-0 display-4" id="title">'<?= htmlspecialchars($ozellik['ozellik_adi']) ?>' Değerleri</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/varyantlar/liste">Özellikler</a></li>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($ozellik['ozellik_adi']) ?></li>
                                </ul>
                            </nav>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <a href="/<?= $yonetimurl ?>/varyantlar/ekle-deger/<?= $ozellik_id ?>" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                                <i data-acorn-icon="plus"></i><span>Yeni Değer Ekle</span>
                            </a>
                            <div class="btn-group ms-1 check-all-container">
                                <div class="btn btn-outline-primary btn-custom-control p-0 ps-3 pe-2" id="datatableCheckAllButton"><span class="form-check float-end"><input type="checkbox" class="form-check-input" id="datatableCheckAll" /></span></div>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-offset="0,3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php if (hasPermission('Yönetici')) : ?><button class="dropdown-item delete-datatable disabled" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data-table-rows slim">
                    <div class="data-table-responsive-wrapper">
                        <table id="degerlerDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Değer</th>
                                    <th class="text-muted text-small text-uppercase">Sıra</th>
                                    <th class="empty">&nbsp;</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Seçili Değerleri Sil</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">Seçili değerleri kalıcı olarak silmek istediğinizden emin misiniz?</div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button><button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    const config = {
        tableSelector: '#degerlerDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>'
        },
        columns: [{
                data: 'id',
                title: 'ID',
                render: (d, t, r) => `<div class="d-none"><input type="checkbox" class="form-check-input row-selector-checkbox" value="${d}"></div>${d}`
            },
            {
                data: 'deger',
                title: 'Değer',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/varyantlar/duzenle-deger/${r.id}" class="list-item-heading body">${d}</a>`
            },
            {
                data: 'sira',
                title: 'Sıra'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/varyantlar/duzenle-deger/${r.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start"><i data-acorn-icon="edit-square"></i><span>Detaya Git</span></a>`
            }
        ],
        customOptions: {
            columnDefs: [{
                targets: [0],
                visible: false,
                searchable: false
            }]
        }
    };
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ServerSideDataTable !== 'undefined') new ServerSideDataTable(config);
    });
</script>