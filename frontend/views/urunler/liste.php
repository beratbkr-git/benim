<?php
// views/urunler/liste.php
// Bu sayfa, UrunController'dan gelen verilerle dinamik olarak doldurulur ve
// Orfarm temasının shop-left-sidebar.html dosyası baz alınarak yeniden düzenlenmiştir.

// Ürün sayısını hesapla
$baslangic_urun = ($sayfa - 1) * $limit + 1;
$bitis_urun = min($sayfa * $limit, $toplam_urun);

// Filtrelerin varlığını kontrol et
$filtre_var = !empty($kategori_slug) || !empty($marka_id) || !empty($min_fiyat) || !empty($max_fiyat) || !empty($rating);

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
                            <span>Ürünler</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- shop-area-start -->
    <section class="shop-area-start grey-bg pb-200">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-12 col-md-12">
                    <div class="tpshop__leftbar">
                        <!-- Filtreleri Temizleme Butonu -->
                        <?php if ($filtre_var): ?>
                            <div class="tpshop__widget mb-30 pb-25">
                                <button id="filtreleri-temizle" class="tp-btn w-100" onclick="window.location.href='/urunler'">Tüm Filtreleri Kaldır</button>
                            </div>
                        <?php endif; ?>
                        <!-- Kategori Filtresi -->
                        <div class="tpshop__widget mb-30 pb-25">
                            <h4 class="tpshop__widget-title">Kategoriler</h4>
                            <?php
                            $kategori_slugs = isset($kategori_slug) ? explode(',', $kategori_slug) : [];
                            foreach ($kategoriler as $kategori): ?>
                                <div class="form-check">
                                    <input class="form-check-input kategori-filter" type="checkbox" value="<?= htmlspecialchars($kategori['slug']) ?>"
                                        id="kategori-<?= $kategori['slug'] ?>" <?= in_array($kategori['slug'], $kategori_slugs) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="kategori-<?= $kategori['slug'] ?>">
                                        <?= htmlspecialchars($kategori['kategori_adi']) ?> (<?= $kategori['urun_sayisi'] ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Fiyat Filtresi -->
                        <div class="tpshop__widget mb-30 pb-25">
                            <h4 class="tpshop__widget-title mb-20">Fiyat Aralığı</h4>
                            <div class="productsidebar">
                                <div class="productsidebar__range">
                                    <div id="slider-range"></div>
                                    <div class="price-filter mt-10">
                                        <input type="text" id="amount" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="productsidebar__btn mt-15 mb-15">
                                <button type="button" id="fiyat-filtrele" class="tp-btn">Filtrele</button>
                            </div>
                        </div>

                        <!-- Marka Filtresi -->
                        <div class="tpshop__widget mb-30 pb-25">
                            <h4 class="tpshop__widget-title">Markalar</h4>
                            <?php
                            $marka_ids = isset($marka_id) ? explode(',', $marka_id) : [];
                            foreach ($markalar as $marka): ?>
                                <div class="form-check">
                                    <input class="form-check-input marka-filter" type="checkbox" value="<?= $marka['id'] ?>"
                                        id="marka-<?= $marka['id'] ?>" <?= in_array($marka['id'], $marka_ids) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="marka-<?= $marka['id'] ?>">
                                        <?= htmlspecialchars($marka['marka_adi']) ?> (<?= $marka['urun_sayisi'] ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Derecelendirme Filtresi -->
                        <div class="tpshop__widget">
                            <h4 class="tpshop__widget-title">Derecelendirmeye Göre Filtrele</h4>
                            <?php
                            $yorum_var_mi = false;
                            for ($i = 5; $i >= 1; $i--):
                                $yorum_sayisi = $this->getUrunYorumSayisiByPuan($i);
                                if ($yorum_sayisi > 0) {
                                    $yorum_var_mi = true;
                                }
                            endfor;
                            ?>
                            <?php if ($yorum_var_mi): ?>
                                <?php for ($i = 5; $i >= 1; $i--):
                                    $yorum_sayisi = $this->getUrunYorumSayisiByPuan($i);
                                    if ($yorum_sayisi > 0):
                                ?>
                                        <div class="form-check">
                                            <input class="form-check-input rating-filter" type="checkbox" value="<?= $i ?>" id="rating-<?= $i ?>" <?= $rating == $i ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="rating-<?= $i ?>">
                                                <?php for ($j = 1; $j <= 5; $j++): ?>
                                                    <i class="fa<?= $j <= $i ? 's' : 'r' ?> fa-star"></i>
                                                <?php endfor; ?>
                                                (<?= $yorum_sayisi ?>)
                                            </label>
                                        </div>
                                <?php endif;
                                endfor; ?>
                            <?php else: ?>
                                <p>Henüz yorum bulunmuyor.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Sidebar Banner -->
                        <div class="tpshop__widget">
                            <div class="tpshop__sidbar-thumb mt-35">
                                <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/sidebar-product-1.png" alt="Sidebar Banner">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="tpshop__top ml-60">
                        <!-- Ürün Sayısı ve Sıralama -->
                        <div class="product__filter-content mb-40">
                            <div class="row align-items-center">
                                <div class="col-sm-4">
                                    <div class="product__item-count">
                                        <span>Gösteriliyor <?= $baslangic_urun ?> - <?= $bitis_urun ?> Toplam <?= $toplam_urun ?> Ürün</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="product__navtabs d-flex justify-content-center align-items-center">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <button class="nav-link active" id="nav-grid-tab" data-bs-toggle="tab" data-bs-target="#nav-grid" type="button" role="tab"><i class="fal fa-th"></i></button>
                                                <button class="nav-link" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list" type="button" role="tab"><i class="fal fa-list"></i></button>
                                            </div>
                                        </nav>
                                    </div>
                                </div>
                                <div class="col-sm-4">
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

                        <!-- Ürün Listesi ve Izgara Görünümü -->
                        <div class="tab-content" id="nav-tabContent">
                            <!-- Izgara Görünümü -->
                            <div class="tab-pane fade show active" id="nav-grid" role="tabpanel">
                                <div class="row row-cols-xxl-4 row-cols-xl-4 row-cols-lg-3 row-cols-md-3 row-cols-sm-2 row-cols-1 tpproduct__shop-item">
                                    <?php if (!empty($urunler)): ?>
                                        <?php foreach ($urunler as $urun): ?>
                                            <div class="col">
                                                <div class="tpproduct p-relative mb-20">
                                                    <div class="tpproduct__thumb p-relative text-center">
                                                        <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel']) ?>" alt=""></a>
                                                        <a class="tpproduct__thumb-img" href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel']) ?>" alt=""></a>
                                                        <div class="tpproduct__info bage">
                                                            <span class="tpproduct__info-discount bage__discount">-50%</span>
                                                            <span class="tpproduct__info-hot bage__hot">HOT</span>
                                                        </div>
                                                        <div class="tpproduct__shopping">
                                                            <a title="İstek Listesi" class="istek-listesi-ekle-btn tpproduct__shopping-wishlist" href="javascript::void(0);"><i class="icon-heart icons"></i></a>
                                                            <a class="tpproduct__shopping-wishlist" href="#"><i class="icon-layers"></i></a>
                                                            <a class="tpproduct__shopping-cart" href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><i class="icon-eye"></i></a>
                                                        </div>
                                                    </div>
                                                    <div class="tpproduct__content">
                                                        <span class="tpproduct__content-weight">
                                                            <a href="shop-details-3.html">Fresh Fruits</a>,
                                                            <a href="shop-details-3.html">Vagetables</a>
                                                        </span>
                                                        <h4 class="tpproduct__title">
                                                            <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><?= htmlspecialchars($urun['urun_adi']) ?></a>
                                                        </h4>
                                                        <div class="tpproduct__rating mb-5">
                                                            <?php
                                                            $ortalama_puan = $this->getUrunOrtalamaPuani($urun['id']);
                                                            for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <div class="tpproduct__price">
                                                            <?php
                                                            $min_fiyat = $urun['varyant_var_mi'] ? $urun['min_fiyat'] : $urun['satis_fiyati'];
                                                            $max_fiyat = $urun['varyant_var_mi'] ? $urun['max_fiyat'] : $urun['satis_fiyati'];
                                                            ?>
                                                            <?php if ($urun['varyant_var_mi'] == 1 && $min_fiyat != $max_fiyat): ?>
                                                                <span class="new-price"><?= $this->formatPrice($min_fiyat) ?> - <?= $this->formatPrice($max_fiyat) ?></span>
                                                            <?php else: ?>
                                                                <span class="new-price"><?= $this->formatPrice($min_fiyat) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a class="mt-5 tp-btn-2 sepete-ekle-btn" href="#" data-urun-id="<?= $urun['id'] ?>">Sepete Ekle</a>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="text-center py-5">
                                                <h4>Ürün bulunamadı</h4>
                                                <p>Arama kriterlerinize uygun ürün bulunamadı.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Liste Görünümü -->
                            <div class="tab-pane fade" id="nav-list" role="tabpanel">
                                <?php if (!empty($urunler)): ?>
                                    <?php foreach ($urunler as $urun): ?>
                                        <div class="tplist__product d-flex align-items-center justify-content-between mb-20">
                                            <div class="tplist__product-img">
                                                <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>" class="tplist__product-img-one">
                                                    <img src="<?= htmlspecialchars($urun['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>">
                                                </a>
                                                <div class="tpproduct__info bage">
                                                    <?php // İndirim ve diğer etiketler buraya gelecek 
                                                    ?>
                                                    <span class="tpproduct__info-discount bage__discount">-50%</span>
                                                    <span class="tpproduct__info-hot bage__hot">HOT</span>
                                                </div>
                                            </div>
                                            <div class="tplist__content">
                                                <span class="tpproduct__content-weight">
                                                    <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>">
                                                        <?= htmlspecialchars($urun['marka_adi']) ?>
                                                    </a>
                                                </span>
                                                <h4 class="tplist__content-title"><a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><?= htmlspecialchars($urun['urun_adi']) ?></a></h4>
                                                <div class="tplist__rating mb-5">
                                                    <?php
                                                    $ortalama_puan = $this->getUrunOrtalamaPuani($urun['id']);
                                                    for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <ul class="tplist__content-info">
                                                    <li><?= $urun['kisa_aciklama'] ?></li>
                                                </ul>
                                            </div>
                                            <div class="tplist__price justify-content-end">
                                                <h4 class="tplist__instock">Stok Durumu: <span><?= ($urun['varyant_var_mi'] == 1 ? 'Varyantlı' : ($urun['stok_miktari'] > 0 ? 'Stokta Var' : 'Stokta Yok')) ?></span> </h4>
                                                <h3 class="tplist__count mb-15">
                                                    <?php
                                                    $min_fiyat = $urun['varyant_var_mi'] ? $urun['min_fiyat'] : $urun['satis_fiyati'];
                                                    $max_fiyat = $urun['varyant_var_mi'] ? $urun['max_fiyat'] : $urun['satis_fiyati'];
                                                    ?>
                                                    <?php if ($urun['varyant_var_mi'] == 1 && $min_fiyat != $max_fiyat): ?>
                                                        <span><?= $this->formatPrice($min_fiyat) ?> - <?= $this->formatPrice($max_fiyat) ?></span>
                                                    <?php else: ?>
                                                        <span><?= $this->formatPrice($min_fiyat) ?></span>
                                                    <?php endif; ?>
                                                </h3>
                                                <div class="tplist__shopping d-flex flex-column align-items-end">
                                                    <button class="tp-btn-2 mb-10 sepete-ekle-btn" data-urun-id="<?= $urun['id'] ?>">Sepete Ekle</button>
                                                    <a href="#"><i class="icon-heart icons"></i> İstek Listesi</a>
                                                    <a href="#"><i class="icon-layers"></i> Karşılaştır</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <h4>Ürün bulunamadı</h4>
                                        <p>Arama kriterlerinize uygun ürün bulunamadı.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sayfalama -->
                        <?php if ($toplam_sayfa > 1): ?>
                            <div class="basic-pagination text-center mt-30">
                                <nav>
                                    <ul>
                                        <?php
                                        $query_params = $_GET;
                                        if (isset($query_params['sayfa'])) {
                                            unset($query_params['sayfa']);
                                        }
                                        $query_string = http_build_query($query_params);
                                        $base_url = '/urunler';

                                        if ($sayfa > 1):
                                            $link = $base_url . (empty($query_string) ? '' : '?' . $query_string) . ($sayfa - 1 > 1 ? '&sayfa=' . ($sayfa - 1) : ''); ?>
                                            <li><a href="<?= htmlspecialchars($link) ?>"><i class="fal fa-angle-left"></i></a></li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $toplam_sayfa; $i++):
                                            $link = $base_url . (empty($query_string) ? '' : '?' . $query_string) . ($i > 1 ? '&sayfa=' . $i : '');
                                        ?>
                                            <?php if ($i == $sayfa): ?>
                                                <li><span class="current"><?= $i ?></span></li>
                                            <?php else: ?>
                                                <li><a href="<?= htmlspecialchars($link) ?>"><?= $i ?></a></li>
                                            <?php endif; ?>
                                        <?php endfor; ?>

                                        <?php if ($sayfa < $toplam_sayfa):
                                            $link = $base_url . (empty($query_string) ? '' : '?' . $query_string) . '&sayfa=' . ($sayfa + 1);
                                        ?>
                                            <li><a href="<?= htmlspecialchars($link) ?>"><i class="fal fa-angle-right"></i></a></li>
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
</main>