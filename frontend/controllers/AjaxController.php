<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class AjaxController extends BaseController
{
    public function sepetAdet()
    {
        $this->jsonResponse(['sepet_adet' => $this->getSepetAdet()]);
    }

    public function sepetEkle()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $urun_id = $_POST['urun_id'] ?? 0;
            $varyant_id = $_POST['varyant_id'] ?? null;
            $adet = $_POST['adet'] ?? 1;

            $urun = $this->db->fetch("SELECT * FROM bt_urunler WHERE id = :id AND durum = 'Aktif'", ['id' => $urun_id]);
            if (!$urun) {
                $this->jsonResponse(['success' => false, 'message' => 'Ürün bulunamadı']);
                return;
            }

            if ($varyant_id) {
                $varyant = $this->db->fetch(
                    "SELECT * FROM bt_urun_varyantlari WHERE id = :id AND urun_id = :urun_id",
                    ['id' => $varyant_id, 'urun_id' => $urun_id]
                );
                if (!$varyant || $varyant['stok_adedi'] < $adet) {
                    $this->jsonResponse(['success' => false, 'message' => 'Yeterli stok yok']);
                    return;
                }
            }

            if (!isset($_SESSION['sepet'])) {
                $_SESSION['sepet'] = [];
            }

            $sepet_key = $urun_id . '_' . ($varyant_id ?? '0');

            if (isset($_SESSION['sepet'][$sepet_key])) {
                $_SESSION['sepet'][$sepet_key]['adet'] += $adet;
            } else {
                $_SESSION['sepet'][$sepet_key] = [
                    'urun_id' => $urun_id,
                    'varyant_id' => $varyant_id,
                    'adet' => $adet
                ];
            }

            $sepet_adet = $this->getSepetAdet();
            $this->jsonResponse(['success' => true, 'sepet_adet' => $sepet_adet, 'message' => 'Ürün sepete eklendi']);
        }
    }

    public function urunAra()
    {
        $q = $_GET['q'] ?? '';

        if (strlen($q) < 2) {
            $this->jsonResponse(['urunler' => []]);
            return;
        }

        $urunler = $this->db->fetchAll("
            SELECT u.id, u.urun_adi,
                   (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id) as min_fiyat,
                   (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id AND kapak_mi = 1 LIMIT 1) as kapak_gorsel
            FROM bt_urunler u
            WHERE u.durum = 'Aktif' AND u.urun_adi LIKE :q
            ORDER BY u.urun_adi ASC
            LIMIT 10
        ", ['q' => '%' . $q . '%']);

        $this->jsonResponse(['urunler' => $urunler]);
    }
}
