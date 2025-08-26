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

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'satin-alma-duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'Satın Alma Siparişi Düzenle' : 'Yeni Satın Alma Siparişi';
$form_action = "/{$yonetimurl}/envanter/" . ($is_edit ? "satin-alma-duzenle-kontrol" : "satin-alma-ekle-kontrol");
$siparis_id = $is_edit ? (int)$p4 : 0;

// Varsayılan sipariş verisi
$siparis = [
    'id' => 0,
    'siparis_no' => 'SA-' . strtoupper(uniqid()),
    'tedarikci_id' => null,
    'toplam_tutar' => 0.00,
    'durum' => 'Taslak',
    'siparis_tarihi' => date('Y-m-d'),
    'beklenen_teslimat_tarihi' => null,
    'notlar' => '',
    'detaylar' => [],
];

// Düzenleme modunda verileri çek
if ($is_edit) {
    $siparis_data = $db->fetch("SELECT * FROM bt_satin_alma_siparisleri WHERE id = :id", ['id' => $siparis_id]);
    if ($siparis_data) {
        $siparis = array_merge($siparis, $siparis_data);
        $siparis_detaylari = $db->fetchAll(
            "SELECT T1.*, T2.urun_adi, T3.deger AS varyant_adi
             FROM bt_satin_alma_siparis_detaylari T1
             LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
             LEFT JOIN bt_urun_varyant_degerleri T4 ON T1.varyant_id = T4.varyant_id
             LEFT JOIN bt_varyant_degerleri T3 ON T4.deger_id = T3.id
             WHERE T1.satin_alma_siparis_id = :id",
            ['id' => $siparis_id]
        );
        $siparis['detaylar'] = $siparis_detaylari;
    } else {
        $_SESSION["hata"] = "Satın alma siparişi bulunamadı.";
        header("Location: /{$yonetimurl}/envanter/satin-alma-liste");
        exit();
    }
}

