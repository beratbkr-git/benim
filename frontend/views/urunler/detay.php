<?php
// views/urunler/detay.php
// Bu sayfa, UrunDetayController'dan gelen verilerle dinamik olarak doldurulur ve
// Orfarm temasının shop-details.html dosyası baz alınarak yeniden düzenlenmiştir.
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
                            <span class="tp-breadcrumb__active"><a href="/urunler">Ürünler</a></span>
                            <span class="dvdr">/</span>
                            <span><?= htmlspecialchars($urun['urun_adi']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- shop-details-area-start -->
    <section class="shop-details-area grey-bg pb-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-md-12">
                    <div class="tpdetails__area mr-60 pb-30">
                        <div class="tpdetails__product mb-30">
                            <div class="tpdetails__title-box">
                                <h3 class="tpdetails__title"><?= htmlspecialchars($urun['urun_adi']) ?></h3>
                                <ul class="tpdetails__brand">
                                    <li>Marka: <a href="#"><?= htmlspecialchars($urun['marka_adi'] ?? 'Marka Yok') ?></a> </li>
                                    <li>
                                        <?php
                                        $ortalama_puan = $this->getUrunOrtalamaPuani($urun['id']);
                                        $yorum_sayisi = count($yorumlar);
                                        for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i>
                                        <?php endfor; ?>
                                        <b><?= $yorum_sayisi ?> Yorum</b>
                                    </li>
                                    <li>
                                        SKU: <span><?= htmlspecialchars($urun['urun_kodu'] ?? 'Yok') ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="tpdetails__box">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="tpproduct-details__nab">
                                            <div class="tab-content" id="nav-tabContents">
                                                <?php if (!empty($gorseller)): ?>
                                                    <?php foreach ($gorseller as $index => $gorsel): ?>
                                                        <div class="tab-pane fade w-img <?= $index === 0 ? 'show active' : '' ?>" id="nav-<?= $index ?>" role="tabpanel">
                                                            <img src="<?= htmlspecialchars($gorsel['gorsel_url']) ?>" alt="<?= htmlspecialchars($gorsel['alt_metin'] ?? $urun['urun_adi']) ?>">
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="tab-pane fade show active w-img">
                                                        <img src="/<?= FRONTEND_ASSETS_DIR ?>img/product/placeholder.webp" alt="Görsel Yok">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <nav>
                                                <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
                                                    <?php if (!empty($gorseller)): ?>
                                                        <?php foreach ($gorseller as $index => $gorsel): ?>
                                                            <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="nav-<?= $index ?>-tab" data-bs-toggle="tab" data-bs-target="#nav-<?= $index ?>" type="button" role="tab" aria-controls="nav-<?= $index ?>">
                                                                <img src="<?= htmlspecialchars($gorsel['gorsel_url']) ?>" alt="<?= htmlspecialchars($gorsel['alt_metin'] ?? $urun['urun_adi']) ?>">
                                                            </button>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </nav>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="product__details">
                                            <div class="product__details-price-box">
                                                <h5 class="product__details-price" id="secili-fiyat">
                                                    <?php
                                                    $min_fiyat = $urun['varyant_var_mi'] ? min(array_column($varyantlar, 'fiyat')) : $urun['satis_fiyati'];
                                                    $max_fiyat = $urun['varyant_var_mi'] ? max(array_column($varyantlar, 'fiyat')) : $urun['satis_fiyati'];

                                                    if ($urun['varyant_var_mi'] == 1 && $min_fiyat != $max_fiyat): ?>
                                                        <span class="new-price"><?= $this->formatPrice($min_fiyat) ?> - <?= $this->formatPrice($max_fiyat) ?></span>
                                                    <?php else: ?>
                                                        <span class="new-price"><?= $this->formatPrice($min_fiyat) ?></span>
                                                    <?php endif; ?>
                                                </h5>
                                                <ul class="product__details-info-list">
                                                    <li><?= $urun['kisa_aciklama'] ?></li>
                                                </ul>
                                            </div>

                                            <?php if ($urun['varyant_var_mi'] == 1 && !empty($varyantlar)): ?>
                                                <div class="product__details-variant mb-20">
                                                    <h4>Seçenekler:</h4>
                                                    <div class="product__details-variant-list">
                                                        <?php foreach ($varyantlar as $index => $varyant): ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input varyant-radio" type="radio" name="varyant_id"
                                                                    value="<?= $varyant['id'] ?>" id="varyant<?= $varyant['id'] ?>"
                                                                    data-fiyat="<?= $varyant['fiyat'] ?>" data-stok="<?= $varyant['stok_adedi'] ?>"
                                                                    <?= $index === 0 ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="varyant<?= $varyant['id'] ?>">
                                                                    <?= htmlspecialchars($varyant['varyant_adi']) ?> - <?= $this->formatPrice($varyant['fiyat']) ?>
                                                                    <?php if ($varyant['stok_adedi'] <= 0): ?>
                                                                        <span class="text-danger">(Stokta Yok)</span>
                                                                    <?php elseif ($varyant['stok_adedi'] <= 5 && $varyant['stok_adedi'] > 0): ?>
                                                                        <span class="text-warning">(Son <?= $varyant['stok_adedi'] ?> adet)</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="product__details-cart">
                                                <form id="sepete-ekle-form">
                                                    <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                                                    <div class="product__details-quantity d-flex align-items-center mb-15">
                                                        <b>Adet:</b>
                                                        <div class="product__details-count mr-10">
                                                            <span class="cart-minus"><i class="far fa-minus"></i></span>
                                                            <input class="tp-cart-input" type="text" name="adet" value="1">
                                                            <span class="cart-plus"><i class="far fa-plus"></i></span>
                                                        </div>
                                                        <div class="product__details-btn">
                                                            <button type="submit" class="tp-btn">Sepete Ekle</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <ul class="product__details-check">
                                                    <li>
                                                        <a href="#"><i class="icon-heart icons"></i> İstek listesine ekle</a>
                                                    </li>
                                                    <li>
                                                        <a href="#"><i class="icon-layers"></i> Karşılaştır</a>
                                                    </li>
                                                    <li>
                                                        <a href="#"><i class="icon-share-2"></i> Paylaş</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="product__details-stock mb-25">
                                                <ul>
                                                    <li>Stok Durumu: <i id="stok-durumu">Stokta Var</i></li>
                                                    <li>Kategoriler: <span><?= htmlspecialchars($urun['kategori_adi'] ?? 'Yok') ?></span></li>
                                                    <li>Etiketler: <span><?= htmlspecialchars($urun['etiketler'] ?? 'Yok') ?></span></li>
                                                </ul>
                                            </div>

                                            <div class="product__details-payment text-center">
                                                <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/payment-2.png" alt="">
                                                <span>Güvenli ve Güvenli ödeme garantisi</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tpdescription__box">
                            <div class="tpdescription__box-center d-flex align-items-center justify-content-center">
                                <nav>
                                    <div class="nav nav-tabs" role="tablist">
                                        <button class="nav-link active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button">Ürün Açıklaması</button>
                                        <button class="nav-link" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#nav-information" type="button">Ek Bilgiler</button>
                                        <button class="nav-link" id="nav-review-tab" data-bs-toggle="tab" data-bs-target="#nav-review" type="button">Yorumlar (<?= count($yorumlar) ?>)</button>
                                    </div>
                                </nav>
                            </div>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-description" role="tabpanel">
                                    <div class="tpdescription__content">
                                        <p><?= $urun['aciklama'] ?></p>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="nav-information" role="tabpanel">
                                    <div class="tpdescription__content">
                                        <h5 class="tpdescription__product-title">ÜRÜN DETAYLARI</h5>
                                        <ul class="tpdescription__product-info">
                                            <li>Ağırlık: <?= htmlspecialchars($urun['agirlik']) ?> kg</li>
                                            <li>Boyutlar: <?= htmlspecialchars($urun['boyutlar']) ?? 'Belirtilmemiş' ?></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="nav-review" role="tabpanel">
                                    <div class="tpreview__wrapper">
                                        <h4 class="tpreview__wrapper-title"><?= count($yorumlar) ?> yorum için <?= htmlspecialchars($urun['urun_adi']) ?></h4>
                                        <?php if (!empty($yorumlar)): ?>
                                            <?php foreach ($yorumlar as $yorum): ?>
                                                <div class="tpreview__comment">
                                                    <div class="tpreview__comment-img mr-20">
                                                        <img src="<?= htmlspecialchars($yorum['profil_resmi'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/testimonial/test-avata-1.png') ?>" alt="Müşteri Resmi">
                                                    </div>
                                                    <div class="tpreview__comment-text">
                                                        <div class="tpreview__comment-autor-info d-flex align-items-center justify-content-between">
                                                            <div class="tpreview__comment-author">
                                                                <span><?= htmlspecialchars($yorum['musteri_adi'] ?? 'Anonim') ?></span>
                                                            </div>
                                                            <div class="tpreview__comment-star">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fa<?= $i <= $yorum['puan'] ? 's' : 'r' ?> fa-star"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <span class="date mb-20"><?= date('d F, Y', strtotime($yorum['olusturma_tarihi'])) ?>: </span>
                                                        <p><?= nl2br(htmlspecialchars($yorum['yorum'])) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>Henüz yorum yapılmamış.</p>
                                        <?php endif; ?>
                                        <div class="tpreview__form">
                                            <h4 class="tpreview__form-title mb-25">Bir yorum ekle</h4>
                                            <form action="#">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="tpreview__input mb-30">
                                                            <input type="text" placeholder="Adınız">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="tpreview__input mb-30">
                                                            <input type="email" placeholder="E-posta Adresiniz">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="tpreview__star mb-20">
                                                            <h4 class="title">Puanınız</h4>
                                                            <div class="tpreview__star-icon">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <a href="#"><i class="icon-star_outline1"></i></a>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <div class="tpreview__input mb-30">
                                                            <textarea name="text" placeholder="Mesajınız..."></textarea>
                                                            <div class="tpreview__submit mt-30">
                                                                <button class="tp-btn">Gönder</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-12">
                    <div class="tpsidebar pb-30">
                        <div class="tpsidebar__warning mb-30">
                            <ul>
                                <li>
                                    <div class="tpsidebar__warning-item">
                                        <div class="tpsidebar__warning-icon">
                                            <i class="icon-package"></i>
                                        </div>
                                        <div class="tpsidebar__warning-text">
                                            <p>90 TL üzeri siparişlerde <br> ücretsiz kargo</p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="tpsidebar__warning-item">
                                        <div class="tpsidebar__warning-icon">
                                            <i class="icon-shield"></i>
                                        </div>
                                        <div class="tpsidebar__warning-text">
                                            <p>%100 Organik <br> Garanti</p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="tpsidebar__warning-item">
                                        <div class="tpsidebar__warning-icon">
                                            <i class="icon-package"></i>
                                        </div>
                                        <div class="tpsidebar__warning-text">
                                            <p>60 gün iade süresi</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="tpsidebar__banner mb-30">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/sidebar-product-1.png" alt="">
                        </div>
                        <div class="tpsidebar__product">
                            <h4 class="tpsidebar__title mb-15">Son Ürünler</h4>
                            <?php if (!empty($yeni_urunler)): ?>
                                <?php foreach ($yeni_urunler as $urun_sidebar): ?>
                                    <div class="tpsidebar__product-item">
                                        <div class="tpsidebar__product-thumb p-relative">
                                            <img src="<?= htmlspecialchars($urun_sidebar['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun_sidebar['urun_adi']) ?>">
                                            <div class="tpsidebar__info bage">
                                                <span class="tpproduct__info-hot bage__hot">HOT</span>
                                            </div>
                                        </div>
                                        <div class="tpsidebar__product-content">
                                            <span class="tpproduct__product-category">
                                                <a href="/kategori/<?= htmlspecialchars($urun_sidebar['slug'] ?? 'urunler') ?>">
                                                    <?= htmlspecialchars($urun_sidebar['marka_adi'] ?? '') ?>
                                                </a>
                                            </span>
                                            <h4 class="tpsidebar__product-title">
                                                <a href="/urun/<?= htmlspecialchars($urun_sidebar['slug']) ?>"><?= htmlspecialchars($urun_sidebar['urun_adi']) ?></a>
                                            </h4>
                                            <div class="tpproduct__rating mb-5">
                                                <?php
                                                $ortalama_puan_sidebar = $this->getUrunOrtalamaPuani($urun_sidebar['id']);
                                                for ($i = 1; $i <= 5; $i++): ?>
                                                    <a href="#"><i class="fa<?= $i <= $ortalama_puan_sidebar ? 's' : 'r' ?> fa-star"></i></a>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="tpproduct__price">
                                                <span><?= $this->formatPrice($urun_sidebar['min_fiyat']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- shop-details-area-end -->

    <!-- product-area-start -->
    <section class="product-area whight-product pt-75 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="tpdescription__product-title mb-20">Benzer Ürünler</h5>
                </div>
            </div>
            <div class="tpproduct__arrow double-product p-relative">
                <div class="swiper-container tpproduct-active tpslider-bottom p-relative">
                    <div class="swiper-wrapper">
                        <?php if (!empty($benzer_urunler)): ?>
                            <?php foreach ($benzer_urunler as $urun): ?>
                                <div class="swiper-slide">
                                    <div class="tpproduct p-relative">
                                        <div class="tpproduct__thumb p-relative text-center">
                                            <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>"></a>
                                            <div class="tpproduct__info bage">
                                                <?php if (!empty($urun['indirim_orani'])): // İndirim oranı varsa 
                                                ?>
                                                    <span class="tpproduct__info-discount bage__discount">-<?= $urun['indirim_orani'] ?>%</span>
                                                <?php endif; ?>
                                                <span class="tpproduct__info-hot bage__hot">HOT</span>
                                            </div>
                                            <div class="tpproduct__shopping">
                                                <a class="tpproduct__shopping-wishlist" href="/istek-listesi"><i class="icon-heart icons"></i></a>
                                                <a class="tpproduct__shopping-cart" href="#"><i class="icon-eye"></i></a>
                                            </div>
                                        </div>
                                        <div class="tpproduct__content">
                                            <span class="tpproduct__content-weight">
                                                <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><?= htmlspecialchars($urun['marka_adi']) ?></a>
                                            </span>
                                            <h4 class="tpproduct__title">
                                                <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><?= htmlspecialchars($urun['urun_adi']) ?></a>
                                            </h4>
                                            <div class="tpproduct__rating mb-5">
                                                <?php
                                                $ortalama_puan = $this->getUrunOrtalamaPuani($urun['id']);
                                                for ($i = 1; $i <= 5; $i++): ?>
                                                    <a href="#"><i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i></a>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="tpproduct__price">
                                                <span><?= $this->formatPrice($urun['min_fiyat']) ?></span>
                                            </div>
                                        </div>
                                        <div class="tpproduct__hover-text">
                                            <div class="tpproduct__hover-btn d-flex justify-content-center mb-10">
                                                <a class="tp-btn-2 sepete-ekle-btn" href="#" data-urun-id="<?= $urun['id'] ?>">Sepete Ekle</a>
                                            </div>
                                            <div class="tpproduct__descrip">
                                                <ul>
                                                    <li><?= nl2br(htmlspecialchars($urun['kisa_aciklama'] ?? '')) ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- product-area-end -->
</main>
<script>
    $(document).ready(function() {
        // Sepete Ekleme AJAX fonksiyonu
        function sepeteEkle(urunId, varyantId = null, adet = 1) {
            $.ajax({
                url: '/ajax/sepetEkle',
                method: 'POST',
                data: {
                    urun_id: urunId,
                    varyant_id: varyantId,
                    adet: adet,
                    ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        $('#sepet-adet').text(response.sepet_adet);
                        if (typeof brtToast !== 'undefined') {
                            brtToast.success(response.message);
                        } else {
                            alert(response.message);
                        }
                    } else {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.error(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function() {
                    if (typeof brtToast !== 'undefined') {
                        brtToast.error('Bir hata oluştu');
                    } else {
                        alert('Bir hata oluştu');
                    }
                }
            });
        }

        // Varyant seçimi değiştiğinde fiyat ve stok bilgilerini güncelle
        $('.varyant-radio').on('change', function() {
            var fiyat = parseFloat($(this).data('fiyat'));
            var stok = $(this).data('stok');

            $('#secili-fiyat').text(formatPrice(fiyat));

            if (stok <= 0) {
                $('#sepete-ekle-btn').prop('disabled', true).text('Stokta Yok');
                $('#stok-durumu').html('<i class="fal fa-times text-danger"></i> Stokta Yok');
            } else {
                $('#sepete-ekle-btn').prop('disabled', false).html('<i class="fal fa-shopping-cart"></i> Sepete Ekle');
                $('#stok-durumu').html('<i class="fal fa-check text-success"></i> Stokta Var');
            }
        });

        // Adet artırma/azaltma
        $('.cart-minus').on('click', function() {
            var $input = $(this).siblings('.tp-cart-input');
            var count = parseInt($input.val()) - 1;
            count = count < 1 ? 1 : count;
            $input.val(count);
        });

        $('.cart-plus').on('click', function() {
            var $input = $(this).siblings('.tp-cart-input');
            var count = parseInt($input.val()) + 1;
            $input.val(count);
        });

        // Sepete Ekle butonu için form submit işlemi
        $('#sepete-ekle-form').on('submit', function(e) {
            e.preventDefault();
            var urunId = $(this).find('input[name="urun_id"]').val();
            var varyantId = $(this).find('input[name="varyant_id"]:checked').val() || null;
            var adet = $(this).find('input[name="adet"]').val();

            if (urunId) {
                sepeteEkle(urunId, varyantId, adet);
            }
        });
    });
</script>