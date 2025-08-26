<?php
// views/hesap/siparis-detay.php
// Bu sayfa, kullanıcının sipariş detaylarını gösterir.
global $controller;
?>
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
                            <span class="tp-breadcrumb__active"><a href="/hesap">Hesabım</a></span>
                            <span class="dvdr">/</span>
                            <span class="tp-breadcrumb__active"><a href="/hesap/siparisler">Siparişlerim</a></span>
                            <span class="dvdr">/</span>
                            <span>Sipariş Detayı</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- account-area-start -->
    <section class="account-area pt-80 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <div class="account-sidebar">
                        <div class="account-sidebar__header mb-20">
                            <div class="account-sidebar__header-icon">
                                <i class="icon-user"></i>
                            </div>
                            <div class="account-sidebar__header-text">
                                <h5>Hoş Geldiniz,</h5>
                                <h4><?= htmlspecialchars($musteri['ad_soyad']) ?></h4>
                            </div>
                        </div>
                        <div class="account-nav">
                            <nav>
                                <ul>
                                    <li><a href="/hesap">Hesap Paneli</a></li>
                                    <li><a href="/hesap/profil">Profil Bilgilerim</a></li>
                                    <li><a href="/hesap/siparisler" class="active">Siparişlerim</a></li>
                                    <li><a href="/hesap/adresler">Adres Defteri</a></li>
                                    <li><a href="/cikis">Çıkış Yap</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="account-dashboard">
                        <div class="account-dashboard__content">
                            <h5>Sipariş Detayı - #<?= htmlspecialchars($siparis['siparis_kodu']) ?></h5>
                            <div class="order-details-box mt-30">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="order-info-box mb-30">
                                            <h6>Sipariş Bilgileri</h6>
                                            <ul>
                                                <li>Sipariş Kodu: <span><?= htmlspecialchars($siparis['siparis_kodu']) ?></span></li>
                                                <li>Sipariş Tarihi: <span><?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?></span></li>
                                                <li>Sipariş Durumu: <span><?= htmlspecialchars($siparis['siparis_durumu']) ?></span></li>
                                                <li>Ödeme Yöntemi: <span><?= htmlspecialchars($siparis['odeme_yontemi']) ?></span></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="order-info-box mb-30">
                                            <h6>Kargo Bilgileri</h6>
                                            <ul>
                                                <li>Kargo Firması: <span><?= htmlspecialchars($siparis['kargo_firma_adi'] ?? 'Belirtilmemiş') ?></span></li>
                                                <li>Kargo Yöntemi: <span><?= htmlspecialchars($siparis['kargo_yontem_adi'] ?? 'Belirtilmemiş') ?></span></li>
                                                <li>Kargo Takip No: <span><?= htmlspecialchars($siparis['kargo_takip_no'] ?? 'Yok') ?></span></li>
                                                <?php if (!empty($siparis['kargo_takip_no'])): ?>
                                                    <li><a href="#">Takibi Görüntüle</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-30">
                                    <div class="col-md-6">
                                        <div class="order-info-box mb-30">
                                            <h6>Fatura Adresi</h6>
                                            <address>
                                                <strong><?= htmlspecialchars($fatura_adres['ad_soyad']) ?></strong><br>
                                                <?= htmlspecialchars($fatura_adres['adres']) ?><br>
                                                <?= htmlspecialchars($fatura_adres['ilce']) ?> / <?= htmlspecialchars($fatura_adres['il']) ?><br>
                                                <?= htmlspecialchars($fatura_adres['telefon'] ?? '') ?><br>
                                                <?= htmlspecialchars($fatura_adres['posta_kodu'] ?? '') ?>
                                            </address>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="order-info-box mb-30">
                                            <h6>Teslimat Adresi</h6>
                                            <address>
                                                <strong><?= htmlspecialchars($teslimat_adres['ad_soyad']) ?></strong><br>
                                                <?= htmlspecialchars($teslimat_adres['adres']) ?><br>
                                                <?= htmlspecialchars($teslimat_adres['ilce']) ?> / <?= htmlspecialchars($teslimat_adres['il']) ?><br>
                                                <?= htmlspecialchars($teslimat_adres['telefon'] ?? '') ?><br>
                                                <?= htmlspecialchars($teslimat_adres['posta_kodu'] ?? '') ?>
                                            </address>
                                        </div>
                                    </div>
                                </div>

                                <div class="order-products mt-30">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Ürün Adı</th>
                                                    <th>Varyant</th>
                                                    <th>Adet</th>
                                                    <th>Birim Fiyat</th>
                                                    <th>Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($siparis_detaylari as $detay): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($detay['urun_adi']) ?></td>
                                                        <td><?= htmlspecialchars($detay['varyant_bilgisi'] ?? 'Yok') ?></td>
                                                        <td><?= $detay['adet'] ?></td>
                                                        <td><?= $controller->formatPrice($detay['birim_fiyat']) ?></td>
                                                        <td><?= $controller->formatPrice($detay['toplam_fiyat']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <th>Ara Toplam:</th>
                                                    <td><?= $controller->formatPrice($siparis['toplam_tutar']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <th>Kargo Ücreti:</th>
                                                    <td><?= $controller->formatPrice($siparis['kargo_ucreti']) ?></td>
                                                </tr>
                                                <?php if ($siparis['kupon_indirimi'] > 0): ?>
                                                    <tr>
                                                        <td colspan="3"></td>
                                                        <th>Kupon İndirimi:</th>
                                                        <td>-<?= $controller->formatPrice($siparis['kupon_indirimi']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <th>Ödenen Tutar:</th>
                                                    <td><?= $controller->formatPrice($siparis['odenen_tutar']) ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- account-area-end -->
</main>