<?php

// admin_bt/controllers/MusteriController.php

class MusteriController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }
    public function getCustomerStats()
    {
        $stats = [];

        $stats['total_customers'] = $this->db->fetch("SELECT COUNT(*) as count FROM bt_musteriler")['count'];
        $stats['new_customers_today'] = $this->db->fetch("SELECT COUNT(*) as count FROM bt_musteriler WHERE DATE(kayit_tarihi) = CURDATE()")['count'];
        $stats['new_customers_month'] = $this->db->fetch("SELECT COUNT(*) as count FROM bt_musteriler WHERE MONTH(kayit_tarihi) = MONTH(CURDATE()) AND YEAR(kayit_tarihi) = YEAR(CURDATE())")['count'];
        $stats['active_customers'] = $this->db->fetch("SELECT COUNT(DISTINCT musteri_id) as count FROM bt_siparisler WHERE siparis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['count'];

        return $stats;
    }

    public function getCustomerSegments()
    {
        return $this->db->fetchAll("
            SELECT 
                ms.*,
                COUNT(msi.musteri_id) as musteri_sayisi_gercek
            FROM bt_musteri_segmentleri ms
            LEFT JOIN bt_musteri_segment_iliskileri msi ON ms.id = msi.segment_id
            WHERE ms.durum = 'Aktif'
            GROUP BY ms.id
            ORDER BY ms.olusturma_tarihi DESC
        ");
    }

    public function getTopCustomers($limit = 10)
    {
        return $this->db->fetchAll("
            SELECT 
                m.*,
                COUNT(s.id) as toplam_siparis,
                SUM(s.toplam_tutar) as toplam_harcama,
                MAX(s.siparis_tarihi) as son_siparis_tarihi
            FROM bt_musteriler m
            LEFT JOIN bt_siparisler s ON m.id = s.musteri_id
            GROUP BY m.id
            ORDER BY toplam_harcama DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getCustomerGrowthData($days = 30)
    {
        return $this->db->fetchAll("
            SELECT 
                DATE(kayit_tarihi) as tarih,
                COUNT(*) as yeni_musteriler
            FROM bt_musteriler 
            WHERE kayit_tarihi >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(kayit_tarihi)
            ORDER BY tarih ASC
        ", [$days]);
    }

    public function getCustomerLifetimeValue($customer_id)
    {
        $result = $this->db->fetch("
            SELECT 
                COUNT(s.id) as toplam_siparis,
                SUM(s.toplam_tutar) as toplam_harcama,
                AVG(s.toplam_tutar) as ortalama_siparis_tutari,
                MIN(s.siparis_tarihi) as ilk_siparis,
                MAX(s.siparis_tarihi) as son_siparis,
                DATEDIFF(CURDATE(), MIN(s.siparis_tarihi)) as musteri_yasam_suresi
            FROM bt_siparisler s
            WHERE s.musteri_id = ?
        ", [$customer_id]);

        if ($result['musteri_yasam_suresi'] > 0) {
            $result['aylik_ortalama_harcama'] = $result['toplam_harcama'] / ($result['musteri_yasam_suresi'] / 30);
        } else {
            $result['aylik_ortalama_harcama'] = 0;
        }

        return $result;
    }
    public function getCustomerOrderHistory($customer_id, $limit = 20)
    {
        return $this->db->fetchAll("
            SELECT 
                s.*,
                COUNT(sd.id) as urun_sayisi
            FROM bt_siparisler s
            LEFT JOIN bt_siparis_detaylari sd ON s.id = sd.siparis_id
            WHERE s.musteri_id = ?
            GROUP BY s.id
            ORDER BY s.siparis_tarihi DESC
            LIMIT ?
        ", [$customer_id, $limit]);
    }

    /**
     * Müşteri listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteriler/musteriler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı müşteri verilerini döndürür.
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
            $where_clause .= " AND (ad_soyad LIKE :search_val OR eposta LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_musteriler")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_musteriler" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'ad_soyad', 'eposta', 'toplam_harcama', 'son_giris_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, ad_soyad, eposta, toplam_harcama, son_giris_tarihi
                  FROM bt_musteriler
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $musteriler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $musteriler
        ]);
        exit;
    }

    /**
     * Müşteri detay sayfasını gösterir.
     */
    public function detay($musteri_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        if (empty((int)$musteri_id)) {
            $this->yonlendir('musteriler/liste');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteriler/musteri-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Müşterileri siler (AJAX).
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
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_musteriler WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Müşteriler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek müşteri bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Müşteri silme hatası: " . $e->getMessage());
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
