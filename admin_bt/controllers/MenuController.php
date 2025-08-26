<?php

// admin_bt/controllers/MenuController.php

class MenuController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Menü listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "menuler/menuler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı menü verilerini döndürür.
     */
    public function listele()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        header('Content-Type: application/json');

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = $_POST['search']['value'] ?? '';

        $bind_params = [];
        $where_clause = "";

        if (!empty($search_value)) {
            // Ana menü ve alt menüleri arama
            $where_clause = " WHERE (T1.menu_adi LIKE :search_val OR T1.url LIKE :search_val) OR (T2.menu_adi LIKE :search_val OR T2.url LIKE :search_val) ";
            $bind_params[':search_val'] = '%' . $search_value . '%';
        }

        // Ana menü ve alt menüleri çekmek için JOIN kullanıyoruz
        $query = "SELECT T1.id, T1.menu_adi, T1.url, T1.menuturu, T1.sira, T1.durum,
                  T2.menu_adi AS ust_menu_adi
                  FROM bt_menuler T1
                  LEFT JOIN bt_menuler T2 ON T1.ust_menu_id = T2.id
                  {$where_clause}
                  ORDER BY T1.sira ASC";

        $menuler = $this->db->fetchAll($query, $bind_params);

        // Bu bir hiyerarşik liste olduğu için DataTables'ın listeleme mantığına özel bir düzenleme yapmamız gerekecek.
        // Daha iyi bir çözüm için tüm veriyi çekip PHP tarafında hiyerarşik olarak düzenleyebiliriz.
        // Ancak bu şimdilik bir başlangıç noktası olarak kalsın.

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => count($menuler),
            "recordsFiltered" => count($menuler),
            "data" => $menuler
        ]);
        exit;
    }

    /**
     * Menü ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        $ust_menuler = $this->db->fetchAll("SELECT id, menu_adi FROM bt_menuler WHERE ust_menu_id IS NULL ORDER BY sira ASC");

        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "menuler/menuler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Menü düzenleme formunu gösterir.
     */
    public function duzenle($menu_id)
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        $menu_id = (int)$menu_id;
        $menu = $this->db->fetch("SELECT * FROM bt_menuler WHERE id = :id", ['id' => $menu_id]);
        if (!$menu) {
            $_SESSION['hata'] = "Menü bulunamadı.";
            $this->yonlendir('menuler/liste');
        }
        $ust_menuler = $this->db->fetchAll("SELECT id, menu_adi FROM bt_menuler WHERE ust_menu_id IS NULL AND id != :id ORDER BY sira ASC", ['id' => $menu_id]);

        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "menuler/menuler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni menü ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('menuler/liste');
        }

        $last_insert_id = $this->menuVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Menü başarıyla eklendi.";
            $this->yonlendir('menuler/duzenle/' . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Menü eklenirken bir hata oluştu.";
            $this->yonlendir('menuler/ekle');
        }
    }

    /**
     * Mevcut menüyü güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('menuler/liste');
        }

        $menu_id = (int)($_POST['id'] ?? 0);
        if ($menu_id === 0) {
            $_SESSION["hata"] = "Geçersiz menü ID'si.";
            $this->yonlendir('menuler/liste');
        }

        if ($this->menuVerisiniKaydet($menu_id)) {
            $_SESSION["basari"] = "Menü başarıyla güncellendi.";
        } else {
            $_SESSION["hata"] = "Menü güncellenirken bir hata oluştu.";
        }
        $this->yonlendir('menuler/duzenle/' . $menu_id);
    }

    /**
     * Menüleri siler (AJAX).
     */
    public function sil()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Silme yetkiniz yok.");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->jsonYanit(false, "Geçerli ID bulunamadı.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_menuler WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Menüler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek menü bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Menü silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Menü durumunu günceller (AJAX).
     */
    public function durumGuncelle()
    {
        if (!hasPermission('Admin')) {
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
            $this->db->query("UPDATE bt_menuler SET durum = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Menü durumu güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function menuVerisiniKaydet($menu_id = 0)
    {
        $is_edit = $menu_id > 0;

        $data = [
            'menu_adi' => trim($_POST['menu_adi'] ?? ''),
            'url' => trim($_POST['url'] ?? ''),
            'menuturu' => $_POST['menuturu'] ?? 'url',
            'ust_menu_id' => !empty($_POST['ust_menu_id']) ? (int)$_POST['ust_menu_id'] : null,
            'sira' => (int)($_POST['sira'] ?? 0),
            'durum' => $_POST['durum'] ?? 'Aktif',
        ];

        if (empty($data['menu_adi'])) {
            $_SESSION["hata"] = "Menü adı boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                $this->db->update('bt_menuler', $data, 'id = :id', ['id' => $menu_id]);
                return $menu_id;
            } else {
                return $this->db->insert('bt_menuler', $data);
            }
        } catch (Exception $e) {
            error_log("Menü kaydetme hatası: " . $e->getMessage());
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
