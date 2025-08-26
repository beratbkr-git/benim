<?php
// admin_bt/controllers/VaryantController.php

class VaryantController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    // =====================================================================
    // VARYANT ÖZELLİKLERİ METOTLARI
    // =====================================================================

    /**
     * Varyant özellikleri listeleme sayfasını gösterir (Ana sayfa).
     */
    public function liste()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/ozellikler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için varyant özellikleri verisini döndürür.
     */
    public function listeleOzellik()
    {
        header('Content-Type: application/json');
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $bind_params = [];
        $where_clause = " WHERE 1=1 ";

        if (!empty($search_value)) {
            $where_clause .= " AND ozellik_adi LIKE :search_val ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_varyant_ozellikleri")['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_varyant_ozellikleri" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'ozellik_adi', 'sira'];
        $order_column = $columns[$order_column_index] ?? 'sira';

        $query = "SELECT id, ozellik_adi, sira FROM bt_varyant_ozellikleri
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $ozellikler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $ozellikler
        ]);
        exit;
    }

    public function ekleOzellik()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/ozellikler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function duzenleOzellik($ozellik_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/ozellikler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function ekleOzellikKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('varyantlar/liste');
        }
        $last_id = $this->ozellikVerisiniKaydet(0);
        if ($last_id) {
            $_SESSION["basari"] = "Özellik başarıyla eklendi.";
            $this->yonlendir('varyantlar/duzenle-ozellik/' . $last_id);
        } else {
            $_SESSION["hata"] = "Özellik eklenirken bir hata oluştu.";
            $this->yonlendir('varyantlar/ekle-ozellik');
        }
    }

    public function duzenleOzellikKontrol()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('varyantlar/liste');
        }
        $ozellik_id = (int)($_POST['id'] ?? 0);
        if ($ozellik_id === 0) {
            $_SESSION["hata"] = "Geçersiz ID.";
            $this->yonlendir('varyantlar/liste');
        }
        if ($this->ozellikVerisiniKaydet($ozellik_id)) {
            $_SESSION["basari"] = "Özellik başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Özellik güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('varyantlar/duzenle-ozellik/' . $ozellik_id);
    }

    public function silOzellik()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Silme yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_varyant_ozellikleri WHERE id IN ({$placeholders})", $ids);
            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Özellikler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek özellik bulunamadı.");
            }
        } catch (Exception $e) {
            $this->jsonYanit(false, "Veritabanı hatası: " . $e->getMessage());
        }
    }

    // =====================================================================
    // VARYANT DEĞERLERİ METOTLARI
    // =====================================================================

    public function degerlerListe($ozellik_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/degerler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function listeleDeger($ozellik_id)
    {
        header('Content-Type: application/json');
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $bind_params = [':ozellik_id' => $ozellik_id];
        $where_clause = " WHERE ozellik_id = :ozellik_id ";

        if (!empty($search_value)) {
            $where_clause .= " AND deger LIKE :search_val ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_varyant_degerleri WHERE ozellik_id = :id", [':id' => $ozellik_id])['total'];
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_varyant_degerleri" . $where_clause, $bind_params)['total'];

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'deger', 'sira'];
        $order_column = $columns[$order_column_index] ?? 'sira';

        $query = "SELECT id, deger, sira FROM bt_varyant_degerleri
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $degerler = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $degerler
        ]);
        exit;
    }

    public function ekleDeger($ozellik_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/degerler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function duzenleDeger($deger_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "varyantlar/degerler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function ekleDegerKontrol()
    {
        $ozellik_id = (int)($_POST['ozellik_id'] ?? 0);
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || $ozellik_id === 0) {
            $this->yonlendir('varyantlar/liste');
        }

        $last_id = $this->degerVerisiniKaydet(0);
        if ($last_id) {
            $_SESSION["basari"] = "Değer başarıyla eklendi.";
        } else {
            $_SESSION["hata"] = "Değer eklenirken bir hata oluştu.";
        }
        $this->yonlendir('varyantlar/degerler-liste/' . $ozellik_id);
    }

    public function duzenleDegerKontrol()
    {
        $ozellik_id = (int)($_POST['ozellik_id'] ?? 0);
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || $ozellik_id === 0) {
            $this->yonlendir('varyantlar/liste');
        }

        $deger_id = (int)($_POST['id'] ?? 0);
        if ($deger_id === 0) {
            $_SESSION["hata"] = "Geçersiz ID.";
            $this->yonlendir('varyantlar/degerler-liste/' . $ozellik_id);
        }
        if ($this->degerVerisiniKaydet($deger_id)) {
            $_SESSION["basari"] = "Değer başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Değer güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('varyantlar/degerler-liste/' . $ozellik_id);
    }

    public function silDeger()
    {
        if (!hasPermission('Yönetici')) {
            $this->jsonYanit(false, "Silme yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_varyant_degerleri WHERE id IN ({$placeholders})", $ids);
            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Değerler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek değer bulunamadı.");
            }
        } catch (Exception $e) {
            $this->jsonYanit(false, "Veritabanı hatası: " . $e->getMessage());
        }
    }

    // =====================================================================
    // YENİ EKLENEN AJAX METODU
    // =====================================================================

    /**
     * Tüm varyant özelliklerini ve değerlerini JSON olarak döndürür.
     * Ürün formundaki dinamik varyant arayüzü için kullanılır.
     */
    public function hepsiniGetirJson()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        try {
            $ozellikler = $this->db->fetchAll("SELECT id, ozellik_adi FROM bt_varyant_ozellikleri ORDER BY sira ASC");
            $degerler = $this->db->fetchAll("SELECT id, ozellik_id, deger FROM bt_varyant_degerleri ORDER BY sira ASC");

            $response = [];
            foreach ($ozellikler as $ozellik) {
                $response[$ozellik['id']] = [
                    'id' => $ozellik['id'],
                    'ad' => $ozellik['ozellik_adi'],
                    'degerler' => []
                ];
            }
            foreach ($degerler as $deger) {
                if (isset($response[$deger['ozellik_id']])) {
                    $response[$deger['ozellik_id']]['degerler'][] = [
                        'id' => $deger['id'],
                        'ad' => $deger['deger']
                    ];
                }
            }

            echo json_encode(['success' => true, 'data' => array_values($response)]);
            exit;
        } catch (Exception $e) {
            $this->jsonYanit(false, "Veri alınırken hata oluştu: " . $e->getMessage());
        }
    }

    // --- YARDIMCI METOTLAR ---

    private function ozellikVerisiniKaydet($id = 0)
    {
        $data = ['ozellik_adi' => trim($_POST['ozellik_adi'] ?? ''), 'sira' => (int)($_POST['sira'] ?? 0)];
        if (empty($data['ozellik_adi'])) {
            return false;
        }
        try {
            return ($id > 0) ? ($this->db->update('bt_varyant_ozellikleri', $data, 'id = :id', ['id' => $id]) ? $id : false) : $this->db->insert('bt_varyant_ozellikleri', $data);
        } catch (Exception $e) {
            error_log("Varyant özelliği kaydetme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function degerVerisiniKaydet($id = 0)
    {
        $data = [
            'ozellik_id' => (int)($_POST['ozellik_id'] ?? 0),
            'deger' => trim($_POST['deger'] ?? ''),
            'sira' => (int)($_POST['sira'] ?? 0)
        ];
        if (empty($data['deger']) || $data['ozellik_id'] === 0) {
            return false;
        }
        try {
            return ($id > 0) ? ($this->db->update('bt_varyant_degerleri', $data, 'id = :id', ['id' => $id]) ? $id : false) : $this->db->insert('bt_varyant_degerleri', $data);
        } catch (Exception $e) {
            error_log("Varyant değeri kaydetme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function jsonYanit($success, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    private function yonlendir($path)
    {
        global $yonetimurl;
        header("Location: /{$yonetimurl}/{$path}");
        exit;
    }
}
