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
                        <span><?= $kategori['kategori_adi'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb-area-end -->

<!-- category-area-start -->
<section class="category-area pt-70 pb-40">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section__title-wrapper text-center mb-55">
                    <h3 class="section__title"><?= $kategori['kategori_adi'] ?></h3>
                    <?php if (!empty($kategori['aciklama'])): ?>
                        <p><?= $kategori['aciklama'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alt Kategoriler -->
        <?php if (!empty($alt_kategoriler)): ?>
            <div class="row mb-50">
                <div class="col-xl-12">
                    <h4 class="mb-30">Alt Kategoriler</h4>
                </div>
                <?php foreach ($alt_kategoriler as $alt_kategori): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="category__item text-center mb-30">
                            <div class="category__thumb">
                                <a href="/kategori/<?= $alt_kategori['id'] ?>">
                                    <img src="<?= $alt_kategori['kategori_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/category/category-1.jpg' ?>" alt="<?= $alt_kategori['kategori_adi'] ?>">
                                </a>
                            </div>
                            <div class="category__content">
                                <h3 class="category__title">
                                    <a href="/kategori/<?= $alt_kategori['id'] ?>"><?= $alt_kategori['kategori_adi'] ?></a>
                                </h3>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<!-- category-area-end -->

<!-- shop-area-start -->
<section class="shop-area-start grey-bg pb-200">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="tpshop__top">
                    <div class="tpshop__banner mb-30">
                        <div class="tpshop__content">
                            <span>Toplam <?= $toplam_urun ?> ürün bulundu</span>
                        </div>
                    </div>

                    <!-- Sıralama -->
                    <div class="product__filter-content mb-40">
                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <div class="product__item-count">
                                    <span>Sayfa <?= $sayfa ?> / <?= $toplam_sayfa ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="product__sorting d-flex justify-content-end align-items-center">
                                    <select id="siralama-select" class="form-select">
                                        <option value="yeni" <?= $siralama == 'yeni' ? 'selected' : '' ?>>En Yeni</option>
                                        <option value="populer" <?= $siralama == 'populer' ? 'selected' : '' ?>>En Popüler</option>
                                        <option value="fiyat-artan" <?= $siralama == 'fiyat-artan' ? 'selected' : '' ?>>Fiyat (Düşük-Yüksek)</option>
                                        <option value="fiyat-azalan" <?= $siralama == 'fiyat-azalan' ? 'selected' : '' ?>>Fiyat (Yüksek-Düşük)</option>
                                        <option value="alfabetik" <?= $siralama == 'alfabetik' ? 'selected' : '' ?>>A-Z</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ürün Listesi -->
                    <div class="row">
                        <?php if (!empty($urunler)): ?>
                            <?php foreach ($urunler as $urun): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                    <div class="product__item mb-30">
                                        <div class="product__thumb">
                                            <a href="/urun/<?= $urun['id'] ?>">
                                                <img src="<?= $urun['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/product-1.jpg' ?>" alt="<?= $urun['urun_adi'] ?>">
                                            </a>
                                            <div class="product__action">
                                                <a href="#" class="sepete-ekle-btn" data-urun-id="<?= $urun['id'] ?>"><i class="fal fa-shopping-cart"></i></a>
                                                <a href="/urun/<?= $urun['id'] ?>"><i class="fal fa-eye"></i></a>
                                                <a href="#"><i class="fal fa-heart"></i></a>
                                            </div>
                                        </div>
                                        <div class="product__content">
                                            <h3 class="product__title">
                                                <a href="/urun/<?= $urun['id'] ?>"><?= $urun['urun_adi'] ?></a>
                                            </h3>
                                            <div class="product__price">
                                                <?php if ($urun['min_fiyat'] != $urun['max_fiyat']): ?>
                                                    <span class="new-price"><?= $this->formatPrice($urun['min_fiyat']) ?> - <?= $this->formatPrice($urun['max_fiyat']) ?></span>
                                                <?php else: ?>
                                                    <span class="new-price"><?= $this->formatPrice($urun['min_fiyat']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($urun['marka_adi'])): ?>
                                                <div class="product__brand">
                                                    <span><?= $urun['marka_adi'] ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <h4>Bu kategoride ürün bulunamadı</h4>
                                    <p>Bu kategoriye henüz ürün eklenmemiş.</p>
                                    <a href="/urunler" class="tp-btn">Tüm Ürünleri Gör</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sayfalama -->
                    <?php if ($toplam_sayfa > 1): ?>
                        <div class="basic-pagination text-center mt-30">
                            <nav>
                                <ul>
                                    <?php if ($sayfa > 1): ?>
                                        <li><a href="?sayfa=<?= $sayfa - 1 ?>&siralama=<?= $siralama ?>"><i class="fal fa-angle-left"></i></a></li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                                        <?php if ($i == $sayfa): ?>
                                            <li><span class="current"><?= $i ?></span></li>
                                        <?php else: ?>
                                            <li><a href="?sayfa=<?= $i ?>&siralama=<?= $siralama ?>"><?= $i ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($sayfa < $toplam_sayfa): ?>
                                        <li><a href="?sayfa=<?= $sayfa + 1 ?>&siralama=<?= $siralama ?>"><i class="fal fa-angle-right"></i></a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- shop-area-end -->

<script>
    $(document).ready(function() {
        $('#siralama-select').on('change', function() {
            var url = new URL(window.location);
            url.searchParams.set('siralama', $(this).val());
            url.searchParams.delete('sayfa');
            window.location.href = url.toString();
        });
    });
</script>