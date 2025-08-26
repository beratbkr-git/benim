<?php
// frontend/controllers/IstekListesiController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class IstekListesiController extends BaseController
{
    /**
     * Kullanıcının istek listesi sayfasını gösterir.
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/giris');
            return;
        }

        $musteri = $this->getCurrentCustomer();

        // Varyantlı ve varyantsız ürünleri doğru şekilde çekmek için karmaşık bir sorgu
        $istek_listesi = $this->db->fetchAll("
            SELECT 
                t1.*, 
                t2.urun_adi, 
                t2.slug, 
                t2.satis_fiyati, 
                t4.gorsel_url,
                t3.fiyat AS varyant_fiyati,
                GROUP_CONCAT(CONCAT(t5.deger) SEPARATOR ', ') AS varyant_adi
            FROM bt_istek_listesi t1
            INNER JOIN bt_urunler t2 ON t1.urun_id = t2.id
            LEFT JOIN bt_urun_varyantlari t3 ON t1.varyant_id = t3.id
            LEFT JOIN bt_urun_gorselleri t4 ON t2.id = t4.urun_id AND t4.kapak_mi = 1
            LEFT JOIN bt_urun_varyant_degerleri t6 ON t3.id = t6.varyant_id
            LEFT JOIN bt_varyant_degerleri t5 ON t6.deger_id = t5.id
            WHERE t1.musteri_id = :musteri_id 
            GROUP BY t1.id
            ORDER BY t1.olusturma_tarihi DESC
        ", ['musteri_id' => $musteri['id']]);

        $this->loadView('hesap/istek-listesi.php', [
            'title' => 'İstek Listesi - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'istek_listesi' => $istek_listesi,
            'musteri' => $musteri,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    /**
     * Ürünü istek listesine ekler (AJAX).
     */
    public function ekle()
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $varyant_id = (int)($_POST['varyant_id'] ?? 0) > 0 ? (int)($_POST['varyant_id'] ?? 0) : null;
        $musteri_id = $this->getCurrentCustomer()['id'];

        if ($urun_id === 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün.']);
            return;
        }

        try {
            // Ürünün zaten istek listesinde olup olmadığını kontrol et
            $mevcut_urun = $this->db->fetch("
                SELECT id FROM bt_istek_listesi WHERE musteri_id = :musteri_id AND urun_id = :urun_id AND (varyant_id = :varyant_id OR varyant_id IS NULL)
            ", ['musteri_id' => $musteri_id, 'urun_id' => $urun_id, 'varyant_id' => $varyant_id]);

            if ($mevcut_urun) {
                $this->jsonResponse(['success' => false, 'message' => 'Bu ürün zaten istek listenizde.']);
                return;
            }

            $this->db->insert('bt_istek_listesi', [
                'musteri_id' => $musteri_id,
                'urun_id' => $urun_id,
                'varyant_id' => $varyant_id
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Ürün istek listenize eklendi.']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
        }
    }

    /**
     * Ürünü istek listesinden siler (AJAX).
     */
    public function sil()
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $varyant_id = (int)($_POST['varyant_id'] ?? 0) > 0 ? (int)($_POST['varyant_id'] ?? 0) : null;
        $musteri_id = $this->getCurrentCustomer()['id'];

        if ($urun_id === 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün.']);
            return;
        }

        try {
            $this->db->delete('bt_istek_listesi', 'musteri_id = :musteri_id AND urun_id = :urun_id AND (varyant_id = :varyant_id OR varyant_id IS NULL)', [
                'musteri_id' => $musteri_id,
                'urun_id' => $urun_id,
                'varyant_id' => $varyant_id
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Ürün istek listenizden kaldırıldı.']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
        }
    }
}
