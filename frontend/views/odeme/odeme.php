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

<!-- checkout-area-start -->
<section class="checkout-area pt-100 pb-100">
    <div class="container">
        <form method="POST" id="odeme-form">
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout-wrapper">
                        <h3 class="checkout-wrapper-title">Fatura Bilgileri</h3>

                        <?php if (isset($_SESSION['hata'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['hata'] ?>
                                <?php unset($_SESSION['hata']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="checkout-wrapper-content">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="checkout-wrapper-content-item">
                                        <label>Ad Soyad *</label>
                                        <input type="text" name="ad_soyad" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['ad_soyad'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout-wrapper-content-item">
                                        <label>E-posta Adresi *</label>
                                        <input type="email" name="eposta" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['eposta'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout-wrapper-content-item">
                                        <label>Telefon *</label>
                                        <input type="tel" name="telefon" required value="<?= $this->isLoggedIn() ? $this->getCurrentCustomer()['telefon'] : '' ?>">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="checkout-wrapper-content-item">
                                        <label>Adres *</label>
                                        <textarea name="adres" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout-wrapper-content-item">
                                        <label>İl *</label>
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
                                <div class="col-lg-6">
                                    <div class="checkout-wrapper-content-item">
                                        <label>İlçe *</label>
                                        <input type="text" name="ilce" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout-wrapper-content-item">
                                        <label>Posta Kodu</label>
                                        <input type="text" name="posta_kodu">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="checkout-wrapper-content-item">
                                        <label>Sipariş Notu</label>
                                        <textarea name="musteri_notu" rows="3" placeholder="Siparişiniz hakkında özel notlarınız..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kargo Yöntemi -->
                    <?php if (!empty($kargo_yontemleri)): ?>
                        <div class="checkout-wrapper mt-40">
                            <h3 class="checkout-wrapper-title">Kargo Yöntemi</h3>
                            <div class="checkout-wrapper-content">
                                <?php foreach ($kargo_yontemleri as $index => $kargo): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kargo_yontemi" value="<?= $kargo['id'] ?>"
                                            id="kargo<?= $kargo['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="kargo<?= $kargo['id'] ?>">
                                            <strong><?= $kargo['yontem_adi'] ?></strong>
                                            <span class="float-end"><?= $kargo['ucret'] > 0 ? $this->formatPrice($kargo['ucret']) : 'Ücretsiz' ?></span>
                                            <br>
                                            <small><?= $kargo['aciklama'] ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ödeme Yöntemi -->
                    <?php if (!empty($odeme_yontemleri)): ?>
                        <div class="checkout-wrapper mt-40">
                            <h3 class="checkout-wrapper-title">Ödeme Yöntemi</h3>
                            <div class="checkout-wrapper-content">
                                <?php foreach ($odeme_yontemleri as $index => $odeme): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="odeme_yontemi" value="<?= $odeme['id'] ?>"
                                            id="odeme<?= $odeme['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="odeme<?= $odeme['id'] ?>">
                                            <strong><?= $odeme['yontem_adi'] ?></strong>
                                            <br>
                                            <small><?= $odeme['aciklama'] ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-6">
                    <div class="checkout-wrapper">
                        <h3 class="checkout-wrapper-title">Sipariş Özeti</h3>
                        <div class="checkout-wrapper-content">
                            <div class="checkout-order-table">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ürün</th>
                                            <th>Toplam</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sepet_urunleri as $item): ?>
                                            <tr>
                                                <td>
                                                    <?= $item['urun']['urun_adi'] ?>
                                                    <?php if ($item['varyant']): ?>
                                                        <br><small><?= $item['varyant']['varyant_adi'] ?></small>
                                                    <?php endif; ?>
                                                    <strong> × <?= $item['adet'] ?></strong>
                                                </td>
                                                <td><?= $this->formatPrice($item['toplam']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Ara Toplam</th>
                                            <td><?= $this->formatPrice($sepet_toplam) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Kargo</th>
                                            <td><?= $kargo_ucreti > 0 ? $this->formatPrice($kargo_ucreti) : 'Ücretsiz' ?></td>
                                        </tr>
                                        <tr class="order-total">
                                            <th>Toplam</th>
                                            <td><strong><?= $this->formatPrice($genel_toplam) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="checkout-agreement">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sozlesme" required>
                                    <label class="form-check-label" for="sozlesme">
                                        <a href="/kullanim-kosullari" target="_blank">Kullanım Şartları</a>'nı ve
                                        <a href="/gizlilik-politikasi" target="_blank">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.
                                    </label>
                                </div>
                            </div>

                            <div class="checkout-btn">
                                <button type="submit" class="tp-btn w-100">Siparişi Tamamla</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
<!-- checkout-area-end -->

<script>
    $(document).ready(function() {
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