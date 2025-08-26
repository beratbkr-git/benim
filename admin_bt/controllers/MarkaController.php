<?php
// admin_bt/controllers/MarkaController.php

class MarkaController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Marka listeleme sayfasını gösterir.
     */
    public function liste()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "markalar/markalar-liste.php");
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
            $where_clause .= " AND marka_adi LIKE :search_val ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_markalar")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_markalar" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'marka_adi', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, marka_adi, slug, logo_url, durum, seo_title, seo_description
                  FROM bt_markalar
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $markalar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $markalar
        ]);
        exit;
    }

    /**
     * Marka ekleme formunu gösterir.
     */
    public function ekle()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "markalar/markalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Marka düzenleme formunu gösterir.
     */
    public function duzenle($marka_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "markalar/markalar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni marka ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('markalar/liste');
        }

        $last_insert_id = $this->markaVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Marka başarıyla eklendi.";
            $this->yonlendir('markalar/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Marka eklenirken bir hata oluştu.";
            $this->yonlendir('markalar/ekle');
        }
    }

    /**
     * Mevcut markayı güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('markalar/liste');
        }

        $marka_id = (int)($_POST['id'] ?? 0);
        if ($marka_id === 0) {
            $_SESSION["hata"] = "Geçersiz marka ID'si.";
            $this->yonlendir('markalar/liste');
        }

        if ($this->markaVerisiniKaydet($marka_id)) {
            $_SESSION["basari"] = "Marka başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Marka güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('markalar/duzenle/' . $marka_id);
    }

    /**
     * Markaları siler (AJAX).
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
            // Silinecek markaların logo yollarını bul
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $markalar = $this->db->fetchAll("SELECT logo_url FROM bt_markalar WHERE id IN ({$placeholders})", $ids);

            $result = $this->db->query("DELETE FROM bt_markalar WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                // Dosyaları sunucudan sil
                foreach ($markalar as $marka) {
                    if (!empty($marka['logo_url']) && file_exists($marka['logo_url'])) {
                        unlink($marka['logo_url']);
                    }
                }
                $this->jsonYanit(true, "Markalar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek marka bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Marka silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function markaVerisiniKaydet($marka_id = 0)
    {
        $is_edit = $marka_id > 0;

        $data = [
            'marka_adi' => trim($_POST['marka_adi'] ?? ''),
            'slug' => $this->benzersizSlugOlustur(trim($_POST['marka_adi'] ?? ''), $marka_id),
            'aciklama' => $_POST['aciklama'] ?? '',
            'durum' => $_POST['durum'] ?? 'Aktif',
            'seo_title' => $_POST['seo_title'] ?? '',
            'seo_description' => $_POST['seo_description'] ?? '',
        ];

        if (empty($data['marka_adi'])) {
            $_SESSION["hata"] = "Marka adı boş bırakılamaz.";
            return false;
        }

        // Logo yükleme
        if (isset($_FILES['logo_url']) && $_FILES['logo_url']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['logo_url'], "frontend/assets/uploads/markalar/");
            if ($file_path) {
                $data['logo_url'] = $file_path;
            } else {
                $_SESSION["hata"] = "Logo yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            // Eski logoyu koru
            $marka = $this->db->fetch("SELECT logo_url FROM bt_markalar WHERE id = :id", ['id' => $marka_id]);
            $data['logo_url'] = $marka['logo_url'] ?? null;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_markalar', $data, 'id = :id', ['id' => $marka_id]);
                return $marka_id;
            } else {
                return $this->db->insert('bt_markalar', $data);
            }
        } catch (Exception $e) {
            error_log("Marka kaydetme hatası: " . $e->getMessage());
            $_SESSION["hata"] = "Veritabanı hatası: " . $e->getMessage();
            return false;
        }
    }

    private function benzersizSlugOlustur($text, $except_id = 0)
    {
        $slug = generateSef($text);
        $original_slug = $slug;
        $counter = 1;
        $query = "SELECT id FROM bt_markalar WHERE slug = :slug";
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
