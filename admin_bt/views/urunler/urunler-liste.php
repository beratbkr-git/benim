<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Urun yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl;

// AJAX endpointleri
$urunler_liste_ajax_url = "/{$yonetimurl}/urunler/listele";
$urunler_sil_ajax_url = "/{$yonetimurl}/urunler/sil";
$urunler_durum_guncelle_ajax_url = "/{$yonetimurl}/urunler/ajax-guncelle";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Ürün Yönetimi</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/urunler/liste">Ürünler</a></li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                        <!-- Top Buttons Start -->
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <a href="/<?= $yonetimurl ?>/urunler/ekle" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                                <i data-acorn-icon="plus"></i>
                                <span>Yeni Ürün Ekle</span>
                            </a>
                            <!-- Check Button Start -->
                            <div class="btn-group ms-1 check-all-container">
                                <div class="btn btn-outline-primary btn-custom-control p-0 ps-3 pe-2" id="datatableCheckAllButton">
                                    <span class="form-check float-end">
                                        <input type="checkbox" class="form-check-input" id="datatableCheckAll" />
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-offset="0,3"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    data-submenu></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown dropstart dropdown-submenu">
                                        <button class="dropdown-item dropdown-toggle tag-datatable caret-absolute disabled" type="button">Durum Değiştir</button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item status-update" data-status="Aktif" type="button">Aktif</button>
                                            <button class="dropdown-item status-update" data-status="Pasif" type="button">Pasif</button>
                                            <button class="dropdown-item status-update" data-status="Taslak" type="button">Taslak</button>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <?php if (hasPermission('Yönetici')) { ?>
                                        <button class="dropdown-item disabled delete-datatable" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Check Button End -->
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
                                <input class="form-control datatable-search" placeholder="Ürünlerde Arayın..." data-datatable="#urunlerDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <!-- Print Button Start -->
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#urunlerDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <!-- Print Button End -->
                                <!-- Export Dropdown Start -->
                                <div class="d-inline-block datatable-export" data-datatable="#urunlerDatatable">
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
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#urunlerDatatable" data-childSelector="span">
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
                        <table id="urunlerDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Görsel</th>
                                    <th class="text-muted text-small text-uppercase">Ürün</th>
                                    <th class="text-muted text-small text-uppercase">Marka</th>
                                    <th class="text-muted text-small text-uppercase">Kategoriler</th>
                                    <th class="text-muted text-small text-uppercase">Ürün Tipi</th>
                                    <th class="text-muted text-small text-uppercase">Fiyat</th>
                                    <th class="text-muted text-small text-uppercase">Stok</th>
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

        <!-- Silme Onay Modalı -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seçili Ürünleri Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Seçili ürünleri kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
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
    const urunlerConfig = {
        tableSelector: '#urunlerDatatable',
        api: {
            read: '<?= $urunler_liste_ajax_url ?>',
            delete: '<?= $urunler_sil_ajax_url ?>',
            updateStatus: '<?= $urunler_durum_guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID ve Gizli Checkbox
                data: 'id',
                title: 'ID',
                render: function(data, type, row) {
                    return `<div class="d-none"><input type="checkbox" class="form-check-input row-selector-checkbox" value="${data}"></div>${data}`;
                }
            },
            { // 1 - Görsel
                data: 'ana_gorsel',
                title: 'Görsel',
                orderable: false,
                render: function(data, type, row) {
                    const imageUrl = data ? data : '/admin_bt/assets/img/placeholder-page.webp';
                    return `<div class="sw-12  rounded-xl d-flex justify-content-center align-items-center bg-light"><img src="${imageUrl}" class="img-fluid" alt="görsel" onerror="this.onerror=null;this.src='/admin_bt/assets/img/placeholder-page.webp';"></div>`;
                }
            },
            { // 2 - Ürün Adı ve Kodu
                data: 'urun_adi',
                title: 'Ürün',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/urunler/duzenle/${row.id}" class="list-item-heading body">${data}</a><div class="text-muted text-small">${row.urun_kodu || ''}</div>`;
                }
            },
            { // 3 - Marka
                data: 'marka_adi',
                title: 'Marka',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">Belirtilmemiş</span>';
                }
            },
            { // 4 - Kategoriler
                data: 'kategoriler',
                title: 'Kategoriler',
                orderable: false,
                render: function(data, type, row) {
                    if (!data) return '<span class="text-muted">Yok</span>';
                    const kategoriler = data.split(',');
                    let html = '';
                    kategoriler.slice(0, 2).forEach(kat => {
                        html += `<span class="badge bg-outline-primary me-1">${kat}</span>`;
                    });
                    if (kategoriler.length > 2) {
                        html += `<span class="badge bg-outline-secondary">+${kategoriler.length - 2}</span>`;
                    }
                    return html;
                }
            },
            { // 5 - Ürün Tipi
                data: 'varyant_var_mi',
                title: 'Ürün Tipi',
                render: function(data, type, row) {
                    return data == 1 ? '<span class="badge bg-outline-secondary">Varyantlı</span>' : '<span class="badge bg-outline-primary">Basit</span>';
                }
            },
            { // 6 - Fiyat
                data: 'satis_fiyati',
                title: 'Fiyat',
                render: function(data, type, row) {
                    if (row.varyant_var_mi == 1) {
                        return row.min_varyant_fiyati ? `${parseFloat(row.min_varyant_fiyati).toFixed(2)} ₺'den` : '<span class="text-muted">Varyantlarda</span>';
                    } else {
                        return data ? `${parseFloat(data).toFixed(2)} ₺` : '<span class="text-muted">Belirtilmemiş</span>';
                    }
                }
            },
            { // 7 - Stok
                data: 'stok_miktari',
                title: 'Stok',
                render: function(data, type, row) {
                    return row.varyant_var_mi == 1 ? '<span class="text-muted">Varyantlarda</span>' : (data !== null ? data : 'Stok Yok');
                }
            },
            { // 8 - Durum
                data: 'durum',
                title: 'Durum',
                render: function(data, type, row) {
                    let badgeClass = 'primary';
                    if (data === 'Pasif') badgeClass = 'secondary';
                    if (data === 'Taslak') badgeClass = 'warning';
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 9 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="/<?= $yonetimurl ?>/urunler/duzenle/${row.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start">
                                <i data-acorn-icon="edit-square"></i>
                                <span>Detaya Git</span>
                            </a>
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a target="_blank" class="dropdown-item" href="http://<?= $_SERVER['SERVER_NAME'] ?>/urun/${row.slug}">Önizle</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/<?= $yonetimurl ?>/urunler/kopya-olustur/${row.id}">Kopyala</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item delete-single-item" href="#" data-id="${row.id}" data-name="${row.urun_adi}">Sil</a></li>
                            </ul>
                        </div>
                    `;
                }
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

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            const dataTableInstance = new ServerSideDataTable(urunlerConfig);


        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>