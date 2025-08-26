<?php
// views/hesap/adresler.php
// Bu sayfa, kullanıcının adres defterini AJAX işlemleriyle yönetir.
global $controller;
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
                            <span>Adres Defteri</span>
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
                                    <li><a href="/hesap/profil">Profil Bilgilerim</a></li>
                                    <li><a href="/hesap/siparisler">Siparişlerim</a></li>
                                    <li><a href="/hesap/adresler" class="active">Adres Defteri</a></li>
                                    <li><a href="/cikis">Çıkış Yap</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="account-dashboard">
                        <div class="account-dashboard__content">
                            <div class="address-book">
                                <div class="d-flex justify-content-between align-items-center mb-30">
                                    <h5>Adres Defteri</h5>
                                    <button class="tp-btn-2" data-bs-toggle="modal" data-bs-target="#adresEkleModal">Yeni Adres Ekle</button>
                                </div>
                                <div class="row">
                                    <?php if (!empty($adresler)): ?>
                                        <?php foreach ($adresler as $adres): ?>
                                            <div class="col-md-6 mb-30">
                                                <div class="address-box <?= $adres['varsayilan_adres'] == 1 ? 'active' : '' ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-10">
                                                        <h6><?= htmlspecialchars($adres['adres_baslik']) ?></h6>
                                                        <?php if ($adres['varsayilan_adres'] == 1): ?>
                                                            <span class="badge bg-success">Varsayılan</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <address>
                                                        <strong><?= htmlspecialchars($adres['ad_soyad']) ?></strong><br>
                                                        <?= htmlspecialchars($adres['adres']) ?><br>
                                                        <?= htmlspecialchars($adres['ilce']) ?> / <?= htmlspecialchars($adres['il']) ?><br>
                                                        <?= htmlspecialchars($adres['telefon'] ?? '') ?><br>
                                                        <?= htmlspecialchars($adres['posta_kodu'] ?? '') ?>
                                                    </address>
                                                    <div class="address-actions mt-15">
                                                        <button class="btn btn-sm btn-info adres-duzenle-btn" data-bs-toggle="modal" data-bs-target="#adresDuzenleModal" data-adres='<?= json_encode($adres) ?>'>Düzenle</button>
                                                        <button class="btn btn-sm btn-danger adres-sil-btn" data-id="<?= $adres['id'] ?>">Sil</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">Henüz kayıtlı bir adresiniz bulunmamaktadır.</div>
                                        </div>
                                    <?php endif; ?>
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

<!-- Adres Ekle Modal -->
<div class="modal fade" id="adresEkleModal" tabindex="-1" aria-labelledby="adresEkleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adresEkleModalLabel">Yeni Adres Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/hesap/adresEkle" method="POST" id="adres-ekle-form">
                    <div class="tpform__input mb-20">
                        <label for="adres_baslik">Adres Başlığı</label>
                        <input type="text" name="adres_baslik" id="adres_baslik" placeholder="Ev, İş vb." required>
                    </div>
                    <div class="tpform__input mb-20">
                        <label for="ad_soyad">Ad Soyad</label>
                        <input type="text" name="ad_soyad" id="ad_soyad" placeholder="Adınız Soyadınız" required>
                    </div>
                    <div class="tpform__input mb-20">
                        <label for="adres">Adres</label>
                        <textarea name="adres" id="adres" rows="3" required></textarea>
                    </div>
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="il">İl</label>
                                <input type="text" name="il" id="il" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="ilce">İlçe</label>
                                <input type="text" name="ilce" id="ilce" required>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="posta_kodu">Posta Kodu</label>
                                <input type="text" name="posta_kodu" id="posta_kodu" placeholder="Posta Kodu">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="telefon">Telefon</label>
                                <input type="tel" name="telefon" class="format_phone" id="telefon" placeholder="Telefon numaranız">
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-20">
                        <input class="form-check-input" type="checkbox" name="varsayilan_adres" value="1" id="varsayilan_adres">
                        <label class="form-check-label" for="varsayilan_adres">Varsayılan adres olarak ayarla</label>
                    </div>
                    <button type="submit" class="tp-btn w-100">Adres Ekle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Adres Düzenle Modal -->
