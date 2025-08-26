<?php
// views/hesap/istek-listesi.php
// Bu sayfa, kullanıcının istek listesini görüntüler.
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
                            <span>İstek Listesi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- wishlist-area-start -->
    <section class="wishlist-area pt-80 pb-80">
        <div class="container">

            <div class="account-dashboard">
                <div class="account-dashboard__content">
                    <div class="wishlist-table">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="product-thumbnail">Ürün</th>
                                        <th class="product-name">Ürün Adı</th>
                                        <th class="product-price">Fiyat</th>
                                        <th class="product-stock-status">Stok Durumu</th>
                                        <th class="product-add-to-cart">İşlem</th>
                                        <th class="product-remove">Kaldır</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($istek_listesi)): ?>
                                        <?php foreach ($istek_listesi as $urun):
                                            $fiyat = $urun['varyant_fiyati'] ?? $urun['satis_fiyati'];
                                            $stok_durumu = "Stokta Var";
                                            // Varsayılan ürün stok kontrolü
                                            if (isset($urun['stok_miktari']) && $urun['stok_miktari'] <= 0) {
                                                $stok_durumu = "Stokta Yok";
                                            }
                                            // Varyantlı ürün stok kontrolü
                                            if ($urun['varyant_id']) {
                                                // Bu kısımda varyanta özel stok bilgisi çekilmeli.
                                                // Şu anlık varsayılan olarak kabul edelim.
                                                // if ($urun['varyant_stok_miktari'] <= 0) { ... }
                                            }
                                        ?>
                                            <tr data-urun-id="<?= $urun['urun_id'] ?>" data-varyant-id="<?= $urun['varyant_id'] ?? '' ?>">
                                                <td class="product-thumbnail">
                                                    <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>">
                                                        <img src="<?= htmlspecialchars($urun['gorsel_url'] ?? '/frontend/assets/img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>">
                                                    </a>
                                                </td>
                                                <td class="product-name">
                                                    <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>">
                                                        <?= htmlspecialchars($urun['urun_adi']) ?>
                                                        <?php if ($urun['varyant_adi']): ?>
                                                            (<?= htmlspecialchars($urun['varyant_adi']) ?>)
                                                        <?php endif; ?>
                                                    </a>
                                                </td>
                                                <td class="product-price">
                                                    <span class="amount"><?= $this->formatPrice($fiyat) ?></span>
                                                </td>
                                                <td class="product-stock-status">
                                                    <span class="<?= $stok_durumu === 'Stokta Yok' ? 'text-danger' : 'text-success' ?>">
                                                        <?= $stok_durumu ?>
                                                    </span>
                                                </td>
                                                <td class="product-add-to-cart">
                                                    <button class="tp-btn-2 add-to-cart-btn" <?= $stok_durumu === 'Stokta Yok' ? 'disabled' : '' ?>>
                                                        Sepete Ekle
                                                    </button>
                                                </td>
                                                <td class="product-remove">
                                                    <button class="remove-from-wishlist-btn"><i class="fa fa-times"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">İstek listenizde ürün bulunmamaktadır.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- wishlist-area-end -->
</main>
<script>
    $(document).ready(function() {
        // İlgili butonlara olay dinleyicisi ekle
        $('.add-to-cart-btn').on('click', function() {
            var urunId = $(this).closest('tr').data('urun-id');
            var varyantId = $(this).closest('tr').data('varyant-id');
            sepeteEkle(urunId, varyantId, 1);
        });

        $('.remove-from-wishlist-btn').on('click', function() {
            var urunId = $(this).closest('tr').data('urun-id');
            var varyantId = $(this).closest('tr').data('varyant-id');
            silIstekListesinden(urunId, varyantId);
        });

        // Sepete Ekleme AJAX fonksiyonu (Zaten footer'da var ama buraya da ekledim)
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
                        }
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

        // İstek listesinden silme AJAX fonksiyonu
        function silIstekListesinden(urunId, varyantId = null) {
            if (typeof brtAlert !== 'undefined') {
                brtAlert.confirm('Emin misiniz?', 'Bu ürünü istek listenizden kaldırmak istediğinizden emin misiniz?', 'Evet, kaldır!').then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/istek-listesi/sil',
                            method: 'POST',
                            data: {
                                urun_id: urunId,
                                varyant_id: varyantId
                            },
                            success: function(response) {
                                if (response.success) {
                                    brtAlert.success('Başarılı!', response.message).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    brtAlert.error('Hata!', response.message);
                                }
                            },
                            error: function() {
                                brtAlert.error('Hata!', 'Bir hata oluştu.');
                            }
                        });
                    }
                });
            } else {
                // Fallback to native confirm
                if (confirm('Bu ürünü istek listenizden kaldırmak istediğinizden emin misiniz?')) {
                    $.ajax({
                        url: '/istek-listesi/sil',
                        method: 'POST',
                        data: {
                            urun_id: urunId,
                            varyant_id: varyantId
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            }
        }
    });
</script>