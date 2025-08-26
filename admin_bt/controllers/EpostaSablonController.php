<?php

// admin_bt/controllers/EpostaSablonController.php

class EpostaSablonController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * E-posta şablonları listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "eposta-sablonlari/eposta-sablonlari-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için e-posta şablonu verilerini döndürür.
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
            $where_clause .= " AND (sablon_adi LIKE :search_val OR sablon_kodu LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_eposta_sablonlari")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_eposta_sablonlari" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'sablon_adi', 'sablon_kodu', 'konu', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, sablon_adi, sablon_kodu, konu, durum
                  FROM bt_eposta_sablonlari
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $sablonlar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $sablonlar
        ]);
        exit;
    }

    /**
     * E-posta şablonu ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "eposta-sablonlari/eposta-sablonlari-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * E-posta şablonu düzenleme formunu gösterir.
     */
    public function duzenle($sablon_id)
    {
        if (!hasPermission('Yönetici')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "eposta-sablonlari/eposta-sablonlari-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni şablon ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('eposta-sablonlari/liste');
        }

        $last_insert_id = $this->sablonVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "E-posta şablonu başarıyla eklendi.";
            $this->yonlendir('eposta-sablonlari/liste');
        } else {
            // Hata mesajı zaten session'da ayarlandı
            $this->yonlendir('eposta-sablonlari/ekle');
        }
    }

    /**
     * Mevcut şablonu güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('eposta-sablonlari/liste');
        }

        $sablon_id = (int)($_POST['id'] ?? 0);
        if ($sablon_id === 0) {
            $_SESSION["hata"] = "Geçersiz şablon ID'si.";
            $this->yonlendir('eposta-sablonlari/liste');
        }

        if ($this->sablonVerisiniKaydet($sablon_id)) {
            $_SESSION["basari"] = "E-posta şablonu başarıyla güncellendi.";
            $this->yonlendir('eposta-sablonlari/duzenle/' . $sablon_id);
        } else {
            $this->yonlendir('eposta-sablonlari/duzenle/' . $sablon_id);
        }
    }

    /**
     * E-posta şablonlarını siler.
     */
    public function sil()
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
            $result = $this->db->query("DELETE FROM bt_eposta_sablonlari WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "E-posta şablonları başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek şablon bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("E-posta şablonu silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * E-posta şablonlarının durumunu günceller (AJAX).
     */
    public function durumGuncelle()
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
            $this->db->query("UPDATE bt_eposta_sablonlari SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("E-posta şablonu durumu güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function sablonVerisiniKaydet($sablon_id = 0)
    {
        $is_edit = $sablon_id > 0;

        $data = [
            'sablon_adi' => trim($_POST['sablon_adi'] ?? ''),
            'sablon_kodu' => trim($_POST['sablon_kodu'] ?? ''),
            'konu' => trim($_POST['konu'] ?? ''),
            'html_icerik' => $_POST['html_icerik'] ?? '',
            'metin_icerik' => $_POST['metin_icerik'] ?? '',
            'degiskenler' => $_POST['degiskenler'] ?? '[]',
            'durum' => $_POST['durum'] ?? 'Pasif',
        ];

        if (empty($data['sablon_adi']) || empty($data['sablon_kodu']) || empty($data['konu'])) {
            $_SESSION["hata"] = "Şablon adı, kodu ve konusu boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                // Şablon kodu benzersiz olmalı kontrolü
                $existing = $this->db->fetch("SELECT id FROM bt_eposta_sablonlari WHERE sablon_kodu = :kod AND id != :id", ['kod' => $data['sablon_kodu'], 'id' => $sablon_id]);
                if ($existing) {
                    $_SESSION["hata"] = "Bu şablon kodu zaten kullanılıyor.";
                    return false;
                }
                $this->db->update('bt_eposta_sablonlari', $data, 'id = :id', ['id' => $sablon_id]);
                return $sablon_id;
            } else {
                // Şablon kodu benzersiz olmalı kontrolü
                $existing = $this->db->fetch("SELECT id FROM bt_eposta_sablonlari WHERE sablon_kodu = :kod", ['kod' => $data['sablon_kodu']]);
                if ($existing) {
                    $_SESSION["hata"] = "Bu şablon kodu zaten kullanılıyor.";
                    return false;
                }
                return $this->db->insert('bt_eposta_sablonlari', $data);
            }
        } catch (Exception $e) {
            error_log("E-posta şablonu kaydetme hatası: " . $e->getMessage());
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
