<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class IletisimController extends BaseController
{
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ad_soyad = $_POST['ad_soyad'] ?? '';
            $eposta = $_POST['eposta'] ?? '';
            $telefon = $_POST['telefon'] ?? '';
            $konu = $_POST['konu'] ?? '';
            $mesaj = $_POST['mesaj'] ?? '';

            $hatalar = [];
            if (empty($ad_soyad)) $hatalar[] = 'Ad Soyad zorunludur.';
            if (empty($eposta) || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
            if (empty($konu)) $hatalar[] = 'Konu zorunludur.';
            if (empty($mesaj)) $hatalar[] = 'Mesaj zorunludur.';

            if (empty($hatalar)) {
                $iletisim_data = [
                    'ad_soyad' => $ad_soyad,
                    'eposta' => $eposta,
                    'telefon' => $telefon,
                    'konu' => $konu,
                    'mesaj' => $mesaj,
                    'durum' => 'Yeni',
                    'olusturma_tarihi' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('bt_iletisim_mesajlari', $iletisim_data);
                $_SESSION['basari'] = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
                $this->redirect('/iletisim');
                return;
            } else {
                $_SESSION['hata'] = implode('<br>', $hatalar);
            }
        }

        $this->loadView('iletisim/iletisim.php', [
            'title' => 'İletişim - ' . ($this->site_ayarlari['site_baslik'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }
}
