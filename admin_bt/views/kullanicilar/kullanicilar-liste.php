<?php
// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Yetki kontrolü
if (!hasPermission('Admin')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl;

// AJAX endpointleri
$liste_ajax_url = "/{$yonetimurl}/kullanicilar/listele";
$sil_ajax_url = "/{$yonetimurl}/kullanicilar/sil";
$durum_guncelle_ajax_url = "/{$yonetimurl}/kullanicilar/durum-guncelle";
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
                            <h1 class="mb-0 pb-0 display-4" id="title">Panel Kullanıcıları</h1>
                            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                                <ul class="breadcrumb pt-0">
                                    <li class="breadcrumb-item"><a href="/<?= $yonetimurl ?>">Panel Anasayfa</a></li>
                                    <li class="breadcrumb-item active">Kullanıcılar</li>
                                </ul>
                            </nav>
                        </div>
                        <!-- Title End -->
                        <!-- Top Buttons Start -->
                        <div class="col-12 col-md-5 d-flex align-items-start justify-content-end">
                            <a href="/<?= $yonetimurl ?>/kullanicilar/ekle" class="btn btn-outline-primary btn-icon btn-icon-start w-100 w-md-auto">
                                <i data-acorn-icon="plus"></i>
                                <span>Yeni Kullanıcı Ekle</span>
                            </a>
                            <!-- Check Button Start -->
                            <div class="btn-group ms-1 check-all-container">
                                <div class="btn btn-outline-primary btn-custom-control p-0 ps-3 pe-2" id="datatableCheckAllButton">
                                    <span class="form-check float-end">
                                        <input type="checkbox" class="form-check-input" id="datatableCheckAll" />
                                    </span>
                                </div>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-offset="0,3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-submenu></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown dropstart dropdown-submenu">
                                        <button class="dropdown-item dropdown-toggle tag-datatable caret-absolute disabled" type="button">Durum Değiştir</button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item status-update" data-status="1" type="button">Aktif Yap</button>
                                            <button class="dropdown-item status-update" data-status="0" type="button">Pasif Yap</button>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <?php if (hasPermission('Admin')) : ?>
                                        <button class="dropdown-item delete-datatable disabled" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                    <?php endif; ?>
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
                                <input class="form-control datatable-search" placeholder="Kullanıcılarda Ara..." data-datatable="#kullanicilarDatatable" />
                                <span class="search-magnifier-icon"><i data-acorn-icon="search"></i></span>
                                <span class="search-delete-icon d-none"><i data-acorn-icon="close"></i></span>
                            </div>
                        </div>
                        <!-- Search End -->
                        <div class="col-sm-12 col-md-7 col-lg-9 col-xxl-10 text-end mb-1">
                            <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
                                <!-- Print Button Start -->
                                <button class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print" data-datatable="#kullanicilarDatatable" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-delay="0" title="Yazdır" type="button">
                                    <i data-acorn-icon="print"></i>
                                </button>
                                <!-- Print Button End -->
                                <!-- Export Dropdown Start -->
                                <div class="d-inline-block datatable-export" data-datatable="#kullanicilarDatatable">
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
                            <div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#kullanicilarDatatable" data-childSelector="span">
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
                        <table id="kullanicilarDatatable" class="data-table nowrap hover">
                            <thead>
                                <tr>
                                    <th class="text-muted text-small text-uppercase">ID</th>
                                    <th class="text-muted text-small text-uppercase">Profil</th>
                                    <th class="text-muted text-small text-uppercase">Ad Soyad</th>
                                    <th class="text-muted text-small text-uppercase">E-posta</th>
                                    <th class="text-muted text-small text-uppercase">Yetki</th>
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
                        <h5 class="modal-title">Seçili Kullanıcıları Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Seçili kullanıcıları kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
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
    const kullanicilarConfig = {
        tableSelector: '#kullanicilarDatatable',
        api: {
            read: '<?= $liste_ajax_url ?>',
            delete: '<?= $sil_ajax_url ?>',
            updateStatus: '<?= $durum_guncelle_ajax_url ?>'
        },
        columns: [{ // 0 - ID ve Gizli Checkbox
                data: 'id',
                title: 'ID',
                render: function(data, type, row) {
                    return `<div class="d-none"><input type="checkbox" class="form-check-input row-selector-checkbox" value="${data}"></div>${data}`;
                }
            },
            { // 1 - Profil
                data: 'profil_resmi',
                title: 'Profil',
                orderable: false,
                render: function(data, type, row) {
                    const imageUrl = data ? data : '/admin_bt/assets/img/profile/profile-1.webp';
                    return `<a href="/<?= $yonetimurl ?>/kullanicilar/duzenle/${row.id}" class="d-inline-block">
                                <img src="${imageUrl}" alt="${row.ad_soyad}" class="rounded-circle border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.onerror=null;this.src='/admin_bt/assets/img/profile/profile-1.webp';" />
                            </a>`;
                }
            },
            { // 2 - Ad Soyad
                data: 'ad_soyad',
                title: 'Ad Soyad',
                render: function(data, type, row) {
                    return `<a href="/<?= $yonetimurl ?>/kullanicilar/duzenle/${row.id}" class="list-item-heading body fw-bold">${data}</a>`;
                }
            },
            { // 3 - E-posta
                data: 'eposta',
                title: 'E-posta'
            },
            { // 4 - Yetki
                data: 'yetki_seviyesi',
                title: 'Yetki',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    switch (data) {
                        case 'Admin':
                            badgeClass = 'primary';
                            break;
                        case 'Yönetici':
                            badgeClass = 'info';
                            break;
                        case 'Editör':
                            badgeClass = 'warning';
                            break;
                        default:
                            badgeClass = 'secondary';
                    }
                    return `<span class="badge bg-outline-${badgeClass}">${data}</span>`;
                }
            },
            { // 5 - Durum
                data: 'aktif_mi',
                title: 'Durum',
                render: function(data, type, row) {
                    return data == 1 ? `<span class="badge bg-success">Aktif</span>` : `<span class="badge bg-danger">Pasif</span>`;
                }
            },
            { // 6 - İşlemler
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <a href="/<?= $yonetimurl ?>/kullanicilar/duzenle/${row.id}" class="btn btn-sm btn-outline-primary btn-icon btn-icon-start">
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
            ],
            columnDefs: [{
                targets: [0],
                visible: false,
                searchable: false
            }]
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ServerSideDataTable !== 'undefined') {
            const dataTableInstance = new ServerSideDataTable(kullanicilarConfig);
            // Durum güncelleme butonları için dinleyici
            document.querySelector('#datatableCheckAllButton')?.parentElement?.addEventListener('click', function(event) {
                const button = event.target.closest('.status-update');
                if (button) {
                    event.preventDefault();
                    const status = button.dataset.status;
                    const selectedIds = dataTableInstance._datatable.rows({
                        selected: true
                    }).data().toArray().map(row => row.id);
                    if (selectedIds.length > 0) {
                        dataTableInstance._updateStatus(selectedIds, status);
                    }
                }
            });
            // Tekil silme butonu için dinleyici (datalist içinde olmadığı için)
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