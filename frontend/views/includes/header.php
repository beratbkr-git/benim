<?php
global $p1, $db, $site_ayarlari;

?>

<!doctype html>
<html class="no-js" lang="tr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $title ?? $site_ayarlari['site_baslik'] ?? 'Ketchila E-Ticaret' ?></title>
    <meta name="description" content="<?= $description ?? $site_ayarlari['site_aciklama'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="/<?= FRONTEND_ASSETS_DIR ?>img/logo/favicon.png">

    <!-- CSS here -->
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/animate.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/swiper-bundle.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/slick.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/magnific-popup.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/spacing.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/meanmenu.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/nice-select.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/fontawesome.min.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/icon-dukamarket.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/jquery-ui.css">
    <link rel="stylesheet" href="/<?= FRONTEND_ASSETS_DIR ?>css/main.css">
    <script src="/<?= FRONTEND_ASSETS_DIR ?>js/jquery.js"></script>
    <script src="/<?= FRONTEND_ASSETS_DIR ?>js/toast.js"></script>

    <style>
        .marquee {
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .marquee-content {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 20s linear infinite;
            animation-delay: -7s;
            /* Delay to sync with the clone */
        }

        .marquee-content span {
            display: inline-block;
            margin-right: 100px;
            font-weight: 500;
            color: #fff;
        }

        @keyframes marquee {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(-100%);
            }
        }

        img[data-brt-logo="boyut"] {
            max-width: 41%;
        }
    </style>
    <?php if ($p1 === "hesap") { ?>
        <style>
            /* Orfarm temasıyla uyumlu hesap paneli menüsü */
            .account-sidebar {
                background-color: #fff;
                border: 1px solid #e6eaf0;
                border-radius: 10px;
                padding: 30px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            }

            .account-sidebar__header {
                border-bottom: 1px dashed #e6eaf0;
                padding-bottom: 20px;
            }

            .account-sidebar__header-icon i {
                font-size: 40px;
                color: #96ae00;
            }

            .account-sidebar__header-text h5 {
                font-family: 'Quicksand', sans-serif;
                font-size: 16px;
                font-weight: 500;
                color: #4d5574;
                margin: 0;
            }

            .account-sidebar__header-text h4 {
                font-family: 'Jost', sans-serif;
                font-size: 20px;
                font-weight: 600;
                color: #2d2a6e;
                margin: 5px 0 0;
            }

            .account-nav ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .account-nav ul li a {
                display: block;
                padding: 15px 0;
                color: #4d5574;
                font-size: 16px;
                font-weight: 500;
                border-bottom: 1px dashed #e6eaf0;
                transition: all 0.3s ease-in-out;
            }

            .account-nav ul li a:hover,
            .account-nav ul li a.active {
                color: #96ae00;
                padding-left: 10px;
            }

            .account-nav ul li:last-child a {
                border-bottom: none;
            }

            /* Adres Kartları için CSS */
            .address-box {
                border: 1px solid #e6eaf0;
                border-radius: 10px;
                padding: 25px;
                transition: all 0.3s ease-in-out;
                position: relative;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .address-box.active {
                border-color: #96ae00;
                background-color: #f7f7f9;
                box-shadow: 0 0 15px rgba(150, 174, 0, 0.2);
            }

            .address-box h6 {
                font-family: 'Quicksand', sans-serif;
                font-weight: 700;
                font-size: 16px;
                color: #2d2a6e;
                margin-bottom: 10px;
            }

            .address-box address {
                font-size: 14px;
                color: #4d5574;
                line-height: 1.6;
                margin-bottom: 15px;
            }

            .address-box .address-actions {
                margin-top: auto;
            }

            .address-box .address-actions .btn {
                margin-right: 5px;
            }

            .address-box .badge {
                font-family: 'Jost', sans-serif;
                font-size: 11px;
                font-weight: 600;
                padding: 4px 8px;
                border-radius: 50px;
                color: #fff;
            }

            /* Modal Formları için CSS */
            .modal-content {
                border: none;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .modal-header {
                border-bottom: 1px solid #e6eaf0;
            }

            .modal-title {
                font-family: 'Quicksand', sans-serif;
                font-weight: 700;
                color: #2d2a6e;
            }

            .tpform__input label {
                font-family: 'Jost', sans-serif;
                font-size: 14px;
                font-weight: 500;
                color: #2d2a6e;
                margin-bottom: 5px;
            }

            .tpform__input input,
            .tpform__input textarea {
                width: 100%;
                border: 1px solid #ebeff4;
                border-radius: 5px;
                padding: 12px 15px;
                font-size: 15px;
                color: #4d5574;
                transition: border-color 0.3s;
            }

            .tpform__input input:focus,
            .tpform__input textarea:focus {
                border-color: #96ae00;
                box-shadow: none;
            }

            .tp-btn-2 {
                background-color: #96ae00;
                color: #fff;
                border: none;
                border-radius: 50px;
                font-weight: 600;
            }

            .tp-btn-2:hover {
                background-color: #2d2a6e;
            }
        </style>
    <?php } ?>
</head>

<body>

    <!-- Scroll-top -->
    <button class="scroll-top scroll-to-target" data-target="html">
        <i class="icon-chevrons-up"></i>
    </button>
    <!-- Scroll-top-end-->


    <!-- header-area-start -->
    <header>
        <div class="header__top theme-bg-1 d-none d-md-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-12">
                        <div class="header__top-left">
                            <span><?= $site_ayarlari['ust_bilgi_metni'] ?? 'Ücretsiz kargo 500 TL ve üzeri alışverişlerde!' ?></span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="">
                            <div class="marquee">
                                <div class="marquee-content">
                                    <span>Aynı gün kargo</span>
                                    <span>1000₺ üzeri ücretsiz kargo</span>
                                    <span>Sürpriz kampanyalar</span>
                                    <span>Kapıda ödeme kolaylığı</span>
                                    <span>7/24 müşteri desteği</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="header__top-right d-flex align-items-center">
                            <div class="header__top-link">
                                <a href="/iletisim">İletişim</a>
                                <a href="/siparis-takip">Sipariş Takip</a>
                                <a href="/sss">SSS</a>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header__main-area secondary-header d-none d-xl-block">
            <div class="container">
                <div class="header__for-megamenu p-relative">
                    <div class="row align-items-center header-logo-border">
                        <div class="col-xl-4">
                            <div class="header-three__search">
                                <form action="/urunler" method="GET">
                                    <input type="search" name="q" placeholder="Search products...">
                                    <i class="icon-search"></i>
                                </form>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="header__logo text-center">
                                <a href="/" title="Logo"> <img data-brt-logo="boyut" src="<?= $site_ayarlari['site_logo_url'] ?>" alt="" /></a>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="header__info d-flex align-items-center justify-content-end">
                                <div class="header__info-search tpcolor__purple ml-10">
                                    <button class="tp-search-toggle"><i class="icon-search"></i></button>
                                </div>
                                <div class="header__info-user tpcolor__yellow ml-10">
                                    <a href="/hesap"><i class="icon-user"></i></a>
                                </div>
                                <div class="header__info-wishlist tpcolor__greenish ml-10">
                                    <a href="/istek-listesi"><i class="icon-heart"></i></a>
                                </div>
                                <div class="header__info-cart tpcolor__oasis ml-10 tp-cart-toggle">
                                    <button><i><img src="<?= FRONTEND_ASSETS_DIR ?>img/icon/cart-1.svg" alt=""></i>
                                        <span>5</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-xxl-6 col-xl-8">
                            <div id="header-sticky" class="header__menu main-menu mainmenu-three text-center">
                                <nav id="mobile-menu">
                                    <ul>
                                        <?php
                                        $menucek = $db->fetchAll("SELECT * FROM bt_menuler WHERE durum = 'Aktif' ORDER BY sira ASC");

                                        foreach ($menucek as $menu) {

                                            if ($menu['ust_menu_id'] == 0 && $menu["menuturu"] != 'Dropdown' && $menu["menuturu"] != 'megamenu') {
                                                echo '<li><a href="' . $menu['url'] . '">' . $menu['menu_adi'] . '</a></li>';
                                            } else if ($menu["menuturu"] == 'Dropdown' && $menu['ust_menu_id'] == 0) { ?>
                                                <li class="has-dropdown">
                                                    <a href="<?= $menu['url'] ?>"><?= $menu['menu_adi'] ?></a>
                                                    <?php
                                                    $alt_menuler = $db->fetchAll(
                                                        "SELECT * FROM bt_menuler WHERE alt_menu = :alt_menu AND durum = 'Aktif' ORDER BY sira ASC",
                                                        ['alt_menu' => $menu['id']]
                                                    );
                                                    if (!empty($alt_menuler)) { ?>
                                                        <ul class="submenu">
                                                            <?php foreach ($alt_menuler as $alt_menu) { ?>
                                                                <li><a href="<?= $alt_menu['url'] ?>"><?= $alt_menu['menu_adi'] ?></a></li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php } ?>
                                                </li>
                                            <?php
                                            } else if ($menu["menuturu"] == 'megamenu' && $menu['ust_menu_id'] == 0) { ?>
                                                <li class="has-dropdown has-homemenu">
                                                    <a href="<?= $menu['url'] ?>"><?= $menu['menu_adi'] ?></a>
                                                    <ul class="sub-menu home-menu-style">
                                                        <?php
                                                        $menu_kategoriler = $db->fetchAll("SELECT * FROM bt_kategoriler WHERE durum = :durum ORDER BY :sira ASC", ['durum' => 'Aktif', 'sira' => 'sira']);
                                                        foreach ($menu_kategoriler as $kategori) { ?>
                                                            <li>
                                                                <a href="/kategori/<?= $kategori['slug'] ?>">
                                                                    <img src="<?= $kategori['gorsel_url'] ?? '/' . FRONTEND_ASSETS_DIR . '' ?>" alt="<?= $kategori['kategori_adi'] ?>">
                                                                    <?= $kategori['kategori_adi'] ?>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                        <?php
                                            }
                                        }
                                        ?>



                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tpsearchbar tp-sidebar-area">
            <button class="tpsearchbar__close"><i class="icon-x"></i></button>
            <div class="search-wrap text-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-6 pt-100 pb-100">
                            <h2 class="tpsearchbar__title">Ne Arıyorsunuz?</h2>
                            <div class="tpsearchbar__form">
                                <form action="/urunler" method="GET">
                                    <input type="text" name="q" placeholder="Ürün Adı...">
                                    <button class="tpsearchbar__search-btn"><i class="icon-search"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="search-body-overlay"></div>
        <!-- header-search-end -->

        <!-- header-cart-start -->
        <?php include FRONTEND_VIEW_DIR . 'includes/sepet-popup.php'; ?>

        <!-- header-cart-end -->



        <!-- header-cart-end -->

        <!-- mobile-menu-area -->
        <div id="header-sticky-2" class="tpmobile-menu secondary-mobile-menu d-xl-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-4 col-3 col-sm-3">
                        <div class="mobile-menu-icon">
                            <button class="tp-menu-toggle"><i class="icon-menu1"></i></button>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-6 col-sm-4">
                        <div class="header__logo text-center">
                            <a href="index.html"><img data-brt-logo="boyut" src="<?= $site_ayarlari["site_logo_url"] ?>" alt="logo"></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-3 col-sm-5">
                        <div class="header__info d-flex align-items-center">
                            <div class="header__info-search tpcolor__purple ml-10 d-none d-sm-block">
                                <button class="tp-search-toggle"><i class="icon-search"></i></button>
                            </div>
                            <div class="header__info-user tpcolor__yellow ml-10 d-none d-sm-block">
                                <a href="#"><i class="icon-user"></i></a>
                            </div>
                            <div class="header__info-wishlist tpcolor__greenish ml-10 d-none d-sm-block">
                                <a href="#"><i class="icon-heart icons"></i></a>
                            </div>
                            <div class="header__info-cart tpcolor__oasis ml-10 tp-cart-toggle">
                                <button><i><img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/cart-1.svg" alt=""></i>
                                    <span id="sepet-adet">5</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="body-overlay"></div>
        <!-- mobile-menu-area-end -->

        <!-- sidebar-menu-area -->
        <div class="tpsideinfo">
            <button class="tpsideinfo__close">Close<i class="fal fa-times ml-10"></i></button>
            <div class="tpsideinfo__search text-center pt-35">
                <span class="tpsideinfo__search-title mb-20">What Are You Looking For?</span>
                <form action="#">
                    <input type="text" placeholder="Search Products...">
                    <button><i class="icon-search"></i></button>
                </form>
            </div>
            <div class="tpsideinfo__nabtab">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Menu</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Categories</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                        <div class="mobile-menu"></div>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                        <div class="tpsidebar-categories">
                            <ul>
                                <li><a href="shop-details.html">Dairy Farm</a></li>
                                <li><a href="shop-details.html">Healthy Foods</a></li>
                                <li><a href="shop-details.html">Lifestyle</a></li>
                                <li><a href="shop-details.html">Organics</a></li>
                                <li><a href="shop-details.html">Photography</a></li>
                                <li><a href="shop-details.html">Shopping</a></li>
                                <li><a href="shop-details.html">Tips & Tricks</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tpsideinfo__account-link">
                <a href="log-in.html"><i class="icon-user icons"></i> Login / Register</a>
            </div>
            <div class="tpsideinfo__wishlist-link">
                <a href="wishlist.html" target="_parent"><i class="icon-heart"></i> Wishlist</a>
            </div>
        </div>
        <!-- sidebar-menu-area-end -->
    </header>
    <!-- header-area-end -->
    <script>
        setTimeout(function() {

            <?php if (isset($_SESSION['hata']) && !empty($_SESSION['hata'])): ?>
                if (typeof brtToast !== 'undefined') {
                    brtToast.error('<?= str_replace(["\n", "\r"], '', $_SESSION['hata']) ?>');
                    <?php unset($_SESSION['hata']); ?>
                }
            <?php endif; ?>
            <?php if (isset($_SESSION['basari']) && !empty($_SESSION['basari'])): ?>
                if (typeof brtToast !== 'undefined') {
                    brtToast.success('<?= str_replace(["\n", "\r"], '', $_SESSION['basari']) ?>');
                    <?php unset($_SESSION['basari']); ?>
                }
            <?php endif; ?>
        }, 200);
    </script>