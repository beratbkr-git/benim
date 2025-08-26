<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class GirisController extends BaseController
{
    public function index()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/hesap');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eposta = $_POST['eposta'] ?? '';
            $parola = $_POST['parola'] ?? '';
            $beni_hatirla = isset($_POST['beni_hatirla']);

            if (empty($eposta) || empty($parola)) {
                $_SESSION['hata'] = 'E-posta ve parola alanları zorunludur.';
            } else {
                $musteri = $this->db->fetch("
                    SELECT * FROM bt_musteriler 
                    WHERE eposta = :eposta AND durum = 'Aktif'
                ", ['eposta' => $eposta]);

                if ($musteri && password_verify($parola, $musteri['parola'])) {
                    $_SESSION['musteri_id'] = $musteri['id'];
                    $_SESSION['musteri'] = $musteri;

                    if ($beni_hatirla) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

                        $this->db->insert('bt_musteri_tokens', [
                            'musteri_id' => $musteri['id'],
                            'token' => hash('sha256', $token),
                            'expires' => $expires
                        ]);

                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    }
                    $_SESSION['basari'] = "Giriş Başarılı Hoş Geldiniz";
                    $redirect = $_GET['redirect'] ?? '/hesap';
                    $this->redirect($redirect);
                    return;
                } else {
                    $_SESSION['hata'] = 'E-posta veya parola hatalı.';
                }
            }
            $this->redirect('/giris');
            return;
        }

        $this->loadView('auth/giris.php', [
            'title' => 'Giriş Yap - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    public function cikis()
    {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $this->db->delete('bt_musteri_tokens', 'token = :token', ['token' => hash('sha256', $token)]);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        session_destroy();
        $this->redirect('/');
    }
}
