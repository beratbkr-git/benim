<?php
// frontend/controllers/KayitController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class KayitController extends BaseController
{
    /**
     * Kayıt sayfasını ve form gönderimini yönetir.
     */
    public function index()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/hesap');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ad_soyad = $_POST['ad_soyad'] ?? '';
            $eposta = $_POST['eposta'] ?? '';
            $parola = $_POST['parola'] ?? '';
            $parola_tekrar = $_POST['parola_tekrar'] ?? '';
            $telefon = $_POST['telefon'] ?? '';
            $sartlar = isset($_POST['sartlar']);

            $hatalar = [];

            if (empty($ad_soyad)) {
                $hatalar[] = 'Ad Soyad alanı zorunludur.';
            }

            if (empty($eposta) || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
                $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
            }

            if (empty($parola) || strlen($parola) < 6) {
                $hatalar[] = 'Parola en az 6 karakter olmalıdır.';
            }

            if ($parola !== $parola_tekrar) {
                $hatalar[] = 'Parolalar eşleşmiyor.';
            }

            if (!$sartlar) {
                $hatalar[] = 'Kullanım şartlarını kabul etmelisiniz.';
            }

            if (empty($hatalar)) {
                $mevcut_musteri = $this->db->fetch("
                    SELECT id FROM bt_musteriler WHERE eposta = :eposta
                ", ['eposta' => $eposta]);

                if ($mevcut_musteri) {
                    $hatalar[] = 'Bu e-posta adresi zaten kayıtlı.';
                }
            }

            // Eğer hata yoksa doğrulama kodunu gönder, yoksa hataları session'a yaz
            if (empty($hatalar)) {
                $dogrulama_kodu = rand(100000, 999999);

                $_SESSION['kayit_dogrulama'] = [
                    'durum' => 'Aktif',
                    'ad_soyad' => $ad_soyad,
                    'eposta' => $eposta,
                    'parola' => password_hash($parola, PASSWORD_DEFAULT),
                    'telefon' => $telefon,
                    'dogrulama_kodu' => $dogrulama_kodu,
                    'kod_olusturma_zamani' => time() // Yeni: Kodun oluşturulma zamanı
                ];

                $mail_sonuc = epostaGonder($eposta, 'eposta_dogrulama', [
                    '{musteri_adi}' => $ad_soyad,
                    '{dogrulama_kodu}' => $dogrulama_kodu
                ]);

                if ($mail_sonuc === true) {
                    $_SESSION['basari'] = 'Doğrulama kodunuz e-posta adresinize gönderildi. Lütfen gelen kutunuzu kontrol edin.';
                    $this->redirect('/kayit/dogrulama');
                } else {
                    $_SESSION['hata'] = 'E-posta gönderimi sırasında bir hata oluştu. Lütfen tekrar deneyin. Detay: ' . $mail_sonuc;
                    $this->redirect('/kayit');
                }
                return;
            } else {
                $_SESSION['hata'] = implode('<br>', $hatalar);
                $this->redirect('/kayit');
                return;
            }
        }

        $this->loadView('auth/kayit.php', [
            'title' => 'Üye Ol - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * E-posta doğrulama sayfasını ve formunu yönetir.
     */
    public function dogrulama()
    {
        if (!isset($_SESSION['kayit_dogrulama'])) {
            $this->redirect('/kayit');
            return;
        }

        $kod_olusturma_zamani = $_SESSION['kayit_dogrulama']['kod_olusturma_zamani'] ?? 0;
        $gecerlilik_suresi = 120; // 2 dakika

        if (time() - $kod_olusturma_zamani > $gecerlilik_suresi) {
            $_SESSION['hata'] = 'Doğrulama kodunuzun süresi doldu. Lütfen yeni bir kod isteyin.';
            unset($_SESSION['kayit_dogrulama']);
            $this->redirect('/kayit');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $girilen_kod = $_POST['dogrulama_kodu'] ?? '';
            $beklenen_kod = $_SESSION['kayit_dogrulama']['dogrulama_kodu'];

            if ($girilen_kod == $beklenen_kod) {
                $musteri_data = $_SESSION['kayit_dogrulama'];
                unset($musteri_data['dogrulama_kodu']);
                unset($musteri_data['kod_olusturma_zamani']); // Kaydetmeden önce zaman bilgisini kaldır

                $musteri_id = $this->db->insert('bt_musteriler', $musteri_data);

                if ($musteri_id) {
                    // Kayıt başarılı, hoş geldin e-postası gönder
                    epostaGonder($musteri_data['eposta'], 'hos_geldiniz', [
                        '{musteri_adi}' => $musteri_data['ad_soyad']
                    ]);

                    $_SESSION['musteri_id'] = $musteri_id;
                    $_SESSION['musteri'] = $this->db->fetch("SELECT * FROM bt_musteriler WHERE id = :id", ['id' => $musteri_id]);
                    $_SESSION['basari'] = 'Kayıt işleminiz başarıyla tamamlandı!';
                    unset($_SESSION['kayit_dogrulama']);
                    $this->redirect('/hesap');
                } else {
                    $_SESSION['hata'] = 'Kayıt işlemi sırasında bir hata oluştu.';
                    $this->redirect('/kayit');
                }
            } else {
                $_SESSION['hata'] = 'Girilen doğrulama kodu hatalı. Lütfen tekrar deneyin.';
                $this->redirect('/kayit/dogrulama');
            }
            return;
        }

        $this->loadView('auth/dogrulama.php', [
            'title' => 'E-posta Doğrulama - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet(),
            'kod_olusturma_zamani' => $kod_olusturma_zamani,
            'gecerlilik_suresi' => $gecerlilik_suresi
        ]);
    }

    /**
     * E-posta doğrulama kodunu tekrar gönderme işlemini yönetir.
     */
    public function tekrarKodGonder()
    {
        if (!isset($_SESSION['kayit_dogrulama'])) {
            $this->redirect('/kayit');
            return;
        }

        $son_gonderim_zamani = $_SESSION['kayit_dogrulama']['kod_olusturma_zamani'] ?? 0;
        $tekrar_gonderim_suresi = 60; // 1 dakika

        if (time() - $son_gonderim_zamani < $tekrar_gonderim_suresi) {
            $_SESSION['hata'] = 'Yeni kod göndermek için 1 dakika beklemelisiniz.';
            $this->redirect('/kayit/dogrulama');
            return;
        }

        $dogrulama_kodu = rand(100000, 999999);
        $ad_soyad = $_SESSION['kayit_dogrulama']['ad_soyad'];
        $eposta = $_SESSION['kayit_dogrulama']['eposta'];

        // Yeni kodu ve zamanı session'da güncelle
        $_SESSION['kayit_dogrulama']['dogrulama_kodu'] = $dogrulama_kodu;
        $_SESSION['kayit_dogrulama']['kod_olusturma_zamani'] = time();

        $mail_sonuc = epostaGonder($eposta, 'eposta_dogrulama', [
            '{musteri_adi}' => $ad_soyad,
            '{dogrulama_kodu}' => $dogrulama_kodu
        ]);

        if ($mail_sonuc === true) {
            $_SESSION['basari'] = 'Yeni doğrulama kodunuz e-posta adresinize gönderildi.';
        } else {
            $_SESSION['hata'] = 'E-posta gönderimi sırasında bir hata oluştu. Detay: ' . $mail_sonuc;
        }

        $this->redirect('/kayit/dogrulama');
    }
}