<div class="modal fade" id="adresDuzenleModal" tabindex="-1" aria-labelledby="adresDuzenleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adresDuzenleModalLabel">Adresi Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/hesap/adresDuzenle" method="POST" id="adres-duzenle-form">
                    <input type="hidden" name="id" id="duzenle_adres_id">
                    <div class="tpform__input mb-20">
                        <label for="duzenle_adres_baslik">Adres Başlığı</label>
                        <input type="text" name="adres_baslik" id="duzenle_adres_baslik" required>
                    </div>
                    <div class="tpform__input mb-20">
                        <label for="duzenle_ad_soyad">Ad Soyad</label>
                        <input type="text" name="ad_soyad" id="duzenle_ad_soyad" required>
                    </div>
                    <div class="tpform__input mb-20">
                        <label for="duzenle_adres">Adres</label>
                        <textarea name="adres" id="duzenle_adres" rows="3" required></textarea>
                    </div>
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="duzenle_il">İl</label>
                                <input type="text" name="il" id="duzenle_il" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="duzenle_ilce">İlçe</label>
                                <input type="text" name="ilce" id="duzenle_ilce" required>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="duzenle_posta_kodu">Posta Kodu</label>
                                <input type="text" name="posta_kodu" id="duzenle_posta_kodu">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tpform__input mb-20">
                                <label for="duzenle_telefon">Telefon</label>
                                <input type="tel" name="telefon" class="format_phone" id="duzenle_telefon">
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-20">
                        <input class="form-check-input" type="checkbox" name="varsayilan_adres" value="1" id="duzenle_varsayilan_adres">
                        <label class="form-check-label" for="duzenle_varsayilan_adres">Varsayılan adres olarak ayarla</label>
                    </div>
                    <button type="submit" class="tp-btn w-100">Adresi Güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Hata ve başarı mesajlarını toast olarak göster
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

        // Adres Düzenle Modalını açıldığında verileri doldur
        const adresDuzenleModal = document.getElementById('adresDuzenleModal');
        adresDuzenleModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const adresData = JSON.parse(button.getAttribute('data-adres'));

            const form = document.getElementById('adres-duzenle-form');
            form.action = `/hesap/adresDuzenle/${adresData.id}`;
            document.getElementById('duzenle_adres_id').value = adresData.id;
            document.getElementById('duzenle_adres_baslik').value = adresData.adres_baslik;
            document.getElementById('duzenle_ad_soyad').value = adresData.ad_soyad;
            document.getElementById('duzenle_adres').value = adresData.adres;
            document.getElementById('duzenle_il').value = adresData.il;
            document.getElementById('duzenle_ilce').value = adresData.ilce;
            document.getElementById('duzenle_posta_kodu').value = adresData.posta_kodu;
            document.getElementById('duzenle_telefon').value = adresData.telefon;
            document.getElementById('duzenle_varsayilan_adres').checked = adresData.varsayilan_adres == 1;
        });

        // Adres Ekleme Formu için submit işlemi
        $('#adres-ekle-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#adresEkleModal').modal('hide');
                        location.reload();
                    } else {
                        brtToast.error(response.message);
                    }
                },
                error: function() {
                    brtToast.error('Bir hata oluştu.');
                }
            });
        });

        // Adres Düzenleme Formu için submit işlemi
        $('#adres-duzenle-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#adresDuzenleModal').modal('hide');
                        location.reload();
                    } else {
                        brtToast.error(response.message);
                    }
                },
                error: function() {
                    brtToast.error('Bir hata oluştu.');
                }
            });
        });

        // Adres Silme İşlemi için AJAX
        $('.adres-sil-btn').on('click', function(e) {
            e.preventDefault();
            const adresId = $(this).data('id');
            const url = `/hesap/adresSil/${adresId}`;

            brtAlert.confirm('Emin misiniz?', 'Bu adresi silmek istediğinizden emin misiniz?', 'Evet, sil!').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                brtToast.error(response.message);
                            }
                        },
                        error: function() {
                            brtToast.error('Bir hata oluştu.');
                        }
                    });
                }
            });
        });
    });
</script>