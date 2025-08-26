<?php

// admin_bt/controllers/SiparisController.php

class SiparisController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Sipariş listeleme sayfasını gösterir.
     * Header ve Footer bu metodun içinde çağrılır.
     */
    public function liste()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "siparisler/siparisler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı sipariş verilerini döndürür.
     */
    public function listele()
    {
        header('Content-Type: application/json');

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $bind_params = [];
        $where_clause = " WHERE 1=1 ";

        if (!empty($search_value)) {
            // Müşteri adı, e-postası veya sipariş koduna göre arama yap
            $where_clause .= " AND (T1.siparis_kodu LIKE :search_val OR T2.ad_soyad LIKE :search_val OR T2.eposta LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records_query = "SELECT COUNT(id) as total FROM bt_siparisler";
        $total_records = $this->db->fetch($total_records_query)['total'];

        $total_display_records_query = "SELECT COUNT(T1.id) as total 
                                        FROM bt_siparisler T1 
                                        LEFT JOIN bt_musteriler T2 ON T1.musteri_id = T2.id" . $where_clause;
        $total_display_records = $this->db->fetch($total_display_records_query, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T1.siparis_kodu', 'T2.ad_soyad', 'T1.odenen_tutar', 'T1.siparis_durumu', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';

        $query = "SELECT 
                    T1.id, T1.siparis_kodu, T1.odenen_tutar, T1.siparis_durumu, T1.olusturma_tarihi,
                    T2.ad_soyad AS musteri_adi
                  FROM bt_siparisler T1
                  LEFT JOIN bt_musteriler T2 ON T1.musteri_id = T2.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $siparisler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $siparisler
        ]);
        exit;
    }

    /**
     * Tek bir siparişin detay sayfasını gösterir.
     */
    public function detay($siparis_id)
    {
        if (empty((int)$siparis_id)) {
            $this->yonlendir('siparisler/liste');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "siparisler/siparis-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Sipariş durumunu günceller (AJAX).
     */
    public function durumGuncelle()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        $siparis_id = (int)($_POST['siparis_id'] ?? 0);
        $yeni_durum = trim($_POST['yeni_durum'] ?? '');
        $eski_durum = trim($_POST['eski_durum'] ?? '');

        if ($siparis_id === 0 || empty($yeni_durum)) {
            $this->jsonYanit(false, "Geçersiz veri.");
        }

        try {
            // 1. Sipariş durumunu güncelle
            $this->db->update('bt_siparisler', ['siparis_durumu' => $yeni_durum], 'id = :id', ['id' => $siparis_id]);

            // 2. Durum değişikliğini geçmiş tablosuna kaydet
            $this->db->insert('bt_siparis_durum_gecmisi', [
                'siparis_id' => $siparis_id,
                'eski_durum' => $eski_durum,
                'yeni_durum' => $yeni_durum,
                'aciklama' => 'Durum yönetici tarafından değiştirildi.',
                'kullanici_id' => $_SESSION['kullanici']['id'] ?? null
            ]);

            $this->jsonYanit(true, "Sipariş durumu başarıyla güncellendi.");
        } catch (Exception $e) {
            error_log("Sipariş durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Veritabanı hatası oluştu.");
        }
    }

    /**
     * Siparişe kargo bilgilerini ekler/günceller (AJAX).
     */
    public function kargoGuncelle()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        $siparis_id = (int)($_POST['siparis_id'] ?? 0);
        $kargo_takip_no = trim($_POST['kargo_takip_no'] ?? '');
        $kargo_firma_id = (int)($_POST['kargo_firma_id'] ?? 0);

        if ($siparis_id === 0) {
            $this->jsonYanit(false, "Geçersiz sipariş ID'si.");
        }

        try {
            $data = [
                'kargo_takip_no' => $kargo_takip_no,
                'kargo_firma_id' => $kargo_firma_id > 0 ? $kargo_firma_id : null
            ];
            $this->db->update('bt_siparisler', $data, 'id = :id', ['id' => $siparis_id]);

            $this->jsonYanit(true, "Kargo bilgileri başarıyla güncellendi.");
        } catch (Exception $e) {
            error_log("Kargo bilgisi güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // İADE VE İPTAL TAKİBİ METOTLARI
    // =====================================================================

    /**
     * İade ve iptal listeleme sayfasını gösterir.
     */
    public function iade()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "siparisler/iade-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı iade ve iptal verilerini döndürür.
     */
    public function listeleIade()
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
            // Sipariş kodu, müşteri adı veya iade nedenine göre arama
            $where_clause .= " AND (T2.siparis_kodu LIKE :search_val OR T3.ad_soyad LIKE :search_val OR T1.iade_nedeni LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records_query = "SELECT COUNT(id) as total FROM bt_iade_islemleri";
        $total_records = $this->db->fetch($total_records_query)['total'];

        $total_display_records_query = "SELECT COUNT(T1.id) as total
                                        FROM bt_iade_islemleri T1
                                        LEFT JOIN bt_siparisler T2 ON T1.siparis_id = T2.id
                                        LEFT JOIN bt_musteriler T3 ON T2.musteri_id = T3.id" . $where_clause;
        $total_display_records = $this->db->fetch($total_display_records_query, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T2.siparis_kodu', 'T3.ad_soyad', 'T1.iade_tutari', 'T1.durum', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';
        if ($order_column === 'T2.siparis_kodu') $order_column = 'T2.siparis_kodu';
        if ($order_column === 'T3.ad_soyad') $order_column = 'T3.ad_soyad';


        $query = "SELECT
                    T1.id, T1.iade_tutari, T1.durum, T1.olusturma_tarihi, T1.siparis_id,
                    T2.siparis_kodu,
                    T3.ad_soyad AS musteri_adi
                  FROM bt_iade_islemleri T1
                  LEFT JOIN bt_siparisler T2 ON T1.siparis_id = T2.id
                  LEFT JOIN bt_musteriler T3 ON T2.musteri_id = T3.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $iade_listesi = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $iade_listesi
        ]);
        exit;
    }

    /**
     * İade/iptal işleminin detay sayfasını gösterir.
     */
    public function iadeDetay($iade_id)
    {
        if (empty((int)$iade_id)) {
            $this->yonlendir('siparisler/iade');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "siparisler/iade-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * İade işleminin durumunu günceller (AJAX).
     */
    public function iadeDurumGuncelle()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $iade_id = (int)($_POST['iade_id'] ?? 0);
        $yeni_durum = trim($_POST['yeni_durum'] ?? '');
        $admin_notu = trim($_POST['admin_notu'] ?? '');

        if ($iade_id === 0 || empty($yeni_durum)) {
            $this->jsonYanit(false, "Geçersiz veri.");
        }

        try {
            $this->db->update(
                'bt_iade_islemleri',
                ['durum' => $yeni_durum, 'admin_notu' => $admin_notu],
                'id = :id',
                ['id' => $iade_id]
            );

            // Gerekirse sipariş durumunu da güncelleyebiliriz
            $iade = $this->db->fetch("SELECT siparis_id FROM bt_iade_islemleri WHERE id = :id", ['id' => $iade_id]);
            if ($iade && $yeni_durum === 'Onaylandı') {
                $this->db->update('bt_siparisler', ['siparis_durumu' => 'İade'], 'id = :id', ['id' => $iade['siparis_id']]);
            }

            $this->jsonYanit(true, "İade durumu başarıyla güncellendi.");
        } catch (Exception $e) {
            error_log("İade durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Veritabanı hatası oluştu.");
        }
    }

    // --- YARDIMCI METOTLAR ---

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
