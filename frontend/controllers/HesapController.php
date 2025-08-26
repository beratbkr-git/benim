<?php
// frontend/controllers/HesapController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class HesapController extends BaseController
{
    /**
     * Kullanıcı dashboard ana sayfasını gösterir.
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/giris');
            return;
        }

        $musteri = $this->getCurrentCustomer();

        $son_siparisler = $this->db->fetchAll("
            SELECT * FROM bt_siparisler 
            WHERE musteri_id = :musteri_id 
            ORDER BY olusturma_tarihi DESC 
            LIMIT 5
        ", ['musteri_id' => $musteri['id']]);

        $this->loadView('hesap/hesap.php', [
            'title' => 'Hesabım - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'musteri' => $musteri,
            'son_siparisler' => $son_siparisler,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Kullanıcı profil bilgilerini yönetir.
     */
    public function profil()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/giris');
            return;
        }

        $musteri = $this->getCurrentCustomer();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ad_soyad' => $_POST['ad_soyad'] ?? '',
                'telefon' => $_POST['telefon'] ?? '',
                'dogum_tarihi' => $_POST['dogum_tarihi'] ?? null,
                'cinsiyet' => $_POST['cinsiyet'] ?? null,
                'meslek' => $_POST['meslek'] ?? null,
            ];

            if (!empty($_POST['yeni_parola'])) {
                if ($_POST['yeni_parola'] === $_POST['yeni_parola_tekrar']) {
                    $data['parola'] = password_hash($_POST['yeni_parola'], PASSWORD_DEFAULT);
                } else {
                    $_SESSION['hata'] = 'Yeni parolalar eşleşmiyor.';
                    $this->redirect('/hesap/profil');
                    return;
                }
            }

            $this->db->update('bt_musteriler', $data, 'id = :id', ['id' => $musteri['id']]);
            $_SESSION['basari'] = 'Profil bilgileriniz başarıyla güncellendi.';
            $this->redirect('/hesap/profil');
            return;
        }

        $this->loadView('hesap/profil.php', [
            'title' => 'Profilim - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'musteri' => $musteri,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Kullanıcının sipariş geçmişini listeler.
     */
    public function siparisler()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/giris');
            return;
        }

        $musteri = $this->getCurrentCustomer();
        $sayfa = $_GET['sayfa'] ?? 1;
        $limit = 10;
        $offset = ($sayfa - 1) * $limit;

        $siparisler = $this->db->fetchAll("
            SELECT * FROM bt_siparisler 
            WHERE musteri_id = :musteri_id 
            ORDER BY olusturma_tarihi DESC
            LIMIT {$limit} OFFSET {$offset}
        ", ['musteri_id' => $musteri['id']]);

        $toplam_siparis = $this->db->fetch("
            SELECT COUNT(*) as toplam FROM bt_siparisler 
            WHERE musteri_id = :musteri_id
        ", ['musteri_id' => $musteri['id']])['toplam'];

        $this->loadView('hesap/siparisler.php', [
            'title' => 'Siparişlerim - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'siparisler' => $siparisler,
            'toplam_siparis' => $toplam_siparis,
            'musteri' => $musteri,
            'sayfa' => $sayfa,
            'toplam_sayfa' => ceil($toplam_siparis / $limit),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Kullanıcının adreslerini yönetir.
     */
    public function adresler()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/giris');
            return;
        }

        $musteri = $this->getCurrentCustomer();
        $adresler = $this->db->fetchAll("
            SELECT * FROM bt_musteri_adresleri WHERE musteri_id = :musteri_id
        ", ['musteri_id' => $musteri['id']]);

        $this->loadView('hesap/adresler.php', [
            'title' => 'Adreslerim - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'musteri' => $musteri,
            'adresler' => $adresler,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Yeni adres ekleme işlemini yönetir.
     */
    public function adresEkle()
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $musteri = $this->getCurrentCustomer();

        $data = [
            'musteri_id' => $musteri['id'],
            'adres_baslik' => trim($_POST['adres_baslik'] ?? ''),
            'ad_soyad' => trim($_POST['ad_soyad'] ?? ''),
            'adres' => trim($_POST['adres'] ?? ''),
            'il' => trim($_POST['il'] ?? ''),
            'ilce' => trim($_POST['ilce'] ?? ''),
            'posta_kodu' => trim($_POST['posta_kodu'] ?? ''),
            'telefon' => trim($_POST['telefon'] ?? ''),
            'varsayilan_adres' => isset($_POST['varsayilan_adres']) ? 1 : 0
        ];

        if (empty($data['adres_baslik']) || empty($data['ad_soyad']) || empty($data['adres']) || empty($data['il']) || empty($data['ilce'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Zorunlu alanları doldurunuz.']);
            return;
        }

        try {
            if ($data['varsayilan_adres'] == 1) {
                $this->db->update('bt_musteri_adresleri', ['varsayilan_adres' => 0], 'musteri_id = :musteri_id', ['musteri_id' => $musteri['id']]);
            }
            $this->db->insert('bt_musteri_adresleri', $data);
            $_SESSION['basari'] = 'Yeni adres başarıyla eklendi.';
            $this->jsonResponse(['success' => true, 'message' => 'Yeni adres başarıyla eklendi.']);
        } catch (Exception $e) {
            $_SESSION['hata'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            $this->jsonResponse(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
        }
    }

    /**
     * Mevcut adresi düzenleme işlemini yönetir.
     * @param int $adres_id
     */
    public function adresDuzenle($adres_id)
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $musteri = $this->getCurrentCustomer();
        $adres_id = (int)$adres_id;

        $adres = $this->db->fetch("SELECT id FROM bt_musteri_adresleri WHERE id = :id AND musteri_id = :musteri_id", ['id' => $adres_id, 'musteri_id' => $musteri['id']]);
        if (!$adres) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz adres.']);
            return;
        }

        $data = [
            'adres_baslik' => trim($_POST['adres_baslik'] ?? ''),
            'ad_soyad' => trim($_POST['ad_soyad'] ?? ''),
            'adres' => trim($_POST['adres'] ?? ''),
            'il' => trim($_POST['il'] ?? ''),
            'ilce' => trim($_POST['ilce'] ?? ''),
            'posta_kodu' => trim($_POST['posta_kodu'] ?? ''),
            'telefon' => trim($_POST['telefon'] ?? ''),
            'varsayilan_adres' => isset($_POST['varsayilan_adres']) ? 1 : 0
        ];

        if (empty($data['adres_baslik']) || empty($data['ad_soyad']) || empty($data['adres']) || empty($data['il']) || empty($data['ilce'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Zorunlu alanları doldurunuz.']);
            return;
        }

        try {
            if ($data['varsayilan_adres'] == 1) {
                $this->db->update('bt_musteri_adresleri', ['varsayilan_adres' => 0], 'musteri_id = :musteri_id', ['musteri_id' => $musteri['id']]);
            }

            $this->db->update('bt_musteri_adresleri', $data, 'id = :id', ['id' => $adres_id]);
            $_SESSION['basari'] = 'Adres başarıyla güncellendi.';
            $this->jsonResponse(['success' => true, 'message' => 'Adres başarıyla güncellendi.']);
        } catch (Exception $e) {
            $_SESSION['hata'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            $this->jsonResponse(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
        }
    }

    /**
     * Adresi silme işlemini yönetir.
     * @param int $adres_id
     */
    public function adresSil($adres_id)
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $musteri = $this->getCurrentCustomer();
        $adres_id = (int)$adres_id;

        $adres = $this->db->fetch("SELECT id FROM bt_musteri_adresleri WHERE id = :id AND musteri_id = :musteri_id", ['id' => $adres_id, 'musteri_id' => $musteri['id']]);
        if (!$adres) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz adres.']);
            return;
        }

        try {
            $this->db->delete('bt_musteri_adresleri', 'id = :id', ['id' => $adres_id]);
            $_SESSION['basari'] = 'Adres başarıyla silindi.';
            $this->jsonResponse(['success' => true, 'message' => 'Adres başarıyla silindi.']);
        } catch (Exception $e) {
            $_SESSION['hata'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            $this->jsonResponse(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
        }
    }
}
