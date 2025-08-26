<?php
// Bu kod blogu, sepet pop-up'ı ve ilgili fonksiyonları içerir.
// Bu kodu, projenizin views/includes/header.php dosyasına ekleyebilirsiniz.

// Fiyatı formatlayan global fonksiyon
if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        global $site_ayarlari;
        $kdv_orani = $site_ayarlari['kdv_orani'] ?? 0;
        $para_birimi = $site_ayarlari['varsayilan_para_birimi'] ?? '₺';

        $kdv_dahil_fiyat = $price * (1 + $kdv_orani / 100);
        return number_format($kdv_dahil_fiyat, 2, ',', '.') . ' ' . $para_birimi;
    }
}



// Sepet verisini al
$sepet = getSepet();
$sepet_adet = $sepet['urunler'] ? count($sepet['urunler']) : 0;
?>

<!-- header-cart-start -->
<div class="tpcartinfo tp-cart-info-area p-relative">
    <button class="tpcart__close"><i class="icon-x"></i></button>
    <div class="tpcart">
        <h4 class="tpcart__title">Sepetiniz</h4>
        <div class="tpcart__product">
            <div class="tpcart__product-list">
                <ul>
                    <?php if (!empty($sepet['urunler'])): ?>
                        <?php foreach ($sepet['urunler'] as $item): ?>
                            <li data-urun-id="<?= $item['id'] ?>" data-varyant-id="<?= $item['varyant_id'] ?? '' ?>">
                                <div class="tpcart__item">
                                    <div class="tpcart__img">
                                        <img src="<?= htmlspecialchars($item['kapak_gorsel'] ?? '/frontend/assets/img/product/placeholder.webp') ?>"
                                            alt="<?= htmlspecialchars($item['urun_adi']) ?>">
                                        <div class="tpcart__del">
                                            <button class="sepet-urun-sil"
                                                data-urun-id="<?= $item['id'] ?>"
                                                data-varyant-id="<?= $item['varyant_id'] ?? '' ?>">
                                                <i class="icon-x-circle"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tpcart__content">
                                        <span class="tpcart__content-title">
                                            <a href="/urun/<?= htmlspecialchars($item['slug']) ?>">
                                                <?= htmlspecialchars($item['urun_adi']) ?>
                                            </a>
                                        </span>
                                        <div class="tpcart__cart-price">
                                            <span class="quantity"><?= $item['adet'] ?> x</span>
                                            <span class="new-price"><?= formatPrice($item['fiyat']) ?></span>
                                        </div>
                                    </div>
                                    <div class="tpcart__item-total">
                                        <span class="fw-bold"><?= formatPrice($item['toplam']) ?></span>
                                    </div>
                                </div>
                                <div class="product__details-quantity d-flex align-items-center mt-10">
                                    <div class="product__details-count mr-10">
                                        <span class="cart-minus" data-urun-id="<?= $item['id'] ?>" data-varyant-id="<?= $item['varyant_id'] ?? '' ?>"><i class="far fa-minus"></i></span>
                                        <input class="tp-cart-input" type="text" value="<?= $item['adet'] ?>" readonly>
                                        <span class="cart-plus" data-urun-id="<?= $item['id'] ?>" data-varyant-id="<?= $item['varyant_id'] ?? '' ?>"><i class="far fa-plus"></i></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-center mt-30 mb-30">Sepetiniz boş.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="tpcart__checkout">
                <div class="tpcart__total-price d-flex justify-content-between align-items-center">
                    <span>Ara Toplam:</span>
                    <span class="heilight-price" id="sepet-ara-toplam"><?= formatPrice($sepet['toplam_tutar']) ?></span>
                </div>
                <div class="tpcart__checkout-btn">
                    <a class="tpcart-btn mb-10" href="/sepet">Sepeti Görüntüle</a>
                    <a class="tpcheck-btn" href="/odeme">Ödeme Yap</a>
                </div>
            </div>
        </div>

        <div class="tpcart__free-shipping text-center">
            <span>10km altı siparişlerde <b>ücretsiz kargo</b></span>
        </div>
    </div>
</div>
<div class="cartbody-overlay"></div>
<!-- header-cart-end -->

