<!-- footer-area-start -->
<?php
global $p1; ?>
<section class="feature-area mainfeature__bg pt-50 pb-40" data-background="/<?= FRONTEND_ASSETS_DIR ?>img/shape/footer-shape-1.svg" style="background-image: url(&quot;/<?= FRONTEND_ASSETS_DIR ?>img/shape/footer-shape-1.svg&quot;);">
    <div class="container">
        <div class="mainfeature__border pb-15">
            <div class="row row-cols-lg-5 row-cols-md-3 row-cols-2">
                <div class="col">
                    <div class="mainfeature__item text-center mb-30">
                        <div class="mainfeature__icon">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-1.svg" alt="">
                        </div>
                        <div class="mainfeature__content">
                            <h4 class="mainfeature__title">Fast Delivery</h4>
                            <p>Across West &amp; East India</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="mainfeature__item text-center mb-30">
                        <div class="mainfeature__icon">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-2.svg" alt="">
                        </div>
                        <div class="mainfeature__content">
                            <h4 class="mainfeature__title">safe payment</h4>
                            <p>100% Secure Payment</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="mainfeature__item text-center mb-30">
                        <div class="mainfeature__icon">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-3.svg" alt="">
                        </div>
                        <div class="mainfeature__content">
                            <h4 class="mainfeature__title">Online Discount</h4>
                            <p>Add Multi-buy Discount </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="mainfeature__item text-center mb-30">
                        <div class="mainfeature__icon">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-4.svg" alt="">
                        </div>
                        <div class="mainfeature__content">
                            <h4 class="mainfeature__title">Help Center</h4>
                            <p>Dedicated 24/7 Support </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="mainfeature__item text-center mb-30">
                        <div class="mainfeature__icon">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/icon/feature-icon-5.svg" alt="">
                        </div>
                        <div class="mainfeature__content">
                            <h4 class="mainfeature__title">Curated items</h4>
                            <p>From Handpicked Sellers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<footer>
    <div class="tpfooter__area theme-bg-2">
        <div class="tpfooter__top pb-15">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="tpfooter__widget footer-col-1 mb-50">
                            <div class="footer__widget-title">
                                <div class="footer__logo">
                                    <a href="/"><img src="/<?= FRONTEND_ASSETS_DIR ?>img/logo/logo-white.png" alt="<?= $site_ayarlari['site_baslik'] ?? 'Ketchila' ?>"></a>
                                </div>
                            </div>
                            <div class="footer__widget-content">
                                <p><?= $site_ayarlari['footer_aciklama'] ?? 'Kaliteli ürünler, uygun fiyatlar ve hızlı teslimat ile hizmetinizdeyiz.' ?></p>
                                <div class="tpfooter__widget-social mt-45">
                                    <span class="tpfooter__widget-social-title mb-5">Sosyal Medya:</span>
                                    <?php if (!empty($site_ayarlari['facebook_url'])): ?>
                                        <a href="<?= $site_ayarlari['facebook_url'] ?>"><i class="fab fa-facebook-f"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($site_ayarlari['twitter_url'])): ?>
                                        <a href="<?= $site_ayarlari['twitter_url'] ?>"><i class="fab fa-twitter"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($site_ayarlari['instagram_url'])): ?>
                                        <a href="<?= $site_ayarlari['instagram_url'] ?>"><i class="fab fa-instagram"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="tpfooter__widget footer-col-2 mb-50">
                            <h4 class="tpfooter__widget-title">Hızlı Linkler</h4>
                            <div class="tpfooter__widget-links">
                                <ul>
                                    <li><a href="/hakkimizda">Hakkımızda</a></li>
                                    <li><a href="/iletisim">İletişim</a></li>
                                    <li><a href="/sss">Sıkça Sorulan Sorular</a></li>
                                    <li><a href="/gizlilik-politikasi">Gizlilik Politikası</a></li>
                                    <li><a href="/kullanim-kosullari">Kullanım Koşulları</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-5">
                        <div class="tpfooter__widget footer-col-3 mb-50">
                            <h4 class="tpfooter__widget-title">Kategoriler</h4>
                            <div class="tpfooter__widget-links">
                                <ul>
                                    <li><a href="/kategori/1">Elektronik</a></li>
                                    <li><a href="/kategori/2">Giyim</a></li>
                                    <li><a href="/kategori/3">Ev & Yaşam</a></li>
                                    <li><a href="/kategori/4">Spor</a></li>
                                    <li><a href="/kategori/5">Kitap</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-8 col-sm-7">
                        <div class="tpfooter__widget footer-col-4 mb-50">
                            <h4 class="tpfooter__widget-title">İletişim Bilgileri</h4>
                            <div class="footer__info">
                                <?php if (!empty($site_ayarlari['adres'])): ?>
                                    <div class="footer__info-item d-flex align-items-start">
                                        <div class="footer__info-icon">
                                            <i class="fal fa-map-marker-alt"></i>
                                        </div>
                                        <div class="footer__info-text">
                                            <span><?= $site_ayarlari['adres'] ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($site_ayarlari['telefon'])): ?>
                                    <div class="footer__info-item d-flex align-items-start">
                                        <div class="footer__info-icon">
                                            <i class="fal fa-phone"></i>
                                        </div>
                                        <div class="footer__info-text">
                                            <span><?= $site_ayarlari['telefon'] ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($site_ayarlari['email'])): ?>
                                    <div class="footer__info-item d-flex align-items-start">
                                        <div class="footer__info-icon">
                                            <i class="fal fa-envelope"></i>
                                        </div>
                                        <div class="footer__info-text">
                                            <span><?= $site_ayarlari['email'] ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tpfooter___bottom pt-40 pb-40">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-7 col-sm-12">
                        <div class="tpfooter__copyright">
                            <span class="tpfooter__copyright-text">&copy; <?= date('Y') ?> <?= $site_ayarlari['site_baslik'] ?? 'Ketchila' ?>. Tüm hakları saklıdır.</span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-5 col-sm-12">
                        <div class="tpfooter__copyright-thumb text-end">
                            <img src="/<?= FRONTEND_ASSETS_DIR ?>img/payment/payment.png" alt="Ödeme Yöntemleri">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Custom Alert Modal Markup -->