// Form için gerekli diğer verileri çek
$tedarikciler = $db->fetchAll("SELECT id, firma_adi FROM bt_tedarikciler WHERE durum = 'Aktif'");
$urunler = $db->fetchAll("SELECT id, urun_adi FROM bt_urunler WHERE durum = 'Aktif' ORDER BY urun_adi ASC");
?>
<main>
    <div class="container">
        <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/envanter/satin-alma-liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">Satın Alma Siparişleri</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="satinAlmaForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="satinAlmaForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $siparis_id ?>">
            <div class="row">
                <div class="col-xl-8">
                    <!-- SİPARİŞ BİLGİLERİ KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Sipariş Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="siparis_no">Sipariş Numarası</label>
                                    <input type="text" class="form-control" id="siparis_no" name="siparis_no" value="<?= htmlspecialchars($siparis['siparis_no']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="tedarikci_id">Tedarikçi</label>
                                    <select class="form-select select2Basic" id="tedarikci_id" name="tedarikci_id" required>
                                        <option value="">Tedarikçi Seçiniz</option>
                                        <?php foreach ($tedarikciler as $tedarikci) : ?>
                                            <option value="<?= $tedarikci['id'] ?>" <?= ($siparis['tedarikci_id'] == $tedarikci['id']) ? 'selected' : '' ?>><?= htmlspecialchars($tedarikci['firma_adi']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="beklenen_teslimat_tarihi">Beklenen Teslimat Tarihi</label>
                                    <input type="text" class="form-control" id="beklenen_teslimat_tarihi" name="beklenen_teslimat_tarihi" value="<?= htmlspecialchars($siparis['beklenen_teslimat_tarihi'] ? date('d.m.Y', strtotime($siparis['beklenen_teslimat_tarihi'])) : '') ?>" placeholder="gg.aa.yyyy">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="notlar">Notlar</label>
                                    <textarea name="notlar" class="form-control" rows="3"><?= htmlspecialchars($siparis['notlar']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SİPARİŞ DETAYLARI KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Sipariş Detayları</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="siparisDetaylariTablosu">
                                        <thead>
                                            <tr>
                                                <th>Ürün</th>
                                                <th>Varyant</th>
                                                <th>Miktar</th>
                                                <th>Birim Fiyatı</th>
                                                <th>Toplam</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($siparis['detaylar'] as $detay): ?>
                                                <tr data-detay-id="<?= $detay['id'] ?>">
                                                    <td><?= htmlspecialchars($detay['urun_adi']) ?></td>
                                                    <td><?= htmlspecialchars($detay['varyant_adi'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($detay['siparis_miktari']) ?></td>
                                                    <td><?= number_format($detay['birim_fiyat'], 2) ?></td>
                                                    <td><?= number_format($detay['toplam_fiyat'], 2) ?></td>
                                                    <td><button type="button" class="btn btn-sm btn-danger detay-sil-btn">Sil</button></td>
                                                    <input type="hidden" name="urun_id[]" value="<?= $detay['urun_id'] ?>">
                                                    <input type="hidden" name="varyant_id[]" value="<?= $detay['varyant_id'] ?>">
                                                    <input type="hidden" name="siparis_miktari[]" value="<?= $detay['siparis_miktari'] ?>">
                                                    <input type="hidden" name="birim_fiyat[]" value="<?= $detay['birim_fiyat'] ?>">
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" id="detayEkleBtn" class="btn btn-outline-secondary btn-icon btn-icon-start">
                                        <i data-acorn-icon="plus"></i>
                                        <span>Yeni Ürün Ekle</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <!-- DURUM VE TARİHLER KARTI -->
                    <div class="mb-5">
                        <h2 class="small-title">Durum & Tarihler</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Taslak" <?= ($siparis['durum'] == 'Taslak') ? 'selected' : '' ?>>Taslak</option>
                                        <option value="Gönderildi" <?= ($siparis['durum'] == 'Gönderildi') ? 'selected' : '' ?>>Gönderildi</option>
                                        <option value="Onaylandı" <?= ($siparis['durum'] == 'Onaylandı') ? 'selected' : '' ?>>Onaylandı</option>
                                        <option value="Kısmi Teslim" <?= ($siparis['durum'] == 'Kısmi Teslim') ? 'selected' : '' ?>>Kısmi Teslim</option>
                                        <option value="Tamamlandı" <?= ($siparis['durum'] == 'Tamamlandı') ? 'selected' : '' ?>>Tamamlandı</option>
                                        <option value="İptal" <?= ($siparis['durum'] == 'İptal') ? 'selected' : '' ?>>İptal</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="siparis_tarihi">Sipariş Tarihi</label>
                                    <input type="text" class="form-control" id="siparis_tarihi" name="siparis_tarihi" value="<?= htmlspecialchars(date('d.m.Y', strtotime($siparis['siparis_tarihi']))) ?>" placeholder="gg.aa.yyyy">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="toplam_tutar">Toplam Tutar</label>
                                    <input type="number" class="form-control" id="toplam_tutar" name="toplam_tutar" value="<?= htmlspecialchars($siparis['toplam_tutar']) ?>" readonly>
                                </div>
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

<!-- Ürün Ekleme Modalı -->
<div class="modal fade" id="urunEkleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ürün Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="modal-urun-select">Ürün Seçiniz</label>
                    <select class="form-select" id="modal-urun-select">
                        <option value="">Ürün Seçiniz...</option>
                        <?php foreach ($urunler as $urun): ?>
                            <option value="<?= $urun['id'] ?>"><?= htmlspecialchars($urun['urun_adi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="modal-varyant-alani" style="display: none;">
                    <label class="form-label" for="modal-varyant-select">Varyant Seçiniz</label>
                    <select class="form-select" id="modal-varyant-select">
                        <option value="">Varyant Seçiniz...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="modal-miktar">Miktar</label>
                    <input type="number" class="form-control" id="modal-miktar" value="1" min="1">
                </div>
                <div class="mb-0">
                    <label class="form-label" for="modal-birim-fiyat">Birim Fiyatı</label>
                    <input type="number" class="form-control" id="modal-birim-fiyat" step="0.01" value="0.00">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" id="urunEkleModalBtn" class="btn btn-primary">Ekle</button>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<?php if ($is_edit) : ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Satın Alma Siparişini Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($siparis['siparis_no']) ?>" numaralı satın alma siparişini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yonetimUrl = '<?= $yonetimurl ?>';
        const siparisId = '<?= $siparis_id ?>';

        // Modal'ı ve butonları seç
        const urunEkleModalEl = document.getElementById('urunEkleModal');
        const urunEkleModal = new bootstrap.Modal(urunEkleModalEl);
        const detayEkleBtn = document.getElementById('detayEkleBtn');
        const urunEkleModalBtn = document.getElementById('urunEkleModalBtn');

        // Ürün tablosunu seç
        const siparisDetaylariTablosu = document.getElementById('siparisDetaylariTablosu');

        // Form alanlarını seç
        const toplamTutarInput = document.getElementById('toplam_tutar');
        const tedarikciSelect = $('#tedarikci_id');

        // Sayfa yüklendiğinde Select2 ve Datepicker'ı başlat
        tedarikciSelect.select2();
        $('#durum').select2();
        $('#siparis_tarihi').datepicker({
            format: 'dd.mm.yyyy',
            language: 'tr',
            autoclose: true
        });
        $('#beklenen_teslimat_tarihi').datepicker({
            format: 'dd.mm.yyyy',
            language: 'tr',
            autoclose: true
        });

        // Ürün ekleme modalındaki Select2'leri başlat
        const modalUrunSelect = $('#modal-urun-select');
        const modalVaryantSelect = $('#modal-varyant-select');
        modalUrunSelect.select2();
        modalVaryantSelect.select2();

        // Detay ekle butonuna tıklayınca modalı aç
        detayEkleBtn.addEventListener('click', function() {
            urunEkleModal.show();
        });

        // Ürün seçimi değiştiğinde varyantları dinamik olarak yükle
        modalUrunSelect.on('change', function() {
            const urunId = $(this).val();
            const varyantAlani = document.getElementById('modal-varyant-alani');
            if (urunId) {
                // Varyantları çek
                fetch(`/${yonetimUrl}/envanter/urun-varyantlari-getir/${urunId}`)
                    .then(response => response.json())
                    .then(data => {
                        modalVaryantSelect.empty();
                        if (data.success && data.data.length > 0) {
                            data.data.forEach(varyant => {
                                modalVaryantSelect.append(new Option(varyant.varyant_bilgisi, varyant.id));
                            });
                            varyantAlani.style.display = 'block';
                            modalVaryantSelect.trigger('change');
                        } else {
                            varyantAlani.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Varyant yükleme hatası:', error));
            } else {
                varyantAlani.style.display = 'none';
            }
        });

        // Ürün ekleme modalındaki Ekle butonuna tıklayınca tabloya satır ekle
        urunEkleModalBtn.addEventListener('click', function() {
            const urunId = modalUrunSelect.val();
            const urunAdi = modalUrunSelect.find('option:selected').text();
            const varyantId = modalVaryantSelect.val() || null;
            const varyantAdi = varyantId ? modalVaryantSelect.find('option:selected').text() : '-';
            const miktar = document.getElementById('modal-miktar').value;
            const birimFiyat = document.getElementById('modal-birim-fiyat').value;

            if (urunId && miktar > 0 && birimFiyat >= 0) {
                const yeniSatir = `
                    <tr>
                        <td>${urunAdi}</td>
                        <td>${varyantAdi}</td>
                        <td>${miktar}</td>
                        <td>${parseFloat(birimFiyat).toFixed(2)}</td>
                        <td>${(miktar * birimFiyat).toFixed(2)}</td>
                        <td><button type="button" class="btn btn-sm btn-danger detay-sil-btn">Sil</button></td>
                        <input type="hidden" name="urun_id[]" value="${urunId}">
                        <input type="hidden" name="varyant_id[]" value="${varyantId}">
                        <input type="hidden" name="siparis_miktari[]" value="${miktar}">
                        <input type="hidden" name="birim_fiyat[]" value="${birimFiyat}">
                    </tr>
                `;
                document.querySelector('#siparisDetaylariTablosu tbody').insertAdjacentHTML('beforeend', yeniSatir);

                toplamTutariGuncelle();
                urunEkleModal.hide();
            } else {
                beratbabatoast('danger', 'Lütfen geçerli ürün, miktar ve fiyat giriniz.');
            }
        });

        // Tablodaki sil butonlarına dinleyici ekle
        siparisDetaylariTablosu.addEventListener('click', function(e) {
            if (e.target.classList.contains('detay-sil-btn')) {
                e.target.closest('tr').remove();
                toplamTutariGuncelle();
            }
        });

        // Toplam tutarı dinamik olarak hesapla
        function toplamTutariGuncelle() {
            let toplam = 0;
            siparisDetaylariTablosu.querySelectorAll('tbody tr').forEach(row => {
                const miktar = row.querySelector('input[name="siparis_miktari[]"]').value;
                const birimFiyat = row.querySelector('input[name="birim_fiyat[]"]').value;
                toplam += miktar * birimFiyat;
            });
            toplamTutarInput.value = toplam.toFixed(2);
        }

        // Silme butonu için AJAX
        <?php if ($is_edit) : ?>
            document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
                this.disabled = true;
                fetch(`/${yonetimUrl}/envanter/sil-satin-alma`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [siparisId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Satın alma siparişi başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/envanter/satin-alma-liste`;
                            }, 1500);
                        } else {
                            beratbabatoast("danger", "Silme işlemi başarısız: " + data.message);
                            this.disabled = false;
                        }
                    });
            });
        <?php endif; ?>
    });
</script>