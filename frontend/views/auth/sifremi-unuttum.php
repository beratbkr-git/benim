<?php
// views/auth/sifremi-unuttum.php
// Bu sayfa, şifre sıfırlama e-posta gönderim formunu içerir.
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
                            <span>Şifremi Unuttum</span>
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
                            <h4>Şifremi Unuttum</h4>
                            <p class="mt-10">Kayıtlı e-posta adresinizi girin, şifre sıfırlama linki göndereceğiz.</p>
                        </div>
                        <div class="account-login-inner">
                            <form action="/sifremi-unuttum" method="POST" id="sifre-unuttum-form">
                                <div class="tpform__input mb-20">
                                    <label for="eposta">E-posta</label>
                                    <input type="email" name="eposta" id="eposta" placeholder="E-posta adresiniz" required>
                                </div>
                                <div class="account-button mb-20">
                                    <button type="submit" class="tp-btn-2 w-100">Şifremi Sıfırla</button>
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