<script>
    // Sepet Pop-up için JavaScript
    $(document).ready(function() {
        const cartInfoArea = $('.tpcartinfo');
        const cartToggle = $('.tp-cart-toggle');
        const cartClose = $('.tpcart__close');
        const cartBodyOverlay = $('.cartbody-overlay');

        cartToggle.on('click', function() {
            cartInfoArea.addClass('tp-sidebar-opened');
            cartBodyOverlay.addClass('opened');
        });

        cartClose.on('click', function() {
            cartInfoArea.removeClass('tp-sidebar-opened');
            cartBodyOverlay.removeClass('opened');
        });

        cartBodyOverlay.on('click', function() {
            cartInfoArea.removeClass('tp-sidebar-opened');
            cartBodyOverlay.removeClass('opened');
        });

        // Sepet ürünü silme işlemi için olay dinleyicisi
        $(document).on('click', '.sepet-urun-sil', function() {
            var urunId = $(this).data('urun-id');
            var varyantId = $(this).data('varyant-id') || null;
            sepettenUrunSil(urunId, varyantId);
        });

        // Sepet ürün adedi artırma için olay dinleyicisi
        $(document).on('click', '.cart-plus', function() {
            var urunId = $(this).data('urun-id');
            var varyantId = $(this).data('varyant-id') || null;
            var $input = $(this).siblings('.tp-cart-input');
            var adet = parseInt($input.val()) + 1;
            $input.val(adet); // Input değerini manuel olarak güncelle
            sepetGuncelle(urunId, varyantId, adet, $(this).closest('li'));
        });

        // Sepet ürün adedi azaltma için olay dinleyicisi
        $(document).on('click', '.cart-minus', function() {
            var urunId = $(this).data('urun-id');
            var varyantId = $(this).data('varyant-id') || null;
            var $input = $(this).siblings('.tp-cart-input');
            var adet = parseInt($input.val()) - 1;
            adet = adet < 1 ? 1 : adet;
            $input.val(adet); // Input değerini manuel olarak güncelle
            sepetGuncelle(urunId, varyantId, adet, $(this).closest('li'));
        });

        // Sepetteki ürünü silme AJAX fonksiyonu
        function sepettenUrunSil(urunId, varyantId = null) {
            const vid = varyantId ?? '';
            const itemElement = $(`li[data-urun-id="${urunId}"][data-varyant-id="${vid}"]`);

            $.ajax({
                url: '/sepet/sil',
                method: 'POST',
                data: {
                    urun_id: urunId,
                    varyant_id: varyantId,
                    ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.success(response.message);
                        }
                        itemElement.slideUp(200, function() {
                            $(this).remove();
                            if ($('.tpcart__product-list ul li').length === 0) {
                                $('.tpcart__product-list ul').html('<li class="text-center mt-30 mb-30">Sepetiniz boş.</li>');
                            }
                            guncelleSepetPopUp(response.sepet_adet, response.toplam_tutar);
                        });
                    } else {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.error(response.message);
                        }
                    }
                },
                error: function() {
                    if (typeof brtToast !== 'undefined') {
                        brtToast.error('Bir hata oluştu');
                    }
                }
            });
        }

        // Sepet ürün adedi güncelleme AJAX fonksiyonu
        function sepetGuncelle(urunId, varyantId = null, adet, itemElement) {
            $.ajax({
                url: '/sepet/guncelle',
                method: 'POST',
                data: {
                    urun_id: urunId,
                    varyant_id: varyantId,
                    adet: adet,
                    ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.success(response.message);
                        }
                        itemElement.find('.tp-cart-input').val(adet);
                        itemElement.find('.tpcart__item-total .fw-bold').text(response.urun_toplam_fiyat);
                        guncelleSepetPopUp(response.sepet_adet, response.toplam_tutar);
                    } else {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.error(response.message);
                        }
                        // Hata durumunda input değerini eski haline getir
                        itemElement.find('.tp-cart-input').val(response.eski_adet);
                    }
                },
                error: function() {
                    if (typeof brtToast !== 'undefined') {
                        brtToast.error('Bir hata oluştu');
                    }
                }
            });
        }

        // Sepet pop-up'ını ve genel sepet adetini güncelleyen fonksiyon
        function guncelleSepetPopUp(yeniSepetAdet, yeniToplamTutar) {
            $('#sepet-adet').text(yeniSepetAdet);
            $('#sepet-ara-toplam').text(yeniToplamTutar);
        }
    });
</script>