<div class="modal fade" id="brtAlertModal" tabindex="-1" aria-labelledby="brtAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brtAlertModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="brtAlertModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="tp-btn-2" data-bs-dismiss="modal" id="brtAlertCancelBtn">İptal</button>
                <button type="button" class="tp-btn" data-bs-dismiss="modal" id="brtAlertConfirmBtn">Tamam</button>
            </div>
        </div>
    </div>
</div>

<!-- footer-area-end -->

<!-- JS here -->

<script src="/<?= FRONTEND_ASSETS_DIR ?>js/waypoints.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/bootstrap.bundle.min.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/meanmenu.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/swiper-bundle.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/slick.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/magnific-popup.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/backtotop.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/nice-select.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/sidebar.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/isotope-pkgd.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/imagesloaded-pkgd.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/jquery-ui.js"></script>
<script src="/<?= FRONTEND_ASSETS_DIR ?>js/main.js"></script>


<script>
    // Global brtAlert nesnesi - Orfarm teması ile uyumlu modal yapısı
    window.brtAlert = {
        _show: function(title, text, type, confirmButtonText, callback) {
            const modal = new bootstrap.Modal(document.getElementById('brtAlertModal'));
            document.getElementById('brtAlertModalLabel').textContent = title;
            document.getElementById('brtAlertModalBody').textContent = text;

            const confirmBtn = document.getElementById('brtAlertConfirmBtn');
            const cancelBtn = document.getElementById('brtAlertCancelBtn');

            // Butonları ayarla
            if (type === 'confirm') {
                confirmBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
                confirmBtn.textContent = confirmButtonText || 'Evet';
            } else {
                confirmBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'none';
                confirmBtn.textContent = 'Tamam';
            }

            // Onaylama butonu olayını ayarla
            confirmBtn.onclick = function() {
                modal.hide();
                if (callback) {
                    callback({
                        isConfirmed: true
                    });
                }
            };

            // İptal butonu olayını ayarla
            cancelBtn.onclick = function() {
                modal.hide();
                if (callback) {
                    callback({
                        isConfirmed: false
                    });
                }
            };

            modal.show();
        },
        success: function(title, text) {
            this._show(title, text, 'success', 'Tamam');
        },
        error: function(title, text) {
            this._show(title, text, 'error', 'Tamam');
        },
        confirm: function(title, text, confirmButtonText, callback) {
            this._show(title, text, 'confirm', confirmButtonText, callback);
        }
    };
