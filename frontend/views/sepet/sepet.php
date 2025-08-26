<!-- breadcrumb-area-start -->
<div class="breadcrumb__area grey-bg pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="tp-breadcrumb__content">
                    <div class="tp-breadcrumb__list">
                        <span class="tp-breadcrumb__active"><a href="/">Ana Sayfa</a></span>
                        <span class="dvdr">/</span>
                        <span>Sepetim</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb-area-end -->

<!-- cart-area-start -->
<section class="cart-area pt-100 pb-100">
    <div class="container">
        <?php if (!empty($sepet_urunleri)): ?>
            <div class="row">
                <div class="col-xl-8 col-lg-8">
                    <div class="cart-wrapper">
                        <div class="cart__top d-flex justify-content-between align-items-center">
                            <div class="cart__top-left">
                                <h3>Sepetim</h3>
                            </div>
                            <div class="cart__top-right">
                                <button type="button" id="sepeti-temizle" class="tp-btn tp-btn-2">Sepeti Temizle</button>
                            </div>
                        </div>
                        <div class="cart__inner">
                            <div class="table-content table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="product-thumbnail">Ürün</th>
                                            <th class="cart-product-name">Ürün Adı</th>
                                            <th class="product-price">Fiyat</th>
                                            <th class="product-quantity">Adet</th>
                                            <th class="product-subtotal">Toplam</th>
                                            <th class="product-remove">Kaldır</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sepet_urunleri as $item): ?>
                                            <tr data-sepet-key="<?= $item['key'] ?>">
                                                <td class="product-thumbnail">
                                                    <a href="/urun/<?= $item['urun']['id'] ?>">
                                                        <img src="<?= $item['urun']['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/product-1.jpg' ?>" alt="<?= $item['urun']['urun_adi'] ?>">
                                                    </a>
                                                </td>
                                                <td class="product-name">
                                                    <a href="/urun/<?= $item['urun']['id'] ?>"><?= $item['urun']['urun_adi'] ?></a>
                                                    <?php if ($item['varyant']): ?>
                                                        <div class="cart-product-variant">
                                                            <small><?= $item['varyant']['varyant_adi'] ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="product-price">
                                                    <span class="amount"><?= $this->formatPrice($item['fiyat']) ?></span>
                                                </td>
                                                <td class="product-quantity">
                                                    <div class="cart-plus-minus">
                                                        <input type="text" value="<?= $item['adet'] ?>" class="cart-plus-minus-box adet-input" data-sepet-key="<?= $item['key'] ?>">
                                                    </div>
                                                </td>
                                                <td class="product-subtotal">
                                                    <span class="amount item-toplam"><?= $this->formatPrice($item['toplam']) ?></span>
                                                </td>
                                                <td class="product-remove">
                                                    <a href="#" class="sepetten-kaldir" data-sepet-key="<?= $item['key'] ?>"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="coupon-all">
                                        <div class="coupon d-flex align-items-center">
                                            <input id="coupon_code" class="input-text" name="coupon_code" value="" placeholder="Kupon Kodu" type="text">
                                            <button class="tp-btn" name="apply_coupon" type="button">Kupon Uygula</button>
                                        </div>
                                        <div class="coupon2">
                                            <button class="tp-btn" name="update_cart" type="button" id="sepeti-guncelle">Sepeti Güncelle</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4">
                    <div class="cart-wrapper">
                        <div class="cart__top">
                            <h3>Sepet Özeti</h3>
                        </div>
                        <div class="cart__inner">
                            <div class="cart__total">
                                <h6>Ara Toplam: <span id="ara-toplam"><?= $this->formatPrice($sepet_toplam) ?></span></h6>
                                <h6>Kargo: <span>Ücretsiz</span></h6>
                                <hr>
                                <h5>Toplam: <span id="genel-toplam"><?= $this->formatPrice($sepet_toplam) ?></span></h5>
                            </div>
                            <div class="cart__checkout">
                                <a href="/odeme" class="tp-btn w-100">Ödemeye Geç</a>
                            </div>
                            <div class="cart__continue">
                                <a href="/urunler" class="tp-btn tp-btn-2 w-100">Alışverişe Devam Et</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="cart-wrapper text-center">
                        <div class="cart__empty">
                            <div class="cart__empty-icon">
                                <i class="fal fa-shopping-cart"></i>
                            </div>
                            <h3>Sepetiniz Boş</h3>
                            <p>Sepetinizde henüz ürün bulunmuyor. Alışverişe başlamak için ürünlerimizi inceleyin.</p>
                            <a href="/urunler" class="tp-btn">Alışverişe Başla</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<!-- cart-area-end -->

<script>
    $(document).ready(function() {
        $('.adet-input').on('change', function() {
            var sepetKey = $(this).data('sepet-key');
            var adet = $(this).val();

            $.ajax({
                url: '/sepet/guncelle',
                method: 'POST',
                data: {
                    sepet_key: sepetKey,
                    adet: adet,
                    ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });

        $('.sepetten-kaldir').on('click', function(e) {
            e.preventDefault();
            var sepetKey = $(this).data('sepet-key');

            if (confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
                $.ajax({
                    url: '/sepet/sil',
                    method: 'POST',
                    data: {
                        sepet_key: sepetKey,
                        ajax: true
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        });

        $('#sepeti-temizle').on('click', function() {
            if (confirm('Sepetinizdeki tüm ürünleri kaldırmak istediğinizden emin misiniz?')) {
                $('.sepetten-kaldir').each(function() {
                    var sepetKey = $(this).data('sepet-key');
                    $.ajax({
                        url: '/sepet/sil',
                        method: 'POST',
                        data: {
                            sepet_key: sepetKey,
                            ajax: true
                        }
                    });
                });

                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        });
    });
</script>