<?php
// views/auth/kayit.php
// Bu sayfa, KayitController'dan gelen verilerle dinamik olarak doldurulur ve
// Orfarm temasının log-in.html dosyası baz alınarak yeniden düzenlenmiştir.
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
                            <span>Kayıt Ol</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- register-area-start -->
    <div class="login-area pt-80 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="account-login">
                        <div class="account-login-header mb-25 text-center">
                            <h4>Yeni Hesap Oluşturun</h4>
                            <p class="mt-10">Zaten bir hesabınız var mı? <a href="/giris">Giriş Yap</a></p>
                        </div>
                        <div class="account-login-inner">
                            <form action="/kayit" method="POST" id="kayit-form">
                                <?php if (isset($_SESSION["hata"])) { ?>
                                    <?= str_replace(["\n", "\r"], '', $_SESSION['hata']) ?>
                                <?php } ?>
                                <div class="tpform__input mb-20">
                                    <input type="text" name="ad_soyad" id="ad_soyad" placeholder="Adınız Soyadınız *" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <input type="email" name="eposta" id="eposta" placeholder="E-posta adresiniz *" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <input type="password" name="parola" id="parola" placeholder="Parolanız *" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <input type="password" name="parola_tekrar" id="parola_tekrar" placeholder="Parola tekrarı *" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <input type="tel" name="telefon" class="format_phone" id="telefon" placeholder="Telefon numaranız">
                                </div>
                                <div class="account-remember d-flex justify-content-between mb-20">
                                    <div class="account-remember-check">
                                        <input class="form-check-input" type="checkbox" name="sartlar" id="sartlar" required>
                                        <label for="sartlar">Kullanım şartlarını ve gizlilik politikasını kabul ediyorum.</label>
                                    </div>
                                </div>
                                <div class="account-button mb-20">
                                    <button type="submit" class="tp-btn-2 w-100">Kayıt Ol</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- register-area-end -->
</main>