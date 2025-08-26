<?php
global $site_ayarlari;
?>
<main>
    <!-- slider-area-start -->
    <section class="slider-area tpslider-delay">
        <div class="swiper-container slider-active">
            <div class="swiper-wrapper">
                <?php if (!empty($slider_gorseller)): ?>
                    <?php foreach ($slider_gorseller as $slider): ?>
                        <div class="swiper-slide ">
                            <div class="tpslider pt-90 pb-0 grey-bg" data-background="">
                                <div class="container">
                                    <div class="row align-items-center">
                                        <div class="col-xxl-5 col-lg-6 col-md-6 col-12 col-sm-6">
                                            <div class="tpslider__content pt-20">
                                                <span class="tpslider__sub-title mb-35"><?= htmlspecialchars($site_ayarlari["site_adi"]) ?></span>
                                                <h2 class="tpslider__title mb-30"><?= htmlspecialchars($slider['gorsel_adi']) ?></h2>
                                                <p><?= ($slider['kisa_aciklama']) ?></p>
                                                <div class="tpslider__btn">
                                                    <a class="tp-btn" href="<?= htmlspecialchars($slider['link']) ?>">Şimdi Alışverişe Başla!</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-7 col-lg-6 col-md-6 col-12 col-sm-6">
                                            <div class="tpslider__thumb p-relative pt-15">
                                                <img class="tpslider__thumb-img" src="<?= htmlspecialchars($slider['gorsel_url']) ?>" alt="slider-bg">
                                                <div class="tpslider__shape d-none d-md-block">
                                                    <img class="tpslider__shape-one" src="/<?= FRONTEND_ASSETS_DIR ?>img/slider/178.webp" alt="shape">
                                                    <img class="tpslider__shape-two" src="/<?= FRONTEND_ASSETS_DIR ?>img/slider/372.webp" alt="shape">
                                                    <img class="tpslider__shape-three" src="/<?= FRONTEND_ASSETS_DIR ?>img/slider/228.webp" alt="shape">
                                                    <img class="tpslider__shape-four" src="/<?= FRONTEND_ASSETS_DIR ?>img/slider/215.webp" alt="shape">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="tpslider__arrow d-none  d-xxl-block">
                <button class="tpsliderarrow tpslider__arrow-prv"><i class="icon-chevron-left"></i></button>
                <button class="tpsliderarrow tpslider__arrow-nxt"><i class="icon-chevron-right"></i></button>
            </div>
            <div class="slider-pagination d-xxl-none"></div>
        </div>
    </section>
    <!-- slider-area-end -->


    <!-- feature-area-start -->
    <section class="feature-area whight-feature grey-bg">
        <div class="container">
            <div class="feature-bg-round white-bg pt-50 pb-15">
                <div class="tpfeature-border">
                    <div class="row row-cols-lg-5 row-cols-md-3 row-cols-1">
                        <div class="col">
                            <div class="mainfeature__item text-center mb-45">
                                <div class="mainfeature__icon">
                                    <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-6.svg" alt="">
                                </div>
                                <div class="mainfeature__content">
                                    <h4 class="mainfeature__title">Hızlı Teslimat</h4>
                                    <p>Türkiye Genelinde</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mainfeature__item text-center mb-45">
                                <div class="mainfeature__icon">
                                    <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-7.svg" alt="">
                                </div>
                                <div class="mainfeature__content">
                                    <h4 class="mainfeature__title">Güvenli Ödeme</h4>
                                    <p>%100 Güvenli Ödeme</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mainfeature__item text-center mb-45">
                                <div class="mainfeature__icon">
                                    <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-8.svg" alt="">
                                </div>
                                <div class="mainfeature__content">
                                    <h4 class="mainfeature__title">Online İndirim</h4>
                                    <p>Çoklu Satın Alma İndirimi Ekleme </p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mainfeature__item text-center mb-45">
                                <div class="mainfeature__icon">
                                    <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-9.svg" alt="">
                                </div>
                                <div class="mainfeature__content">
                                    <h4 class="mainfeature__title">Yardım Merkezi</h4>
                                    <p>Özel 7/24 Destek </p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mainfeature__item text-center mb-45">
                                <div class="mainfeature__icon">
                                    <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-10.svg" alt="">
                                </div>
                                <div class="mainfeature__content">
                                    <h4 class="mainfeature__title">Özenle Seçilmiş Ürünler</h4>
                                    <p>Özel Olarak Seçilmiş Satıcılardan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- feature-area-end -->
    <section class="category-area grey-bg pb-40" style="padding-top: 4rem;">
        <div class="container">
            <div class="swiper-container category-active">
                <div class="swiper-wrapper justify-content-center">
                    <?php if (!empty($kategoriler)): ?>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <div class="swiper-slide">
                                <div class="category__item mb-30">
                                    <div class="category__thumb fix mb-15">
                                        <a href="/kategori/<?= htmlspecialchars($kategori['slug']) ?>">
                                            <img src="<?= htmlspecialchars($kategori['kategori_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/category/category-1.jpg') ?>" alt="<?= htmlspecialchars($kategori['kategori_adi']) ?>">
                                        </a>
                                    </div>
                                    <div class="category__content">
                                        <h5 class="category__title">
                                            <a href="/kategori/<?= htmlspecialchars($kategori['slug']) ?>"><?= htmlspecialchars($kategori['kategori_adi']) ?></a>
                                        </h5>
                                        <p><?= $kategori['urun_sayisi'] ?> Ürün</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- product-area-start - En Çok Satan Ürünler -->
    <?php if (!empty($top_selling_products)): ?>
        <section class="weekly-product-area grey-bg whight-product">
            <div class="container">
                <div class="sections__wrapper white-bg pr-50 pl-50">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center">
                            <div class="tpsection mb-15">
                                <h4 class="tpsection__title text-start brand-product-title">En Çok Satan Ürünler</h4>
                                <p class="tpsection__title text-start brand-product-title">Müşterilerimizin en çok tercih ettiği ürünler</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tpproduct__all-item">
                                <a href="/urunler?siralama=populer">Tümünü Görüntüle <i class="icon-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="tpnavtab__area pb-40">
                                <div class="tpproduct__arrow p-relative">
                                    <div class="swiper-container tpproduct-active-2 tpslider-bottom p-relative">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($top_selling_products as $urun): ?>
                                                <div class="swiper-slide">
                                                    <div class="tpproduct p-relative tpprogress__hover">
                                                        <div class="tpproduct__thumb p-relative text-center">
                                                            <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel']) ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp' ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>"></a>
                                                            <div class="tpproduct__info bage">
                                                                <?php if (!empty($urun['indirim_orani'])): // İndirim oranı varsa 
                                                                ?>
                                                                    <span class="tpproduct__info-discount bage__discount">-<?= $urun['indirim_orani'] ?>%</span>
                                                                <?php endif; ?>
                                                                <?php if ($urun['one_cikan_mi']): // Öne çıkan ürünse 
                                                                ?>
                                                                    <span class="tpproduct__info-hot bage__hot">HOT</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="tpproduct__shopping">
                                                                <a class="tpproduct__shopping-wishlist" href="/istek-listesi"><i class="icon-heart icons"></i></a>
                                                                <a class="tpproduct__shopping-cart" href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><i class="icon-eye"></i></a>
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
                                                            <div class="tpproduct__price mb-5">
                                                                <?php if ($urun['min_fiyat'] != $urun['max_fiyat']): ?>
                                                                    <span><?= $this->formatPrice($urun['min_fiyat']) ?> - <?= $this->formatPrice($urun['max_fiyat']) ?></span>
                                                                <?php else: ?>
                                                                    <span><?= $this->formatPrice($urun['min_fiyat']) ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="tpproduct__progress">
                                                                <div class="progress mb-5">
                                                                    <div class="progress-bar" role="progressbar" style="width: <?= ($urun['stok_takibi'] && $urun['stok_miktari'] > 0) ? ($urun['stok_miktari'] / 100) * 100 : 0 ?>%;" aria-valuenow="<?= $urun['stok_miktari'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <span>Satış: <b><?= $urun['toplam_satis'] ?? 0 ?></b></span>
                                                            </div>
                                                        </div>
                                                        <div class="tpproduct__hover-text">
                                                            <div class="tpproduct__hover-btn d-flex justify-content-center mb-10">
                                                                <a class="tp-btn-2 sepete-ekle-btn" href="#" data-urun-id="<?= $urun['id'] ?>">Sepete Ekle</a>
                                                            </div>
                                                            <div class="tpproduct__descrip">
                                                                <ul>
                                                                    <li><?= $urun['kisa_aciklama'] ?></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>



    <!-- product-area-start -->
    <div class="weekly-product-area whight-product grey-bg">
        <div class="container">
            <div class="sections__wrapper white-bg pl-50 pr-50 pb-10">
                <div class="row">
                    <div class="col-md-6">
                        <div class="tpnavtab__area tpnavtab__newitem">
                            <nav>
                                <div class="nav tp-nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link active" id="nav-arrivals-tab" data-bs-toggle="tab" data-bs-target="#nav-arrivals" type="button" role="tab">Yeni Ürünler</button>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tpproduct__all-item">
                            <a href="/urunler">Tümünü Görüntüle <i class="icon-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="tpnavtab__area pb-40">
                            <div class="tab-content" id="nav-tabContent-tp">
                                <div class="tab-pane fade show active" id="nav-arrivals" role="tabpanel">
                                    <div class="tpproduct__arrow p-relative">
                                        <div class="swiper-container tpproduct-active-2 tpslider-bottom p-relative tpproduct-priority">
                                            <div class="swiper-wrapper">
                                                <?php if (!empty($yeni_urunler)): ?>
                                                    <?php foreach ($yeni_urunler as $urun): ?>
                                                        <div class="swiper-slide">
                                                            <div class="tpproduct p-relative">
                                                                <div class="tpproduct__thumb p-relative text-center">
                                                                    <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>"></a>
                                                                    <div class="tpproduct__info bage">
                                                                        <span class="tpproduct__info-discount bage__discount">-50%</span>
                                                                    </div>
                                                                    <div class="tpproduct__shopping">
                                                                        <a class="tpproduct__shopping-wishlist" href="#"><i class="icon-heart icons"></i></a>
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
                                                                            <i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i>
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
                                                                            <li><?= $urun['kisa_aciklama'] ?></li>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- product-area-end -->


    <!-- product-area-start -->
    <div class="weekly-product-area whight-product grey-bg">
        <div class="container">
            <div class="sections__wrapper white-bg pl-50 pr-50 pb-10">
                <div class="row">
                    <div class="col-md-6">
                        <div class="tpnavtab__area tpnavtab__newitem">
                            <nav>
                                <div class="nav tp-nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link active" id="nav-arrivals-tab" data-bs-toggle="tab" data-bs-target="#nav-arrivals" type="button" role="tab">Yeni Ürünler</button>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tpproduct__all-item">
                            <a href="/urunler">Tümünü Görüntüle <i class="icon-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="tpnavtab__area pb-40">
                            <div class="tab-content" id="nav-tabContent-tp">
                                <div class="tab-pane fade show active" id="nav-arrivals" role="tabpanel">
                                    <div class="tpproduct__arrow p-relative">
                                        <div class="swiper-container tpproduct-active-2 tpslider-bottom p-relative tpproduct-priority">
                                            <div class="swiper-wrapper">
                                                <?php if (!empty($yeni_urunler)): ?>
                                                    <?php foreach ($yeni_urunler as $urun): ?>
                                                        <div class="swiper-slide">
                                                            <div class="tpproduct p-relative">
                                                                <div class="tpproduct__thumb p-relative text-center">
                                                                    <a href="/urun/<?= htmlspecialchars($urun['slug']) ?>"><img src="<?= htmlspecialchars($urun['kapak_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/product/placeholder.webp') ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>"></a>
                                                                    <div class="tpproduct__info bage">
                                                                        <span class="tpproduct__info-discount bage__discount">-50%</span>
                                                                    </div>
                                                                    <div class="tpproduct__shopping">
                                                                        <a class="tpproduct__shopping-wishlist" href="#"><i class="icon-heart icons"></i></a>
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
                                                                            <i class="fa<?= $i <= $ortalama_puan ? 's' : 'r' ?> fa-star"></i>
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
                                                                            <li><?= $urun['kisa_aciklama'] ?></li>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- product-area-end -->

    <!-- testimonial-area-start -->
    <section class="testimonial-area pt-60 pb-60">
        <div class="container">
            <div class="testimonial__shape p-relative">
                <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/tree-leaf-4.svg" alt="" class="testimonial__shape-one">
                <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/tree-leaf-5.svg" alt="" class="testimonial__shape-two">
                <img src="/<?= FRONTEND_ASSETS_DIR ?>img/shape/tree-leaf-6.png" alt="" class="testimonial__shape-three">
            </div>
            <div class="swiper-container tptestimonial-active p-relative">
                <div class="swiper-wrapper">
                    <?php if (!empty($yorumlar)): // Örnek için yorum verisi çekildiğini varsayıyoruz 
                    ?>
                        <?php foreach ($yorumlar as $yorum): ?>
                            <div class="swiper-slide">
                                <div class="row justify-content-center p-relative">
                                    <div class="col-md-8">
                                        <div class="tptestimonial__item text-center ">
                                            <div class="tptestimonial__avata mb-25">
                                                <img src="<?= htmlspecialchars($yorum['profil_resmi'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/testimonial/test-avata-1.png') ?>" alt="Müşteri Resmi">
                                            </div>
                                            <div class="tptestimonial__content">
                                                <p><?= nl2br(htmlspecialchars($yorum['yorum'])) ?></p>
                                                <div class="tptestimonial__rating mb-5">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <a href="#"><i class="fa<?= $i <= $yorum['puan'] ? 's' : 'r' ?> fa-star"></i></a>
                                                    <?php endfor; ?>
                                                </div>
                                                <h4 class="tptestimonial__title"><?= htmlspecialchars($yorum['ad_soyad']) ?></h4>
                                                <span class="tptestimonial__avata-position"><?= htmlspecialchars($yorum['firma'] ?? 'Müşteri') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Henüz yorum bulunmuyor.</p>
                    <?php endif; ?>
                </div>
                <div class="tptestimonial-arrow d-none d-md-block">
                    <button class="testi-arrow tptestimonial-arrow-left"><i class="icon-chevron-left"></i></button>
                    <button class="testi-arrow tptestimonial-arrow-right"><i class="icon-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </section>
    <!-- testimonial-area-end -->

    <!-- cart-area-start - Kategori Kartları -->
    <section class="cart-area pt-30">
        <div class="container">
            <div class="swiper-container product-details-active">
                <div class="swiper-wrapper">
                    <?php if (!empty($kategoriler)): ?>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <div class="swiper-slide">
                                <div class="tpcartitem">
                                    <div class="tpcartitem__thumb mb-15">
                                        <a href="/kategori/<?= htmlspecialchars($kategori['slug']) ?>"><img src="<?= htmlspecialchars($kategori['kategori_gorsel'] ?? '/' . FRONTEND_ASSETS_DIR . 'img/cart/cart-1.jpg') ?>" alt="<?= htmlspecialchars($kategori['kategori_adi']) ?>"></a>
                                    </div>
                                    <div class="tpcartitem__content">
                                        <h3 class="tpcartitem__title mb-15"><a href="/kategori/<?= htmlspecialchars($kategori['slug']) ?>"><?= htmlspecialchars($kategori['kategori_adi']) ?></a></h3>
                                        <!-- Alt kategoriler veya popüler ürünler dinamik olarak çekilebilir -->
                                        <ul class="tplist__content-info">
                                            <li><a href="#">Alt Kategori 1</a></li>
                                            <li><a href="#">Alt Kategori 2</a></li>
                                        </ul>
                                        <span class="tpcartitem__all"><a href="/kategori/<?= htmlspecialchars($kategori['slug']) ?>">Tümünü Görüntüle <i class="icon-chevron-right"></i></a></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- cart-area-end -->

    <!-- blog-area-start -->
    <section class="blog-area pb-20 pt-50">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <div class="tpsection mb-15">
                        <h4 class="tpsection__title text-start brand-product-title">Son Blog Yazıları</h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tpproduct__all-item">
                        <a href="/blog">Tümünü Görüntüle <i class="icon-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="swiper-container tpblog-active">
                <div class="swiper-wrapper">
                    <?php if (!empty($blog_yazilari)): // Blog yazıları için varsayılan bir veri çekildiğini varsayıyoruz 
                    ?>
                        <?php foreach ($blog_yazilari as $yazi): ?>
                            <div class="swiper-slide">
                                <div class="tpblog__item">
                                    <div class="tpblog__thumb fix">
                                        <a href="/blog/<?= htmlspecialchars($yazi['slug']) ?>"><img src="<?= htmlspecialchars($yazi['gorsel']) ?>" alt="<?= htmlspecialchars($yazi['baslik']) ?>"></a>
                                    </div>
                                    <div class="tpblog__wrapper">
                                        <div class="tpblog__entry-wap">
                                            <span class="cat-links"><a href="/blog?kategori=<?= htmlspecialchars($yazi['kategori_slug']) ?>"><?= htmlspecialchars($yazi['kategori_adi']) ?></a></span>
                                            <span class="author-by"><a href="#"><?= htmlspecialchars($yazi['yazar_adi']) ?></a></span>
                                            <span class="post-data"><a href="#"><?= date('d F. Y', strtotime($yazi['tarih'])) ?></a></span>
                                        </div>
                                        <h4 class="tpblog__title"><a href="/blog/<?= htmlspecialchars($yazi['slug']) ?>"><?= htmlspecialchars($yazi['baslik']) ?></a></h4>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- blog-area-end -->

</main>