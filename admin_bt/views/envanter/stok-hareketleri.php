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
$liste_ajax_url = "/{$yonetimurl}/envanter/listele-stok-hareketleri";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Stok Hareketleri</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/envanter/stok-hareketleri">Envanter & Stok</a></li>
                                    <li class="breadcrumb-item active">Stok Hareketleri</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                        <!-- Top Buttons Start -->
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <!-- Diğer butonlar buraya gelebilir, örneğin "Stok Girişi Yap" -->
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
                                <input class="form-control datatable-search" placeholder="Hareketlerde Ara..." data-datatable="#stokHareketleriDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <!-- Print Button Start -->
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#stokHareketleriDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <!-- Print Button End -->
                                <!-- Export Dropdown Start -->
                                <div class="d-inline-block datatable-export" data-datatable="#stokHareketleriDatatable">
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
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#stokHareketleriDatatable" data-childSelector="span">
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
                        <table id="stokHareketleriDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Ürün</th>
                                    <th class="text-muted text-small text-uppercase">Hareket Tipi</th>
                                    <th class="text-muted text-small text-uppercase">Miktar</th>
                                    <th class="text-muted text-small text-uppercase">Önceki Stok</th>
                                    <th class="text-muted text-small text-uppercase">Yeni Stok</th>
                                    <th class="text-muted text-small text-uppercase">Tarih</th>
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
    const stokHareketleriConfig = {
        tableSelector: '#stokHareketleriDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            // Diğer API endpoint'leri buraya eklenebilir
        },
        columns: [{ // 0 - ID
                data: 'id',
                title: 'ID'
            },
            { // 1 - Ürün
                data: 'urun_adi',
                title: 'Ürün',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/urunler/duzenle/${row.urun_id}" class="list-item-heading body fw-bold">${data}</a>`;
                }
            },
            { // 2 - Hareket Tipi
                data: 'hareket_tipi',
                title: 'Hareket Tipi',
                render: function(data, type, row) {
                    let badgeClass = 'primary';
                    switch (data) {
                        case 'Giriş':
                            badgeClass = 'success';
                            break;
                        case 'Çıkış':
                            badgeClass = 'danger';
                            break;
                        case 'İade':
                            badgeClass = 'warning';
                            break;
                        default:
                            badgeClass = 'info';
                    }
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 3 - Miktar
                data: 'miktar',
                title: 'Miktar'
            },
            { // 4 - Önceki Stok
                data: 'onceki_stok',
                title: 'Önceki Stok'
            },
            { // 5 - Yeni Stok
                data: 'yeni_stok',
                title: 'Yeni Stok'
            },
            { // 6 - Tarih
                data: 'olusturma_tarihi',
                title: 'Tarih',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `${date.toLocaleDateString('tr-TR')} <small class="text-muted">${date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</small>`;
                }
            },
            { // 7 - İşlemler (Bu tabloda işlem butonu olmayabilir)
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function() {
                    return ''; // İşlem butonu yok
                }
            }
        ],
        customOptions: {
            order: [
                [6, "desc"]
            ] // Varsayılan olarak tarihe göre en yeniden eskiye sırala
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            new ServerSideDataTable(stokHareketleriConfig);
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>