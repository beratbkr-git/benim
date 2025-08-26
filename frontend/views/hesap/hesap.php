<?php
// views/hesap/hesap.php
// Bu sayfa, kullanıcının ana hesap paneli olarak görev yapar.

// Sol taraftaki menü ve sağdaki içerik için Orfarm temasının yapısı kullanıldı.
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
                            <span>Hesabım</span>
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
                                    <li><a href="/hesap" class="active">Hesap Paneli</a></li>
                                    <li><a href="/hesap/profil">Profil Bilgilerim</a></li>
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
                            <div class="welcome-box">
                                <p>Merhaba, <b><?= htmlspecialchars($musteri['ad_soyad']) ?></b></p>
                                <p>Hesap panelinizden son siparişlerinizi görüntüleyebilir, gönderim ve fatura adreslerinizi yönetebilir ve profil bilgilerinizi düzenleyebilirsiniz.</p>
                            </div>
                            <div class="recent-orders mt-50">
                                <h5>Son Siparişlerim</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Sipariş Kodu</th>
                                                <th>Durum</th>
                                                <th>Tarih</th>
                                                <th>Toplam</th>
                                                <th>Aksiyon</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($son_siparisler)): ?>
                                                <?php foreach ($son_siparisler as $siparis): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($siparis['siparis_kodu']) ?></td>
                                                        <td><?= htmlspecialchars($siparis['siparis_durumu']) ?></td>
                                                        <td><?= date('d.m.Y', strtotime($siparis['olusturma_tarihi'])) ?></td>
                                                        <td><?= $this->formatPrice($siparis['odenen_tutar']) ?></td>
                                                        <td><a href="/hesap/siparisler/detay/<?= $siparis['id'] ?>" class="btn btn-sm btn-primary">Görüntüle</a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Henüz bir siparişiniz bulunmamaktadır.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="all-orders mt-20">
                                    <a href="/hesap/siparisler" class="btn btn-secondary">Tüm Siparişleri Görüntüle</a>
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