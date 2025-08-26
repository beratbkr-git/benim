<!-- breadcrumb-area-start -->
<div class="breadcrumb__area grey-bg pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="tp-breadcrumb__content">
                    <div class="tp-breadcrumb__list">
                        <span class="tp-breadcrumb__active"><a href="/">Ana Sayfa</a></span>
                        <span class="dvdr">/</span>
                        <span>İletişim</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb-area-end -->

<!-- contact-area-start -->
<section class="contact-area pt-100 pb-100">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-8">
                <div class="contact-wrapper">
                    <div class="contact-wrapper-top">
                        <h3>Bizimle İletişime Geçin</h3>
                        <p>Sorularınız, önerileriniz veya şikayetleriniz için bize ulaşabilirsiniz.</p>
                    </div>

                    <?php if (isset($_SESSION['basari'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['basari'] ?>
                            <?php unset($_SESSION['basari']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['hata'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['hata'] ?>
                            <?php unset($_SESSION['hata']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="contact-wrapper-content">
                        <form method="POST">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="contact-wrapper-content-item">
                                        <label>Ad Soyad *</label>
                                        <input type="text" name="ad_soyad" value="<?= $_POST['ad_soyad'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="contact-wrapper-content-item">
                                        <label>E-posta Adresi *</label>
                                        <input type="email" name="eposta" value="<?= $_POST['eposta'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="contact-wrapper-content-item">
                                        <label>Telefon</label>
                                        <input type="tel" name="telefon" value="<?= $_POST['telefon'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="contact-wrapper-content-item">
                                        <label>Konu *</label>
                                        <select name="konu" required>
                                            <option value="">Konu Seçiniz</option>
                                            <option value="Genel Bilgi" <?= ($_POST['konu'] ?? '') == 'Genel Bilgi' ? 'selected' : '' ?>>Genel Bilgi</option>
                                            <option value="Sipariş" <?= ($_POST['konu'] ?? '') == 'Sipariş' ? 'selected' : '' ?>>Sipariş</option>
                                            <option value="Ürün" <?= ($_POST['konu'] ?? '') == 'Ürün' ? 'selected' : '' ?>>Ürün</option>
                                            <option value="Kargo" <?= ($_POST['konu'] ?? '') == 'Kargo' ? 'selected' : '' ?>>Kargo</option>
                                            <option value="İade" <?= ($_POST['konu'] ?? '') == 'İade' ? 'selected' : '' ?>>İade</option>
                                            <option value="Şikayet" <?= ($_POST['konu'] ?? '') == 'Şikayet' ? 'selected' : '' ?>>Şikayet</option>
                                            <option value="Öneri" <?= ($_POST['konu'] ?? '') == 'Öneri' ? 'selected' : '' ?>>Öneri</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="contact-wrapper-content-item">
                                        <label>Mesajınız *</label>
                                        <textarea name="mesaj" rows="6" required><?= $_POST['mesaj'] ?? '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="contact-wrapper-content-item">
                                        <button type="submit" class="tp-btn">Mesaj Gönder</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4">
                <div class="contact-info">
                    <div class="contact-info-top">
                        <h3>İletişim Bilgileri</h3>
                    </div>
                    <div class="contact-info-content">
                        <?php if (!empty($site_ayarlari['adres'])): ?>
                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="fal fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-info-text">
                                    <h4>Adres</h4>
                                    <p><?= $site_ayarlari['adres'] ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($site_ayarlari['telefon'])): ?>
                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="fal fa-phone"></i>
                                </div>
                                <div class="contact-info-text">
                                    <h4>Telefon</h4>
                                    <p><?= $site_ayarlari['telefon'] ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($site_ayarlari['email'])): ?>
                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="fal fa-envelope"></i>
                                </div>
                                <div class="contact-info-text">
                                    <h4>E-posta</h4>
                                    <p><?= $site_ayarlari['email'] ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fal fa-clock"></i>
                            </div>
                            <div class="contact-info-text">
                                <h4>Çalışma Saatleri</h4>
                                <p>Pazartesi - Cuma: 09:00 - 18:00<br>
                                    Cumartesi: 09:00 - 16:00<br>
                                    Pazar: Kapalı</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-social">
                        <h4>Sosyal Medya</h4>
                        <div class="contact-social-links">
                            <?php if (!empty($site_ayarlari['facebook_url'])): ?>
                                <a href="<?= $site_ayarlari['facebook_url'] ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($site_ayarlari['twitter_url'])): ?>
                                <a href="<?= $site_ayarlari['twitter_url'] ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($site_ayarlari['instagram_url'])): ?>
                                <a href="<?= $site_ayarlari['instagram_url'] ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- contact-area-end -->