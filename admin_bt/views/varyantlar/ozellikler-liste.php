<?php
if (!defined('YONETIM_DIR')) {
    exit();
}
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}
global $yonetimurl;
$liste_ajax_url = "/{$yonetimurl}/varyantlar/listele-ozellik";
$sil_ajax_url = "/{$yonetimurl}/varyantlar/sil-ozellik";
?>
<main>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="page-title-container">
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <h1 class="mb-0 pb-0 display-4" id="title">Varyant Özellikleri</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/varyantlar/liste">Varyantlar</a></li>
                                </ul>
                            </nav>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <a href="/<?= $yonetimurl ?>/varyantlar/ekle-ozellik" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                                <i data-acorn-icon="plus"></i>
                                <span>Yeni Özellik Ekle</span>
                            </a>
                            <div class="btn-group ms-1 check-all-container">
                                <div class="btn btn-outline-primary btn-custom-control p-0 ps-3 pe-2" id="datatableCheckAllButton">
                                    <span class="form-check float-end">
                                        <input type="checkbox" class="form-check-input" id="datatableCheckAll" />
                                    </span>
                                </div>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-offset="0,3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php if (hasPermission('Yönetici')) : ?>
                                        <button class="dropdown-item delete-datatable disabled" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data-table-rows slim">
                    <div class="row">
                        <div class="col-sm-12 col-md-5 col-lg-3 col-xxl-2 mb-1">
                            <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
                                <input class="form-control datatable-search" placeholder="Özelliklerde Ara..." data-datatable="#ozelliklerDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="data-table-responsive-wrapper">
                        <table id="ozelliklerDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Özellik Adı</th>
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
                                <h5 class="modal-title">Seçili Özellikleri Sil</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">Seçili özellikleri kalıcı olarak silmek istediğinizden emin misiniz?</div>
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
        tableSelector: '#ozelliklerDatatable',
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
                data: 'ozellik_adi',
                title: 'Özellik Adı',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/varyantlar/duzenle-ozellik/${r.id}" class="list-item-heading body">${d}</a>`
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
                render: (d, t, r) => `
                <a href="/<?= $yonetimurl ?>/varyantlar/degerler-liste/${r.id}" class="btn btn-sm btn-outline-secondary btn-icon btn-icon-start me-1">
                    <i data-acorn-icon="list"></i><span>Değerleri Yönet</span>
                </a>
                <a href="/<?= $yonetimurl ?>/varyantlar/duzenle-ozellik/${r.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start">
                    <i data-acorn-icon="edit-square"></i><span>Detaya Git</span>
                </a>`
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