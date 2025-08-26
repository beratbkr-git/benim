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

global $yonetimurl;

// AJAX endpointleri
$yontemler_liste_ajax_url = "/{$yonetimurl}/kargolar/listele-kargo-yontemleri";
$yontem_sil_ajax_url = "/{$yonetimurl}/kargolar/kargo-yontemi-sil";
$yontem_durum_guncelle_ajax_url = "/{$yonetimurl}/kargolar-yontemi-durum-guncelle";
?>
<main>
    <div class="container">
        <div class="row">
            <div class="col">
                <!-- Title and Top Buttons Start -->
                <div class="page-title-container">
                    <div class="row">
                        <!-- Title Start -->
                        <div class="col-12 col-md-7">
                            <h1 class="mb-0 pb-0 display-4" id="title">Kargo Yöntemleri</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item active">Kargo Yöntemleri Liste</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                    </div>
                </div>
                <ul class="nav nav-tabs nav-tabs-line nav-tabs-line-active" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemleri-liste" class="nav-link <?= $active_tab === 'kargo-yontemler-liste' ? 'active' : '' ?>" id="yontemler-tab" role="tab" aria-selected="<?= $active_tab === 'kargo-yontemler-liste' ? 'true' : 'false' ?>">
                            Kargo Yöntemleri
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="/<?= $yonetimurl ?>/kargolar/kargo-firmalari-liste" class="nav-link <?= $active_tab === 'kargo-firmalar-liste' ? 'active' : '' ?>" id="firmalar-tab" role="tab" aria-selected="<?= $active_tab === 'kargo-firmalar-liste' ? 'true' : 'false' ?>">
                            Kargo Firmaları
                        </a>
                    </li>
                </ul>
                <div class="data-table-rows slim">
                    <div class="row">
                        <div class="col-12 text-end mb-3">
                            <a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemi-ekle" class="btn btn-outline-primary btn-icon btn-icon-start">
                                <i data-acorn-icon="plus"></i>
                                <span>Yeni Yöntem Ekle</span>
                            </a>
                        </div>
                    </div>
                    <div class="data-table-responsive-wrapper">
                        <table id="kargoYontemleriDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Yöntem Adı</th>
                                    <th class="text-muted text-small text-uppercase">Hesaplama Tipi</th>
                                    <th class="text-muted text-small text-uppercase">Temel Ücret</th>
                                    <th class="text-muted text-small text-uppercase">Durum</th>
                                    <th class="empty">&nbsp;</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <!-- Silme Onay Modalı -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seçili Öğeleri Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Seçili öğeleri kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const kargoYontemleriConfig = {
        tableSelector: '#kargoYontemleriDatatable',
        api: {
            read: '<?= $yontemler_liste_ajax_url ?>',
            delete: '<?= $yontem_sil_ajax_url ?>',
            updateStatus: '<?= $yontem_durum_guncelle_ajax_url ?>'
        },
        columns: [{
                data: 'id',
                title: 'ID'
            },
            {
                data: 'yontem_adi',
                title: 'Yöntem Adı',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemi-duzenle/${r.id}">${d}</a>`
            },
            {
                data: 'hesaplama_tipi',
                title: 'Hesaplama Tipi'
            },
            {
                data: 'temel_ucret',
                title: 'Temel Ücret'
            },
            {
                data: 'durum',
                title: 'Durum',
                render: (d) => d === 'Aktif' ? `<span class="badge bg-success">${d}</span>` : `<span class="badge bg-danger">${d}</span>`
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemi-duzenle/${r.id}" class="btn btn-sm btn-outline-primary">Detay</a>`
            }
        ]
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            new ServerSideDataTable(kargoYontemleriConfig);
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>