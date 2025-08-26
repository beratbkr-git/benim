<?php

// admin_bt/controllers/KategoriController.php

class KategoriController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Kategori listeleme sayfasını gösterir.
     */
    public function liste()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kategoriler/kategoriler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı veri döndürür.
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
            $where_clause .= " AND (T1.kategori_adi LIKE :search_val OR T2.kategori_adi LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kategoriler")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(T1.id) as total FROM bt_kategoriler T1 LEFT JOIN bt_kategoriler T2 ON T1.ust_kategori_id = T2.id" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['T1.id', 'T1.kategori_adi', 'T2.kategori_adi', 'T1.sira', 'T1.durum'];
        $order_column = $columns[$order_column_index] ?? 'T1.sira';

        $query = "SELECT T1.id, T1.kategori_adi, T1.slug, T1.ust_kategori_id, T1.sira, T1.durum, T1.gorsel_url, T2.kategori_adi AS ust_kategori_adi
                  FROM bt_kategoriler T1
                  LEFT JOIN bt_kategoriler T2 ON T1.ust_kategori_id = T2.id
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $kategoriler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $kategoriler
        ]);
        exit;
    }

    /**
     * Kategori ekleme formunu gösterir.
     */
    public function ekle()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kategoriler/kategoriler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Kategori düzenleme formunu gösterir.
     */
    public function duzenle($kategori_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kategoriler/kategoriler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kategori ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kategoriler/liste');
        }

        $last_insert_id = $this->kategoriVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kategori başarıyla eklendi.";
            $this->yonlendir('kategoriler/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Kategori eklenirken bir hata oluştu.";
            $this->yonlendir('kategoriler/ekle');
        }
    }

    /**
     * Mevcut kategoriyi güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kategoriler/liste');
        }

        $kategori_id = (int)($_POST['id'] ?? 0);
        if ($kategori_id === 0) {
            $_SESSION["hata"] = "Geçersiz kategori ID'si.";
            $this->yonlendir('kategoriler/liste');
        }

        if ($this->kategoriVerisiniKaydet($kategori_id)) {
            $_SESSION["basari"] = "Kategori başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Kategori güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('kategoriler/duzenle/' . $kategori_id);
    }

    /**
     * Kategorileri siler (AJAX).
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
            // Silme işlemi
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db->query("DELETE FROM bt_kategoriler WHERE id IN ({$placeholders})", $ids);

            $this->jsonYanit(true, "Kategoriler başarıyla silindi.");
        } catch (Exception $e) {
            error_log("Kategori silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function kategoriVerisiniKaydet($kategori_id = 0)
    {
        $is_edit = $kategori_id > 0;

        $data = [
            'kategori_adi' => trim($_POST['kategori_adi'] ?? ''),
            'slug' => generateSef(trim($_POST['kategori_adi'] ?? '')),
            'ust_kategori_id' => !empty($_POST['ust_kategori_id']) ? (int)$_POST['ust_kategori_id'] : null,
            'aciklama' => $_POST['aciklama'] ?? '',
            'seo_title' => $_POST['seo_title'] ?? '',
            'seo_description' => $_POST['seo_description'] ?? '',
            'seo_keywords' => $_POST['seo_keywords'] ?? '',
            'sira' => (int)($_POST['sira'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Aktif',
            'featured' => isset($_POST['featured']) ? 1 : 0,
        ];

        if (empty($data['kategori_adi'])) {
            $_SESSION["hata"] = "Kategori adı boş bırakılamaz.";
            return false;
        }

        // Görsel yükleme işlemleri
        $gorsel_uploaded = isset($_FILES['gorsel_url']) && $_FILES['gorsel_url']['error'] === UPLOAD_ERR_OK;
        $banner_uploaded = isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK;

        if ($gorsel_uploaded) {
            $file_path = dosyaYukle($_FILES['gorsel_url'], "frontend/assets/uploads/kategoriler/");
            if ($file_path) {
                $data['gorsel_url'] = $file_path;
            } else {
                $_SESSION["hata"] = "Görsel yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            $kategori_eski = $this->db->fetch("SELECT gorsel_url FROM bt_kategoriler WHERE id = :id", ['id' => $kategori_id]);
            $data['gorsel_url'] = $kategori_eski['gorsel_url'] ?? null;
        }

        if ($banner_uploaded) {
            $file_path = dosyaYukle($_FILES['banner_image'], "frontend/assets/uploads/kategoriler/");
            if ($file_path) {
                $data['banner_image'] = $file_path;
            } else {
                $_SESSION["hata"] = "Banner yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            $kategori_eski = $this->db->fetch("SELECT banner_image FROM bt_kategoriler WHERE id = :id", ['id' => $kategori_id]);
            $data['banner_image'] = $kategori_eski['banner_image'] ?? null;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_kategoriler', $data, 'id = :id', ['id' => $kategori_id]);
                return $kategori_id;
            } else {
                return $this->db->insert('bt_kategoriler', $data);
            }
        } catch (Exception $e) {
            error_log("Kategori kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false;
        }
    }

    private function benzersizSlugOlustur($text, $except_id = 0)
    {
        $slug = generateSef($text);
        $original_slug = $slug;
        $counter = 1;
        $query = "SELECT id FROM bt_kategoriler WHERE slug = :slug";
        $params = ['slug' => $slug];
        if ($except_id > 0) {
            $query .= " AND id != :id";
            $params['id'] = $except_id;
        }
        while ($this->db->fetch($query, $params)) {
            $slug = $original_slug . '-' . $counter;
            $params['slug'] = $slug;
            $counter++;
        }
        return $slug;
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
