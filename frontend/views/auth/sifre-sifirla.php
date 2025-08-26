<?php
// views/auth/sifre-sifirla.php
// Bu sayfa, kullanıcının yeni bir parola belirlemesi için kullanılır.
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
                            <span>Şifre Sıfırlama</span>
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
                            <h4>Yeni Parola Oluştur</h4>
                            <p class="mt-10">Lütfen hesabınız için yeni bir parola belirleyin.</p>
                        </div>
                        <div class="account-login-inner">
                            <form action="/sifremi-unuttum/sifirla" method="POST" id="sifre-sifirla-form">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <input type="hidden" name="eposta" value="<?= htmlspecialchars($eposta) ?>">
                                <div class="tpform__input mb-20">
                                    <label for="yeni_parola">Yeni Parola</label>
                                    <input type="password" name="yeni_parola" id="yeni_parola" placeholder="Yeni parolanızı girin" required>
                                </div>
                                <div class="tpform__input mb-20">
                                    <label for="yeni_parola_tekrar">Yeni Parola Tekrar</label>
                                    <input type="password" name="yeni_parola_tekrar" id="yeni_parola_tekrar" placeholder="Yeni parolanızı tekrar girin" required>
                                </div>
                                <div class="account-button mb-20">
                                    <button type="submit" class="tp-btn-2 w-100">Parolayı Güncelle</button>
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