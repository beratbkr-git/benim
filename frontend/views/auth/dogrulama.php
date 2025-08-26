<?php
// views/auth/dogrulama.php
// Bu sayfa, KayitController'dan gelen verilerle dinamik olarak doldurulur ve
// e-posta doğrulama kodunun girilmesi için kullanılır.

$kalan_sure = max(0, ($_SESSION['kayit_dogrulama']['kod_olusturma_zamani'] ?? 0) + 60 - time());
$tekrar_gonder_bekleme_suresi = max(0, ($_SESSION['kayit_dogrulama']['kod_olusturma_zamani'] ?? 0) + 60 - time());
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
                            <span>E-posta Doğrulama</span>
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
                            <h4>E-posta Doğrulama</h4>
                            <p class="mt-10">E-posta adresinize gönderdiğimiz 6 haneli doğrulama kodunu girin.</p>
                        </div>
                        <div class="account-login-inner">
                            <form action="/kayit/dogrulama" method="POST" id="dogrulama-form">
                                <div class="tpform__input mb-20 d-flex justify-content-center">
                                    <input class="form-control me-2 text-center verification-input" type="text" name="digit1" maxlength="1" id="digit1">
                                    <input class="form-control me-2 text-center verification-input" type="text" name="digit2" maxlength="1" id="digit2">
                                    <input class="form-control me-2 text-center verification-input" type="text" name="digit3" maxlength="1" id="digit3">
                                    <input class="form-control me-2 text-center verification-input" type="text" name="digit4" maxlength="1" id="digit4">
                                    <input class="form-control me-2 text-center verification-input" type="text" name="digit5" maxlength="1" id="digit5">
                                    <input class="form-control text-center verification-input" type="text" name="digit6" maxlength="1" id="digit6">
                                    <input type="hidden" name="dogrulama_kodu" id="dogrulama_kodu_input">
                                </div>
                                <div class="account-button mb-20">
                                    <button type="submit" class="tp-btn-2 w-100">Doğrula</button>
                                </div>
                            </form>

                            <div class="text-center mt-3">
                                <p>Kodu almadınız mı?</p>
                                <button id="tekrar-gonder-btn" class="btn btn-link" onclick="window.location.href='/kayit/tekrarKodGonder'" <?= $tekrar_gonder_bekleme_suresi > 0 ? 'disabled' : '' ?>>
                                    Kodu Tekrar Gönder
                                </button>
                                <p id="geri-sayim-sayac" class="text-muted mt-2" style="<?= $tekrar_gonder_bekleme_suresi > 0 ? '' : 'display: none;' ?>">
                                    Yeni kod göndermek için <span id="kalan-sure"><?= $tekrar_gonder_bekleme_suresi ?></span> saniye bekleyin.
                                </p>
                            </div>
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


        // 6 haneli inputlar için JavaScript
        const inputs = document.querySelectorAll('.verification-input');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenInput();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Kopyala-yapıştır işlevselliği
            input.addEventListener('paste', (e) => {
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                // Sadece ilk inputa yapıştırıldığında çalışsın ve 6 hane olsun
                if (index === 0 && paste.length === 6 && /^\d+$/.test(paste)) {
                    e.preventDefault();
                    for (let i = 0; i < paste.length; i++) {
                        inputs[i].value = paste[i];
                    }
                    updateHiddenInput();
                    inputs[inputs.length - 1].focus();
                }
            });
        });

        function updateHiddenInput() {
            let fullCode = '';
            inputs.forEach(input => {
                fullCode += input.value;
            });
            document.getElementById('dogrulama_kodu_input').value = fullCode;
        }

        // Geri sayım sayacı
        let kalanSure = <?= $kalan_sure ?>;
        const geriSayimEkrani = document.getElementById('geri-sayim-sayac');
        const kalanSureEkrani = document.getElementById('kalan-sure');

        if (kalanSure > 0) {
            geriSayimEkrani.style.display = 'block';
            const interval = setInterval(() => {
                kalanSure--;
                if (kalanSure <= 0) {
                    clearInterval(interval);
                    geriSayimEkrani.style.display = 'none';
                }
                kalanSureEkrani.textContent = kalanSure;
            }, 1000);
        }

        // Tekrar kod gönderme butonu için geri sayım
        let tekrarGonderSure = <?= $tekrar_gonder_bekleme_suresi ?>;
        const tekrarGonderBtn = document.getElementById('tekrar-gonder-btn');
        const kalanSureTekrarEkrani = document.getElementById('kalan-sure-tekrar');

        if (tekrarGonderSure > 0) {
            tekrarGonderBtn.disabled = true;
            const interval2 = setInterval(() => {
                tekrarGonderSure--;
                if (tekrarGonderSure <= 0) {
                    clearInterval(interval2);
                    tekrarGonderBtn.disabled = false;
                }
                // HTML'de tekrar süre göstermeyi de ekleyebilirsin
            }, 1000);
        }
    });
</script>