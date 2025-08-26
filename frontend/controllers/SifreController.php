<?php
// frontend/controllers/SifreController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');
require_once(__DIR__ . '/../../function/functions.php');

class SifreController extends BaseController
{
    /**
     * Şifremi unuttum formunu gösterir ve e-posta gönderimini yönetir.
     */
    public function index()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/hesap');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eposta = $_POST['eposta'] ?? '';

            if (empty($eposta) || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['hata'] = 'Lütfen geçerli bir e-posta adresi girin.';
                $this->redirect('/sifremi-unuttum');
                return;
            }

            $musteri = $this->db->fetch("
                SELECT id, ad_soyad FROM bt_musteriler WHERE eposta = :eposta AND durum = 'Aktif'
            ", ['eposta' => $eposta]);

            if ($musteri) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $token_hash = hash('sha256', $token);

                $this->db->insert('bt_musteri_tokens', [
                    'musteri_id' => $musteri['id'],
                    'token' => $token_hash, // Hashlenmiş token'ı kaydet
                    'expires' => $expires
                ]);

                $sifre_sifirlama_linki = 'http://' . $_SERVER['HTTP_HOST'] . '/sifremi-unuttum/sifirla?token=' . $token . '&eposta=' . urlencode($eposta);

                $mail_sonuc = epostaGonder($eposta, 'sifre_sifirlama', [
                    '{musteri_adi}' => $musteri['ad_soyad'],
                    '{sifre_sifirlama_linki}' => $sifre_sifirlama_linki
                ]);

                if ($mail_sonuc === true) {
                    $_SESSION['basari'] = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.';
                } else {
                    $_SESSION['hata'] = 'E-posta gönderimi sırasında bir hata oluştu. Lütfen tekrar deneyin. Detay: ' . $mail_sonuc;
                }
            } else {
                $_SESSION['hata'] = 'Bu e-posta adresine kayıtlı bir kullanıcı bulunamadı.';
            }

            $this->redirect('/sifremi-unuttum');
            return;
        }

        $this->loadView('auth/sifremi-unuttum.php', [
            'title' => 'Şifremi Unuttum - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Şifre sıfırlama sayfasını gösterir ve yeni şifreyi kaydeder.
     */
    public function sifirla()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/hesap');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $eposta = $_POST['eposta'] ?? '';
        } else if ($_GET) {
            $token = $_GET['token'] ?? '';
            $eposta = $_GET['eposta'] ?? '';
        }

        if (empty($token) || empty($eposta)) {
            $_SESSION['hata'] = 'Geçersiz şifre sıfırlama bağlantısı.';
            $this->redirect('/sifremi-unuttum');
            return;
        }

        $token_hash = hash('sha256', $token);
        $musteri = $this->db->fetch("
            SELECT t1.id, t1.eposta, t2.expires 
            FROM bt_musteriler t1
            INNER JOIN bt_musteri_tokens t2 ON t1.id = t2.musteri_id
            WHERE t1.eposta = :eposta AND t2.token = :token_hash AND t2.expires > NOW()
        ", ['eposta' => $eposta, 'token_hash' => $token_hash]);

        if (!$musteri) {
            $_SESSION['hata'] = 'Şifre sıfırlama bağlantınız geçersiz veya süresi dolmuş.';
            $this->redirect('/sifremi-unuttum');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $yeni_parola = $_POST['yeni_parola'] ?? '';
            $yeni_parola_tekrar = $_POST['yeni_parola_tekrar'] ?? '';

            if (empty($yeni_parola) || $yeni_parola !== $yeni_parola_tekrar || strlen($yeni_parola) < 6) {
                $_SESSION['hata'] = 'Yeni parolalar eşleşmiyor veya en az 6 karakter olmalıdır.';
                $this->redirect('/sifremi-unuttum/sifirla?token=' . $token . '&eposta=' . urlencode($eposta));
                return;
            }

            $this->db->update('bt_musteriler', ['parola' => password_hash($yeni_parola, PASSWORD_DEFAULT)], 'id = :id', ['id' => $musteri['id']]);

            $this->db->delete('bt_musteri_tokens', 'token = :token', ['token' => $token_hash]);

            $_SESSION['basari'] = 'Şifreniz başarıyla güncellendi. Artık yeni şifrenizle giriş yapabilirsiniz.';
            $this->redirect('/giris');
            return;
        }

        $this->loadView('auth/sifre-sifirla.php', [
            'title' => 'Şifre Sıfırlama - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'token' => $token,
            'eposta' => $eposta,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }
}
