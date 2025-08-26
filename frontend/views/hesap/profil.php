<?php
// views/hesap/profil.php
// Bu sayfa, kullanıcının profil bilgilerini yönetir.
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
                            <span class="tp-breadcrumb__active"><a href="/hesap">Hesabım</a></span>
                            <span class="dvdr">/</span>
                            <span>Profil Bilgilerim</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcrumb-area-end -->

    <!-- account-area-start -->
    <section class="account-area pt-80 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <div class="account-sidebar">
                        <div class="account-sidebar__header mb-20">
                            <div class="account-sidebar__header-icon">
                                <i class="icon-user"></i>
                            </div>
                            <div class="account-sidebar__header-text">
                                <h5>Hoş Geldiniz,</h5>
                                <h4><?= htmlspecialchars($musteri['ad_soyad']) ?></h4>
                            </div>
                        </div>
                        <div class="account-nav">
                            <nav>
                                <ul>
                                    <li><a href="/hesap">Hesap Paneli</a></li>
                                    <li><a href="/hesap/profil" class="active">Profil Bilgilerim</a></li>
                                    <li><a href="/hesap/siparisler">Siparişlerim</a></li>
                                    <li><a href="/hesap/adresler">Adres Defteri</a></li>
                                    <li><a href="/cikis">Çıkış Yap</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="account-dashboard">
                        <div class="account-dashboard__content">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="profile-info-box mb-30">
                                        <h5>Profil Bilgilerim</h5>
                                        <form action="/hesap/profil" method="POST">
                                            <div class="tpform__input mb-20">
                                                <label for="ad_soyad">Ad Soyad</label>
                                                <input type="text" name="ad_soyad" id="ad_soyad" value="<?= htmlspecialchars($musteri['ad_soyad']) ?>" required>
                                            </div>
                                            <div class="tpform__input mb-20">
                                                <label for="eposta">E-posta Adresi</label>
                                                <input type="email" name="eposta" id="eposta" value="<?= htmlspecialchars($musteri['eposta']) ?>" disabled>
                                            </div>
                                            <div class="tpform__input mb-20">
                                                <label for="telefon">Telefon</label>
                                                <input type="tel" name="telefon" id="telefon" value="<?= htmlspecialchars($musteri['telefon'] ?? '') ?>">
                                            </div>
                                            <div class="tpform__input mb-20">
                                                <label for="dogum_tarihi">Doğum Tarihi</label>
                                                <input type="date" name="dogum_tarihi" id="dogum_tarihi" value="<?= htmlspecialchars($musteri['dogum_tarihi'] ?? '') ?>">
                                            </div>
                                            <div class="tpform__input mb-20">
                                                <label for="cinsiyet">Cinsiyet</label>
                                                <select name="cinsiyet" id="cinsiyet">
                                                    <option value="">Seçiniz</option>
                                                    <option value="Erkek" <?= ($musteri['cinsiyet'] ?? '') === 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                                                    <option value="Kadın" <?= ($musteri['cinsiyet'] ?? '') === 'Kadın' ? 'selected' : '' ?>>Kadın</option>
                                                    <option value="Belirtmek İstemiyorum" <?= ($musteri['cinsiyet'] ?? '') === 'Belirtmek İstemiyorum' ? 'selected' : '' ?>>Belirtmek İstemiyorum</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="tp-btn-2">Bilgileri Güncelle</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-info-box mb-30">
                                        <h5>Parola Değiştirme</h5>
                                        <form action="/hesap/profil" method="POST">
                                            <div class="tpform__input mb-20">
                                                <label for="yeni_parola">Yeni Parola</label>
                                                <input type="password" name="yeni_parola" id="yeni_parola">
                                            </div>
                                            <div class="tpform__input mb-20">
                                                <label for="yeni_parola_tekrar">Yeni Parola Tekrar</label>
                                                <input type="password" name="yeni_parola_tekrar" id="yeni_parola_tekrar">
                                            </div>
                                            <button type="submit" class="tp-btn-2">Parolayı Güncelle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- account-area-end -->
</main>