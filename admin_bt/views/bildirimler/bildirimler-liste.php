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

global $yonetimurl;

// AJAX endpointleri
$liste_ajax_url = "/{$yonetimurl}/bildirimler/listele";
$okundu_guncelle_ajax_url = "/{$yonetimurl}/bildirimler/okundu-olarak-isaretle";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Bildirimler</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item active">Bildirimler</li>
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
                                    <button class="dropdown-item status-update disabled" data-status="1" type="button">Okundu Olarak İşaretle</button>
                                </div>
                            </div>
                        </div>
                        <!-- Top Buttons End -->
                    </div>
                </div>
                <!-- Title and Top Buttons End -->

                <!-- Content Start -->
                <div class="data-table-rows slim">
                    <!-- Controls Start -->
                    <div class="row">
                        <!-- Search Start -->
                        <div class="col-sm-12 col-md-5 col-lg-3 col-xxl-2 mb-1">
                            <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
                                <input class="form-control datatable-search" placeholder="Bildirimlerde Ara..." data-datatable="#bildirimlerDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <!-- Print Button Start -->
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#bildirimlerDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <!-- Print Button End -->
                                <!-- Export Dropdown Start -->
                                <div class="d-inline-block datatable-export" data-datatable="#bildirimlerDatatable">
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
                                <!-- Export Dropdown End -->
                            </div>
                            <!-- Length Start -->
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#bildirimlerDatatable" data-childSelector="span">
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
                            <!-- Length End -->
                        </div>
                    </div>
                    <!-- Controls End -->

                    <!-- Table Start -->
                    <div class="data-table-responsive-wrapper">
                        <table id="bildirimlerDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Başlık</th>
                                    <th class="text-muted text-small text-uppercase">Öncelik</th>
                                    <th class="text-muted text-small text-uppercase">Okundu</th>
                                    <th class="text-muted text-small text-uppercase">Oluşturma Tarihi</th>
                                    <th class="empty">&nbsp;</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <!-- Table End -->
                </div>
                <!-- Content End -->
            </div>
        </div>
    </div>
</main>
<script>
    const bildirimlerConfig = {
        tableSelector: '#bildirimlerDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            updateStatus: '<?= $okundu_guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID ve Gizli Checkbox
                data: 'id',
                title: 'ID',
                render: function(data, type, row) {
                    const checkbox = row.okundu_mu == 0 ? `<input type="checkbox" class="form-check-input row-selector-checkbox" value="${data}">` : '';
                    return `<div class="d-none">${checkbox}</div>${data}`;
                }
            },
            { // 1 - Başlık
                data: 'baslik',
                title: 'Başlık',
                render: function(data, type, row) {
                    const link = row.eylem_url ? row.eylem_url : '#';
                    const is_read = row.okundu_mu == 1 ? 'fw-normal' : 'fw-bold';
                    return `<a href="${link}" class="list-item-heading body ${is_read}">${data}</a>`;
                }
            },
            { // 2 - Öncelik
                data: 'oncelik',
                title: 'Öncelik',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    switch (data) {
                        case 'acil':
                            badgeClass = 'danger';
                            break;
                        case 'yuksek':
                            badgeClass = 'warning';
                            break;
                        case 'normal':
                            badgeClass = 'primary';
                            break;
                        default:
                            badgeClass = 'secondary';
                    }
                    return `<span class="badge bg-outline-${badgeClass}">${data.toUpperCase()}</span>`;
                }
            },
            { // 3 - Okundu Mu
                data: 'okundu_mu',
                title: 'Okundu',
                render: function(data, type, row) {
                    return data == 1 ? `<i data-acorn-icon="check-square" class="text-success"></i>` : `<i data-acorn-icon="square" class="text-danger"></i>`;
                }
            },
            { // 4 - Oluşturma Tarihi
                data: 'olusturma_tarihi',
                title: 'Oluşturma Tarihi',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `${date.toLocaleDateString('tr-TR')} <small class="text-muted">${date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</small>`;
                }
            },
            { // 5 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-primary okundu-isaretle-btn" data-id="${row.id}">
                            Okundu İşaretle
                        </button>
                    `;
                }
            }
        ],
        customOptions: {
            order: [
                [4, "desc"]
            ],
            rowCallback: function(row, data) {
                if (data.okundu_mu == 0) {
                    $(row).addClass('fw-bold');
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            const dataTableInstance = new ServerSideDataTable(bildirimlerConfig);

            // Okundu olarak işaretleme butonu için dinleyici
            document.querySelector('#bildirimlerDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.okundu-isaretle-btn');
                if (button) {
                    event.preventDefault();
                    const id = button.dataset.id;
                    if (id) {
                        dataTableInstance._updateStatus([id], 1);
                    }
                }
            });
            // Toplu okundu olarak işaretleme
            document.querySelector('.status-update')?.addEventListener('click', function(event) {
                const status = event.target.dataset.status;
                const selectedIds = dataTableInstance._datatable.rows({
                    selected: true
                }).data().toArray().map(row => row.id);
                if (selectedIds.length > 0) {
                    dataTableInstance._updateStatus(selectedIds, status);
                }
            });
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>