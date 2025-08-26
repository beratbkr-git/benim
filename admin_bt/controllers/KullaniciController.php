<?php

// admin_bt/controllers/KullaniciController.php

class KullaniciController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Kullanıcı listeleme sayfasını gösterir.
     */
    public function liste()
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kullanicilar/kullanicilar-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı kullanıcı verilerini döndürür.
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
        $where_clause = " WHERE 1=1 ";

        if (!empty($search_value)) {
            // Her bir arama koşulu için benzersiz adlandırılmış parametreler kullanın
            $where_clause .= " AND (ad_soyad LIKE :search_val_ad_soyad OR eposta LIKE :search_val_eposta) ";

            // Her bir parametre için ilgili değeri bind_params dizisine ekleyin
            $bind_params['search_val_ad_soyad'] = '%' . $search_value . '%';
            $bind_params['search_val_eposta'] = '%' . $search_value . '%';
        }


        $total_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kullanicilar")['total'];

        // total_display_records sorgusu için de aynı parametreleri kullanın
        $total_display_records = $this->db->fetch("SELECT COUNT(id) as total FROM bt_kullanicilar" . $where_clause, $bind_params)['total'];


        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = ['id', 'ad_soyad', 'eposta', 'yetki_seviyesi', 'aktif_mi', 'profil_resmi'];
        $order_column = $columns[$order_column_index] ?? 'id';

        $query = "SELECT id, ad_soyad, eposta, yetki_seviyesi, aktif_mi, profil_resmi
                  FROM bt_kullanicilar
                  {$where_clause}
                  ORDER BY {$order_column} {$order_direction}
                  LIMIT {$length} OFFSET {$start}";

        $kullanicilar = $this->db->fetchAll($query, $bind_params);

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $kullanicilar
        ]);
        exit;
    }

    /**
     * Kullanıcı ekleme formunu gösterir.
     */
    public function ekle()
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kullanicilar/kullanicilar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Kullanıcı düzenleme formunu gösterir.
     */
    public function duzenle($kullanici_id)
    {
        if (!hasPermission('Admin')) {
            $this->yonlendir('anasayfa');
        }
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "kullanicilar/kullanicilar-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni kullanıcı ekleme işlemini yapar.
     */
    public function ekleKontrol()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kullanicilar/liste');
        }

        $last_insert_id = $this->kullaniciVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Kullanıcı başarıyla eklendi.";
            $this->yonlendir('kullanicilar/duzenle/' . $last_insert_id);
        } else {
            // Hata mesajı zaten session'da ayarlandı
            $this->yonlendir('kullanicilar/ekle');
        }
    }

    /**
     * Mevcut kullanıcıyı güncelleme işlemini yapar.
     */
    public function duzenleKontrol()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->yonlendir('kullanicilar/liste');
        }

        $kullanici_id = (int)($_POST['id'] ?? 0);
        if ($kullanici_id === 0) {
            $_SESSION["hata"] = "Geçersiz kullanıcı ID'si.";
            $this->yonlendir('kullanicilar/liste');
        }

        if ($this->kullaniciVerisiniKaydet($kullanici_id)) {
            $_SESSION["basari"] = "Kullanıcı başarıyla güncellendi.";
            $this->yonlendir('kullanicilar/duzenle/' . $kullanici_id);
        } else {
            // Hata mesajı zaten session'da ayarlandı
            $this->yonlendir('kullanicilar/duzenle/' . $kullanici_id);
        }
    }

    /**
     * Kullanıcıları siler (AJAX).
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
            // Admin'ler kendilerini silemesin
            $user_id = $_SESSION['kullanici']['id'] ?? null;
            if (in_array($user_id, $ids)) {
                $this->jsonYanit(false, "Kendi hesabınızı silemezsiniz.");
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $result = $this->db->query("DELETE FROM bt_kullanicilar WHERE id IN ({$placeholders})", $ids);

            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Kullanıcılar başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek kullanıcı bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Kullanıcı silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Kullanıcı durumunu günceller (AJAX).
     */
    public function durumGuncelle()
    {
        if (!hasPermission('Admin')) {
            $this->jsonYanit(false, "Yetkiniz yok.");
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($ids) || !is_array($ids) || !in_array($status, [0, 1])) {
            $this->jsonYanit(false, 'Geçersiz ID listesi veya durum bilgisi.');
        }

        try {
            // Admin'ler kendi durumlarını değiştiremesin
            $user_id = $_SESSION['kullanici']['id'] ?? null;
            if (in_array($user_id, $ids) && (int)$status === 0) {
                $this->jsonYanit(false, "Kendi hesabınızı pasif hale getiremezsiniz.");
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([(int)$status], $ids);
            $this->db->query("UPDATE bt_kullanicilar SET aktif_mi = ? WHERE id IN ($placeholders)", $params);
            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Kullanıcı durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası oluştu.');
        }
    }

    // =====================================================================
    // YARDIMCI METOTLAR
    // =====================================================================

    private function kullaniciVerisiniKaydet($kullanici_id = 0)
    {
        $is_edit = $kullanici_id > 0;

        $data = [
            'ad_soyad' => trim($_POST['ad_soyad'] ?? ''),
            'eposta' => trim($_POST['eposta'] ?? ''),
            'yetki_seviyesi' => $_POST['yetki_seviyesi'] ?? 'Editör',
            'aktif_mi' => isset($_POST['aktif_mi']) ? 1 : 0,
        ];

        // Yeni şifre girilmişse hashle
        if (!empty($_POST['parola'])) {
            $data['parola'] = password_hash($_POST['parola'], PASSWORD_DEFAULT);
        } else if (!$is_edit) {
            $_SESSION["hata"] = "Yeni kullanıcı için parola zorunludur.";
            return false;
        }

        // Profil resmi yükleme işlemi
        if (isset($_FILES['profil_resmi']) && $_FILES['profil_resmi']['error'] === UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['profil_resmi'], "frontend/assets/uploads/kullanicilar/");
            if ($file_path) {
                $data['profil_resmi'] = $file_path;
            } else {
                $_SESSION["hata"] = "Profil resmi yüklenirken bir hata oluştu.";
                return false;
            }
        } elseif ($is_edit) {
            // Eğer düzenleme yapılıyor ve yeni bir resim yüklenmediyse mevcut resmi koru
            $existing_user = $this->db->fetch("SELECT profil_resmi FROM bt_kullanicilar WHERE id = :id", ['id' => $kullanici_id]);
            $data['profil_resmi'] = $existing_user['profil_resmi'] ?? null;
        }

        if (empty($data['ad_soyad']) || empty($data['eposta'])) {
            $_SESSION["hata"] = "Ad Soyad ve E-posta boş bırakılamaz.";
            return false;
        }

        try {
            if ($is_edit) {
                // E-posta benzersiz olmalı kontrolü
                $existing_user = $this->db->fetch("SELECT id FROM bt_kullanicilar WHERE eposta = :eposta AND id != :id", ['eposta' => $data['eposta'], 'id' => $kullanici_id]);
                if ($existing_user) {
                    $_SESSION["hata"] = "Bu e-posta adresi zaten kullanılıyor.";
                    return false;
                }
                $this->db->update('bt_kullanicilar', $data, 'id = :id', ['id' => $kullanici_id]);
                return $kullanici_id;
            } else {
                // E-posta benzersiz olmalı kontrolü
                $existing_user = $this->db->fetch("SELECT id FROM bt_kullanicilar WHERE eposta = :eposta", ['eposta' => $data['eposta']]);
                if ($existing_user) {
                    $_SESSION["hata"] = "Bu e-posta adresi zaten kullanılıyor.";
                    return false;
                }
                return $this->db->insert('bt_kullanicilar', $data);
            }
        } catch (Exception $e) {
            error_log("Kullanıcı kaydetme hatası: " . $e->getMessage());
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
