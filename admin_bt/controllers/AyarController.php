<?php

// admin_bt/controllers/AyarController.php

class AyarController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    // =====================================================================
    // GENEL AYARLAR METOTLARI
    // =====================================================================

    /**
     * Genel ayarlar sayfasını gösterir.
     */
    public function genel()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/genel.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * E-ticaret ayarları sayfasını gösterir.
     */
    public function eticaret()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/eticaret.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * E-posta ayarları sayfasını gösterir.
     */
    public function eposta()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/eposta.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Ödeme Yöntemleri sayfasını gösterir.
     */
    public function odeme()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/odeme.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Vergi Oranları sayfasını gösterir.
     */
    public function vergi()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/vergi.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Dil ve Para Birimi sayfasını gösterir.
     */
    public function dil()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/dil.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Ayarları kaydeder.
     */
    public function kaydet()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $_SESSION["hata"] = "Geçersiz istek.";
            $this->yonlendir('ayarlar/genel');
        }

        try {
            $ayarlar = $_POST['ayarlar'] ?? [];
            if (!empty($ayarlar)) {
                foreach ($_FILES as $key => $resim) {
                    if ($resim["size"] > 0) {
                        $ayarlar[$key] = dosyaYukle($resim, "frontend/assets/uploads/genel/");
                    }
                }
                foreach ($ayarlar as $anahtar => $deger) {
                    $this->db->update(
                        'bt_site_ayarlari',
                        ['ayar_degeri' => $deger],
                        'ayar_anahtari = :anahtar',
                        ['anahtar' => $anahtar]
                    );
                }
            }
            $_SESSION["basari"] = "Ayarlar başarıyla güncellendi.";
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        } catch (Exception $e) {
            error_log("Ayar kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        }
    }

    /**
     * Gelişmiş ayarları kaydeder.
     */
    public function kaydetGelismis()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $_SESSION["hata"] = "Geçersiz istek.";
            $this->yonlendir('ayarlar/eticaret');
        }

        try {
            $ayarlar = $_POST['ayarlar'] ?? [];
            if (!empty($ayarlar)) {
                foreach ($ayarlar as $anahtar => $deger) {
                    $this->db->update(
                        'bt_sistem_ayarlari_gelismis',
                        ['ayar_degeri' => $deger],
                        'ayar_anahtari = :anahtar',
                        ['anahtar' => $anahtar]
                    );
                }
            }
            $_SESSION["basari"] = "Ayarlar başarıyla güncellendi.";
            // Hangi sayfadan geldiğini bulup oraya yönlendir.
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        } catch (Exception $e) {
            error_log("Gelişmiş ayar kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        }
    }

    // =====================================================================
    // ÖDEME YÖNTEMLERİ METOTLARI
    // =====================================================================

    /**
     * DataTables için ödeme yöntemi verilerini döndürür.
     */
    public function listeleOdemeYontemleri()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        header('Content-Type: application/json');

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $bind_params = [];
        $where_clause = " WHERE 1=1 ";

        if (!empty($search_value)) {
            $where_clause .= " AND (yontem_adi LIKE :search_val OR yontem_kodu LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_odeme_yontemleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_odeme_yontemleri" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'yontem_adi', 'yontem_kodu', 'komisyon_orani', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, yontem_adi, yontem_kodu, komisyon_orani, durum
                  FROM bt_odeme_yontemleri
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $yontemler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $yontemler
        ]);
        exit;
    }

    /**
     * Ödeme yöntemi ekleme formunu gösterir.
     */
    public function odemeYontemiEkle()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/odeme-yontemi-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Ödeme yöntemi düzenleme formunu gösterir.
     */
    public function odemeYontemiDuzenle($yontem_id)
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "ayarlar/odeme-yontemi-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni ödeme yöntemi ekleme işlemini yapar.
     */
    public function odemeYontemiEkleKontrol()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('ayarlar/odeme');
        }

        $last_insert_id = $this->odemeYontemiVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Ödeme yöntemi başarıyla eklendi.";
            $this->yonlendir('ayarlar/odeme-yontemi-liste');
        } else {
            // Hata mesajı zaten session'da ayarlandı
            $this->yonlendir('ayarlar/odeme-yontemi-ekle');
        }
    }

    /**
     * Mevcut ödeme yöntemini güncelleme işlemini yapar.
     */
    public function odemeYontemiDuzenleKontrol()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('ayarlar/odeme');
        }

        $yontem_id = (int)($_POST['id'] ?? 0);
        if ($yontem_id === 0) {
            $_SESSION["hata"] = "Geçersiz ödeme yöntemi ID'si.";
            $this->yonlendir('ayarlar/odeme');
        }

        if ($this->odemeYontemiVerisiniKaydet($yontem_id)) {
            $_SESSION["basari"] = "Ödeme yöntemi başarıyla güncellendi.";
            $this->yonlendir('ayarlar/odeme-yontemi-duzenle/' . $yontem_id);
        } else {
            $this->yonlendir('ayarlar/odeme-yontemi-duzenle/' . $yontem_id);
        }
    }

    /**
     * Ödeme yöntemlerini siler.
     */
    public function odemeYontemiSil()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_odeme_yontemleri WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Ödeme yöntemleri başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek ödeme yöntemi bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Ödeme yöntemi silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Ödeme yöntemlerinin durumunu günceller (AJAX).
     */
    public function odemeDurumGuncelle()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($ids) || !is_array($ids) || !in_array($status, ['Aktif', 'Pasif'])) {
            $this->jsonYanit(false, 'Geçersiz ID listesi veya durum bilgisi.');
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);
            $this->db->query("UPDATE bt_odeme_yontemleri SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Ödeme durumu güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function odemeYontemiVerisiniKaydet($yontem_id = 0)
    {
        $is_edit = $yontem_id > 0;

        $data = [
            'yontem_adi' => trim($_POST['yontem_adi'] ?? ''),
            'yontem_kodu' => trim($_POST['yontem_kodu'] ?? ''),
            'aciklama' => trim($_POST['aciklama'] ?? ''),
            'komisyon_orani' => (float)($_POST['komisyon_orani'] ?? 0),
            'min_tutar' => (float)($_POST['min_tutar'] ?? 0),
            'max_tutar' => empty($_POST['max_tutar']) ? null : (float)($_POST['max_tutar']),
            'durum' => $_POST['durum'] ?? 'Pasif',
        ];

        if (empty($data['yontem_adi']) || empty($data['yontem_kodu'])) {
            $_SESSION["hata"] = "Yöntem adı ve Yöntem kodu boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                // Yöntem kodu benzersiz olmalı kontrolü
                $existing = $this->db->fetch("SELECT id FROM bt_odeme_yontemleri WHERE yontem_kodu = :kod AND id != :id", ['kod' => $data['yontem_kodu'], 'id' => $yontem_id]);
                if ($existing) {
                    $_SESSION["hata"] = "Bu yöntem kodu zaten kullanılıyor.";
                    return false;
                }
                $this->db->update('bt_odeme_yontemleri', $data, 'id = :id', ['id' => $yontem_id]);
                return $yontem_id;
            } else {
                // Yöntem kodu benzersiz olmalı kontrolü
                $existing = $this->db->fetch("SELECT id FROM bt_odeme_yontemleri WHERE yontem_kodu = :kod", ['kod' => $data['yontem_kodu']]);
                if ($existing) {
                    $_SESSION["hata"] = "Bu yöntem kodu zaten kullanılıyor.";
                    return false;
                }
                return $this->db->insert('bt_odeme_yontemleri', $data);
            }
        } catch (Exception $e) {
            error_log("Ödeme yöntemi kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false;
        }
    }

    private function jsonYanit($success, $message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    private function yonlendir($path)
    {
        global $yonetimurl;
        header("Location: /{$yonetimurl}/{$path}");
        exit;
    }
}
