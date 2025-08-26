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
$liste_ajax_url = "/{$yonetimurl}/envanter/listele-tedarikciler";
$sil_ajax_url = "/{$yonetimurl}/envanter/sil-tedarikciler";
$durum_guncelle_ajax_url = "/{$yonetimurl}/envanter/guncelle-tedarikciler";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Tedarikçiler</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/envanter/tedarikciler-liste">Envanter & Stok</a></li>
                                    <li class="breadcrumb-item active">Tedarikçiler</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                        <!-- Top Buttons Start -->
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <a href="/<?= $yonetimurl ?>/envanter/tedarikciler-ekle" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                                <i data-acorn-icon="plus"></i>
                                <span>Yeni Tedarikçi Ekle</span>
                            </a>
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
                                <input class="form-control datatable-search" placeholder="Tedarikçilerde Ara..." data-datatable="#tedarikcilerDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <!-- Print Button Start -->
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#tedarikcilerDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <!-- Print Button End -->
                                <!-- Export Dropdown Start -->
                                <div class="d-inline-block datatable-export" data-datatable="#tedarikcilerDatatable">
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
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#tedarikcilerDatatable" data-childSelector="span">
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
                        <table id="tedarikcilerDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Firma Adı</th>
                                    <th class="text-muted text-small text-uppercase">İletişim Kişisi</th>
                                    <th class="text-muted text-small text-uppercase">Telefon</th>
                                    <th class="text-muted text-small text-uppercase">E-posta</th>
                                    <th class="text-muted text-small text-uppercase">Durum</th>
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
    const tedarikcilerConfig = {
        tableSelector: '#tedarikcilerDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>',
            updateStatus: '<?= $durum_guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID
                data: 'id',
                title: 'ID'
            },
            { // 1 - Firma Adı
                data: 'firma_adi',
                title: 'Firma Adı',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/envanter/tedarikciler-duzenle/${row.id}" class="list-item-heading body fw-bold">${data}</a>`;
                }
            },
            { // 2 - İletişim Kişisi
                data: 'iletisim_kisi',
                title: 'İletişim Kişisi'
            },
            { // 3 - Telefon
                data: 'telefon',
                title: 'Telefon',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { // 4 - E-posta
                data: 'eposta',
                title: 'E-posta',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { // 5 - Durum
                data: 'durum',
                title: 'Durum',
                render: function(data, type, row) {
                    let badgeClass = 'primary';
                    if (data === 'Pasif') badgeClass = 'secondary';
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 6 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <a href="/<?= $yonetimurl ?>/envanter/tedarikciler-duzenle/${row.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start">
                            <i data-acorn-icon="edit-square"></i>
                            <span>Detay</span>
                        </a>
                    `;
                }
            }
        ],
        customOptions: {
            order: [
                [0, "desc"]
            ]
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            new ServerSideDataTable(tedarikcilerConfig);
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>