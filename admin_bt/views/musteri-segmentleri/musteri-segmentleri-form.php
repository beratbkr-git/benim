<?php
// bu dosyanin disaridan dogrudan erisilmesini engeller.
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
// Müşteri segmenti yönetimi için yetki kontrolü
if (!hasPermission('Editör')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Segment Düzenle' : 'Yeni Segment Ekle';
$form_action = "/{$yonetimurl}/musteri-segmentleri/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$segment_id = $is_edit ? (int)$p4 : 0;

// Varsayılan segment verisi
$segment = [
    'id' => 0,
    'segment_adi' => '',
    'aciklama' => '',
    'durum' => 'Aktif',
    'kriterler' => '[]',
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $segment_data = $db->fetch("SELECT * FROM bt_musteri_segmentleri WHERE id = :id", ['id' => $segment_id]);
    if ($segment_data) {
        $segment = array_merge($segment, $segment_data);
    } else {
        $_SESSION["hata"] = "Segment bulunamadı.";
        header("Location: /{$yonetimurl}/musteri-segmentleri/liste");
        exit();
    }
}

// Müşteri segmenti kriterleri için kullanılacak değerleri çekelim (bu veriler dinamik olabilir)
$urunler_listesi = $db->fetchAll("SELECT id, urun_adi FROM bt_urunler WHERE durum = 'Aktif' ORDER BY urun_adi ASC");
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
                            <span class="text-small align-middle">Segmentler</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="segmentForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="segmentForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $segment_id ?>">
            <input type="hidden" name="kriterler" id="kriterlerInput" value='<?= htmlspecialchars($segment['kriterler'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>'>
            <div class="row">
                <div class="col-xl-8">
                    <!-- SEGMENT BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Segment Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="segment_adi">Segment Adı</label>
                                    <input type="text" class="form-control" id="segment_adi" name="segment_adi" value="<?= htmlspecialchars($segment['segment_adi']) ?>" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Açıklama</label>
                                    <textarea name="aciklama" class="form-control" rows="5"><?= htmlspecialchars($segment['aciklama']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KRİTERLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Segment Kriterleri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div id="kriterlerAlani">
                                    <!-- Kriter satırları buraya dinamik olarak eklenecek -->
                                </div>
                                <button type="button" id="kriterEkleBtn" class="btn btn-outline-primary btn-icon btn-icon-start mt-3">
                                    <i data-acorn-icon="plus"></i>
                                    <span>Kural Ekle</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- YAYINLAMA KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Yayınlama</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($segment['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($segment['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                    </select>
                                </div>
                                <?php if ($is_edit) : ?>
                                    <a href="/<?= $yonetimurl ?>/musteri-segmentleri/segment-detay/<?= $segment_id ?>" class="btn btn-outline-primary w-100 mt-3">Detaylarını Gör</a>
                                <?php endif; ?>
                                <?php if ($is_edit) : ?>
                                    <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Silme Onay Modalı -->
<?php if ($is_edit) : ?>
    <div class=" modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
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
            document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
                this.disabled = true;
                const segmentId = '<?= $segment_id ?>';
                const yonetimUrl = '<?= $yonetimurl ?>';

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
<?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kriterlerInput = document.getElementById('kriterlerInput');
        const kriterlerAlani = document.getElementById('kriterlerAlani');
        const kriterEkleBtn = document.getElementById('kriterEkleBtn');
        const segmentForm = document.getElementById('segmentForm');

        // Kullanılabilir kriterler ve operatörler
        const urunlerListesi = <?= json_encode($urunler_listesi) ?>;

        const kriterler = [{
                key: 'toplam_harcama',
                label: 'Toplam Harcama',
                type: 'number'
            },
            {
                key: 'siparis_sayisi',
                label: 'Toplam Sipariş Sayısı',
                type: 'number'
            },
            {
                key: 'son_giris_tarihi',
                label: 'Son Giriş Tarihi',
                type: 'date'
            },
            {
                key: 'cinsiyet',
                label: 'Cinsiyet',
                type: 'enum',
                values: ['Erkek', 'Kadın', 'Belirtmek İstemiyorum']
            },
            {
                key: 'durum',
                label: 'Durum',
                type: 'enum',
                values: ['Aktif', 'Pasif']
            },
            {
                key: 'urun_alimi',
                label: 'Ürün Alan',
                type: 'urunler',
                values: urunlerListesi
            }
        ];

        const operatorler = {
            'number': [{
                key: 'greater_than',
                label: '>'
            }, {
                key: 'less_than',
                label: '<'
            }, {
                key: 'equal',
                label: '='
            }],
            'date': [{
                key: 'greater_than',
                label: 'Sonrasına'
            }, {
                key: 'less_than',
                label: 'Öncesine'
            }, {
                key: 'equal',
                label: 'Eşit'
            }],
            'enum': [{
                key: 'equal',
                label: 'Eşit'
            }, {
                key: 'not_equal',
                label: 'Eşit Değil'
            }],
            'urunler': [{
                key: 'equal',
                label: 'Eşit'
            }, {
                key: 'not_equal',
                label: 'Eşit Değil'
            }]
        };

        // Kural satırı oluşturan fonksiyon
        function kuralSatiriOlustur(kriterData) {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-center kriter-satiri';

            const selectedKriter = kriterler.find(k => k.key === kriterData.field) || kriterler[0];
            const selectedOperator = kriterData.operator;
            const selectedValue = kriterData.value;

            const kriterHtml = kriterler.map(k => `<option value="${k.key}" ${selectedKriter.key === k.key ? 'selected' : ''}>${k.label}</option>`).join('');

            const operatorHtml = operatorler[selectedKriter.type].map(o => `<option value="${o.key}" ${selectedOperator === o.key ? 'selected' : ''}>${o.label}</option>`).join('');

            let degerHtml;
            if (selectedKriter.type === 'enum') {
                degerHtml = `<select class="select2Basic form-select kural-deger-input" data-placeholder="Değer Seç">${selectedKriter.values.map(v => `<option value="${v}" ${selectedValue === v ? 'selected' : ''}>${v}</option>`).join('')}</select>`;
            } else if (selectedKriter.type === 'date') {
                degerHtml = `<input type="text" class="form-control kural-deger-input date-picker" value="${selectedValue}">`;
            } else if (selectedKriter.type === 'urunler') {
                degerHtml = `<select class="select2Basic form-select kural-deger-input" data-placeholder="Ürün Seç">${urunlerListesi.map(u => `<option value="${u.id}" ${selectedValue == u.id ? 'selected' : ''}>${u.urun_adi}</option>`).join('')}</select>`;
            } else {
                degerHtml = `<input type="number" class="form-control kural-deger-input" value="${selectedValue}">`;
            }

            row.innerHTML = `
                <div class="col-md-5">
                    <select class="select2Basic form-select kural-kriter-select">${kriterHtml}</select>
                </div>
                <div class="col-md-2">
                    <select class="select2Basic form-select kural-operator-select">${operatorHtml}</select>
                </div>
                <div class="col-md-4">
                    ${degerHtml}
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-icon btn-icon-only btn-danger kural-sil-btn"><i data-acorn-icon="bin"></i></button>
                </div>
            `;

            kriterlerAlani.appendChild(row);

            $(row.querySelector('.kural-kriter-select')).select2();
            $(row.querySelector('.kural-operator-select')).select2({
                minimumResultsForSearch: Infinity
            });
            if (selectedKriter.type === 'enum' || selectedKriter.type === 'urunler') {
                $(row.querySelector('.kural-deger-input')).select2();
            } else if (selectedKriter.type === 'date') {
                $(row.querySelector('.date-picker')).datepicker({
                    format: 'dd.mm.yyyy',
                    language: 'tr',
                    autoclose: true
                });
            }
        }

        kriterEkleBtn.addEventListener('click', () => {
            const defaultKriterData = {
                field: kriterler[0].key,
                operator: operatorler[kriterler[0].type][0].key,
                value: ''
            };
            kuralSatiriOlustur(defaultKriterData);
            if (typeof AcornIcons !== 'undefined') new AcornIcons().replace();
        });

        kriterlerAlani.addEventListener('change', function(e) {
            const selectElement = e.target.closest('select');
            if (!selectElement) return;

            const satir = selectElement.closest('.kriter-satiri');
            if (selectElement.classList.contains('kural-kriter-select')) {
                const secilenKriterKey = selectElement.value;
                const secilenKriter = kriterler.find(k => k.key === secilenKriterKey);

                const operatorSelect = satir.querySelector('.kural-operator-select');
                const degerAlani = satir.querySelector('.col-md-4');

                // Operatörleri güncelle
                const yeniOperatorHtml = operatorler[secilenKriter.type].map(o => `<option value="${o.key}">${o.label}</option>`).join('');
                $(operatorSelect).html(yeniOperatorHtml).trigger('change');

                // Değer alanını güncelle
                let yeniDegerHtml;
                if (secilenKriter.type === 'enum') {
                    yeniDegerHtml = `<select class="form-select kural-deger-input" data-placeholder="Değer Seç">${secilenKriter.values.map(v => `<option value="${v}">${v}</option>`).join('')}</select>`;
                } else if (secilenKriter.type === 'date') {
                    yeniDegerHtml = `<input type="text" class="form-control kural-deger-input date-picker">`;
                } else if (secilenKriter.type === 'urunler') {
                    yeniDegerHtml = `<select class="form-select kural-deger-input" data-placeholder="Ürün Seç">${urunlerListesi.map(u => `<option value="${u.id}">${u.urun_adi}</option>`).join('')}</select>`;
                } else {
                    yeniDegerHtml = `<input type="number" class="form-control kural-deger-input">`;
                }
                degerAlani.innerHTML = yeniDegerHtml;

                // Yeni eklenen alanlar için Select2 ve Datepicker'ı başlat
                if (secilenKriter.type === 'enum' || secilenKriter.type === 'urunler') {
                    $(degerAlani.querySelector('.kural-deger-input')).select2();
                } else if (secilenKriter.type === 'date') {
                    $(degerAlani.querySelector('.date-picker')).datepicker({
                        format: 'dd.mm.yyyy',
                        language: 'tr',
                        autoclose: true
                    });
                }
            }
            segmentVerileriniHazirla();
        });

        kriterlerAlani.addEventListener('click', function(e) {
            if (e.target.closest('.kural-sil-btn')) {
                const row = e.target.closest('.kriter-satiri');
                $(row).find('select').each(function() {
                    $(this).select2('destroy');
                });
                row.remove();
                segmentVerileriniHazirla();
            }
        });

        segmentForm.addEventListener('submit', function() {
            segmentVerileriniHazirla();
        });

        function segmentVerileriniHazirla() {
            const kriterler = [];
            document.querySelectorAll('.kriter-satiri').forEach(row => {
                const field = row.querySelector('.kural-kriter-select').value;
                const operator = row.querySelector('.kural-operator-select').value;
                let value = row.querySelector('.kural-deger-input').value;

                const kriterTipi = (kriterler.find(k => k.key === field) || {}).type;
                if (kriterTipi === 'date') {
                    const parts = value.split('.');
                    if (parts.length === 3) {
                        value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                }
                kriterler.push({
                    field: field,
                    operator: operator,
                    value: value
                });
            });
            kriterlerInput.value = JSON.stringify(kriterler);
        }

        function mevcutKriterleriYukle() {
            try {
                const mevcutKriterler = JSON.parse(kriterlerInput.value);
                if (Array.isArray(mevcutKriterler)) {
                    mevcutKriterler.forEach(kriterData => {
                        kuralSatiriOlustur(kriterData);
                    });
                }
            } catch (e) {
                console.error("Mevcut kriterler JSON parse edilemedi:", e);
                kriterEkleBtn.click();
            }
        }

        mevcutKriterleriYukle();
    });
</script>