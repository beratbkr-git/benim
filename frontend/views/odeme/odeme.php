<main>
    <!-- breadcrumb-area-start -->
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
    <!-- breadcrumb-area-end -->

    <!-- checkout-area start -->
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
                                            <textarea name="adres" rows="3"></textarea>
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
                                <div class="ship-different-title mt-20">
                                    <h3>
                                        <label for="fatura_farkli">Fatura adresim farklı?</label>
                                        <input id="fatura_farkli" type="checkbox" name="fatura_farkli">
                                    </h3>
                                </div>
                                <div id="fatura-adres-wrapper" style="display:none;">
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
                                                    <?= $item['urun']['urun_adi'] ?>
                                                    <?php if ($item['varyant']): ?>
                                                        <br><small><?= $item['varyant']['varyant_adi'] ?></small>
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
                                            <td><span class="amount"><?= $this->formatPrice($sepet_toplam) ?></span></td>
                                        </tr>
                                        <tr class="shipping">
                                            <th>Kargo</th>
                                            <td><span class="amount"><?= $kargo_ucreti > 0 ? $this->formatPrice($kargo_ucreti) : 'Ücretsiz' ?></span></td>
                                        </tr>
                                        <tr class="order-total">
                                            <th>Toplam</th>
                                            <td><strong><span class="amount"><?= $this->formatPrice($genel_toplam) ?></span></strong></td>
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
                                                <input type="radio" name="kargo_yontemi" value="<?= $kargo['id'] ?>" id="kargo<?= $kargo['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                                <label for="kargo<?= $kargo['id'] ?>">
                                                    <?= $kargo['yontem_adi'] ?>:
                                                    <?= $kargo['ucret'] > 0 ? $this->formatPrice($kargo['ucret']) : 'Ücretsiz' ?>
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
                                            <input class="form-check-input" type="radio" name="odeme_yontemi" value="<?= $odeme['id'] ?>" id="odeme<?= $odeme['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
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
    <!-- checkout-area end -->

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

        $('#adres_id').on('change', toggleAdresForm);
        toggleAdresForm();

        const toggleFaturaForm = () => {
            if ($('#fatura_farkli').is(':checked')) {
                $('#fatura-adres-wrapper').show();
            } else {
                $('#fatura-adres-wrapper').hide();
            }
        };

        $('#fatura_farkli').on('change', toggleFaturaForm);
        toggleFaturaForm();

        $('#fatura_adres_id').on('change', function() {
            if ($(this).val() === 'yeni') {
                $('#fatura-adres-form').show();
                $('#fatura-adres-detay').empty();
            } else {
                $('#fatura-adres-form').hide();
                renderAdres($(this).val(), '#fatura-adres-detay');
            }
        }).trigger('change');

        $('#odeme-form').on('submit', function(e) {
            if (!$('#sozlesme').is(':checked')) {
                e.preventDefault();
                alert('Kullanım şartlarını kabul etmelisiniz.');
                return false;
            }

            $(this).find('button[type="submit"]').prop('disabled', true).text('İşleniyor...');
        });
    });
</script>
