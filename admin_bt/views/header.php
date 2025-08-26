<?php

// Bu dosyanın dışarıdan doğrudan erişilmesini engeller.
// yonetim.php içinde require_once ile çağrıldığı için burada tekrar güvenlik kontrolü yapmıyoruz.
// Yetki kontrolü için $_SESSION["kullanici"] verisi kullanılır
$current_user_role = $_SESSION['kullanici']['yetki_seviyesi'] ?? 'Misafir';
$current_user_name = $_SESSION['kullanici']['ad_soyad'] ?? 'Bilinmeyen Kullanıcı';
$current_user_profile_img = $_SESSION['kullanici']['profil_resmi'] ?? '/admin_bt/assets/img/profile/profile-1.webp'; // Varsayılan profil resmi
// Global site ayarları (config.php'den geliyor)
global $site_ayarlari;
global $yonetimurl; // index.php'den geliyor
global $p1, $p2, $p3, $p4; // URL parametreleri (header.php'de menü aktifliği için kullanılıyor)
?>
<!DOCTYPE html>
<html lang="tr" data-footer="true">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title><?= $site_ayarlari['site_adi'] ?? 'Site Adı' ?> Yönetim Paneli</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/admin_bt/assets/font/CS-Interface/style.css" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/OverlayScrollbars.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/tagify.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/datatables.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/bootstrap-datepicker3.standalone.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/dropzone.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/select2.min.css" />
    <link rel="stylesheet" href="/admin_bt/assets/css/vendor/select2-bootstrap4.min.css" />
    <!-- Vendor Styles End -->
    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="/admin_bt/assets/css/styles.css" />
    <!-- Template Base Styles End -->
    <link rel="stylesheet" href="/admin_bt/assets/css/main.css" />
    <script src="/admin_bt/assets/js/base/loader.js"></script>
    <script src="/frontend/assets/js/toast.js"></script>
    <script src="/admin_bt/assets/js/vendor/jquery-3.5.1.min.js"></script>
    <style>
        #datatableRows tbody tr td div {
            z-index: 9999999999999999 !important;
        }
    </style>
</head>