</script>
<script>
    // Sayfaya özel ve ortak JavaScript kodları
    $(document).ready(function() {
        // İstek listesine ekleme fonksiyonu
        function istekListesiEkle(urunId, varyantId = null) {
            $.ajax({
                url: '/istek-listesi/ekle',
                method: 'POST',
                data: {
                    urun_id: urunId,
                    varyant_id: varyantId
                },
                success: function(response) {
                    if (typeof brtToast !== 'undefined') {
                        if (response.success) {
                            brtToast.success(response.message);
                        } else {
                            brtToast.error(response.message);
                        }
                    } else {
                        if (response.success) {
                            brtAlert.success('Başarılı!', response.message);
                        } else {
                            brtAlert.error('Hata!', response.message);
                        }
                    }
                },
                error: function() {
                    if (typeof brtToast !== 'undefined') {
                        brtToast.error('Bir hata oluştu');
                    } else {
                        brtAlert.error('Hata!', 'Bir hata oluştu.');
                    }
                }
            });
        }

        // Sepete Ekleme AJAX fonksiyonu
        function sepeteEkle(urunId, varyantId = null, adet = 1) {
            $.ajax({
                url: '/sepet/ekle',
                method: 'POST',
                data: {
                    urun_id: urunId,
                    varyant_id: varyantId,
                    adet: adet,
                    ajax: true
                },
                success: function(response) {
                    if (response.success) {
                        $('#sepet-adet').text(response.sepet_adet);
                        if (typeof brtToast !== 'undefined') {
                            brtToast.success(response.message);
                        } else {
                            alert(response.message);
                        }
                    } else {
                        if (typeof brtToast !== 'undefined') {
                            brtToast.error(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function() {
                    if (typeof brtToast !== 'undefined') {
                        brtToast.error('Bir hata oluştu');
                    } else {
                        alert('Bir hata oluştu');
                    }
                }
            });
        }

        // Sepete Ekle butonu
        $(document).ready(function() {
            $('.sepete-ekle-btn').on('click', function(e) {
                e.preventDefault();
                var urunId = $(this).data('urun-id');
                var varyantId = $(this).data('varyant-id') || null;
                var adet = $(this).data('adet') || 1;
                sepeteEkle(urunId, varyantId, adet);
            });
            // Genel istek listesi butonu dinleyicisi
            $('.istek-listesi-ekle-btn').on('click', function(e) {
                e.preventDefault();
                var urunId = $(this).data('urun-id');
                var varyantId = $(this).data('varyant-id') || null;
                istekListesiEkle(urunId, varyantId);
            });
        });
        const marquee = document.getElementById('marquee-content');
        if (marquee) {
            const clone = marquee.cloneNode(true);
            marquee.parentElement.appendChild(clone);

            let position = 0;
            const speed = 0.5;

            function render() {
                marquee.style.transform = `translateX(${position}px)`;
                clone.style.transform = `translateX(${position + marquee.scrollWidth}px)`;
            }

            function animate() {
                position -= speed;
                if (Math.abs(position) >= marquee.scrollWidth) {
                    position = 0;
                }
                render();
                requestAnimationFrame(animate);
            }
            animate();
        }

        <?php if ($p1 == 'urunler'):
            $min_fiyatt = $_GET['min_fiyat'] ?? null;
            $max_fiyatt = $_GET['max_fiyat'] ?? null;
        ?>
            var minPrice = 0;
            var maxPrice = 10000;
            var currentMinPrice = parseFloat('<?= $min_fiyatt ?? 0 ?>');
            var currentMaxPrice = parseFloat('<?= $max_fiyatt ?? 8000 ?>');

            $("#slider-range").slider({
                range: true,
                min: minPrice,
                max: maxPrice,
                values: [currentMinPrice, currentMaxPrice],
                slide: function(event, ui) {
                    $("#amount").val(ui.values[0] + " ₺ - " + ui.values[1] + " ₺");
                }
            });
            $("#amount").val($("#slider-range").slider("values", 0) + " ₺ - " + $("#slider-range").slider("values", 1) + " ₺");

            function updateUrl(params) {
                var url = new URL(window.location.href);
                for (const key in params) {
                    if (params[key]) {
                        url.searchParams.set(key, params[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                }
                url.searchParams.delete('sayfa');
                window.location.href = url.toString();
            }

            $('#siralama-select').on('change', function() {
                var newSiralama = $(this).val();
                updateUrl({
                    siralama: newSiralama
                });
            });

            // Kategori filtresi
            $('.kategori-filter').on('change', function() {
                var checkedCategories = $('.kategori-filter:checked').map(function() {
                    return this.value;
                }).get().join(',');

                updateUrl({
                    kategori: checkedCategories
                });
            });

            // Marka filtresi
            $('.marka-filter').on('change', function() {
                var checkedBrands = $('.marka-filter:checked').map(function() {
                    return this.value;
                }).get().join(',');

                updateUrl({
                    marka: checkedBrands
                });
            });

            // Derecelendirme filtresi
            $('.rating-filter').on('change', function() {
                var selectedRating = null;
                if ($(this).is(':checked')) {
                    selectedRating = $(this).val();
                }
                updateUrl({
                    rating: selectedRating
                });
            });

            // Fiyat filtrele butonu
            $('#fiyat-filtrele').on('click', function() {
                var values = $("#slider-range").slider("values");
                updateUrl({
                    min_fiyat: values[0],
                    max_fiyat: values[1]
                });
            });

            // Filtreleri temizle butonu
            $('#filtreleri-temizle').on('click', function() {
                window.location.href = '/urunler';
            });
        <?php endif; ?>
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const formatPhoneValue = (val) => {
            let input = val.replace(/\D/g, "");
            if (!input.startsWith("90")) {
                input = "90" + input;
            }

            const trimmedInput = input.substring(2, 12);

            let formatted = "+90";
            if (trimmedInput.length > 0) {
                formatted += ` (${trimmedInput.substring(0, 3)}`;
            }
            if (trimmedInput.length > 3) {
                formatted += `) ${trimmedInput.substring(3, 6)}`;
            }
            if (trimmedInput.length > 6) {
                formatted += ` ${trimmedInput.substring(6, 8)}`;
            }
            if (trimmedInput.length > 8) {
                formatted += ` ${trimmedInput.substring(8, 10)}`;
            }

            return formatted;
        };

        const applyPhoneFormattingToAll = () => {
            document.querySelectorAll(".format_phone, input[name='telefon']").forEach((telefon) => {
                const raw = telefon.value.replace(/\D/g, "");
                if (raw.length >= 10) {
                    telefon.value = formatPhoneValue(telefon.value);
                }
            });
        };

        const attachEventsToInputs = () => {
            document.querySelectorAll(".format_phone, input[name='telefon']").forEach((telefon) => {
                if (telefon.dataset.eventsAttached) return; // sadece 1 kere bağla
                telefon.dataset.eventsAttached = "true";

                telefon.addEventListener("focus", function() {
                    const raw = telefon.value.replace(/\D/g, "");
                    if (raw.length >= 10 && !telefon.value.startsWith("+90")) {
                        telefon.value = formatPhoneValue(telefon.value);
                    } else if (telefon.value.trim() === "") {
                        telefon.value = "+90 ";
                    }
                    telefon.setSelectionRange(telefon.value.length, telefon.value.length);
                });

                telefon.addEventListener("input", function(e) {
                    const cursorPos = e.target.selectionStart;
                    const prevLength = e.target.value.length;

                    e.target.value = formatPhoneValue(e.target.value);

                    const newLength = e.target.value.length;
                    const diff = newLength - prevLength;
                    e.target.setSelectionRange(cursorPos + diff, cursorPos + diff);
                });

                telefon.addEventListener("keydown", function(e) {
                    if (
                        (e.key === "Backspace" || e.key === "Delete") &&
                        telefon.selectionStart <= 4
                    ) {
                        e.preventDefault();
                    }
                });

                const form = telefon.closest("form");
                if (form && !form.dataset.phoneFormatHandled) {
                    form.dataset.phoneFormatHandled = "true";
                    form.addEventListener("submit", function() {
                        document.querySelectorAll(".format_phone, input[name='telefon']").forEach((input) => {
                            let raw = input.value.replace(/\D/g, "");
                            if (!raw.startsWith("90")) {
                                raw = "90" + raw;
                            }
                            input.value = raw;
                        });
                    });

                }
            });
        };

        // ✅ Sayfa yüklendiğinde inputları hazırla ve formatla
        applyPhoneFormattingToAll();
        attachEventsToInputs();

        // ✅ Popup gibi sonradan gelen içerikleri de izlemek için MutationObserver
        const observer = new MutationObserver(() => {
            applyPhoneFormattingToAll();
            attachEventsToInputs();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    });
</script>

</body>

</html>