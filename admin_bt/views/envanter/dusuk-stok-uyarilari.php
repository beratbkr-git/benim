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
$liste_ajax_url = "/{$yonetimurl}/envanter/listele-dusuk-stok-uyarilari";
$guncelle_ajax_url = "/{$yonetimurl}/envanter/guncelle-dusuk-stok-uyarisi";
$sil_ajax_url = "/{$yonetimurl}/envanter/sil-dusuk-stok-uyarisi";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Düşük Stok Uyarıları</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>/envanter/stok-hareketleri">Envanter & Stok</a></li>
                                    <li class="breadcrumb-item active">Düşük Stok Uyarıları</li>
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
                                    <button class="dropdown-item status-update disabled" type="button" data-status="Çözüldü">Çözüldü Olarak İşaretle</button>
                                    <button class="dropdown-item status-update disabled" type="button" data-status="Göz Ardı Edildi">Göz Ardı Et</button>
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
                                <input class="form-control datatable-search" placeholder="Uyarılarda Ara..." data-datatable="#uyariDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#uyariDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <div class="d-inline-block datatable-export" data-datatable="#uyariDatatable">
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
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#uyariDatatable" data-childSelector="span">
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
                        <table id="uyariDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Ürün</th>
                                    <th class="text-muted text-small text-uppercase">Varyant</th>
                                    <th class="text-muted text-small text-uppercase">Mevcut Stok</th>
                                    <th class="text-muted text-small text-uppercase">Minimum Stok</th>
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
                        <h5 class="modal-title">Seçili Uyarıları Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Seçili düşük stok uyarılarını kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
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
    const uyariConfig = {
        tableSelector: '#uyariDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>',
            updateStatus: '<?= $guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID ve Gizli Checkbox
                data: 'id',
                title: 'ID',
                render: function(data, type, row) {
                    return `<div class="d-none"><input type="checkbox" class="form-check-input row-selector-checkbox" value="${data}"></div>${data}`;
                }
            },
            { // 1 - Ürün Adı
                data: 'urun_adi',
                title: 'Ürün',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/urunler/duzenle/${row.urun_id}" class="list-item-heading body fw-bold">${data}</a>`;
                }
            },
            { // 2 - Varyant
                data: 'varyant_adi',
                title: 'Varyant',
                render: function(data, type, row) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            { // 3 - Mevcut Stok
                data: 'mevcut_stok',
                title: 'Mevcut Stok',
                render: function(data, type, row) {
                    return data;
                }
            },
            { // 4 - Minimum Stok
                data: 'minimum_stok',
                title: 'Minimum Stok'
            },
            { // 5 - Durum
                data: 'durum',
                title: 'Durum',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    if (data === 'Aktif') badgeClass = 'danger';
                    if (data === 'Çözüldü') badgeClass = 'success';
                    if (data === 'Göz Ardı Edildi') badgeClass = 'info';
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 6 - Tarih
                data: 'olusturma_tarihi',
                title: 'Tarih',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `${date.toLocaleDateString('tr-TR')} <small class="text-muted">${date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</small>`;
                }
            },
            { // 7 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item status-update" href="#" data-status="Çözüldü" data-id="${row.id}">Çözüldü Olarak İşaretle</a></li>
                                <li><a class="dropdown-item status-update" href="#" data-status="Göz Ardı Edildi" data-id="${row.id}">Göz Ardı Et</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item delete-single-item" href="#" data-id="${row.id}">Sil</a></li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        customOptions: {
            order: [
                [6, "desc"]
            ]
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            const dataTableInstance = new ServerSideDataTable(uyariConfig);
            // Durum güncelleme butonları için dinleyici
            document.querySelector('#uyariDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.status-update');
                if (button) {
                    event.preventDefault();
                    const status = button.dataset.status;
                    const id = button.dataset.id;
                    if (id) {
                        dataTableInstance._updateStatus([id], status);
                    }
                }
            });

            // Tekil silme butonu için dinleyici
            document.querySelector('#uyariDatatable')?.addEventListener('click', function(event) {
                const button = event.target.closest('.delete-single-item');
                if (button) {
                    event.preventDefault();
                    const id = button.dataset.id;
                    if (id) {
                        if (confirm('Bu uyarıyı silmek istediğinizden emin misiniz?')) {
                            dataTableInstance._deleteRows([id]);
                        }
                    }
                }
            });

        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }
    });
</script>