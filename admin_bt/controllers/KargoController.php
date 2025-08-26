<?php

// admin_bt/controllers/KargoController.php

class KargoController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Kargo yöntemleri ve firmaları listeleme sayfasını gösterir.
     */
    public function kargoYontemleriListe()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-yontemleri-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }
    public function kargoFirmalariListe()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-firmalari-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }


    /**
     * DataTables için kargo yöntemi verilerini döndürür.
     */
    public function listeleKargoYontemleri()
    {
        if (!hasPermission('Editör')) {
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
            $where_clause .= " AND (yontem_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kargo_yontemleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kargo_yontemleri" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'yontem_adi', 'temel_ucret', 'hesaplama_tipi', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, yontem_adi, temel_ucret, hesaplama_tipi, durum, aciklama, ucretsiz_kargo_limiti
                  FROM bt_kargo_yontemleri
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
     * Kargo yöntemi ekleme formunu gösterir.
     */
    public function kargoYontemiEkle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-yontemi-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Kargo yöntemi düzenleme formunu gösterir.
     */
    public function kargoYontemiDuzenle($yontem_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-yontemi-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kargo yöntemi ekleme işlemini yapar.
     */
    public function kargoYontemiEkleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kargo/liste');
        }

        $last_insert_id = $this->kargoYontemiVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kargo yöntemi başarıyla eklendi.";
            $this->yonlendir('kargolar/kargo-yontemi-duzenle/' . $last_insert_id);
        } else {
            $this->yonlendir('kargolar/kargo-yontemi-ekle');
        }
    }

    /**
     * Mevcut kargo yöntemini güncelleme işlemini yapar.
     */
    public function kargoYontemiDuzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kargo/liste');
        }

        $yontem_id = (int)($_POST['id'] ?? 0);
        if ($yontem_id === 0) {
            $_SESSION["hata"] = "Geçersiz kargo yöntemi ID'si.";
            $this->yonlendir('kargolar/kargo-yontemleri-liste');
        }

        if ($this->kargoYontemiVerisiniKaydet($yontem_id)) {
            $_SESSION["basari"] = "Kargo yöntemi başarıyla güncellendi.";
            $this->yonlendir('kargolar/kargo-yontemi-duzenle/' . $yontem_id);
        } else {
            $this->yonlendir('kargolar/kargo-yontemi-duzenle/' . $yontem_id);
        }
    }

    /**
     * Kargo yöntemlerini siler.
     */
    public function kargoYontemiSil()
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
            $result = $this->db->query("DELETE FROM bt_kargo_yontemleri WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Kargo yöntemleri başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek kargo yöntemi bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Kargo yöntemi silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Kargo yöntemlerinin durumunu günceller (AJAX).
     */
    public function kargoYontemiDurumGuncelle()
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
            $this->db->query("UPDATE bt_kargo_yontemleri SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Kargo durumu güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    /**
     * DataTables için kargo firması verilerini döndürür.
     */
    public function listeleKargoFirmalari()
    {
        if (!hasPermission('Editör')) {
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
            $where_clause .= " AND (firma_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kargo_firmalari")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kargo_firmalari" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'logo_url', 'firma_adi', 'takip_url_sablonu', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';
        if ($order_column === 'logo_url') {
            $order_column = 'id';
        }

        $query = "SELECT id, firma_adi, takip_url_sablonu, logo_url, durum
                  FROM bt_kargo_firmalari
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $firmalar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $firmalar
        ]);
        exit;
    }

    /**
     * Kargo firması ekleme formunu gösterir.
     */
    public function kargoFirmaEkle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-firma-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Kargo firması düzenleme formunu gösterir.
     */
    public function kargoFirmaDuzenle($firma_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kargolar/kargo-firma-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kargo firması ekleme işlemini yapar.
     */
    public function kargoFirmaEkleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kargo/liste');
        }

        $last_insert_id = $this->kargoFirmaVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kargo firması başarıyla eklendi.";
            $this->yonlendir('kargolar/kargo-firma-liste');
        } else {
            $this->yonlendir('kargolar/kargo-firma-ekle');
        }
    }

    /**
     * Mevcut kargo firmasını güncelleme işlemini yapar.
     */
    public function kargoFirmaDuzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kargo/liste');
        }

        $firma_id = (int)($_POST['id'] ?? 0);
        if ($firma_id === 0) {
            $_SESSION["hata"] = "Geçersiz kargo firması ID'si.";
            $this->yonlendir('kargolar/kargo-firmalari-liste');
        }

        if ($this->kargoFirmaVerisiniKaydet($firma_id)) {
            $_SESSION["basari"] = "Kargo firması başarıyla güncellendi.";
            $this->yonlendir('kargolar/kargo-firma-duzenle/' . $firma_id);
        } else {
            $this->yonlendir('kargolar/kargo-firma-duzenle/' . $firma_id);
        }
    }

    /**
     * Kargo firmalarını siler (AJAX).
     */
    public function kargoFirmaSil()
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
            $result = $this->db->query("DELETE FROM bt_kargo_firmalari WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Kargo firmaları başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek kargo firması bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Kargo firması silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function kargoYontemiVerisiniKaydet($yontem_id = 0)
    {
        $is_edit = $yontem_id > 0;

        $data = [
            'yontem_adi' => trim($_POST['yontem_adi'] ?? ''),
            'aciklama' => trim($_POST['aciklama'] ?? ''),
            'temel_ucret' => (float)($_POST['temel_ucret'] ?? 0),
            'hesaplama_tipi' => $_POST['hesaplama_tipi'] ?? 'sabit',
            'birim_ucret' => (float)($_POST['birim_ucret'] ?? 0),
            'yuzde_orani' => (float)($_POST['yuzde_orani'] ?? 0),
            'ucretsiz_kargo_limiti' => (float)($_POST['ucretsiz_kargo_limiti'] ?? 0),
            'min_teslimat_gun' => (int)($_POST['min_teslimat_gun'] ?? 1),
            'max_teslimat_gun' => (int)($_POST['max_teslimat_gun'] ?? 7),
            'durum' => $_POST['durum'] ?? 'Aktif',
        ];

        if (empty($data['yontem_adi'])) {
            $_SESSION["hata"] = "Yöntem adı boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_kargo_yontemleri', $data, 'id = :id', ['id' => $yontem_id]);
                return $yontem_id;
            } else {
                return $this->db->insert('bt_kargo_yontemleri', $data);
            }
        } catch (Exception $e) {
            error_log("Kargo yöntemi kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false;
        }
    }

    private function kargoFirmaVerisiniKaydet($firma_id = 0)
    {
        $is_edit = $firma_id > 0;

        $data = [
            'firma_adi' => trim($_POST['firma_adi'] ?? ''),
            'takip_url_sablonu' => trim($_POST['takip_url_sablonu'] ?? ''),
            'durum' => $_POST['durum'] ?? 'Aktif',
        ];

        if (empty($data['firma_adi'])) {
            $_SESSION["hata"] = "Firma adı boş bırakılamaz.";
            return false;
        }

        // Logo yükleme
        if (isset($_FILES['logo_url']) && $_FILES['logo_url']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['logo_url'], "frontend/assets/uploads/kargo-firmalari/");
            if ($file_path) {
                $data['logo_url'] = $file_path;
            } else {
                $_SESSION["hata"] = "Logo yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            // Eski logoyu koru
            $firma = $this->db->fetch("SELECT logo_url FROM bt_kargo_firmalari WHERE id = :id", ['id' => $firma_id]);
            $data['logo_url'] = $firma['logo_url'] ?? null;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_kargo_firmalari', $data, 'id = :id', ['id' => $firma_id]);
                return $firma_id;
            } else {
                return $this->db->insert('bt_kargo_firmalari', $data);
            }
        } catch (Exception $e) {
            error_log("Kargo firması kaydetme hatası: " . $e->getMessage());
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
