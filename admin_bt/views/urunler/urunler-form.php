    <?php
    // bu dosyanin disaridan dogrudan erisilmesini engeller
    if (!defined('YONETIM_DIR')) {
        header("Location: /404");
        exit();
    }
    // Urun yönetimi için yetki kontrolü
    if (!hasPermission('Editör')) {
        header("Location: /{$yonetimurl}");
        exit();
    }

    global $yonetimurl, $p3, $p4, $db;

    $is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
    $page_title = $is_edit ? 'Ürün Düzenle' : 'Yeni Ürün Ekle';
    // Form action'u, kontrolcüdeki doğru metotlara yönlendirir
    $form_action = "/{$yonetimurl}/urunler/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
    $urun_id = $is_edit ? (int)$p4 : 0;

    // Varsayılan ürün verisi
    $urun = [
        'id' => 0,
        'urun_adi' => '',
        'urun_kodu' => '',
        'marka_id' => null,
        'aciklama' => '',
        'kisa_aciklama' => '',
        'durum' => 'Aktif',
        'one_cikan_mi' => 0,
        'kategori_ids' => [],
        'resimler' => [],
        'meta_title' => '',
        'meta_description' => '',
        'etiketler' => '',
        'varyant_var_mi' => 0,
        'satis_fiyati' => null,
        'stok_miktari' => null,
        'varyant_verileri' => '{"options":[],"combinations":[]}'
    ];

    // Düzenleme modunda verileri çek
    if ($is_edit) {
        $urun_data = $db->fetch("SELECT * FROM bt_urunler WHERE id = :id", ['id' => $urun_id]);
        if ($urun_data) {
            $urun = array_merge($urun, $urun_data);
            $kategori_iliski = $db->fetchAll("SELECT kategori_id FROM bt_urun_kategori_iliski WHERE urun_id = :urun_id", ['urun_id' => $urun_id]);
            $urun['kategori_ids'] = array_column($kategori_iliski, 'kategori_id');
            $urun['resimler'] = $db->fetchAll("SELECT id, gorsel_url FROM bt_urun_gorselleri WHERE urun_id = :urun_id ORDER BY sira ASC", ['urun_id' => $urun_id]);
        } else {
            $_SESSION["hata"] = "Ürün bulunamadı.";
            header("Location: /{$yonetimurl}/urunler/liste");
            exit();
        }
    }

    // Form için gerekli diğer verileri çek
    $kategoriler = $db->fetchAll("SELECT id, kategori_adi FROM bt_kategoriler WHERE durum = 'Aktif' ORDER BY kategori_adi ASC");
    $markalar = $db->fetchAll("SELECT id, marka_adi FROM bt_markalar WHERE durum = 'Aktif' ORDER BY marka_adi ASC");
    $tum_varyant_ozellikleri = $db->fetchAll("SELECT id, ozellik_adi FROM bt_varyant_ozellikleri ORDER BY sira ASC");

    ?>
    <main>
        <div class="container">
            <!-- SAYFA BAŞLIĞI VE BUTONLAR -->
            <div class="page-title-container">
                <div class="row g-0">
                    <div class="col-auto mb-3 mb-md-0 me-auto">
                        <div class="w-auto sw-md-30">
                            <a href="/<?= $yonetimurl ?>/urunler/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                                <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                                <span class="text-small align-middle">Ürünler</span>
                            </a>
                            <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-end justify-content-end">
                        <?php if ($is_edit) : ?>
                            <a href="/<?= $yonetimurl ?>/urunler/kopya-olustur/<?= $urun_id ?>" class="btn btn-outline-secondary btn-icon btn-icon-start w-100 w-md-auto me-1">
                                <i data-acorn-icon="copy"></i>
                                <span>Kopyala</span>
                            </a>
                        <?php endif; ?>
                        <button type="submit" form="urunForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                            <i data-acorn-icon="save"></i>
                            <span>Kaydet</span>
                        </button>
                    </div>
                </div>
            </div>

            <form id="urunForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
                <input type="hidden" name="id" value="<?= $urun_id ?>">
                <input type="hidden"
                    name="varyant_verileri"
                    id="varyantVerileriInput"
                    value='<?= htmlspecialchars($urun["varyant_verileri"] ?: "{\"options\":[],\"combinations\":[]}", ENT_QUOTES, "UTF-8") ?>'>


                <div class="row">
                    <div class="col-xl-8">
                        <!-- ÜRÜN BİLGİLERİ KARTI -->
                        <div class="mb-5">
                            <h2 class="small-title">Ürün Bilgileri</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="urun_adi">Ürün Adı</label>
                                        <input type="text" class="form-control" id="urun_adi" name="urun_adi" value="<?= htmlspecialchars($urun['urun_adi']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Kısa Açıklama</label>
                                        <textarea name="kisa_aciklama" class="ckeditor form-control" rows="3"><?= htmlspecialchars($urun['kisa_aciklama']) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea name="aciklama" class="ckeditor"><?= htmlspecialchars($urun['aciklama']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GÖRSELLER KARTI -->
                        <div class="mb-5">
                            <h2 class="small-title">Görseller</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="dropzone" id="urunGaleriDropzone"></div>
                                    <input type="hidden" name="resimler" id="resimlerInput" value='<?= htmlspecialchars(json_encode(array_column($urun['resimler'], 'gorsel_url')), ENT_QUOTES, 'UTF-8') ?>'>
                                </div>
                            </div>
                        </div>

                        <!-- VARYANTLAR KARTI -->
                        <div id="varyantlarKarti" class="mb-5 <?= !$urun['varyant_var_mi'] ? 'd-none' : '' ?>">
                            <h2 class="small-title">Varyantlar</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div id="varyantSecimAlani">
                                        <!-- Burası JS ile dinamik olarak doldurulacak -->
                                    </div>
                                    <button type="button" id="varyantEkleBtn" class="btn btn-outline-primary btn-icon btn-icon-start mt-2">
                                        <i data-acorn-icon="plus"></i>
                                        <span>Özellik Ekle</span>
                                    </button>
                                    <hr>
                                    <div id="varyantKombinasyonTablosu" class="table-responsive">
                                        <!-- Oluşturulan varyantlar buraya gelecek -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO KARTI -->
                        <div class="mb-5">
                            <h2 class="small-title">SEO Ayarları</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="meta_title">Meta Başlık</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($urun['meta_title']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="meta_description">Meta Açıklama</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars($urun['meta_description']) ?></textarea>
                                    </div>
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
                                            <option value="Aktif" <?= ($urun['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Pasif" <?= ($urun['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                            <option value="Taslak" <?= ($urun['durum'] == 'Taslak') ? 'selected' : '' ?>>Taslak</option>
                                        </select>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="oneCikanMi" name="one_cikan_mi" value="1" <?= $urun['one_cikan_mi'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="oneCikanMi">Öne Çıkan Ürün</label>
                                    </div>
                                    <?php if ($is_edit) : ?>
                                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- ORGANİZASYON KARTI -->
                        <div class="mb-5">
                            <h2 class="small-title">Organizasyon</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="urun_kodu">Ürün Kodu (SKU)</label>
                                        <input type="text" class="form-control" id="urun_kodu" name="urun_kodu" value="<?= htmlspecialchars($urun['urun_kodu']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="marka_id">Marka</label>
                                        <select class="form-select select2Basic" id="marka_id" name="marka_id">
                                            <option value="0">Marka Seçiniz</option>
                                            <?php foreach ($markalar as $marka) : ?>
                                                <option value="<?= $marka['id'] ?>" <?= ($urun['marka_id'] == $marka['id']) ? 'selected' : '' ?>><?= htmlspecialchars($marka['marka_adi']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="kategori_ids">Kategoriler</label>
                                        <select id="kategori_ids" name="kategori_ids[]" class="form-select select2Basic" multiple="multiple" data-placeholder="Kategori Seçiniz">
                                            <?php foreach ($kategoriler as $kategori) : ?>
                                                <option value="<?= $kategori['id'] ?>" <?= (in_array($kategori['id'], $urun['kategori_ids'])) ? 'selected' : '' ?>><?= htmlspecialchars($kategori['kategori_adi']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="etiketler">Etiketler</label>
                                        <input id="etiketler" name="etiketler" class="form-control" value="<?= htmlspecialchars($urun['etiketler']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FİYAT & STOK KARTI -->
                        <div class="mb-5">
                            <h2 class="small-title">Fiyat & Stok</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="varyantVarMi" name="varyant_var_mi" value="1" <?= $urun['varyant_var_mi'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="varyantVarMi">Bu ürünün varyantları var.</label>
                                    </div>
                                    <div id="varyantsizUrunAlanlari" class="<?= $urun['varyant_var_mi'] ? 'd-none' : '' ?>">
                                        <div class="mb-3">
                                            <label class="form-label" for="satis_fiyati">Satış Fiyatı (₺)</label>
                                            <input type="text" class="form-control" id="satis_fiyati" name="satis_fiyati" value="<?= htmlspecialchars($urun['satis_fiyati'] ?? '') ?>">
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label" for="stok_miktari">Stok Miktarı</label>
                                            <input type="number" class="form-control" id="stok_miktari" name="stok_miktari" value="<?= htmlspecialchars($urun['stok_miktari'] ?? '') ?>">
                                        </div>
                                    </div>
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
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ürünü Sil</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">"<?= htmlspecialchars($urun['urun_adi']) ?>" adlı ürünü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button><button type="button" id="deleteProductButton" class="btn btn-danger">Evet, Sil</button></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urunId = <?= $urun_id ?>;
            const yonetimUrl = '<?= $yonetimurl ?>';

            // =================================================================
            // VARYANT YÖNETİMİ
            // =================================================================
            const varyantVerileriInput = document.getElementById('varyantVerileriInput');
            let mevcutVaryantVerisi;
            try {
                mevcutVaryantVerisi = JSON.parse((varyantVerileriInput.value || '').trim() || '{"options":[],"combinations":[]}');
            } catch (e) {
                console.warn('varyant_verileri JSON parse edilemedi, default kullanılacak.', e);
                mevcutVaryantVerisi = {
                    options: [],
                    combinations: []
                };
            }


            let tumVaryantlar = [];
            let varyantlarYuklendi = false;

            const varyantVarMiCheckbox = document.getElementById('varyantVarMi');
            const varyantsizAlanlar = document.getElementById('varyantsizUrunAlanlari');
            const varyantlarKarti = document.getElementById('varyantlarKarti');
            const varyantSecimAlani = document.getElementById('varyantSecimAlani');
            const varyantEkleBtn = document.getElementById('varyantEkleBtn');
            const kombinasyonTablosu = document.getElementById('varyantKombinasyonTablosu');
            async function safeJson(response) {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON bekleniyordu ama gelen:', text.slice(0, 300));
                    beratbabatoast('danger', 'Sunucu beklenmeyen bir cevap döndürdü.');
                    return {
                        success: false
                    };
                }
            }

            async function varyantVerileriniGetir() {
                if (varyantlarYuklendi) return;
                varyantEkleBtn.disabled = true;
                try {
                    const response = await fetch(`/${yonetimUrl}/varyantlar/hepsini-getir-json`);
                    const result = await safeJson(response);

                    console.log("Sunucudan gelen varyant verisi:", result); // HATA AYIKLAMA
                    if (result.success) {
                        tumVaryantlar = result.data;
                        varyantlarYuklendi = true;
                        mevcutVaryantlariYukle();
                    } else {
                        beratbabatoast('danger', 'Varyant verileri alınamadı.');
                    }
                } catch (error) {
                    console.error('Varyant verileri alınırken hata:', error);
                } finally {
                    varyantEkleBtn.disabled = false;
                }
            }

            function toggleVaryantAlanlari() {
                const isChecked = varyantVarMiCheckbox.checked;
                varyantsizAlanlar.classList.toggle('d-none', isChecked);
                varyantlarKarti.classList.toggle('d-none', !isChecked);
                if (isChecked && !varyantlarYuklendi) {
                    varyantVerileriniGetir();
                }
            }

            varyantEkleBtn.addEventListener('click', () => {
                if (!varyantlarYuklendi) {
                    beratbabatoast('info', 'Lütfen varyant verilerinin yüklenmesini bekleyin.');
                    return;
                }
                const seciliOzellikler = Array.from(document.querySelectorAll('.varyant-ozellik-secim')).map(el => el.value);
                const kalanOzellikler = tumVaryantlar.filter(o => !seciliOzellikler.includes(o.id.toString()));

                if (kalanOzellikler.length === 0) {
                    beratbabatoast('warning', 'Eklenecek başka özellik kalmadı.');
                    return;
                }

                const options = kalanOzellikler.map(o => `<option value="${o.id}">${o.ad}</option>`).join('');
                const newRow = document.createElement('div');
                newRow.className = 'row align-items-end mb-3 varyant-secim-satiri';
                newRow.innerHTML = `
                <div class="col-md-4"><label class="form-label">Özellik</label><select class="form-select varyant-ozellik-secim">${options}</select></div>
                <div class="col-md-7"><label class="form-label">Değerler</label><select class="form-select varyant-deger-secim" multiple="multiple"></select></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-icon btn-icon-only varyant-sil-btn"><i data-acorn-icon="bin"></i></button></div>
            `;
                varyantSecimAlani.appendChild(newRow);

                const ozellikSelect = newRow.querySelector('.varyant-ozellik-secim');
                const degerSelect = newRow.querySelector('.varyant-deger-secim');

                // jQuery sadece Select2'yi başlatmak için kullanılıyor
                $(ozellikSelect).select2();
                $(degerSelect).select2({
                    tags: true,
                    placeholder: "Değerleri seçin veya yazın"
                });

                updateDegerlerForOzellik(ozellikSelect);

                if (typeof AcornIcons !== 'undefined') new AcornIcons().replace();
            });

            varyantSecimAlani.addEventListener('change', (e) => {
                if (e.target.classList.contains('varyant-ozellik-secim')) {
                    updateDegerlerForOzellik(e.target);
                }
            });

            // jQuery ile olay dinleyicisi ekliyoruz çünkü Select2 DOM'u değiştiriyor
            $(varyantSecimAlani).on('change', '.varyant-deger-secim', kombinasyonlariOlustur);

            varyantSecimAlani.addEventListener('click', (e) => {
                const silBtn = e.target.closest('.varyant-sil-btn');
                if (silBtn) {
                    // Select2'yi yok et, sonra DOM'dan kaldır
                    $(silBtn.closest('.varyant-secim-satiri')).find('select').select2('destroy');
                    silBtn.closest('.varyant-secim-satiri').remove();
                    kombinasyonlariOlustur();
                }
            });

            function updateDegerlerForOzellik(ozellikSelectElement) {
                const satir = ozellikSelectElement.closest('.varyant-secim-satiri');
                const degerSelect = satir.querySelector('.varyant-deger-secim');
                const secilenOzellikId = ozellikSelectElement.value;

                const ozellikData = tumVaryantlar.find(o => o.id.toString() === secilenOzellikId);

                // jQuery ile Select2'nin içeriğini temizle
                $(degerSelect).empty();

                if (ozellikData && ozellikData.degerler) {
                    const options = ozellikData.degerler.map(d => new Option(d.ad, d.ad, false, false));
                    // jQuery ile yeni seçenekleri ekle
                    $(degerSelect).append(options);
                }
                // jQuery ile Select2'yi güncelle
                $(degerSelect).trigger('change');
            }

            function kombinasyonlariOlustur() {
                const eskiKombinasyonlar = {};
                kombinasyonTablosu.querySelectorAll('tr[data-kombinasyon-key]').forEach(row => {
                    const key = row.dataset.kombinasyonKey;
                    eskiKombinasyonlar[key] = {
                        fiyat: row.querySelector('input[name^="varyant_fiyat"]').value,
                        stok: row.querySelector('input[name^="varyant_stok"]').value,
                        sku: row.querySelector('input[name^="varyant_sku"]').value
                    };
                });

                const secimler = [];
                document.querySelectorAll('.varyant-secim-satiri').forEach(row => {
                    const ozellikSelect = row.querySelector('.varyant-ozellik-secim');
                    const ozellikAdi = ozellikSelect.options[ozellikSelect.selectedIndex].text;
                    const degerler = Array.from(row.querySelector('.varyant-deger-secim').selectedOptions).map(opt => opt.value);
                    if (degerler.length > 0) secimler.push({
                        ozellikAdi,
                        degerler
                    });
                });

                if (secimler.length === 0) {
                    kombinasyonTablosu.innerHTML = '';
                    return;
                }

                const kombinasyonlar = secimler.reduce((a, b) => a.flatMap(x => b.degerler.map(y => [...x, y])), [
                    []
                ]);

                let tabloHtml = `<table class="table table-striped"><thead><tr>${secimler.map(s => `<th>${s.ozellikAdi}</th>`).join('')}<th>Fiyat (₺)</th><th>Stok</th><th>SKU</th></tr></thead><tbody>`;

                kombinasyonlar.forEach(kombinasyon => {
                    if (kombinasyon.length === 0) return;
                    const key = kombinasyon.join(' / ');
                    const eskiVeri = eskiKombinasyonlar[key] || {
                        fiyat: '',
                        stok: '',
                        sku: ''
                    };

                    tabloHtml += `<tr data-kombinasyon-key="${key}">`;
                    kombinasyon.forEach(deger => {
                        tabloHtml += `<td>${deger}</td>`;
                    });
                    tabloHtml += `
                    <td><input type="text" class="form-control form-control-sm" name="varyant_fiyat[${key}]" value="${eskiVeri.fiyat}"></td>
                    <td><input type="number" class="form-control form-control-sm" name="varyant_stok[${key}]" value="${eskiVeri.stok}"></td>
                    <td><input type="text" class="form-control form-control-sm" name="varyant_sku[${key}]" value="${eskiVeri.sku}"></td>
                </tr>`;
                });
                tabloHtml += `</tbody></table>`;
                kombinasyonTablosu.innerHTML = tabloHtml;
            }

            document.getElementById('urunForm').addEventListener('submit', () => {
                if (!varyantVarMiCheckbox.checked) {
                    varyantVerileriInput.value = '{"options":[],"combinations":[]}';
                    return;
                }
                const options = [];
                document.querySelectorAll('.varyant-secim-satiri').forEach(row => {
                    const ozellikSelect = row.querySelector('.varyant-ozellik-secim');
                    const ozellikAdi = ozellikSelect.options[ozellikSelect.selectedIndex].text;
                    const degerler = Array.from(row.querySelector('.varyant-deger-secim').selectedOptions).map(opt => opt.value);
                    if (degerler.length > 0) options.push({
                        name: ozellikAdi,
                        values: degerler
                    });
                });
                const combinations = [];
                kombinasyonTablosu.querySelectorAll('tr[data-kombinasyon-key]').forEach(row => {
                    const key = row.dataset.kombinasyonKey;
                    combinations.push({
                        attributes: key.split(' / '),
                        price: row.querySelector('input[name^="varyant_fiyat"]').value,
                        stock: row.querySelector('input[name^="varyant_stok"]').value,
                        sku: row.querySelector('input[name^="varyant_sku"]').value
                    });
                });
                const finalData = {
                    options,
                    combinations
                };
                console.log("Kaydedilecek Varyant Verisi:", JSON.stringify(finalData, null, 2)); // HATA AYIKLAMA
                varyantVerileriInput.value = JSON.stringify(finalData);
            });

            function mevcutVaryantlariYukle() {
                console.log("Mevcut varyantlar yükleniyor...", mevcutVaryantVerisi);
                if (mevcutVaryantVerisi && mevcutVaryantVerisi.options && mevcutVaryantVerisi.options.length > 0) {
                    mevcutVaryantVerisi.options.forEach(option => {
                        varyantEkleBtn.click();
                        const sonSatir = varyantSecimAlani.lastElementChild;
                        const ozellikSelect = sonSatir.querySelector('.varyant-ozellik-secim');
                        const degerSelect = sonSatir.querySelector('.varyant-deger-secim');

                        const ozellikOption = Array.from(ozellikSelect.options).find(o => o.text === option.name);
                        if (ozellikOption) {
                            ozellikSelect.value = ozellikOption.value;
                        }

                        $(ozellikSelect).trigger('change');

                        option.values.forEach(val => {
                            if (!Array.from(degerSelect.options).find(o => o.value === val)) {
                                const newOption = new Option(val, val, true, true);
                                degerSelect.add(newOption);
                            }
                        });
                        $(degerSelect).val(option.values).trigger('change');
                    });

                    // DÜZELTME: Kombinasyon tablosunu doldurma işlemi, tüm seçenekler eklendikten sonra yapılmalı.
                    // Zamanlama sorunlarını önlemek için küçük bir gecikme ekliyoruz.
                    setTimeout(() => {
                        if (mevcutVaryantVerisi.combinations) {
                            console.log("Kombinasyon verileri dolduruluyor:", mevcutVaryantVerisi.combinations);
                            mevcutVaryantVerisi.combinations.forEach(combo => {
                                const key = combo.attributes.join(' / ');
                                const row = kombinasyonTablosu.querySelector(`tr[data-kombinasyon-key="${key}"]`);
                                if (row) {
                                    console.log(`'${key}' anahtarı için satır bulundu. Veriler dolduruluyor.`);
                                    row.querySelector('input[name^="varyant_fiyat"]').value = combo.price || '';
                                    row.querySelector('input[name^="varyant_stok"]').value = combo.stock || '';
                                    row.querySelector('input[name^="varyant_sku"]').value = combo.sku || '';
                                } else {
                                    console.warn(`'${key}' anahtarı için kombinasyon satırı bulunamadı.`);
                                }
                            });
                        }
                    }, 100); // 100 milisaniye gecikme
                }
            }

            // Başlangıç
            varyantVarMiCheckbox.addEventListener('change', toggleVaryantAlanlari);
            toggleVaryantAlanlari();

        });
    </script>
    <!-- VARYANT YÖNETİMİ İÇİN JAVASCRIPT -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urunId = <?= $urun_id ?>;
            const yonetimUrl = '<?= $yonetimurl ?>';

            // =================================================================
            // DROPZONE GALERİ YÖNETİMİ
            // =================================================================
            if (typeof Dropzone !== 'undefined' && typeof DropzoneTemplates !== 'undefined' && typeof Sortable !== 'undefined') {
                Dropzone.autoDiscover = false;

                let resimlerInput = document.getElementById('resimlerInput');
                let resimler = JSON.parse(resimlerInput.value || '[]');

                const urunGaleriDropzone = new Dropzone("#urunGaleriDropzone", {
                    url: `/${yonetimUrl}/urunler/galeri-yukle`,
                    paramName: "file",
                    maxFilesize: 5,
                    acceptedFiles: "image/*",
                    addRemoveLinks: true,
                    dictRemoveFile: "",
                    dictDefaultMessage: "Resimleri buraya sürükleyin veya tıklayın.",
                    previewTemplate: DropzoneTemplates.previewTemplate,
                    init: function() {
                        const myDropzone = this;

                        new Sortable(myDropzone.previewsContainer, {
                            animation: 150,
                            onEnd: function() {
                                const newOrder = Array.from(myDropzone.previewsContainer.querySelectorAll('.dz-preview'))
                                    .map(el => el.mockFile ? el.mockFile.serverUrl : null)
                                    .filter(url => url !== null);
                                resimler = newOrder;
                                resimlerInput.value = JSON.stringify(resimler);
                            }
                        });

                        resimler.forEach(fileUrl => {
                            const mockFile = {
                                name: fileUrl.split('/').pop(),
                                size: 12345,
                                accepted: true,
                                serverUrl: fileUrl
                            };
                            myDropzone.emit("addedfile", mockFile);
                            myDropzone.emit("thumbnail", mockFile, fileUrl);
                            myDropzone.emit("complete", mockFile);
                            myDropzone.files.push(mockFile);
                            mockFile.previewElement.mockFile = mockFile;
                        });

                        this.on("success", function(file, response) {
                            if (response.success) {
                                file.serverUrl = response.data.file_path;
                                resimler.push(response.data.file_path);
                                resimlerInput.value = JSON.stringify(resimler);
                                file.previewElement.mockFile = file;
                            } else {
                                beratbabatoast("danger", "Resim yüklenemedi: " + response.message);
                                myDropzone.removeFile(file);
                            }
                        });

                        this.on("removedfile", function(file) {
                            resimler = resimler.filter(url => url !== file.serverUrl);
                            resimlerInput.value = JSON.stringify(resimler);
                        });
                    }
                });
            }


            // =================================================================
            // DİĞER SCRIPTLER
            // =================================================================
            <?php if ($is_edit) : ?>
                document.getElementById('deleteProductButton')?.addEventListener('click', function() {
                    this.disabled = true;
                    fetch(`/${yonetimurl}/urunler/sil`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                ids: [urunId]
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                beratbabatoast("success", "Ürün başarıyla silindi. Yönlendiriliyorsunuz...");
                                setTimeout(() => {
                                    window.location.href = `/${yonetimurl}/urunler/liste`;
                                }, 1500);
                            } else {
                                beratbabatoast("danger", "Silme işlemi başarısız: " + (data.message || "Bilinmeyen bir hata oluştu."));
                                this.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Hata:', error);
                            beratbabatoast("danger", "Silme işlemi sırasında bir sunucu hatası oluştu.");
                            this.disabled = false;
                        });
                });
            <?php endif; ?>
        });
    </script>