<?php

// admin_bt/controllers/KampanyaController.php

class KampanyaController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    // =====================================================================
    // KAMPANYA YÖNETİMİ METOTLARI
    // =====================================================================

    /**
     * Kampanya listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kampanyalar-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için kampanya verilerini döndürür.
     */
    public function listele()
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
            $where_clause .= " AND (kampanya_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kampanyalar")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kampanyalar" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'kampanya_adi', 'indirim_tipi', 'indirim_degeri', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, kampanya_adi, indirim_tipi, indirim_degeri, durum, baslangic_tarihi, bitis_tarihi
                  FROM bt_kampanyalar
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $kampanyalar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $kampanyalar
        ]);
        exit;
    }

    /**
     * Kampanya ekleme/düzenleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kampanyalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Kupon ekleme/düzenleme formunu gösterir.
     */
    public function duzenle($kampanya_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kampanyalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kampanya ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kampanyalar/liste');
        }

        $last_insert_id = $this->kampanyaVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kampanya başarıyla eklendi.";
            $this->yonlendir('kampanyalar/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Kampanya eklenirken bir hata oluştu.";
            $this->yonlendir('kampanyalar/ekle');
        }
    }

    /**
     * Mevcut kampanyayı güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kampanyalar/liste');
        }

        $kampanya_id = (int)($_POST['id'] ?? 0);
        if ($kampanya_id === 0) {
            $_SESSION["hata"] = "Geçersiz kampanya ID'si.";
            $this->yonlendir('kampanyalar/liste');
        }

        if ($this->kampanyaVerisiniKaydet($kampanya_id)) {
            $_SESSION["basari"] = "Kampanya başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Kampanya güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('kampanyalar/duzenle/' . $kampanya_id);
    }

    /**
     * Kampanyaları siler (AJAX).
     */
    public function sil()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Silme yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_kampanyalar WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Kampanyalar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek kampanya bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Kampanya silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // KUPON YÖNETİMİ METOTLARI
    // =====================================================================

    /**
     * İndirim kuponları listeleme sayfasını gösterir.
     */
    public function kuponlarListe()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kuponlar-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için kupon verilerini döndürür.
     */
    public function listeleKupon()
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
            $where_clause .= " AND (kupon_kodu LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_indirim_kuponlari")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_indirim_kuponlari" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'kupon_kodu', 'indirim_tipi', 'indirim_degeri', 'kullanim_limiti', 'durum', 'bitis_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'id';
        if ($order_column === 'bitis_tarihi') {
            $order_column = 'bitis_tarihi';
        }

        $query = "SELECT id, kupon_kodu, indirim_tipi, indirim_degeri, kullanim_limiti, kullanilan_adet, durum, bitis_tarihi
                  FROM bt_indirim_kuponlari
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $kuponlar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $kuponlar
        ]);
        exit;
    }

    /**
     * Kupon ekleme/düzenleme formunu gösterir.
     */
    public function ekleKupon()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kuponlar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function duzenleKupon($kupon_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kampanyalar/kuponlar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kupon ekleme işlemini yapar.
     */
    public function ekleKuponKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kampanyalar/kuponlar-liste');
        }

        $last_insert_id = $this->kuponVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kupon başarıyla eklendi.";
            $this->yonlendir('kampanyalar/duzenle-kupon/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Kupon eklenirken bir hata oluştu.";
            $this->yonlendir('kampanyalar/ekle-kupon');
        }
    }

    /**
     * Mevcut kuponu güncelleme işlemini yapar.
     */
    public function duzenleKuponKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kampanyalar/kuponlar-liste');
        }

        $kupon_id = (int)($_POST['id'] ?? 0);
        if ($kupon_id === 0) {
            $_SESSION["hata"] = "Geçersiz kupon ID'si.";
            $this->yonlendir('kampanyalar/kuponlar-liste');
        }

        if ($this->kuponVerisiniKaydet($kupon_id)) {
            $_SESSION["basari"] = "Kupon başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Kupon güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('kampanyalar/duzenle-kupon/' . $kupon_id);
    }

    /**
     * Kuponları siler (AJAX).
     */
    public function silKupon()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Silme yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_indirim_kuponlari WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Kuponlar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek kupon bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Kupon silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Kupon durumunu günceller (AJAX).
     */
    public function kuponDurumGuncelle()
    {
        if (!hasPermission('Editör')) {
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
            $this->db->query("UPDATE bt_indirim_kuponlari SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Kupon durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function kuponVerisiniKaydet($kupon_id = 0)
    {
        $is_edit = $kupon_id > 0;

        $data = [
            'kupon_kodu' => trim($_POST['kupon_kodu'] ?? ''),
            'indirim_tipi' => $_POST['indirim_tipi'] ?? 'Yuzde',
            'indirim_degeri' => (float)($_POST['indirim_degeri'] ?? 0),
            'min_sepet_tutari' => (float)($_POST['min_sepet_tutari'] ?? 0),
            'kullanim_limiti' => (int)($_POST['kullanim_limiti'] ?? -1),
            'durum' => $_POST['durum'] ?? 'Pasif',
            'baslangic_tarihi' => $_POST['baslangic_tarihi'] ?? null,
            'bitis_tarihi' => $_POST['bitis_tarihi'] ?? null,
        ];

        if (empty($data['kupon_kodu'])) {
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_indirim_kuponlari', $data, 'id = :id', ['id' => $kupon_id]);
                return $kupon_id;
            } else {
                return $this->db->insert('bt_indirim_kuponlari', $data);
            }
        } catch (Exception $e) {
            error_log("Kupon kaydetme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function kampanyaVerisiniKaydet($kampanya_id = 0)
    {
        $is_edit = $kampanya_id > 0;

        $data = [
            'kampanya_adi' => trim($_POST['kampanya_adi'] ?? ''),
            'indirim_tipi' => $_POST['indirim_tipi'] ?? 'Yuzde',
            'indirim_degeri' => (float)($_POST['indirim_degeri'] ?? 0),
            'min_sepet_tutari' => (float)($_POST['min_sepet_tutari'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Pasif',
            'baslangic_tarihi' => $_POST['baslangic_tarihi'] ?? null,
            'bitis_tarihi' => $_POST['bitis_tarihi'] ?? null,
            'kriterler' => $_POST['kriterler'] ?? null
        ];

        if (empty($data['kampanya_adi'])) {
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_kampanyalar', $data, 'id = :id', ['id' => $kampanya_id]);
                return $kampanya_id;
            } else {
                return $this->db->insert('bt_kampanyalar', $data);
            }
        } catch (Exception $e) {
            error_log("Kampanya kaydetme hatası: " . $e->getMessage());
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
