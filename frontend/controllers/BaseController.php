<?php

class BaseController
{
    protected $db;
    protected $site_ayarlari;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
        global $site_ayarlari;
        $this->site_ayarlari = $site_ayarlari;
    }

    protected function loadView($view_file, $data = [])
    {
        extract($data);

        require_once(FRONTEND_VIEW_DIR . "includes/header.php");

        require_once(FRONTEND_VIEW_DIR . $view_file);

        require_once(FRONTEND_VIEW_DIR . "includes/footer.php");
    }

    protected function loadPartialView($view_file, $data = [])
    {
        extract($data);
        require_once(FRONTEND_VIEW_DIR . $view_file);
    }

    protected function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function isLoggedIn()
    {
        return isset($_SESSION['musteri_id']);
    }

    protected function getCurrentCustomer()
    {
        if ($this->isLoggedIn()) {
            return $this->db->fetch("SELECT * FROM bt_musteriler WHERE id = :id", ['id' => $_SESSION['musteri_id']]);
        }
        return null;
    }

    protected function getSepetAdet()
    {
        if (!isset($_SESSION['sepet'])) return 0;
        return array_sum(array_column($_SESSION['sepet'], 'adet'));
    }

    /**
     * Fiyatı KDV dahil ve para birimi sembolüyle formatlar.
     * @param float $price
     * @return string
     */
    protected function formatPrice($price)
    {

        $kdv_orani = $this->site_ayarlari['kdv_orani'] ?? $this->db->fetch("SELECT ayar_degeri FROM bt_sistem_ayarlari_gelismis WHERE ayar_anahtari = 'kdv_orani'")['ayar_degeri'] ?? 0;
        $para_birimi = $this->site_ayarlari['varsayilan_para_birimi'] ?? '₺';

        $kdv_dahil_fiyat = $price * (1 + $kdv_orani / 100);

        return number_format($kdv_dahil_fiyat, 2, ',', '.') . ' ' . $para_birimi;
    }
    /**
     * Bir ürünün onaylanmış yorumlarının ortalama puanını hesaplar.
     * @param int $urun_id
     * @return float
     */
    protected function getUrunOrtalamaPuani($urun_id)
    {
        $result = $this->db->fetch("
            SELECT AVG(puan) as ortalama_puan
            FROM bt_urun_yorumlari
            WHERE urun_id = :urun_id AND durum = 'Onaylandı'
        ", ['urun_id' => $urun_id]);

        return $result['ortalama_puan'] ?? 0.0;
    }
    /**
     * Belirli bir puana sahip ürün yorumlarının sayısını döndürür.
     * @param int $puan
     * @return int
     */
    protected function getUrunYorumSayisiByPuan($puan)
    {
        $result = $this->db->fetch("
            SELECT COUNT(*) as sayi
            FROM bt_urun_yorumlari
            WHERE puan = :puan AND durum = 'Onaylandı'
        ", ['puan' => $puan]);

        return $result['sayi'] ?? 0;
    }
}
