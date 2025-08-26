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
global $yonetimurl, $db, $p4;
$segment_id = $p4;
$segment_data = $db->fetch("SELECT * FROM bt_musteri_segmentleri WHERE id = :id", ['id' => $segment_id]);
if ($segment_data) {
    $segment = array_merge($segment, $segment_data);
} else {
    $_SESSION["hata"] = "Segment bulunamadı.";
    header("Location: /{$yonetimurl}/musteri-segmentleri/liste");
    exit();
}

// AJAX endpointleri
$liste_ajax_url = "/{$yonetimurl}/musteri-segmentleri/segment-musterileri-liste/{$segment['id']}";
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/musteri-segmentleri/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Müşteri Segmentleri</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= htmlspecialchars($segment['segment_adi']) ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <!-- SEGMENT BİLGİLERİ KARTI -->
                <h2 class="small-title">Segment Özeti</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <p class="mb-1"><strong>Açıklama:</strong> <?= htmlspecialchars($segment['aciklama'] ?? '-') ?></p>
                        <p class="mb-1"><strong>Durum:</strong>
                            <?php if ($segment['durum'] === 'Aktif'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-0"><strong>Müşteri Sayısı:</strong> <?= htmlspecialchars($segment['musteri_sayisi']) ?></p>
                    </div>
                </div>

                <!-- SEGMENT KRİTERLERİ KARTI -->
                <h2 class="small-title">Segment Kriterleri</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $kriterler = json_decode($segment['kriterler'], true);
                            if (!empty($kriterler) && is_array($kriterler)):
                                foreach ($kriterler as $kriter):
                                    $operator_map = [
                                        'equal' => '=',
                                        'not_equal' => '!=',
                                        'greater_than' => '>',
                                        'less_than' => '<'
                                    ];
                                    $operator = $operator_map[$kriter['operator']] ?? '=';
                                    $kriter_adi = ucwords(str_replace('_', ' ', $kriter['field']));
                            ?>
                                    <div class="col-12">
                                        <span class="badge bg-outline-primary"><?= htmlspecialchars($kriter_adi) ?></span>
                                        <span class="text-muted"><?= htmlspecialchars($operator) ?></span>
                                        <span class="badge bg-outline-secondary"><?= htmlspecialchars($kriter['value']) ?></span>
                                    </div>
                                <?php
                                endforeach;
                            else:
                                ?>
                                <div class="col-12">
                                    <p class="text-muted">Bu segment için herhangi bir kriter belirlenmemiş.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- İŞLEMLER KARTI -->
                <h2 class="small-title">İşlemler</h2>
                <div class="card mb-5">
                    <div class="card-body">
                        <a href="/<?= $yonetimurl ?>/musteri-segmentleri/duzenle/<?= $segment['id'] ?>" class="btn btn-outline-primary w-100 mb-2">
                            Düzenle
                        </a>
                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEGMENTTEKİ MÜŞTERİLER TABLOSU -->
        <h2 class="small-title mt-5">Segmentteki Müşteriler</h2>
        <div class="data-table-rows slim">
            <div class="data-table-responsive-wrapper">
                <table id="segmentMusterileriDatatable" class="data-table nowrap hover">
                    <thead>
                        <tr>
                            <th class="text-muted text-small text-uppercase">ID</th>
                            <th class="text-muted text-small text-uppercase">Ad Soyad</th>
                            <th class="text-muted text-small text-uppercase">E-posta</th>
                            <th class="text-muted text-small text-uppercase">Toplam Harcama</th>
                            <th class="text-muted text-small text-uppercase">Son Giriş</th>
                            <th class="empty">&nbsp;</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Segmenti Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                "<?= htmlspecialchars($segment['segment_adi']) ?>" adlı segmenti kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yonetimUrl = '<?= $yonetimurl ?>';
        const segmentId = '<?= $segment['id'] ?>';

        // Müşteri listesi için DataTables yapılandırması
        const musteriListesiConfig = {
            tableSelector: '#segmentMusterileriDatatable',
            api: {
                read: `/${yonetimUrl}/musteri-segmentleri/segment-musterileri-liste/${segmentId}`
            },
            columns: [{
                    data: 'id',
                    title: 'ID'
                },
                {
                    data: 'ad_soyad',
                    title: 'Ad Soyad',
                    render: (d, t, r) => `<a href="/<?= $yonetimurl ?>/musteriler/detay/${r.id}">${d}</a>`
                },
                {
                    data: 'eposta',
                    title: 'E-posta'
                },
                {
                    data: 'toplam_harcama',
                    title: 'Toplam Harcama',
                    render: (d) => `₺${parseFloat(d).toFixed(2)}`
                },
                {
                    data: 'son_giris_tarihi',
                    title: 'Son Giriş',
                    render: (d) => d ? new Date(d).toLocaleDateString('tr-TR') : '-'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: (d, t, r) => `
                        <a href="/<?= $yonetimurl ?>/musteriler/detay/${r.id}" class="btn btn-sm btn-outline-primary">
                            Detay
                        </a>
                    `
                }
            ],
            customOptions: {
                order: [
                    [0, "desc"]
                ]
            }
        };

        if (typeof ServerSideDataTable !== 'undefined') {
            new ServerSideDataTable(musteriListesiConfig);
        } else {
            console.error('ServerSideDataTable sınıfı bulunamadı.');
        }

        // Segment silme butonu için AJAX isteği
        document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
            this.disabled = true;
            fetch(`/${yonetimUrl}/musteri-segmentleri/sil`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: [segmentId]
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        beratbabatoast("success", "Segment başarıyla silindi. Yönlendiriliyorsunuz...");
                        setTimeout(() => {
                            window.location.href = `/${yonetimurl}/musteri-segmentleri/liste`;
                        }, 1500);
                    } else {
                        beratbabatoast("danger", "Silme işlemi başarısız: " + data.message);
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    beratbabatoast("danger", "Silme işlemi sırasında bir sunucu hatası oluştu.");
                    this.disabled = false;
                });
        });
    });
</script>