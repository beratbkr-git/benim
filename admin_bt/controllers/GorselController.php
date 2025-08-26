<?php

// admin_bt/controllers/GorselController.php

class GorselController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Görsel listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "gorseller/gorseller-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı görsel verilerini döndürür.
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
            $where_clause .= " AND (gorsel_adi LIKE :search_val OR konum LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_gorseller")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_gorseller" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'gorsel_adi', 'konum', 'sira', 'durum'];
        $order_column = $columns[$order_column_index] ?? 'sira';

        $query = "SELECT id, gorsel_adi, gorsel_url, konum, sira, durum, mobil_ayri_gorunum, mobil_gorsel_url
                  FROM bt_gorseller
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $gorseller = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $gorseller
        ]);
        exit;
    }

    /**
     * Görsel ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "gorseller/gorseller-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Görsel düzenleme formunu gösterir.
     */
    public function duzenle($gorsel_id)
    {
        if (!hasPermission('Editör')) {
            $this->yonlendir('anasayfa');
        }
        $gorsel_id = (int)$gorsel_id;
        $gorsel = $this->db->fetch("SELECT * FROM bt_gorseller WHERE id = :id", ['id' => $gorsel_id]);
        if (!$gorsel) {
            $_SESSION['hata'] = "Görsel bulunamadı.";
            $this->yonlendir('gorseller/liste');
        }

        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "gorseller/gorseller-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni görsel ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('gorseller/liste');
        }

        $last_insert_id = $this->gorselVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Görsel başarıyla eklendi.";
            $this->yonlendir('gorseller/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Görsel eklenirken bir hata oluştu.";
            $this->yonlendir('gorseller/ekle');
        }
    }

    /**
     * Mevcut görseli güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('gorseller/liste');
        }

        $gorsel_id = (int)($_POST['id'] ?? 0);
        if ($gorsel_id === 0) {
            $_SESSION["hata"] = "Geçersiz görsel ID'si.";
            $this->yonlendir('gorseller/liste');
        }

        if ($this->gorselVerisiniKaydet($gorsel_id)) {
            $_SESSION["basari"] = "Görsel başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Görsel güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('gorseller/duzenle/' . $gorsel_id);
    }

    /**
     * Görselleri siler (AJAX).
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
            // Önce silinecek görsellerin dosya yollarını bul
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $gorseller = $this->db->fetchAll("SELECT gorsel_url, mobil_gorsel_url FROM bt_gorseller WHERE id IN ({$placeholders})", $ids);

            // Veritabanından sil
            $result = $this->db->query("DELETE FROM bt_gorseller WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                // Dosyaları sunucudan sil
                foreach ($gorseller as $gorsel) {
                    if (file_exists($gorsel['gorsel_url'])) {
                        unlink($gorsel['gorsel_url']);
                    }
                    if (!empty($gorsel['mobil_gorsel_url']) && file_exists($gorsel['mobil_gorsel_url'])) {
                        unlink($gorsel['mobil_gorsel_url']);
                    }
                }
                $this->jsonYanit(true, "Görseller başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek görsel bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Görsel silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function gorselVerisiniKaydet($gorsel_id = 0)
    {
        $is_edit = $gorsel_id > 0;

        $data = [
            'gorsel_adi' => trim($_POST['gorsel_adi'] ?? ''),
            'kisa_aciklama' => trim($_POST['kisa_aciklama'] ?? ''),
            'konum' => trim($_POST['konum'] ?? 'basibos_resimler'),
            'link' => trim($_POST['link'] ?? ''),
            'sira' => (int)($_POST['sira'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Aktif',
            'mobil_ayri_gorunum' => isset($_POST['mobil_ayri_gorunum']) ? 1 : 0,
        ];

        if (empty($data['gorsel_adi'])) {
            $_SESSION["hata"] = "Görsel adı boş bırakılamaz.";
            return false;
        }

        // Ana görsel (gorsel_url) yükleme işlemi
        if (isset($_FILES['gorsel_url']) && $_FILES['gorsel_url']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['gorsel_url'], "frontend/assets/uploads/gorseller/");
            if ($file_path) {
                $data['gorsel_url'] = $file_path;
            } else {
                $_SESSION["hata"] = "Ana görsel yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif (!$is_edit) {
            $_SESSION["hata"] = "Yeni görsel için bir dosya seçimi zorunludur.";
            return false;
        }

        // Mobil görsel yükleme işlemi (checkbox işaretli ise)
        if ($data['mobil_ayri_gorunum'] && isset($_FILES['mobil_gorsel_url']) && $_FILES['mobil_gorsel_url']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['mobil_gorsel_url'], "frontend/assets/uploads/gorseller/");
            if ($file_path) {
                $data['mobil_gorsel_url'] = $file_path;
            } else {
                $_SESSION["hata"] = "Mobil görsel yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit && !$data['mobil_ayri_gorunum']) {
            // Düzenleme modunda ve mobil görsel checkbox'ı kaldırılmışsa
            $data['mobil_gorsel_url'] = null;
        } elseif ($is_edit) {
            // Düzenleme modunda ve yeni dosya yüklenmemişse eski mobil görseli koru
            $gorsel_eski = $this->db->fetch("SELECT mobil_gorsel_url FROM bt_gorseller WHERE id = :id", ['id' => $gorsel_id]);
            $data['mobil_gorsel_url'] = $gorsel_eski['mobil_gorsel_url'] ?? null;
        }


        try {
            if ($is_edit) {
                // Eski ana görseli koru eğer yeni görsel yüklenmemişse
                if (!isset($data['gorsel_url'])) {
                    $gorsel_eski = $this->db->fetch("SELECT gorsel_url FROM bt_gorseller WHERE id = :id", ['id' => $gorsel_id]);
                    $data['gorsel_url'] = $gorsel_eski['gorsel_url'] ?? null;
                }

                $this->db->update('bt_gorseller', $data, 'id = :id', ['id' => $gorsel_id]);
                return $gorsel_id;
            } else {
                return $this->db->insert('bt_gorseller', $data);
            }
        } catch (Exception $e) {
            error_log("Görsel kaydetme hatası: " . $e->getMessage());
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
