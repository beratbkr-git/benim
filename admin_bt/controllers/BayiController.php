<?php

// admin_bt/controllers/BayiController.php

class BayiController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Bayi listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "bayiler/bayiler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı bayi verilerini döndürür.
     */
    public function listele()
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
            $where_clause .= " AND (T1.firma_adi LIKE :search_val OR T2.ad_soyad LIKE :search_val OR T2.eposta LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_bayiler")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total FROM bt_bayiler T1 LEFT JOIN bt_kullanicilar T2 ON T1.kullanici_id = T2.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['T1.id', 'T1.firma_adi', 'T2.ad_soyad', 'T2.eposta', 'T1.komisyon_orani', 'T1.onay_durumu'];
        $order_column = $columns[$order_column_index] ?? 'T1.id';

        $query = "SELECT
                    T1.id, T1.firma_adi, T1.komisyon_orani, T1.onay_durumu, T2.ad_soyad, T2.eposta, T2.id AS kullanici_id
                  FROM bt_bayiler T1
                  LEFT JOIN bt_kullanicilar T2 ON T1.kullanici_id = T2.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $bayiler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $bayiler
        ]);
        exit;
    }

    /**
     * Bayi detay sayfasını gösterir.
     */
    public function detay($bayi_id)
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        if (empty((int)$bayi_id)) {
            $this->yonlendir('bayiler/liste');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "bayiler/bayi-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Bayi ve ilgili kullanıcıyı siler (AJAX).
     */
    public function sil()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            // Önce ilgili kullanıcı ID'lerini bul
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $kullanici_ids = $this->db->fetchAll("SELECT kullanici_id FROM bt_bayiler WHERE id IN ({$placeholders})", $ids);

            // Bayileri sil (ON DELETE CASCADE ile ilgili bt_kullanicilar tablosu da silinir)
            $result = $this->db->query("DELETE FROM bt_bayiler WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Bayiler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek bayi bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Bayi silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Bayi onay durumunu günceller (AJAX).
     */
    public function durumGuncelle()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $data = $_POST;
        $bayi_id = (int)($data['id'] ?? 0);
        $yeni_durum = trim($data['onay_durumu'] ?? '');

        if ($bayi_id === 0 || empty($yeni_durum)) {
            $this->jsonYanit(false, "Geçersiz veri.");
        }

        try {
            $this->db->update(
                'bt_bayiler',
                ['onay_durumu' => $yeni_durum],
                'id = :id',
                ['id' => $bayi_id]
            );
            $this->jsonYanit(true, "Bayi durumu başarıyla güncellendi.");
        } catch (Exception $e) {
            error_log("Bayi durumu güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Veritabanı hatası oluştu.");
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
