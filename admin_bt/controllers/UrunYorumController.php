<?php

// admin_bt/controllers/UrunYorumController.php

class UrunYorumController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Ürün yorumları listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "urun-yorumlari/urun-yorumlari-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Ürün yorumu detay sayfasını gösterir.
     */
    public function detay($yorum_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }

        $yorum_id = (int)$yorum_id;
        if ($yorum_id === 0) {
            $_SESSION['hata'] = "Geçersiz yorum ID'si.";
            $this->yonlendir('urun-yorumlari/liste');
        }

        $yorum_data = $this->db->fetch(
            "SELECT T1.*, T2.urun_adi, T2.id as urun_id, T3.ad_soyad, T3.eposta, T3.id as musteri_id
             FROM bt_urun_yorumlari T1
             LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
             LEFT JOIN bt_musteriler T3 ON T1.musteri_id = T3.id
             WHERE T1.id = :id",
            ['id' => $yorum_id]
        );

        if (!$yorum_data) {
            $_SESSION['hata'] = "Yorum bulunamadı.";
            $this->yonlendir('urun-yorumlari/liste');
        }

        $yorum = $yorum_data;

        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "urun-yorumlari/urun-yorumlari-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı yorum verilerini döndürür.
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
            $where_clause .= " AND (T1.yorum LIKE :search_val OR T2.urun_adi LIKE :search_val OR T3.ad_soyad LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_urun_yorumlari")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total
                                        FROM bt_urun_yorumlari T1
                                        LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
                                        LEFT JOIN bt_musteriler T3 ON T1.musteri_id = T3.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T2.urun_adi', 'T3.ad_soyad', 'T1.puan', 'T1.yorum', 'T1.durum', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';

        $query = "SELECT
                    T1.id, T1.puan, T1.yorum, T1.durum, T1.olusturma_tarihi,
                    T2.urun_adi, T2.id as urun_id,
                    T3.ad_soyad, T3.id as musteri_id
                  FROM bt_urun_yorumlari T1
                  LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
                  LEFT JOIN bt_musteriler T3 ON T1.musteri_id = T3.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $yorumlar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $yorumlar
        ]);
        exit;
    }

    /**
     * Yorum onaylama işlemini yapar (AJAX).
     */
    public function onayla()
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
            $this->db->query("UPDATE bt_urun_yorumlari SET durum = 'Onaylandı' WHERE id IN ({$placeholders})", $ids);
            $this->jsonYanit(true, "Yorumlar başarıyla onaylandı.");
        } catch (Exception $e) {
            error_log("Yorum onaylama hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Yorum reddetme işlemini yapar (AJAX).
     */
    public function reddet()
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
            $this->db->query("UPDATE bt_urun_yorumlari SET durum = 'Reddedildi' WHERE id IN ({$placeholders})", $ids);
            $this->jsonYanit(true, "Yorumlar başarıyla reddedildi.");
        } catch (Exception $e) {
            error_log("Yorum reddetme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Yorumları siler (AJAX).
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
            $result = $this->db->query("DELETE FROM bt_urun_yorumlari WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Yorumlar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek yorum bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Yorum silme hatası: " . $e->getMessage());
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
