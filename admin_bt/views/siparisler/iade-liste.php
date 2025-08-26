<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Sipariş yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl;

// AJAX endpointleri
$liste_ajax_url = "/{$yonetimurl}/siparisler/listele-iade";
$sil_ajax_url = "/{$yonetimurl}/siparisler/sil-iade"; // Henüz bu metot yok, ancak DataTables için ekliyoruz
$durum_guncelle_ajax_url = "/{$yonetimurl}/siparisler/iade-durum-guncelle";

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
                            <h1 class="mb-0 pb-0 display-4" id="title">İade & İptal Takibi</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/siparisler/liste">Siparişler</a></li>
                                    <li class="breadcrumb-item active">İade/İptal</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
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
                                <input class="form-control datatable-search" placeholder="İadelerde Ara..." data-datatable="#iadeDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <!-- Dropdown for status updates -->
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <div class="dropdown-as-select d-inline-block datatable-status-update" data-datatable="#iadeDatatable">
                                    <button class="btn p-0 dropdown-toggle disabled" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-offset="0,3">
                                        <span class="btn btn-foreground-alternate dropdown-toggle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Durum Değiştir">
                                            Durum Değiştir
                                        </span>
                                    </button>
                                    <div class="dropdown-menu shadow dropdown-menu-end">
                                        <button class="dropdown-item status-update" data-status="Onaylandı" type="button">Onaylandı</button>
                                        <button class="dropdown-item status-update" data-status="Reddedildi" type="button">Reddedildi</button>
                                        <button class="dropdown-item status-update" data-status="Tamamlandı" type="button">Tamamlandı</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Print Button Start -->
                            <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#iadeDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                <i data-acorn-icon="print"></i>
                            </button>
                            <!-- Print Button End -->
                            <!-- Export Dropdown Start -->
                            <div class="d-inline-block datatable-export" data-datatable="#iadeDatatable">
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
                            <!-- Length Start -->
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#iadeDatatable" data-childSelector="span">
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
                        <table id="iadeDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Sipariş Kodu</th>
                                    <th class="text-muted text-small text-uppercase">Müşteri</th>
                                    <th class="text-muted text-small text-uppercase">İade Tutarı</th>
                                    <th class="text-muted text-small text-uppercase">Durum</th>
                                    <th class="text-muted text-small text-uppercase">Talep Tarihi</th>
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
    const iadeConfig = {
        tableSelector: '#iadeDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>',
            updateStatus: '<?= $durum_guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID
                data: 'id',
                title: 'ID'
            },
            { // 1 - Sipariş Kodu
                data: 'siparis_kodu',
                title: 'Sipariş Kodu',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/siparisler/detay/${row.siparis_id}" class="list-item-heading body fw-bold">${data}</a>`;
                }
            },
            { // 2 - Müşteri
                data: 'musteri_adi',
                title: 'Müşteri',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">Misafir</span>';
                }
            },
            { // 3 - İade Tutarı
                data: 'iade_tutari',
                title: 'İade Tutarı',
                render: function(data, type, row) {
                    return `${parseFloat(data).toFixed(2)} ₺`;
                }
            },
            { // 4 - Durum
                data: 'durum',
                title: 'Durum',
                render: function(data, type, row) {
                    let badgeClass = 'primary';
                    switch (data) {
                        case 'Onaylandı':
                            badgeClass = 'success';
                            break;
                        case 'Reddedildi':
                            badgeClass = 'danger';
                            break;
                        case 'Tamamlandı':
                            badgeClass = 'info';
                            break;
                        default:
                            badgeClass = 'warning'; // Beklemede
                    }
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 5 - Talep Tarihi
                data: 'olusturma_tarihi',
                title: 'Talep Tarihi',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `${date.toLocaleDateString('tr-TR')} <small class="text-muted">${date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</small>`;
                }
            },
            { // 6 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <a href="/<?= $yonetimurl ?>/siparisler/iade-detay/${row.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start">
                            <i data-acorn-icon="eye"></i>
                            <span>Detayları Gör</span>
                        </a>
                    `;
                }
            }
        ],
        customOptions: {
            order: [
                [5, "desc"]
            ] // Varsayılan olarak tarihe göre en yeniden eskiye sırala
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            new ServerSideDataTable(iadeConfig);
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>