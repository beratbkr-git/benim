<?php

// admin_bt/controllers/EnvanterController.php

class EnvanterController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Dashboard için envanter verilerini toplar.
     * @return array
     */
    public function dashboard()
    {
        // Yetki kontrolü
        if (!hasPermission('Editör')) {
            return ['stock_summary' => ['dusuk_stok' => 0, 'stokta_yok' => 0], 'low_stock_products' => []];
        }

        // Düşük stoklu ürün sayısını çek
        $lowStockCount = $this->db->fetch("SELECT COUNT(id) as total FROM bt_dusuk_stok_uyarilari WHERE durum = 'Aktif'")['count'];

        // Stokta olmayan ürün sayısını çek
        $outOfStockCount = $this->db->fetch(
            "SELECT COUNT(id) as count
             FROM bt_urunler
             WHERE stok_takibi = 1 AND stok_miktari <= 0 AND varyant_var_mi = 0"
        )['count'];

        // Düşük stoklu ürünlerin listesini çek (ilk 5 tanesi)
        $lowStockProducts = $this->db->fetchAll(
            "SELECT T1.urun_adi, T1.stok_miktari
             FROM bt_urunler T1
             LEFT JOIN bt_dusuk_stok_uyarilari T2 ON T1.id = T2.urun_id
             WHERE T2.durum = 'Aktif'
             OR (T1.stok_takibi = 1 AND T1.stok_miktari <= T1.minimum_stok_miktari AND T1.varyant_var_mi = 0)
             LIMIT 5"
        );

        // Varyantlı ürünlerde düşük stoklu varyantları listele (ilk 5 tanesi)
        $lowStockVariants = $this->db->fetchAll(
            "SELECT T1.urun_adi, T2.stok_adedi AS stok_miktari
             FROM bt_urunler T1
             JOIN bt_urun_varyantlari T2 ON T1.id = T2.urun_id
             WHERE T1.varyant_var_mi = 1 AND T2.stok_adedi <= T1.minimum_stok_miktari
             LIMIT 5"
        );

        // İki listeyi birleştirip benzersiz hale getir
        $finalLowStockList = array_merge($lowStockProducts, $lowStockVariants);

        // Toplam düşük stok sayısını hesapla
        $totalLowStockCount = $lowStockCount + count($lowStockVariants);

        // Stokta olmayan varyantları say
        $outOfStockVariantsCount = $this->db->fetch(
            "SELECT COUNT(T2.id) as count
             FROM bt_urunler T1
             JOIN bt_urun_varyantlari T2 ON T1.id = T2.urun_id
             WHERE T1.varyant_var_mi = 1 AND T2.stok_adedi <= 0"
        )['count'];

        $totalOutOfStockCount = $outOfStockCount + $outOfStockVariantsCount;

        return [
            'stock_summary' => [
                'dusuk_stok' => $totalLowStockCount,
                'stokta_yok' => $totalOutOfStockCount
            ],
            'low_stock_products' => $finalLowStockList
        ];
    }

    /**
     * Stok hareketleri listeleme sayfasını gösterir.
     */
    public function stokHareketleri()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/stok-hareketleri.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için stok hareketi verilerini döndürür.
     */
    public function listeleStokHareketleri()
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
            $where_clause .= " AND (T1.hareket_tipi LIKE :search_val OR T2.urun_adi LIKE :search_val OR T1.aciklama LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_stok_hareketleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total FROM bt_stok_hareketleri T1 LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T2.urun_adi', 'T1.hareket_tipi', 'T1.miktar', 'T1.onceki_stok', 'T1.yeni_stok', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';

        $query = "SELECT
                    T1.id, T1.urun_id, T1.hareket_tipi, T1.miktar, T1.onceki_stok, T1.yeni_stok, T1.olusturma_tarihi,
                    T2.urun_adi
                  FROM bt_stok_hareketleri T1
                  LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $hareketler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $hareketler
        ]);
        exit;
    }

    /**
     * Düşük stok uyarıları listeleme sayfasını gösterir.
     */
    public function dusukStokUyarilari()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/dusuk-stok-uyarilari.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için düşük stok uyarı verilerini döndürür.
     */
    public function listeleDusukStokUyarilari()
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
            $where_clause .= " AND (T2.urun_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_dusuk_stok_uyarilari")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total FROM bt_dusuk_stok_uyarilari T1 LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T2.urun_adi', 'varyant_adi', 'T1.mevcut_stok', 'T1.minimum_stok', 'T1.durum', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';

        $query = "SELECT
                    T1.id, T1.urun_id, T1.varyant_id, T1.mevcut_stok, T1.minimum_stok, T1.durum, T1.olusturma_tarihi,
                    T2.urun_adi,
                    (SELECT varyant_bilgisi FROM bt_urun_varyantlari WHERE id = T1.varyant_id) AS varyant_adi
                  FROM bt_dusuk_stok_uyarilari T1
                  LEFT JOIN bt_urunler T2 ON T1.urun_id = T2.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $uyarilar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $uyarilar
        ]);
        exit;
    }

    /**
     * Düşük stok uyarısının durumunu günceller.
     */
    public function guncelleDusukStokUyarisi()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($ids) || !is_array($ids) || !in_array($status, ['Aktif', 'Çözüldü', 'Göz Ardı Edildi'])) {
            $this->jsonYanit(false, 'Geçersiz ID listesi veya durum bilgisi.');
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);
            $this->db->query("UPDATE bt_dusuk_stok_uyarilari SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Uyarılar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Düşük stok uyarısı güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    /**
     * Düşük stok uyarılarını siler.
     */
    public function silDusukStokUyarisi()
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
            $result = $this->db->query("DELETE FROM bt_dusuk_stok_uyarilari WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Uyarılar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek uyarı bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Düşük stok uyarısı silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Satın alma siparişleri listeleme sayfasını gösterir.
     */
    public function satinAlmaListe()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/satin-alma-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için satın alma siparişi verilerini döndürür.
     */
    public function listeleSatinAlma()
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
            $where_clause .= " AND (T1.siparis_no LIKE :search_val OR T2.firma_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_satin_alma_siparisleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total FROM bt_satin_alma_siparisleri T1 LEFT JOIN bt_tedarikciler T2 ON T1.tedarikci_id = T2.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'T1.siparis_no', 'T2.firma_adi', 'T1.toplam_tutar', 'T1.durum', 'T1.olusturma_tarihi'];
        $order_column = $columns[$order_column_index] ?? 'T1.olusturma_tarihi';

        $query = "SELECT
                    T1.id, T1.siparis_no, T1.toplam_tutar, T1.durum, T1.olusturma_tarihi,
                    T2.firma_adi
                  FROM bt_satin_alma_siparisleri T1
                  LEFT JOIN bt_tedarikciler T2 ON T1.tedarikci_id = T2.id
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
     * Satın alma siparişi ekleme/düzenleme formunu gösterir.
     */
    public function satinAlmaEkle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/satin-alma-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Satın alma siparişi düzenleme formunu gösterir.
     */
    public function satinAlmaDuzenle($siparis_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/satin-alma-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni satın alma siparişi ekleme işlemini yapar.
     */
    public function satinAlmaEkleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('envanter/satin-alma-liste');
        }

        $last_insert_id = $this->satinAlmaVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Satın alma siparişi başarıyla eklendi.";
            $this->yonlendir('envanter/satin-alma-duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Satın alma siparişi eklenirken bir hata oluştu.";
            $this->yonlendir('envanter/satin-alma-ekle');
        }
    }

    /**
     * Mevcut satın alma siparişini güncelleme işlemini yapar.
     */
    public function satinAlmaDuzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('envanter/satin-alma-liste');
        }

        $siparis_id = (int)($_POST['id'] ?? 0);
        if ($siparis_id === 0) {
            $_SESSION["hata"] = "Geçersiz sipariş ID'si.";
            $this->yonlendir('envanter/satin-alma-liste');
        }

        if ($this->satinAlmaVerisiniKaydet($siparis_id)) {
            $_SESSION["basari"] = "Satın alma siparişi başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Satın alma siparişi güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('envanter/satin-alma-duzenle/' . $siparis_id);
    }

    /**
     * Satın alma siparişlerini siler.
     */
    public function silSatinAlma()
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
            $result = $this->db->query("DELETE FROM bt_satin_alma_siparisleri WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Satın alma siparişleri başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek sipariş bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Satın alma siparişi silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Tedarikçiler listeleme sayfasını gösterir.
     */
    public function tedarikcilerListe()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/tedarikciler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için tedarikçi verilerini döndürür.
     */
    public function listeleTedarikciler()
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
            $where_clause .= " AND (firma_adi LIKE :search_val OR iletisim_kisi LIKE :search_val OR eposta LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_tedarikciler")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_tedarikciler" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'firma_adi', 'iletisim_kisi', 'telefon', 'eposta', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, firma_adi, iletisim_kisi, telefon, eposta, durum, teslimat_suresi, odeme_kosullari
                  FROM bt_tedarikciler
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $tedarikciler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $tedarikciler
        ]);
        exit;
    }

    /**
     * Tedarikçi ekleme/düzenleme formunu gösterir.
     */
    public function tedarikcilerEkle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/tedarikciler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Tedarikçi düzenleme formunu gösterir.
     */
    public function tedarikcilerDuzenle($tedarikci_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "envanter/tedarikciler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni tedarikçi ekleme işlemini yapar.
     */
    public function tedarikcilerEkleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('envanter/tedarikciler-liste');
        }

        $last_insert_id = $this->tedarikciVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Tedarikçi başarıyla eklendi.";
            $this->yonlendir('envanter/tedarikciler-duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Tedarikçi eklenirken bir hata oluştu.";
            $this->yonlendir('envanter/tedarikciler-ekle');
        }
    }

    /**
     * Mevcut tedarikçiyi güncelleme işlemini yapar.
     */
    public function tedarikcilerDuzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('envanter/tedarikciler-liste');
        }

        $tedarikci_id = (int)($_POST['id'] ?? 0);
        if ($tedarikci_id === 0) {
            $_SESSION["hata"] = "Geçersiz tedarikçi ID'si.";
            $this->yonlendir('envanter/tedarikciler-liste');
        }

        if ($this->tedarikciVerisiniKaydet($tedarikci_id)) {
            $_SESSION["basari"] = "Tedarikçi başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Tedarikçi güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('envanter/tedarikciler-duzenle/' . $tedarikci_id);
    }

    /**
     * Tedarikçileri siler.
     */
    public function silTedarikciler()
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
            $result = $this->db->query("DELETE FROM bt_tedarikciler WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Tedarikçiler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek tedarikçi bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Tedarikçi silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function satinAlmaVerisiniKaydet($siparis_id = 0)
    {
        $is_edit = $siparis_id > 0;

        $siparis_data = [
            'siparis_no' => trim($_POST['siparis_no'] ?? ''),
            'tedarikci_id' => (int)($_POST['tedarikci_id'] ?? 0),
            'toplam_tutar' => (float)($_POST['toplam_tutar'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Taslak',
            'siparis_tarihi' => $_POST['siparis_tarihi'] ?? date('Y-m-d'),
            'beklenen_teslimat_tarihi' => $_POST['beklenen_teslimat_tarihi'] ?? null,
            'notlar' => $_POST['notlar'] ?? null,
            'kullanici_id' => $_SESSION['kullanici']['id'] ?? null,
        ];

        if (empty($siparis_data['siparis_no']) || $siparis_data['tedarikci_id'] === 0) {
            $_SESSION["hata"] = "Sipariş No ve Tedarikçi seçimi zorunludur.";
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_satin_alma_siparisleri', $siparis_data, 'id = :id', ['id' => $siparis_id]);
                $last_id = $siparis_id;
                // Mevcut detayları silip yeniden ekle
                $this->db->delete('bt_satin_alma_siparis_detaylari', 'satin_alma_siparis_id = :id', ['id' => $last_id]);
            } else {
                $last_id = $this->db->insert('bt_satin_alma_siparisleri', $siparis_data);
                if (!$last_id) {
                    $_SESSION["hata"] = "Satın alma siparişi eklenirken veritabanı hatası oluştu.";
                    return false;
                }
            }

            // Sipariş detaylarını ekle
            $urun_ids = $_POST['urun_id'] ?? [];
            if (!empty($urun_ids)) {
                foreach ($urun_ids as $index => $urun_id) {
                    $detay_data = [
                        'satin_alma_siparis_id' => $last_id,
                        'urun_id' => (int)$urun_id,
                        'varyant_id' => (int)($_POST['varyant_id'][$index] ?? null),
                        'siparis_miktari' => (int)($_POST['siparis_miktari'][$index] ?? 0),
                        'birim_fiyat' => (float)($_POST['birim_fiyat'][$index] ?? 0),
                        'toplam_fiyat' => (float)($_POST['siparis_miktari'][$index] * $_POST['birim_fiyat'][$index] ?? 0),
                        'durum' => 'Beklemede' // Varsayılan durum
                    ];
                    $this->db->insert('bt_satin_alma_siparis_detaylari', $detay_data);
                }
            }

            return $last_id;
        } catch (Exception $e) {
            error_log("Satın alma siparişi kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false;
        }
    }

    private function tedarikciVerisiniKaydet($tedarikci_id = 0)
    {
        $is_edit = $tedarikci_id > 0;

        $data = [
            'firma_adi' => trim($_POST['firma_adi'] ?? ''),
            'iletisim_kisi' => trim($_POST['iletisim_kisi'] ?? ''),
            'telefon' => trim($_POST['telefon'] ?? ''),
            'eposta' => trim($_POST['eposta'] ?? ''),
            'adres' => trim($_POST['adres'] ?? ''),
            'vergi_no' => trim($_POST['vergi_no'] ?? ''),
            'odeme_kosullari' => trim($_POST['odeme_kosullari'] ?? ''),
            'teslimat_suresi' => (int)($_POST['teslimat_suresi'] ?? 7),
            'durum' => $_POST['durum'] ?? 'Aktif',
        ];

        if (empty($data['firma_adi'])) {
            $_SESSION["hata"] = "Firma adı boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_tedarikciler', $data, 'id = :id', ['id' => $tedarikci_id]);
                return $tedarikci_id;
            } else {
                return $this->db->insert('bt_tedarikciler', $data);
            }
        } catch (Exception $e) {
            error_log("Tedarikçi kaydetme hatası: " . $e->getMessage());
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
