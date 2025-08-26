<?php
// frontend/controllers/SepetController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class SepetController extends BaseController
{
    /**
     * Sepet sayfasını gösterir.
     */
    public function index()
    {
        // Sepet sayfasının içeriğini burada oluşturabilirsiniz.
        // Şimdilik boş bırakıyorum.
    }

    /**
     * Ürünü sepete ekler veya adedini günceller (AJAX).
     */
    public function ekle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $varyant_id = (int)($_POST['varyant_id'] ?? 0) > 0 ? (int)($_POST['varyant_id'] ?? 0) : null;
        $adet = (int)($_POST['adet'] ?? 1);

        if ($urun_id <= 0 || $adet <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün veya adet.']);
            return;
        }

        if (!isset($_SESSION['sepet'])) {
            $_SESSION['sepet'] = [];
        }

        // Anahtar oluşturma mantığı düzeltildi
        $sepet_key = $urun_id . ($varyant_id ? '_' . $varyant_id : '_');

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
        $this->jsonResponse(['success' => true, 'message' => 'Ürün sepete eklendi.', 'sepet_adet' => $sepet_adet]);
    }

    /**
     * Sepetteki ürünün adedini günceller (AJAX).
     */
    /**
     * Sepetteki ürünün adedini günceller (AJAX).
     */
    public function guncelle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $varyant_id = (int)($_POST['varyant_id'] ?? 0) > 0 ? (int)($_POST['varyant_id'] ?? 0) : null;
        $adet = (int)($_POST['adet'] ?? 1);

        if ($urun_id <= 0 || $adet <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün veya adet.']);
            return;
        }

        // Anahtar oluşturma mantığı düzeltildi
        $sepet_key = $urun_id . ($varyant_id ? '_' . $varyant_id : '_');
        $eski_adet = $_SESSION['sepet'][$sepet_key]['adet'] ?? 0;

        if (isset($_SESSION['sepet'][$sepet_key])) {
            $_SESSION['sepet'][$sepet_key]['adet'] = $adet;

            $sepet = getSepet();
            $urun_toplam_fiyat = 0;
            foreach ($sepet['urunler'] as $urun) {
                if ($urun['id'] == $urun_id && ($urun['varyant_id'] ?? null) == $varyant_id) {
                    $urun_toplam_fiyat = $urun['toplam'];
                    break;
                }
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Ürün adedi güncellendi.',
                'sepet_adet' => $this->getSepetAdet(),
                'toplam_tutar' => $this->formatPrice($sepet['toplam_tutar']),
                'urun_toplam_fiyat' => $this->formatPrice($urun_toplam_fiyat),
                'eski_adet' => $eski_adet
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Ürün sepette bulunamadı.']);
        }
    }

    /**
     * Ürünü sepetten siler (AJAX).
     */
    public function sil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }

        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $varyant_id = (int)($_POST['varyant_id'] ?? 0) > 0 ? (int)($_POST['varyant_id'] ?? 0) : null;

        if ($urun_id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün.']);
            return;
        }

        $sepet_key = $urun_id . ($varyant_id ? '_' . $varyant_id : '_');

        if (isset($_SESSION['sepet'][$sepet_key])) {
            unset($_SESSION['sepet'][$sepet_key]);

            $sepet = getSepet();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Ürün sepetten kaldırıldı.',
                'sepet_adet' => $this->getSepetAdet(),
                'toplam_tutar' => $this->formatPrice($sepet['toplam_tutar'])
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Ürün sepette bulunamadı.']);
        }
    }
}