<div id="root">
    <div id="nav" class="nav-container d-flex">
        <div class="nav-content d-flex">
            <!-- Logo Start -->
            <div class="logo position-relative">
                <a href="/<?= $yonetimurl ?>">
                    <div class="">
                        <img src="<?= $site_ayarlari['site_logo_url'] ?? '/admin_bt/assets/img/logo/logo-light.svg' ?>" alt="" />
                    </div>
                </a>
            </div>
            <!-- Logo End -->
            <!-- User Menu Start -->
            <div class="user-container d-flex">
                <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="profile" alt="<?= $current_user_name ?>" src="<?= $current_user_profile_img ?>" onerror="this.onerror=null;this.src='/admin_bt/assets/img/profile/profile-1.webp';" />
                    <div class="name"><?= $current_user_name ?></div>
                </a>
                <div class="dropdown-menu dropdown-menu-end user-menu wide">
                    <div class="row mb-3 ms-0 me-0">
                        <div class="col-12 ps-1 mb-2">
                            <div class="text-extra-small text-primary">HESABIM</div>
                        </div>
                        <div class="col-12 ps-1 pe-1">
                            <ul class="list-unstyled row">
                                <li class="col-md-6">
                                    <a href="/<?= $yonetimurl ?>/kullanicilar/duzenle/<?= $_SESSION['kullanici']['id'] ?? '' ?>">
                                        <i data-acorn-icon="user" class="me-2" data-acorn-size="17"></i>
                                        <span class="align-middle">Profilim</span>
                                    </a>
                                </li>
                                <?php if (hasPermission('Admin')) { ?>
                                    <li class="col-md-6">
                                        <a href="/<?= $yonetimurl ?>/ayarlar/genel">
                                            <i data-acorn-icon="gear" class="me-2" data-acorn-size="17"></i>
                                            <span class="align-middle">Ayarlar</span>
                                        </a>
                                    </li>
                                <?php } ?>
                                <?php if (hasPermission('Admin')) { ?>
                                    <hr class="mb-3">
                                    <li class="col-md-6">
                                        <a href="/<?= $yonetimurl ?>/addons">
                                            <i data-acorn-icon="puzzle" class="me-2" data-acorn-size="17"></i>
                                            <span class="align-middle">Eklentiler</span>
                                        </a>
                                    </li>
                                    <li class="col-md-6">
                                        <a href="/<?= $yonetimurl ?>/araclar">
                                            <i data-acorn-icon="tool" class="me-2" data-acorn-size="17"></i>
                                            <span class="align-middle">Araçlar</span>
                                        </a>
                                    </li>
                                <?php } ?>
                                <hr class="mb-3">
                                <li class="col-md-12 text-center">
                                    <a href="/<?= $yonetimurl ?>/cikis">
                                        <i data-acorn-icon="logout" class="me-2" data-acorn-size="17"></i>
                                        <span class="align-middle">Çıkış Yap</span>
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
            <!-- User Menu End -->
            <!-- Icons Menu Start -->
            <ul class="list-unstyled list-inline text-center menu-icons">
                <li class="list-inline-item">
                    <a href="#" id="pinButton" class="pin-button">
                        <i data-acorn-icon="lock-on" class="unpin" data-acorn-size="18"></i>
                        <i data-acorn-icon="lock-off" class="pin" data-acorn-size="18"></i>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="colorButton">
                        <i data-acorn-icon="light-on" class="light" data-acorn-size="18"></i>
                        <i data-acorn-icon="light-off" class="dark" data-acorn-size="18"></i>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" data-bs-toggle="dropdown" data-bs-target="#notifications" aria-haspopup="true" aria-expanded="false" class="notification-button">
                        <div class="position-relative d-inline-flex">
                            <i data-acorn-icon="bell" data-acorn-size="18"></i>
                            <span class="position-absolute notification-dot rounded-xl"></span>
                            <span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"></span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end wide notification-dropdown scroll-out" id="notifications">
                        <div class="scroll">
                            <ul class="list-unstyled border-last-none" id="notification-list">
                                <!-- Bildirimler buraya dinamik olarak eklenecek -->
                            </ul>
                        </div>
                        <div class="text-center">
                            <a href="/<?= $yonetimurl ?>/bildirimler/liste" class="text-small text-muted text-uppercase">Tüm Bildirimleri Görüntüle</a>
                        </div>
                    </div>
                </li>
            </ul>
            <!-- Icons Menu End -->
            <!-- Menu Start -->
            <div class="menu-container flex-grow-1">
                <ul id="menu" class="menu">
                    <!-- E-TİCARET MENÜSÜ BAŞLANGIÇ -->
                    <?php if (hasPermission('Editör')) { ?>
                        <li class="<?= ($p2 === 'anasayfa' || $p2 === null) ? 'active' : '' ?>">
                            <a href="/<?= $yonetimurl ?>/anasayfa">
                                <i data-acorn-icon="home" class="icon" data-acorn-size="18"></i>
                                <span class="label">Panel Anasayfa</span>
                            </a>
                        </li>
                        <!-- Mega Menü: E-Ticaret Yönetimi -->
                        <li class="mega">
                            <a href="#ecommerceMenu" data-href="javascript:void(0);">
                                <i data-acorn-icon="grid-3" class="icon" data-acorn-size="18"></i>
                                <span class="label">E-Ticaret Yönetimi</span>
                            </a>
                            <ul id="ecommerceMenu">
                                <li>
                                    <a href="#urunlerMenu" data-href="/<?= $yonetimurl ?>/urunler">
                                        <i data-acorn-icon="shop" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Ürün Yönetimi</span>
                                    </a>
                                    <ul id="urunlerMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/urunler/liste">
                                                <span class="label">Ürünler</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/urunler/ekle">
                                                <span class="label">Yeni Ürün Ekle</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/kategoriler/liste">
                                                <span class="label">Kategoriler</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/markalar/liste">
                                                <span class="label">Markalar</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/varyantlar/liste">
                                                <span class="label">Varyant Ayarları</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/urun-yorumlari/liste">
                                                <span class="label">Ürün Yorumları</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#siparislerMenu" data-href="/<?= $yonetimurl ?>/siparisler">
                                        <i data-acorn-icon="cart" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Sipariş Yönetimi</span>
                                    </a>
                                    <ul id="siparislerMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/siparisler/liste">
                                                <span class="label">Tüm Siparişler</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/siparisler/iade">
                                                <span class="label">İade/İptal Takibi</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#kargolarMenu" data-href="/<?= $yonetimurl ?>/kargolar">
                                        <i data-acorn-icon="cart" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Kargo Bilgileri</span>
                                    </a>
                                    <ul id="kargolarMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/kargolar/kargo-yontemleri-liste">
                                                <span class="label">Kargo Yöntemleri</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/kargolar/kargo-firmalari-liste">
                                                <span class="label">Kargo Firmaları</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#musteriMenu" data-href="/<?= $yonetimurl ?>/musteriler">
                                        <i data-acorn-icon="users" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Müşteri & Bayi</span>
                                    </a>
                                    <ul id="musteriMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/musteriler/liste">
                                                <span class="label">Müşteriler</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/musteri-segmentleri/liste">
                                                <span class="label">Müşteri Segmentleri</span>
                                            </a>
                                        </li>
                                        <?php if (hasPermission('Yönetici')) { ?>
                                            <li>
                                                <a href="/<?= $yonetimurl ?>/bayiler/liste">
                                                    <span class="label">Bayiler</span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <li>
                                    <a href="#envanterMenu" data-href="/<?= $yonetimurl ?>/envanter">
                                        <i data-acorn-icon="box" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Envanter & Stok</span>
                                    </a>
                                    <ul id="envanterMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/envanter/stok-hareketleri">
                                                <span class="label">Stok Hareketleri</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/envanter/dusuk-stok-uyarilari">
                                                <span class="label">Düşük Stok Uyarıları</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/envanter/satin-alma-liste">
                                                <span class="label">Satın Alma Siparişleri</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/envanter/tedarikciler-liste">
                                                <span class="label">Tedarikçiler</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#pazarlamaMenu" data-href="/<?= $yonetimurl ?>/kampanyalar">
                                        <i data-acorn-icon="gift" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Pazarlama</span>
                                    </a>
                                    <ul id="pazarlamaMenu">
                                        <!-- <li>
                                    <a href="/<?= $yonetimurl ?>/kampanyalar/liste">
                                        <span class="label">Kampanyalar</span>
                                    </a>
                                </li> -->
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/kampanyalar/kuponlar-liste">
                                                <span class="label">Kuponlar</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="/<?= $yonetimurl ?>/anasayfa">
                                        <i data-acorn-icon="chart-2" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Raporlar & Analitik</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <!-- E-TİCARET MENÜSÜ BİTİŞ -->
                    <!-- İÇERİK YÖNETİMİ BAŞLANIG -->
                    <?php if (hasPermission('Editör')) { ?>

                        <!-- Mega Menü: E-Ticaret Yönetimi -->
                        <li class="">
                            <a href="#icerikMenu" data-href="javascript:void(0);">
                                <i data-acorn-icon="grid-3" class="icon" data-acorn-size="18"></i>
                                <span class="label">İçerik Kontrolleri</span>
                            </a>
                            <ul id="icerikMenu">
                                <li>
                                    <a href="#sayfalarMenu" data-href="/<?= $yonetimurl ?>/sayfalar">
                                        <i data-acorn-icon="shop" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Sayfa Yönetimi</span>
                                    </a>
                                    <ul id="sayfalarMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/sayfalar/liste">
                                                <span class="label">Sayfalar Liste</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/sayfalar/ekle">
                                                <span class="label">Yeni Sayfa Ekle</span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>
                                <li>
                                    <a href="#menulerMenu" data-href="/<?= $yonetimurl ?>/menuler">
                                        <i data-acorn-icon="shop" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Menü Yönetimi</span>
                                    </a>
                                    <ul id="menulerMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/menuler/liste">
                                                <span class="label">Menüler Liste</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/menuler/ekle">
                                                <span class="label">Yeni Menü Ekle</span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>
                                <li>
                                    <a href="#gorselMenu" data-href="/<?= $yonetimurl ?>/gorseller">
                                        <i data-acorn-icon="shop" class="icon" data-acorn-size="18"></i>
                                        <span class="label">Görsel İşlemleri</span>
                                    </a>
                                    <ul id="gorselMenu">
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/gorseller/liste">
                                                <span class="label">Görsel Liste</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/<?= $yonetimurl ?>/gorseller/ekle">
                                                <span class="label">Yeni Görsel Ekle</span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <!-- İÇERİK YONETİMİ BİTİS -->
                    <!-- YÖNETİM VE SİSTEM MENÜLERİ -->
                    <?php if (hasPermission('Admin')) { ?>
                        <li class="">
                            <a href="#kullanicilar">
                                <i data-acorn-icon="user" class="icon" data-acorn-size="18"></i>
                                <span class="label">Panel Kullanıcıları</span>
                            </a>
                            <ul id="kullanicilar">
                                <li>
                                    <a href="/<?= $yonetimurl ?>/kullanicilar/liste">
                                        <span class="label">Tüm Kullanıcılar</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/<?= $yonetimurl ?>/kullanicilar/ekle">
                                        <span class="label">Kullanıcı Ekle</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (hasPermission('Yönetici')) { ?>
                        <li class="active">
                            <a href="#ayarlarMenu" data-href="/<?= $yonetimurl ?>/ayarlar">
                                <i data-acorn-icon="gear" class="icon" data-acorn-size="18"></i>
                                <span class="label">Ayarlar</span>
                            </a>
                            <ul id="ayarlarMenu">
                                <li>
                                    <a href="/<?= $yonetimurl ?>/ayarlar/genel">
                                        <span class="label">Genel Ayarlar</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/<?= $yonetimurl ?>/ayarlar/eticaret">
                                        <span class="label">E-ticaret</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/<?= $yonetimurl ?>/ayarlar/odeme">
                                        <span class="label">Ödeme Yöntemleri</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="/<?= $yonetimurl ?>/ayarlar/eposta">
                                        <span class="label">E-posta Ayarları</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/<?= $yonetimurl ?>/eposta-sablonlari/liste">
                                        <span class="label">E-posta Şablonları</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <li>
                        <a href="/<?= $yonetimurl ?>/cikis">
                            <i data-acorn-icon="logout" class="icon" data-acorn-size="18"></i>
                            <span class="label">Çıkış</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Menu End -->
            <!-- Mobile Buttons Start -->
            <div class="mobile-buttons-container">
                <!-- Search Button Start -->
                <a href="#" class="search-button mobile-search-button d-lg-none">
                    <i data-acorn-icon="search"></i>
                </a>
                <!-- Search Button End -->
                <!-- Scrollspy Mobile Button Start -->
                <a href="#" id="scrollSpyButton" class="spy-button" data-bs-toggle="dropdown">
                    <i data-acorn-icon="menu-dropdown"></i>
                </a>
                <!-- Scrollspy Mobile Dropdown End -->
                <!-- Menu Button Start -->
                <a href="#" id="mobileMenuButton" class="menu-button">
                    <i data-acorn-icon="menu"></i>
                </a>
                <!-- Menu Button End -->
            </div>
            <!-- Mobile Buttons End -->
        </div>
        <div class="nav-shadow"></div>
    </div>
    <!-- Alert Messages -->
    <?php if (isset($_SESSION["basari"]) || isset($_SESSION["hata"])): ?>
        <div class="container-fluid" style="position: relative;  padding-top: 85px;">
            <div class="container">
                <?php if (isset($_SESSION["basari"])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION["basari"]; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION["basari"]); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION["hata"])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION["hata"]; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION["hata"]); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    </header>