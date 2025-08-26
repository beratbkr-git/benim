<?php

// admin_bt/controllers/SayfaController.php

class SayfaController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Sayfa listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "sayfalar/sayfalar-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı sayfa verilerini döndürür.
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
            $where_clause .= " AND (sayfa_adi LIKE :search_val OR slug LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_sayfa")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_sayfa" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'sayfa_adi', 'slug', 'sira', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, sayfa_adi, slug, sira, durum, resim
                  FROM bt_sayfa
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $sayfalar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $sayfalar
        ]);
        exit;
    }

    /**
     * Sayfa ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "sayfalar/sayfalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Sayfa düzenleme formunu gösterir.
     */
    public function duzenle($sayfa_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "sayfalar/sayfalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni sayfa ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('sayfalar/liste');
        }

        $last_insert_id = $this->sayfaVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Sayfa başarıyla eklendi.";
            $this->yonlendir('sayfalar/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Sayfa eklenirken bir hata oluştu.";
            $this->yonlendir('sayfalar/ekle');
        }
    }

    /**
     * Mevcut sayfayı güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('sayfalar/liste');
        }

        $sayfa_id = (int)($_POST['id'] ?? 0);
        if ($sayfa_id === 0) {
            $_SESSION["hata"] = "Geçersiz sayfa ID'si.";
            $this->yonlendir('sayfalar/liste');
        }

        if ($this->sayfaVerisiniKaydet($sayfa_id)) {
            $_SESSION["basari"] = "Sayfa başarıyla güncellendi.";
        }
        $this->yonlendir('sayfalar/duzenle/' . $sayfa_id);
    }

    /**
     * Sayfaları siler (AJAX).
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
            $result = $this->db->query("DELETE FROM bt_sayfa WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Sayfalar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek sayfa bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Sayfa silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function sayfaVerisiniKaydet($sayfa_id = 0)
    {
        global $site_ayarlari;
        $is_edit = $sayfa_id > 0;

        $data = [
            'sayfa_adi' => trim($_POST['sayfa_adi'] ?? ''),
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'icerik' => $_POST['icerik'] ?? '',
            'resim' => $_POST['resim'] ?? '',
            'banner' => $_POST['banner'] ?? '',
            'sira' => (int)($_POST['sira'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Aktif',
            'dosya' => trim($_POST['dosya'] ?? ''),
        ];

        if (empty($data['sayfa_adi'])) {
            $_SESSION["hata"] = "Sayfa adı ve sef link boş bırakılamaz.";
            return false;
        }

        if ($data['sayfa_adi'] === "Anasayfa") {
            $data['slug'] = '';
        } else {
            $data['slug'] = generateSef($data['sayfa_adi']);
        }

        if (empty($data['meta_title'])) {
            $data['meta_title'] = $data['sayfa_adi'] . ' | ' . $site_ayarlari['site_adi'];
        }
        if (isset($_FILES['resim']) && $_FILES['resim']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['resim'], "frontend/assets/uploads/sayfalar/");
            if ($file_path) {
                $data['resim'] = $file_path;
            } else {
                $_SESSION["hata"] = "Ana görsel yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            $sayfa_eski = $this->db->fetch("SELECT resim FROM bt_sayfa WHERE id = :id", ['id' => $sayfa_id]);
            $data['resim'] = $sayfa_eski['resim'] ?? null;
        }

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['banner'], "frontend/assets/uploads/sayfalar/");
            if ($file_path) {
                $data['banner'] = $file_path;
            } else {
                $_SESSION["hata"] = "Banner görseli yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            $sayfa_eski = $this->db->fetch("SELECT banner FROM bt_sayfa WHERE id = :id", ['id' => $sayfa_id]);
            $data['banner'] = $sayfa_eski['banner'] ?? null;
        }
        try {
            if ($is_edit) {
                $this->db->update('bt_sayfa', $data, 'id = :id', ['id' => $sayfa_id]);
                return $sayfa_id;
            } else {
                return $this->db->insert('bt_sayfa', $data);
            }
        } catch (Exception $e) {
            error_log("Sayfa kaydetme hatası: " . $e->getMessage());
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
