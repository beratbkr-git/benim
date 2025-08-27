<main>
    <div class="breadcrumb__area grey-bg pt-5 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="tp-breadcrumb__content">
                        <div class="tp-breadcrumb__list">
                            <span class="tp-breadcrumb__active"><a href="/">Ana Sayfa</a></span>
                            <span class="dvdr">/</span>
                            <span class="tp-breadcrumb__active"><a href="/sepet">Sepetim</a></span>
                            <span class="dvdr">/</span>
                            <span>Ödeme</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="coupon-area pt-10 pb-30">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="coupon-accordion">
                        <h3>Returning customer? <span id="showlogin">Click here to login</span></h3>
                        <div id="checkout-login" class="coupon-content">
                            <div class="coupon-info">
                                <p class="coupon-text">Lütfen siparişinizi görmek için giriş yapın.</p>
                                <form action="/giris" method="post">
                                    <p class="form-row-first">
                                        <label>E-posta <span class="required">*</span></label>
                                        <input type="text" name="eposta">
                                    </p>
                                    <p class="form-row-last">
                                        <label>Parola <span class="required">*</span></label>
                                        <input type="password" name="parola">
                                    </p>
                                    <p class="form-row">
                                        <button class="tp-btn tp-color-btn" type="submit">Giriş Yap</button>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="coupon-accordion">
                        <h3>Have a coupon? <span id="showcoupon">Click here to enter your code</span></h3>
                        <div id="checkout_coupon" class="coupon-checkout-content">
                            <div class="coupon-info">
                                <form action="#">
                                    <p class="checkout-coupon">
                                        <input type="text" placeholder="Coupon Code">
                                        <button class="tp-btn tp-color-btn" type="submit">Apply Coupon</button>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="checkout-area pb-50">
        <div class="container">
            <form method="POST" id="odeme-form">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="checkbox-form">
                            <h3>Teslimat Bilgileri</h3>
                            <?php if (isset($_SESSION['hata'])): ?>
                                <div class="alert alert-danger">
                                    <?= $_SESSION['hata'] ?>
                                    <?php unset($_SESSION['hata']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($this->isLoggedIn() && !empty($adresler)): ?>
                                <div class="checkout-form-list mb-3">
                                    <label>Teslimat Adresi</label>
                                    <select name="adres_id" id="adres_id" class="form-select">
                                        <?php foreach ($adresler as $a): ?>
                                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['adres_baslik']) ?></option>
                                        <?php endforeach; ?>
                                        <option value="yeni">Yeni adres ekle</option>
                                    </select>
                                    <div id="adres-detay" class="mt-2"></div>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="adres_id" value="yeni">
                            <?php endif; ?>

                            <div id="adres-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="checkout-form-list">
                                            <label>Ad Soyad <span class="required">*</span></label>
                                            <input type="text" name="ad_soyad" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['ad_soyad'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>E-posta Adresi <span class="required">*</span></label>
                                            <input type="email" name="eposta" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['eposta'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>Telefon <span class="required">*</span></label>
                                            <input type="tel" name="telefon" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['telefon'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="checkout-form-list">
                                            <label>Adres <span class="required">*</span></label>
                                            <textarea name="adres" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>İl <span class="required">*</span></label>
                                            <select name="il" required>
                                                <option value="">İl Seçiniz</option>
                                                <option value="İstanbul">İstanbul</option>
                                                <option value="Ankara">Ankara</option>
                                                <option value="İzmir">İzmir</option>
                                                <option value="Bursa">Bursa</option>
                                                <option value="Antalya">Antalya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>İlçe <span class="required">*</span></label>
                                            <input type="text" name="ilce" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>Posta Kodu</label>
                                            <input type="text" name="posta_kodu">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="checkout-form-list">
                                            <label>Sipariş Notu</label>
                                            <textarea name="musteri_notu" rows="3" placeholder="Siparişiniz hakkında özel notlarınız..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="fatura_farkli" name="fatura_farkli">
                                    <label class="form-check-label" for="fatura_farkli">Fatura adresim farklı</label>
                                </div>
                            </div>

                            <div id="fatura-adres-wrapper">
                                <?php if ($this->isLoggedIn() && !empty($adresler)): ?>
                                    <div class="checkout-form-list mb-3">
                                        <label>Fatura Adresi</label>
                                        <select name="fatura_adres_id" id="fatura_adres_id" class="form-select">
                                            <?php foreach ($adresler as $a): ?>
                                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['adres_baslik']) ?></option>
                                            <?php endforeach; ?>
                                            <option value="yeni">Yeni adres ekle</option>
                                        </select>
                                        <div id="fatura-adres-detay" class="mt-2"></div>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="fatura_adres_id" value="yeni">
                                <?php endif; ?>

                                <div id="fatura-adres-form">
                                    <div class="checkout-form-list">
                                        <label>Adres <span class="required">*</span></label>
                                        <textarea name="fatura_adres" rows="3"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>İl <span class="required">*</span></label>
                                                <input type="text" name="fatura_il">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>İlçe <span class="required">*</span></label>
                                                <input type="text" name="fatura_ilce">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>Posta Kodu</label>
                                                <input type="text" name="fatura_posta_kodu">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="your-order mb-30">
                            <h3>Sipariş Özeti</h3>
                            <div class="your-order-table table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="product-name">Ürün</th>
                                            <th class="product-total">Toplam</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sepet_urunleri as $item): ?>
                                            <tr class="cart_item">
                                                <td class="product-name">
                                                    <?= $item['urun_adi'] ?>
                                                    <?php if (!empty($item['varyant_bilgisi'])): ?>
                                                        <br><small><?= $item['varyant_bilgisi'] ?></small>
                                                    <?php endif; ?>
                                                    <strong class="product-quantity"> × <?= $item['adet'] ?></strong>
                                                </td>
                                                <td class="product-total">
                                                    <span class="amount"><?= $this->formatPrice($item['toplam']) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="cart-subtotal">
                                            <th>Ara Toplam</th>
                                            <td><span class="amount" id="sepet-ara-toplam"><?= $this->formatPrice($sepet_toplam) ?></span></td>
                                        </tr>
                                        <tr class="shipping">
                                            <th>Kargo</th>
                                            <td><span class="amount" id="kargo-ucreti-text"><?= $kargo_ucreti > 0 ? $this->formatPrice($kargo_ucreti) : 'Ücretsiz' ?></span></td>
                                        </tr>
                                        <tr class="order-total">
                                            <th>Genel Toplam</th>
                                            <td><strong><span class="amount" id="genel-toplam-text"><?= $this->formatPrice($genel_toplam) ?></span></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <?php if (!empty($kargo_yontemleri)): ?>
                                <div class="checkout-shipping mt-3">
                                    <h4>Kargo Yöntemi</h4>
                                    <ul>
                                        <?php foreach ($kargo_yontemleri as $index => $kargo): ?>
                                            <li>
                                                <input type="radio" name="kargo_yontemi" value="<?= $kargo['id'] ?>" id="kargo<?= $kargo['id'] ?>" data-ucret="<?= $kargo['temel_ucret'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                                <label for="kargo<?= $kargo['id'] ?>">
                                                    <?= $kargo['yontem_adi'] ?>:
                                                    <?= $kargo['temel_ucret'] > 0 ? $this->formatPrice($kargo['temel_ucret']) : 'Ücretsiz' ?>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($odeme_yontemleri)): ?>
                                <div class="payment-method mt-20">
                                    <?php foreach ($odeme_yontemleri as $index => $odeme): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="odeme_yontemi" value="<?= $odeme['id'] ?>" id="odeme<?= $odeme['id'] ?>" data-komisyon="<?= $odeme['komisyon_orani'] ?? 0 ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="odeme<?= $odeme['id'] ?>">
                                                <strong><?= $odeme['yontem_adi'] ?></strong><br><small><?= $odeme['aciklama'] ?></small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="order-button-payment mt-20">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="sozlesme" required>
                                    <label class="form-check-label" for="sozlesme">
                                        <a href="/kullanim-kosullari" target="_blank">Kullanım Şartları</a>'nı ve
                                        <a href="/gizlilik-politikasi" target="_blank">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.
                                    </label>
                                </div>
                                <button type="submit" class="tp-btn tp-color-btn w-100">Siparişi Tamamla</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <section class="feature-area mainfeature__bg pt-50 pb-40" data-background="/frontend/assets/img/shape/footer-shape-1.svg">
        <div class="container">
            <div class="mainfeature__border pb-15">
                <div class="row row-cols-lg-5 row-cols-md-3 row-cols-2">
                    <div class="col">
                        <div class="mainfeature__item text-center mb-30">
                            <div class="mainfeature__icon">
                                <img src="/frontend/assets/img/icon/feature-icon-1.svg" alt="">
                            </div>
                            <div class="mainfeature__content">
                                <h4 class="mainfeature__title">Fast Delivery</h4>
                                <p>Across West & East India</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mainfeature__item text-center mb-30">
                            <div class="mainfeature__icon">
                                <img src="/frontend/assets/img/icon/feature-icon-2.svg" alt="">
                            </div>
                            <div class="mainfeature__content">
                                <h4 class="mainfeature__title">safe payment</h4>
                                <p>100% Secure Payment</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mainfeature__item text-center mb-30">
                            <div class="mainfeature__icon">
                                <img src="/frontend/assets/img/icon/feature-icon-3.svg" alt="">
                            </div>
                            <div class="mainfeature__content">
                                <h4 class="mainfeature__title">Online Discount</h4>
                                <p>Add Multi-buy Discount </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mainfeature__item text-center mb-30">
                            <div class="mainfeature__icon">
                                <img src="/frontend/assets/img/icon/feature-icon-4.svg" alt="">
                            </div>
                            <div class="mainfeature__content">
                                <h4 class="mainfeature__title">Help Center</h4>
                                <p>Dedicated 24/7 Support </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mainfeature__item text-center mb-30">
                            <div class="mainfeature__icon">
                                <img src="/frontend/assets/img/icon/feature-icon-5.svg" alt="">
                            </div>
                            <div class="mainfeature__content">
                                <h4 class="mainfeature__title">Curated items</h4>
                                <p>From Handpicked Sellers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    $(document).ready(function() {
        const adreslerData = <?= json_encode($adresler ?? [], JSON_UNESCAPED_UNICODE); ?>;
        const kargoData = <?= json_encode($kargo_yontemleri ?? [], JSON_UNESCAPED_UNICODE); ?>;
        const odemeData = <?= json_encode($odeme_yontemleri ?? [], JSON_UNESCAPED_UNICODE); ?>;
        const sepet_ham_toplam_val = parseFloat('<?= $sepet_toplam ?>');

        const renderAdres = (id, target) => {
            const a = adreslerData.find(item => item.id == id);
            if (a) {
                $(target).html(`<div class="p-2 border rounded">${a.adres}<br>${a.il}/${a.ilce}<br>${a.telefon}</div>`).show();
            } else {
                $(target).empty().hide();
            }
        };

        const toggleAdresForm = () => {
            const val = $('#adres_id').val();
            if (val && val !== 'yeni') {
                $('#adres-form').hide().find('input,textarea,select').prop('required', false);
                renderAdres(val, '#adres-detay');
            } else {
                $('#adres-form').show().find('input,textarea,select').prop('required', true);
                $('#adres-detay').empty();
            }
        };

        const toggleFaturaForm = () => {
            if ($('#fatura_farkli').is(':checked')) {
                $('#fatura-adres-wrapper').show();
                if ($('#fatura_adres_id').length) {
                    $('#fatura_adres_id').trigger('change');
                } else {
                    $('#fatura-adres-form').find('input,textarea,select').prop('required', true);
                }
            } else {
                $('#fatura-adres-wrapper').hide().find('input,textarea,select').prop('required', false);
            }
        };

        $('#adres_id').on('change', toggleAdresForm);
        toggleAdresForm();

        $('#fatura_farkli').on('change', toggleFaturaForm);
        toggleFaturaForm();

        $('#fatura_adres_id').on('change', function() {
            const val = $(this).val();
            if (val === 'yeni') {
                $('#fatura-adres-form').show().find('input,textarea,select').prop('required', true);
                $('#fatura-adres-detay').empty();
            } else {
                $('#fatura-adres-form').hide().find('input,textarea,select').prop('required', false);
                renderAdres(val, '#fatura-adres-detay');
            }
        });

        <?php if ($this->isLoggedIn() && !empty($adresler)): ?>
            $('#adres_id').trigger('change');
            $('#fatura_adres_id').trigger('change');
        <?php endif; ?>

        const formatPrice = (val) => {
            return new Intl.NumberFormat('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(val) + ' ₺';
        };

        const updateTotals = () => {
            let sepet_toplam = sepet_ham_toplam_val;

            // Kargo ücretini al
            const kargoId = $('input[name="kargo_yontemi"]:checked').val();
            const kargo = kargoData.find(k => k.id == kargoId);
            const kargoUcreti = kargo ? parseFloat(kargo.temel_ucret) : 0;

            // Ödeme komisyonunu al ve toplam tutara uygula
            const odemeId = $('input[name="odeme_yontemi"]:checked').val();
            const odeme = odemeData.find(o => o.id == odemeId);
            const komisyonOrani = odeme ? parseFloat(odeme.komisyon_orani) : 0;

            const kdv_orani = parseFloat('<?= $this->db->fetch("SELECT ayar_degeri FROM bt_sistem_ayarlari_gelismis WHERE ayar_anahtari = 'kdv_orani'")['ayar_degeri'] ?? 0; ?>');
            const kdv_dahil_sepet_toplam = sepet_toplam * (1 + kdv_orani / 100);

            const genel_toplam = kdv_dahil_sepet_toplam + kargoUcreti;
            const komisyon_tutari = genel_toplam * (komisyonOrani / 100);

            const son_genel_toplam = genel_toplam + komisyon_tutari;


            // HTML elemanlarını güncelle
            $('#sepet-ara-toplam').text(formatPrice(sepet_toplam));
            $('.shipping .amount').text(kargoUcreti > 0 ? formatPrice(kargoUcreti) : 'Ücretsiz');
            $('.order-total .amount').text(formatPrice(son_genel_toplam));
        };

        // Kargo veya ödeme yöntemi değiştiğinde toplamları güncelle
        $('input[name="kargo_yontemi"], input[name="odeme_yontemi"]').on('change', updateTotals);

        // Sayfa yüklendiğinde toplamları bir kez güncelle
        updateTotals();

        $('#odeme-form').on('submit', function(e) {
            if (!$('#sozlesme').is(':checked')) {
                e.preventDefault();
                alert('Kullanım şartlarını ve Gizlilik Politikasını kabul etmelisiniz.');
                return false;
            }

            $(this).find('button[type="submit"]').prop('disabled', true).text('İşleniyor...');
        });
    });
</script>
