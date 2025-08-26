<?php
// views/auth/giris.php
// Bu sayfa, GirisController'dan gelen verilerle dinamik olarak doldurulur ve
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
                            <span>Giriş Yap</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- login-area-start -->
    <div class="login-area pt-80 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="account-login">
                        <div class="account-login-header mb-25 text-center">
                            <h4>Hesabınıza Giriş Yapın</h4>
                            <p class="mt-10">Henüz bir hesabınız yok mu? <a href="/kayit">Kayıt Ol</a></p>
                        </div>
                        <div class="account-login-inner">
                            <form action="/giris" method="POST" id="giris-form">
                                <div class="tpform__input mb-20">
                                    <input type="email" name="eposta" id="eposta" placeholder="E-posta adresiniz *" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <input type="password" name="parola" id="parola" placeholder="Parolanız *" required>
                                </div>
                                <div class="account-remember d-flex justify-content-between mb-20">
                                    <div class="account-remember-check">
                                        <input class="form-check-input" type="checkbox" name="beni_hatirla" id="beni_hatirla">
                                        <label for="beni_hatirla">Beni Hatırla</label>
                                    </div>
                                    <div class="account-remember-forget">
                                        <a href="/sifremi-unuttum">Şifrenizi mi unuttunuz?</a>
                                    </div>
                                </div>
                                <div class="account-button mb-20">
                                    <button type="submit" class="tp-btn-2 w-100">Giriş Yap</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- login-area-end -->
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (isset($_SESSION['hata']) && !empty($_SESSION['hata'])): ?>
            if (typeof brtToast !== 'undefined') {
                brtToast.error('<?= $_SESSION['hata'] ?>');
                <?php unset($_SESSION['hata']); ?>
            }
        <?php endif; ?>
        <?php if (isset($_SESSION['basari']) && !empty($_SESSION['basari'])): ?>
            if (typeof brtToast !== 'undefined') {
                brtToast.success('<?= $_SESSION['basari'] ?>');
                <?php unset($_SESSION['basari']); ?>
            }
        <?php endif; ?>
    });
</script>