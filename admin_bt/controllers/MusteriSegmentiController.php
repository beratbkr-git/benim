<?php

// admin_bt/controllers/MusteriSegmentiController.php

class MusteriSegmentiController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Müşteri segmentleri listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteri-segmentleri/musteri-segmentleri-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı müşteri segmenti verilerini döndürür.
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
            $where_clause .= " AND (segment_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_musteri_segmentleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_musteri_segmentleri" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'segment_adi', 'musteri_sayisi', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, segment_adi, aciklama, musteri_sayisi, durum, kriterler
                  FROM bt_musteri_segmentleri
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $segmentler = $this->db->fetchAll($query, $bind_params);


        unset($segment); // Referansı sil

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $segmentler
        ]);
        exit;
    }

    /**
     * Segment ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteri-segmentleri/musteri-segmentleri-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Segment düzenleme formunu gösterir.
     */
    public function duzenle($segment_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteri-segmentleri/musteri-segmentleri-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni segment ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('musteri-segmentleri/liste');
        }

        $last_insert_id = $this->segmentVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Segment başarıyla eklendi.";
            $this->yonlendir('musteri-segmentleri/liste');
        } else {
            $_SESSION["hata"] = "Segment eklenirken bir hata oluştu.";
            $this->yonlendir('musteri-segmentleri/ekle');
        }
    }

    /**
     * Mevcut segmenti güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('musteri-segmentleri/liste');
        }

        $segment_id = (int)($_POST['id'] ?? 0);
        if ($segment_id === 0) {
            $_SESSION["hata"] = "Geçersiz segment ID'si.";
            $this->yonlendir('musteri-segmentleri/liste');
        }

        if ($this->segmentVerisiniKaydet($segment_id)) {
            $_SESSION["basari"] = "Segment başarıyla güncellendi.";
        }
        $this->yonlendir('musteri-segmentleri/duzenle/' . $segment_id);
    }

    /**
     * Müşteri segmentlerini siler (AJAX).
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
            $result = $this->db->query("DELETE FROM bt_musteri_segmentleri WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Segmentler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek segment bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Segment silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Segment durumunu günceller (AJAX).
     */
    public function ajaxGuncelle()
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
            $this->db->query("UPDATE bt_musteri_segmentleri SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Segment durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    /**
     * Belirli bir segmente ait müşterileri listeler (DataTables için).
     */
    public function segmentMusterileriListe($segment_id)
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        header('Content-Type: application/json');

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $segment = $this->db->fetch("SELECT kriterler FROM bt_musteri_segmentleri WHERE id = :id", ['id' => $segment_id]);
        if (!$segment) {
            echo json_encode([
                "draw" => intval($draw),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
            exit;
        }

        // Dinamik sorgu oluşturma
        $sorgu_sonucu = $this->_dinamikMusteriSorgusu($segment['kriterler'], $search_value, $start, $length);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => $sorgu_sonucu['total'],
            "recordsFiltered" => $sorgu_sonucu['filtered'],
            "data" => $sorgu_sonucu['data']
        ]);
        exit;
    }

    /**
     * Müşteri segmenti detay sayfasını gösterir.
     */
    public function segmentDetay($segment_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }

        $segment_id = (int)$segment_id;
        if ($segment_id === 0) {
            $_SESSION['hata'] = "Geçersiz segment ID'si.";
            $this->yonlendir('musteri-segmentleri/liste');
        }

        $segment = $this->db->fetch("SELECT * FROM bt_musteri_segmentleri WHERE id = :id", ['id' => $segment_id]);

        if (!$segment) {
            $_SESSION['hata'] = "Segment bulunamadı.";
            $this->yonlendir('musteri-segmentleri/liste');
        }

        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "musteri-segmentleri/musteri-segmentleri-detay.php");
        require_once(VIEW_DIR . "footer.php");
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    /**
     * Dinamik olarak müşteri sorgusu oluşturur ve sonucu döndürür.
     */
    private function _dinamikMusteriSorgusu($kriterler_json, $search_value = '', $limit = 10, $offset = 0)
    {
        $kriterler = json_decode($kriterler_json, true);
        if (!is_array($kriterler) || empty($kriterler)) {
            return ['total' => 0, 'filtered' => 0, 'data' => []];
        }

        $where_clauses = [];
        $bind_params = [];
        $sql_join = "";
        $select_fields = "bt_musteriler.id, ad_soyad, eposta, toplam_harcama, son_giris_tarihi";

        $has_siparis_sayisi_kriter = false;
        $siparis_sayisi_operator = '';
        $siparis_sayisi_value = 0;

        foreach ($kriterler as $index => $kriter) {
            $field = $kriter['field'];
            $operator_key = $kriter['operator'];
            $value = $kriter['value'];

            $operator_map = [
                'equal' => '=',
                'not_equal' => '!=',
                'greater_than' => '>',
                'less_than' => '<'
            ];
            $operator = $operator_map[$operator_key] ?? '=';
            $param_key = ":val{$index}";

            // Özel alanları işle
            if ($field === 'toplam_harcama') {
                $where_clauses[] = "{$field} {$operator} {$param_key}";
                $bind_params[$param_key] = (float)$value;
            } else if ($field === 'siparis_sayisi') {
                $has_siparis_sayisi_kriter = true;
                $siparis_sayisi_operator = $operator;
                $siparis_sayisi_value = (int)$value;
            } else if ($field === 'son_giris_tarihi') {
                $where_clauses[] = "DATE({$field}) {$operator} {$param_key}";
                $bind_params[$param_key] = $value;
            } else if ($field === 'cinsiyet' || $field === 'meslek' || $field === 'il' || $field === 'durum') {
                $where_clauses[] = "{$field} {$operator} {$param_key}";
                $bind_params[$param_key] = $value;
            } else if ($field === 'urun_alimi') {
                $sql_join .= " INNER JOIN (SELECT DISTINCT musteri_id FROM bt_siparis_detaylari WHERE urun_id = :urun_id) AS urun_alimi ON bt_musteriler.id = urun_alimi.musteri_id ";
                $bind_params[':urun_id'] = $value;
            }
        }

        // Eğer sipariş sayısı kriteri varsa, JOIN ve WHERE koşulunu ayarla
        if ($has_siparis_sayisi_kriter) {
            $sql_join .= " LEFT JOIN (SELECT musteri_id, COUNT(id) AS siparis_sayisi FROM bt_siparisler GROUP BY musteri_id) AS s ON bt_musteriler.id = s.musteri_id ";
            $where_clauses[] = "COALESCE(s.siparis_sayisi, 0) {$siparis_sayisi_operator} :siparis_sayisi_val";
            $bind_params[':siparis_sayisi_val'] = $siparis_sayisi_value;
        }

        $ana_where_clause = implode(' AND ', $where_clauses);
        if (empty($ana_where_clause)) {
            $ana_where_clause = "1=1";
        }

        // Arama kutusu için ekstra filtre
        $arama_where_clause = "";
        if (!empty($search_value)) {
            $arama_where_clause = " AND (ad_soyad LIKE :search_val OR eposta LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        // Toplam kayıt sayısını bul
        $total_query = "SELECT COUNT(bt_musteriler.id) as total FROM bt_musteriler {$sql_join} WHERE {$ana_where_clause} {$arama_where_clause}";
        $total_count = $this->db->fetch($total_query, $bind_params)['total'] ?? 0;

        // Müşterileri çek
        $data_query = "SELECT {$select_fields} FROM bt_musteriler {$sql_join} WHERE {$ana_where_clause} {$arama_where_clause} ORDER BY bt_musteriler.id DESC LIMIT {$limit} OFFSET {$offset}";
        $musteriler = $this->db->fetchAll($data_query, $bind_params);

        return [
            'total' => $total_count,
            'filtered' => $total_count,
            'data' => $musteriler
        ];
    }
    /**
     * Dinamik olarak bir segmente ait müşteri sayısını hesaplar.
     */
    private function _musteriSayisiHesapla($kriterler_json)
    {
        $kriterler = json_decode($kriterler_json, true);
        if (!is_array($kriterler) || empty($kriterler)) {
            return 0;
        }

        $where_clauses = [];
        $bind_params = [];
        $sql_join = "";

        foreach ($kriterler as $index => $kriter) {
            $field = $kriter['field'];
            $operator_key = $kriter['operator'];
            $value = $kriter['value'];

            $operator_map = [
                'equal' => '=',
                'not_equal' => '!=',
                'greater_than' => '>',
                'less_than' => '<'
            ];
            $operator = $operator_map[$operator_key] ?? '=';

            $param_key = ":val{$index}";

            if ($field === 'urun_alimi') {
                $sql_join .= " INNER JOIN (SELECT DISTINCT musteri_id FROM bt_siparis_detaylari WHERE urun_id = :urun_id) AS urun_alimi ON bt_musteriler.id = urun_alimi.musteri_id ";
                $bind_params[':urun_id'] = $value;
            } else {
                $where_clauses[] = "{$field} {$operator} {$param_key}";
                $bind_params[$param_key] = $value;
            }
        }

        $ana_where_clause = implode(' AND ', $where_clauses);
        $total_query = "SELECT COUNT(bt_musteriler.id) as total FROM bt_musteriler {$sql_join} WHERE {$ana_where_clause}";

        return $this->db->fetch($total_query, $bind_params)['total'] ?? 0;
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function segmentVerisiniKaydet($segment_id = 0)
    {
        $is_edit = $segment_id > 0;

        $data = [
            'segment_adi' => trim($_POST['segment_adi'] ?? ''),
            'aciklama' => $_POST['aciklama'] ?? '',
            'durum' => $_POST['durum'] ?? 'Pasif',
            'kriterler' => $_POST['kriterler'] ?? '[]',
        ];

        if (empty($data['segment_adi'])) {
            $_SESSION["hata"] = "Segment adı boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                $existing_segment = $this->db->fetch("SELECT id FROM bt_musteri_segmentleri WHERE segment_adi = :ad AND id != :id", ['ad' => $data['segment_adi'], 'id' => $segment_id]);
                if ($existing_segment) {
                    $_SESSION["hata"] = "Bu segment adı zaten kullanılıyor.";
                    return false;
                }
                $this->db->update('bt_musteri_segmentleri', $data, 'id = :id', ['id' => $segment_id]);
                $this->_musteriSayisiniGuncelle($segment_id, $data['kriterler']);
                return true; // Başarılı güncelleme
            } else {
                $existing_segment = $this->db->fetch("SELECT id FROM bt_musteri_segmentleri WHERE segment_adi = :ad", ['ad' => $data['segment_adi']]);
                if ($existing_segment) {
                    $_SESSION["hata"] = "Bu segment adı zaten kullanılıyor.";
                    return false;
                }
                $last_id = $this->db->insert('bt_musteri_segmentleri', $data);
                if ($last_id) {
                    $this->_musteriSayisiniGuncelle($last_id, $data['kriterler']);
                    return $last_id; // Yeni eklenen ID'yi döndür
                }
                return false;
            }
        } catch (Exception $e) {
            error_log("Segment kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false; // Hata durumunda false döndür
        }
    }

    private function _musteriSayisiniGuncelle($segment_id, $kriterler_json)
    {
        // 1. Kriterlere uyan müşteri ID'lerini bul
        $musteriler = $this->_dinamikMusteriSorgusu($kriterler_json, '', 9999999, 0)['data'];
        $musteri_ids = array_column($musteriler, 'id');

        // 2. Sayıyı bt_musteri_segmentleri tablosunda güncelle
        $musteri_sayisi = count($musteri_ids);
        $this->db->update('bt_musteri_segmentleri', ['musteri_sayisi' => $musteri_sayisi], 'id = :id', ['id' => $segment_id]);

        // 3. bt_musteri_segment_iliskileri tablosunu temizle ve yeniden doldur
        $this->db->delete('bt_musteri_segment_iliskileri', 'segment_id = :id', ['id' => $segment_id]);
        if (!empty($musteri_ids)) {
            $values = [];
            foreach ($musteri_ids as $id) {
                $values[] = "({$segment_id}, {$id})";
            }
            $query = "INSERT INTO bt_musteri_segment_iliskileri (segment_id, musteri_id) VALUES " . implode(',', $values);
            $this->db->query($query);
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
