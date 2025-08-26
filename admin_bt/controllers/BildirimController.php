<?php

// admin_bt/controllers/BildirimController.php

class BildirimController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Bildirim listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "bildirimler/bildirimler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için bildirim verilerini döndürür.
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
            $where_clause .= " AND (baslik LIKE :search_val OR mesaj LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_bildirimler")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_bildirimler" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['id', 'bildirim_tipi', 'baslik', 'mesaj', 'oncelik', 'okundu_mu', 'olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, bildirim_tipi, baslik, mesaj, oncelik, okundu_mu, olusturma_tarihi, eylem_url
                  FROM bt_bildirimler
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $bildirimler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $bildirimler
        ]);
        exit;
    }

    /**
     * Belirli bir bildirimi okundu olarak işaretler (AJAX).
     */
    public function okunduOlarakIsaretle()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([1], $ids);
            $this->db->query("UPDATE bt_bildirimler SET okundu_mu = ? WHERE id IN ($placeholders)", $params);

            $this->jsonYanit(true, "Bildirimler okundu olarak işaretlendi.");
        } catch (Exception $e) {
            error_log("Bildirim güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Okunmamış bildirimleri çeker ve JSON olarak döner (header için).
     */
    public function getOkunmamisBildirimler()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        try {
            $bildirimler = $this->db->fetchAll(
                "SELECT * FROM bt_bildirimler WHERE okundu_mu = 0 ORDER BY olusturma_tarihi DESC LIMIT 5"
            );
            $okunmamis_sayisi = $this->db->fetch("SELECT COUNT(id) as count FROM bt_bildirimler WHERE okundu_mu = 0")['count'];

            $this->jsonYanit(true, "Bildirimler başarıyla çekildi.", [
                'bildirimler' => $bildirimler,
                'sayi' => $okunmamis_sayisi
            ]);
        } catch (Exception $e) {
            error_log("Okunmamış bildirimleri getirme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

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
