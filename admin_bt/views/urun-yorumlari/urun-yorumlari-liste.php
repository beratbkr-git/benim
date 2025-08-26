<?php
// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl;

// AJAX endpointleri
$liste_ajax_url = "/{$yonetimurl}/urun-yorumlari/listele";
$onayla_ajax_url = "/{$yonetimurl}/urun-yorumlari/onayla";
$reddet_ajax_url = "/{$yonetimurl}/urun-yorumlari/reddet";
$sil_ajax_url = "/{$yonetimurl}/urun-yorumlari/sil";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Ürün Yorumları</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item active">Ürün Yorumları</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                        <!-- Top Buttons Start -->
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <div class="btn-group ms-1 check-all-container">
                                <div class="btn btn-outline-primary btn-custom-control p-0 ps-3 pe-2" id="datatableCheckAllButton">
                                    <span class="form-check float-end">
                                        <input type="checkbox" class="form-check-input" id="datatableCheckAll" />
                                    </span>
                                </div>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-offset="0,3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-submenu></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item status-update disabled" data-status="Onaylandı" type="button">Onayla</button>
                                    <button class="dropdown-item status-update disabled" data-status="Reddedildi" type="button">Reddet</button>
                                    <div class="dropdown-divider"></div>
                                    <?php if (hasPermission('Yönetici')) : ?>
                                        <button class="dropdown-item delete-datatable disabled" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Top Buttons End -->
                    </div>
                </div>
                <!-- Title and Top Buttons End -->

                <!-- Content Start -->
                <div class="data-table-rows slim">
                    <div class="row">
                        <div class="col-sm-12 col-md-5 col-lg-3 col-xxl-2 mb-1">
                            <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
                                <input class="form-control datatable-search" placeholder="Yorumlarda Ara..." data-datatable="#yorumlarDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#yorumlarDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <div class="d-inline-block datatable-export" data-datatable="#yorumlarDatatable">
                                    <button class="btn p-0" data-bs-toggle="dropdown" type="button" data-bs-offset="0,3">
                                        <span class="btn btn-icon btn-icon-only btn-foreground-alternate shadow dropdown" data-bs-delay="0" data-bs-placement="top" data-bs-toggle="tooltip" title="Dışa Aktar">
                                            <i data-acorn-icon="download"></i>
                                        </span>
                                    </button>
                                    <div class="dropdown-menu shadow dropdown-menu-end">
                                        <button class="dropdown-item export-copy" type="button">Kopyala</button>
                                        <button class="dropdown-item export-excel" type="button">Excel</button>
                                        <button class="dropdown-item export-cvs" type="button">CSV</button>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#yorumlarDatatable" data-childSelector="span">
                                <button class="btn p-0 shadow" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-offset="0,3">
                                    <span class="btn btn-foreground-alternate dropdown-toggle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Gösterilen Adet">
                                        10 Adet
                                    </span>
                                </button>
                                <div class="dropdown-menu shadow dropdown-menu-end">
                                    <a class="dropdown-item active" href="#">10 Adet</a>
                                    <a class="dropdown-item" href="#">20 Adet</a>
                                    <a class="dropdown-item" href="#">50 Adet</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="data-table-responsive-wrapper">
                        <table id="yorumlarDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Ürün</th>
                                    <th class="text-muted text-small text-uppercase">Müşteri</th>
                                    <th class="text-muted text-small text-uppercase">Puan</th>
                                    <th class="text-muted text-small text-uppercase">Yorum</th>
                                    <th class="text-muted text-small text-uppercase">Durum</th>
                                    <th class="text-muted text-small text-uppercase">Tarih</th>
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
                        <h5 class="modal-title">Seçili Yorumları Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Seçili yorumları kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
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
    const yorumlarConfig = {
        tableSelector: '#yorumlarDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>',
            updateStatus: {
                onayla: '<?= $onayla_ajax_url ?>',
                reddet: '<?= $reddet_ajax_url ?>'
            }
        },
        columns: [{
                data: 'id',
                title: 'ID'
            },
            {
                data: 'urun_adi',
                title: 'Ürün',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/urunler/duzenle/${r.urun_id}">${d}</a>`
            },
            {
                data: 'ad_soyad',
                title: 'Müşteri',
                render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/musteriler/detay/${r.musteri_id}">${d}</a>`
            },
            {
                data: 'puan',
                title: 'Puan',
                render: (d) => `<div class="rating-star" data-initial-rating="${d}" data-readonly="true"></div>`
            },
            {
                data: 'yorum',
                title: 'Yorum',
                render: (d) => `<div class="text-truncate" style="max-width: 250px;">${d || '-'}</div>`
            },
            {
                data: 'durum',
                title: 'Durum',
                render: (d) => {
                    let badgeClass = '';
                    if (d === 'Onaylandı') badgeClass = 'success';
                    else if (d === 'Reddedildi') badgeClass = 'danger';
                    else badgeClass = 'warning'; // Beklemede
                    return `<span class="badge bg-outline-${badgeClass}">${d}</span>`;
                }
            },
            {
                data: 'olusturma_tarihi',
                title: 'Tarih',
                render: (d) => {
                    const date = new Date(d);
                    return `${date.toLocaleDateString('tr-TR')} <small class="text-muted">${date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</small>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (d, t, r) => {
                    let actions = '';
                    if (r.durum === 'Beklemede') {
                        actions += `<button class="btn btn-sm btn-outline-success onayla-btn" data-id="${r.id}">Onayla</button>`;
                        actions += `<button class="btn btn-sm btn-outline-danger reddet-btn ms-1" data-id="${r.id}">Reddet</button>`;
                    } else {
                        actions += `<a href="/<?= $yonetimurl ?>/urun-yorumlari/yanitla/${r.id}" class="btn btn-sm btn-outline-secondary">Yanıtla</a>`;
                    }
                    return `
                        <div class="btn-group">
                            ${actions}
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item delete-single-item" href="#" data-id="${r.id}">Sil</a></li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        customOptions: {
            order: [
                [6, "desc"]
            ],
            rowCallback: function(row, data) {
                if (data.durum === 'Beklemede') {
                    $(row).addClass('bg-warning-light');
                }
                // Her satır yüklendiğinde rating eklentisini başlat
                $(row).find('.rating-star').barrating({
                    theme: 'bootstrap-stars',
                    readonly: true,
                    showValues: false,
                    showSelectedRating: false
                });
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            const dataTableInstance = new ServerSideDataTable(yorumlarConfig);

            // Onayla butonu için dinleyici
            document.querySelector('#yorumlarDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.onayla-btn');
                if (button) {
                    event.preventDefault();
                    const id = button.dataset.id;
                    if (id) {
                        dataTableInstance._updateStatus([id], 'Onaylandı', {
                            api: {
                                updateStatus: '<?= $onayla_ajax_url ?>'
                            }
                        });
                    }
                }
            });

            // Reddet butonu için dinleyici
            document.querySelector('#yorumlarDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.reddet-btn');
                if (button) {
                    event.preventDefault();
                    const id = button.dataset.id;
                    if (id) {
                        dataTableInstance._updateStatus([id], 'Reddedildi', {
                            api: {
                                updateStatus: '<?= $reddet_ajax_url ?>'
                            }
                        });
                    }
                }
            });

            // Toplu durum güncelleme için dinleyici
            document.querySelector('.dropdown-menu')?.addEventListener('click', function(event) {
                const button = event.target.closest('.status-update');
                if (button) {
                    event.preventDefault();
                    const status = button.dataset.status;
                    const selectedIds = dataTableInstance._datatable.rows({
                        selected: true
                    }).data().toArray().map(row => row.id);
                    if (selectedIds.length > 0) {
                        let updateUrl = status === 'Onaylandı' ? '<?= $onayla_ajax_url ?>' : '<?= $reddet_ajax_url ?>';
                        dataTableInstance._updateStatus(selectedIds, status, {
                            api: {
                                updateStatus: updateUrl
                            }
                        });
                    }
                }
            });

            // Tekil silme butonu için dinleyici
            document.querySelector('#yorumlarDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.delete-single-item');
                if (button) {
                    event.preventDefault();
                    const id = button.dataset.id;
                    if (id) {
                        if (confirm('Bu yorumu silmek istediğinizden emin misiniz?')) {
                            dataTableInstance._deleteRows([id]);
                        }
                    }
                }
            });

            // Toplu silme butonu için dinleyici
            document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
                this.disabled = true;
                const selectedIds = dataTableInstance._datatable.rows({
                    selected: true
                }).data().toArray().map(row => row.id);
                if (selectedIds.length > 0) {
                    dataTableInstance._deleteRows(selectedIds);
                }
            });

        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>