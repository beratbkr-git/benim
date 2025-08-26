<?php
// admin_bt/controllers/UrunController.php

class UrunController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Ürün listeleme sayfasını gösterir.
     * Header ve Footer bu metodun içinde çağrılır.
     */
    public function liste()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "urunler/urunler-liste.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * DataTables için sunucu taraflı veri döndürür.
     * Bu bir AJAX metodudur, header/footer içermez.
     */
    public function listele()
    {
        header('Content-Type: application/json');
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search_value = '';
        if (isset($_POST['search'])) {
            if (is_array($_POST['search']) && isset($_POST['search']['value'])) {
                $search_value = $_POST['search']['value'];
            } else {
                $search_value = $_POST['search'];
            }
        }

        $bind_params = [];
        $where_clause = " WHERE 1=1 ";
        if (!empty($search_value)) {
            // Use unique named placeholders for each condition
            $where_clause .= " AND (T1.urun_adi LIKE :search_val1 OR T1.urun_kodu LIKE :search_val2 OR T2.marka_adi LIKE :search_val3 OR T4.kategori_adi LIKE :search_val4) ";

            $bind_params['search_val1'] = '%' . $search_value . '%';
            $bind_params['search_val2'] = '%' . $search_value . '%';
            $bind_params['search_val3'] = '%' . $search_value . '%';
            $bind_params['search_val4'] = '%' . $search_value . '%';
        }

        $total_records_query = "SELECT COUNT(id) as total FROM bt_urunler";
        $total_records = $this->db->fetch($total_records_query)['total'];

        $total_display_records_query = "SELECT COUNT(DISTINCT T1.id) as total 
                                    FROM bt_urunler T1 
                                    LEFT JOIN bt_markalar T2 ON T1.marka_id = T2.id
                                    LEFT JOIN bt_urun_kategori_iliski T3 ON T1.id = T3.urun_id
                                    LEFT JOIN bt_kategoriler T4 ON T3.kategori_id = T4.id"
            . $where_clause;

        // This is the correct call that should be used.
        $total_display_records = $this->db->fetch($total_display_records_query, $bind_params)['total'];

        // --- The following section is redundant and should be removed. ---
        // $display_params = !empty($search_value) ? ['search_val' => '%' . $search_value . '%'] : [];
        // $total_display_records = $this->db->fetch($total_display_records_query, $display_params)['total'];
        // --- End of redundant section ---

        $order_column_index = $_POST['order'][0]['column'] ?? 1;
        $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
        $columns = ['T1.id', 'ana_gorsel', 'T1.urun_adi', 'T2.marka_adi', 'kategoriler', 'T1.varyant_var_mi', 'T1.satis_fiyati', 'T1.stok_miktari', 'T1.durum'];
        $order_column = $columns[$order_column_index] ?? 'T1.id';
        if (in_array($order_column, ['kategoriler', 'ana_gorsel'])) {
            $order_column = 'T1.id';
        }

        // Asıl veri listesi (Main data list)
        $query = "SELECT T1.slug,
        T1.id, T1.urun_adi, T1.urun_kodu, T1.durum, T1.stok_miktari, T1.satis_fiyati, T1.varyant_var_mi, T2.marka_adi,
        (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = T1.id ORDER BY sira ASC LIMIT 1) AS ana_gorsel,
        (SELECT GROUP_CONCAT(T4.kategori_adi SEPARATOR ',') FROM bt_urun_kategori_iliski T3 JOIN bt_kategoriler T4 ON T3.kategori_id = T4.id WHERE T3.urun_id = T1.id) AS kategoriler,
        (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = T1.id) AS min_varyant_fiyati
      FROM bt_urunler T1
      LEFT JOIN bt_markalar T2 ON T1.marka_id = T2.id
      LEFT JOIN bt_urun_kategori_iliski T3 ON T1.id = T3.urun_id
      LEFT JOIN bt_kategoriler T4 ON T3.kategori_id = T4.id
      {$where_clause}
      GROUP BY T1.id
      ORDER BY {$order_column} {$order_direction}
      LIMIT {$length} OFFSET {$start}";
        $urunler = $this->db->fetchAll($query, $bind_params); // Use the same $bind_params

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($total_display_records),
            "data" => $urunler
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Yeni ürün ekleme formunu gösterir.
     * Header ve Footer bu metodun içinde çağrılır.
     */
    public function ekle()
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "urunler/urunler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Ürün düzenleme formunu gösterir.
     * Header ve Footer bu metodun içinde çağrılır.
     */
    public function duzenle($urun_id)
    {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "urunler/urunler-form.php");
        require_once(VIEW_DIR . "footer.php");
    }

    /**
     * Yeni ürün ekleme işlemini yapar.
     * Bu bir form işleyicisidir, header/footer içermez.
     */
    public function ekleKontrol()
    {
        global $yonetimurl;
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /{$yonetimurl}/urunler/liste");
            exit;
        }

        $last_insert_id = $this->urunVerisiniKaydet(0);

        if ($last_insert_id) {
            $_SESSION["basari"] = "Ürün başarıyla eklendi.";
            header("Location: /{$yonetimurl}/urunler/duzenle/" . $last_insert_id);
        } else {
            $_SESSION["hata"] = "Ürün eklenirken bir hata oluştu.";
            header("Location: /{$yonetimurl}/urunler/ekle");
        }
        exit;
    }

    /**
     * Mevcut ürünü güncelleme işlemini yapar.
     * Bu bir form işleyicisidir, header/footer içermez.
     */
    public function duzenleKontrol()
    {
        global $yonetimurl;
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /{$yonetimurl}/urunler/liste");
            exit;
        }

        $urun_id = (int)($_POST['id'] ?? 0);
        if ($urun_id === 0) {
            $_SESSION["hata"] = "Geçersiz ürün ID'si.";
            header("Location: /{$yonetimurl}/urunler/liste");
            exit;
        }

        $result = $this->urunVerisiniKaydet($urun_id);

        if ($result) {
            $_SESSION["basari"] = "Ürün başarıyla güncellendi.";
        }
        header("Location: /{$yonetimurl}/urunler/duzenle/" . $urun_id);
        exit;
    }

    /**
     * Ürünleri siler (AJAX).
     * Bu bir AJAX metodudur, header/footer içermez.
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
            $result = $this->db->query("DELETE FROM bt_urunler WHERE id IN ({$placeholders})", $ids);
            if ($result->rowCount() > 0) {
                $this->jsonYanit(true, "Ürünler başarıyla silindi.");
            } else {
                $this->jsonYanit(false, "Silinecek ürün bulunamadı.");
            }
        } catch (Exception $e) {
            error_log("Ürün silme hatası: " . $e->getMessage());
            $this->jsonYanit(false, "Bir veritabanı hatası oluştu.");
        }
    }

    /**
     * Dropzone ile galeriye resim yükler (AJAX).
     * Bu bir AJAX metodudur, header/footer içermez.
     */
    public function galeriYukle()
    {
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Resim yükleme yetkiniz yok.");
        }

        $upload_dir = "frontend/assets/uploads/urun_galeri/";
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $file_path = dosyaYukle($_FILES['file'], $upload_dir);
            if ($file_path !== false) {
                $this->jsonYanit(true, "Dosya yüklendi.", ['file_path' => $file_path]);
            } else {
                $this->jsonYanit(false, "Dosya yüklenirken bir hata oluştu.");
            }
        } else {
            $this->jsonYanit(false, "Dosya yüklenemedi.");
        }
    }

    /**
     * DataTables'den gelen toplu durum güncelleme isteğini işler.
     * Bu bir AJAX metodudur, header/footer içermez.
     */
    public function ajaxGuncelle()
    {
        header('Content-Type: application/json');
        if (!hasPermission('Editör')) {
            $this->jsonYanit(false, "Bu işlem için yetkiniz yok.");
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($ids) || !is_array($ids) || !in_array($status, ['Aktif', 'Pasif', 'Taslak'])) {
            $this->jsonYanit(false, 'Geçersiz ID listesi veya durum bilgisi.');
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);

            $this->db->query("UPDATE bt_urunler SET durum = ? WHERE id IN ($placeholders)", $params);

            $this->jsonYanit(true, 'Durumlar başarıyla güncellendi.');
        } catch (PDOException $e) {
            error_log("Durum güncelleme hatası: " . $e->getMessage());
            $this->jsonYanit(false, 'Veritabanı hatası nedeniyle durumlar güncellenemedi.');
        }
    }

    // --- YARDIMCI VE ÖZEL METOTLAR ---

    /**
     * Bir ürünü tüm bilgileriyle kopyalar.
     * Bu bir yönlendirme metodudur, header/footer içermez.
     */
    public function kopyaOlustur($urun_id)
    {
        global $yonetimurl;
        if (!hasPermission('Editör')) {
            $_SESSION["hata"] = "Bu işlem için yetkiniz yok.";
            header("Location: /{$yonetimurl}/urunler/liste");
            exit;
        }

        try {
            // Orijinal ürünü bul
            $original_urun = $this->db->fetch("SELECT * FROM bt_urunler WHERE id = :id", ['id' => $urun_id]);
            if (!$original_urun) {
                throw new Exception("Kopyalanacak ürün bulunamadı.");
            }

            // Yeni ürün verisini hazırla
            $yeni_urun_data = $original_urun;
            unset($yeni_urun_data['id']); // ID'yi kaldır
            $yeni_urun_data['urun_adi'] = $original_urun['urun_adi'] . ' - Kopya';
            $yeni_urun_data['slug'] = $this->benzersizSlugOlustur($yeni_urun_data['urun_adi']);
            $yeni_urun_data['durum'] = 'Taslak';
            $yeni_urun_data['olusturma_tarihi'] = date('Y-m-d H:i:s');

            // Yeni ürünü veritabanına ekle
            $yeni_urun_id = $this->db->insert('bt_urunler', $yeni_urun_data);
            if (!$yeni_urun_id) {
                throw new Exception("Ürün kopyası oluşturulamadı.");
            }

            // Kategori ilişkilerini kopyala
            $kategoriler = $this->db->fetchAll("SELECT kategori_id FROM bt_urun_kategori_iliski WHERE urun_id = :id", ['id' => $urun_id]);
            $this->kategorileriGuncelle($yeni_urun_id, array_column($kategoriler, 'kategori_id'));

            // Görselleri kopyala (sadece veritabanı kaydı, dosyalar aynı kalır)
            $resimler = $this->db->fetchAll("SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = :id", ['id' => $urun_id]);
            $this->resimleriGuncelle($yeni_urun_id, array_column($resimler, 'gorsel_url'));

            $_SESSION["basari"] = "'{$original_urun['urun_adi']}' ürünü başarıyla kopyalandı.";
            header("Location: /{$yonetimurl}/urunler/duzenle/" . $yeni_urun_id);
        } catch (Exception $e) {
            $_SESSION["hata"] = "Kopyalama sırasında hata: " . $e->getMessage();
            header("Location: /{$yonetimurl}/urunler/liste");
        }
        exit;
    }

    /**
     * Formdan gelen veriyi veritabanına kaydeder (ekleme/güncelleme için ortak).
     */
    private function urunVerisiniKaydet($urun_id = 0)
    {
        $is_edit = $urun_id > 0;

        // Form verilerini al ve temizle
        $data = [
            'urun_adi' => trim($_POST['urun_adi'] ?? ''),
            'urun_kodu' => trim($_POST['urun_kodu'] ?? null),
            'marka_id' => (int)($_POST['marka_id'] ?? 0) > 0 ? (int)$_POST['marka_id'] : null,
            'aciklama' => $_POST['aciklama'] ?? '',
            'kisa_aciklama' => $_POST['kisa_aciklama'] ?? '',
            'durum' => $_POST['durum'] ?? 'Taslak',
            'one_cikan_mi' => isset($_POST['one_cikan_mi']) ? 1 : 0,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'etiketler' => trim($_POST['etiketler'] ?? ''),
            'varyant_var_mi' => isset($_POST['varyant_var_mi']) ? 1 : 0,
            'satis_fiyati' => empty($_POST['satis_fiyati']) ? null : (float)$_POST['satis_fiyati'],
            'stok_miktari' => empty($_POST['stok_miktari']) ? null : (int)$_POST['stok_miktari'],
            'varyant_verileri' => $_POST['varyant_verileri'] ?? '{"options":[],"combinations":[]}'
        ];

        if (empty($data['urun_adi'])) {
            return false;
        }

        // Slug oluştur/güncelle
        $data['slug'] = $this->benzersizSlugOlustur($data['urun_adi'], $urun_id);

        try {
            if ($is_edit) {
                $this->db->update('bt_urunler', $data, 'id = :id', ['id' => $urun_id]);
                $last_id = $urun_id;
            } else {
                $data['olusturma_tarihi'] = date('Y-m-d H:i:s');
                $last_id = $this->db->insert('bt_urunler', $data);
            }

            if (!$last_id) {
                throw new Exception("Ürün veritabanına kaydedilemedi.");
            }

            // İlişkili verileri güncelle
            $this->kategorileriGuncelle($last_id, $_POST['kategori_ids'] ?? []);
            $this->resimleriGuncelle($last_id, json_decode($_POST['resimler'] ?? '[]', true));

            // Varyantları güncelle
            if ($data['varyant_var_mi']) {
                $this->varyantlariGuncelle($last_id, $data['varyant_verileri']);
            } else {
                // Varyant yoksa, mevcut varyantları temizle
                $this->db->delete('bt_urun_varyantlari', 'urun_id = :id', ['id' => $last_id]);
            }

            return $last_id;
        } catch (Exception $e) {
            error_log("Ürün kaydetme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function benzersizSlugOlustur($text, $except_id = 0)
    {
        $slug = generateSef($text);
        $original_slug = $slug;
        $counter = 1;
        $query = "SELECT id FROM bt_urunler WHERE slug = :slug";
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

    private function kategorileriGuncelle($urun_id, $kategori_ids)
    {
        $this->db->delete('bt_urun_kategori_iliski', 'urun_id = :urun_id', ['urun_id' => $urun_id]);
        if (!empty($kategori_ids)) {
            foreach ($kategori_ids as $kategori_id) {
                if ((int)$kategori_id > 0) {
                    $this->db->insert('bt_urun_kategori_iliski', ['urun_id' => $urun_id, 'kategori_id' => (int)$kategori_id]);
                }
            }
        }
    }

    private function resimleriGuncelle($urun_id, $resimler)
    {
        $this->db->delete('bt_urun_gorselleri', 'urun_id = :urun_id', ['urun_id' => $urun_id]);
        if (!empty($resimler)) {
            $sira = 0;
            foreach ($resimler as $resim_url) {
                $this->db->insert('bt_urun_gorselleri', [
                    'urun_id' => $urun_id,
                    'gorsel_url' => $resim_url,
                    'alt_metin' => '', // Gerekirse formdan alınabilir
                    'sira' => $sira++
                ]);
            }
        }
    }

    /**
     * DÜZELTİLDİ: Ürünün varyantlarını veritabanında günceller.
     */
    private function varyantlariGuncelle($urun_id, $varyant_json)
    {
        $veri = json_decode($varyant_json, true);
        if (!$veri || !isset($veri['combinations']) || empty($veri['combinations'])) {
            $this->db->delete('bt_urun_varyantlari', 'urun_id = :id', ['id' => $urun_id]);
            return;
        }

        // Önceki tüm varyantları temizle (ON DELETE CASCADE ilişkili değerleri siler)
        $this->db->delete('bt_urun_varyantlari', 'urun_id = :id', ['id' => $urun_id]);

        foreach ($veri['combinations'] as $combo) {
            $varyant_data = [
                'urun_id' => $urun_id,
                'fiyat' => empty($combo['price']) ? 0.00 : (float)$combo['price'],
                'stok_adedi' => empty($combo['stock']) ? 0 : (int)$combo['stock'],
                'varyant_kodu' => $combo['sku'] ?? null
            ];
            $yeni_varyant_id = $this->db->insert('bt_urun_varyantlari', $varyant_data);

            if (!$yeni_varyant_id) {
                error_log("Varyant oluşturulamadı: " . json_encode($varyant_data));
                continue; // Bu kombinasyonu atla, diğerleriyle devam et
            }

            // 2. Bu varyantı değerleriyle ilişkilendir
            foreach ($combo['attributes'] as $index => $deger_adi) {
                // İlgili özellik adını JSON'dan bul
                $ozellik_adi = $veri['options'][$index]['name'] ?? null;
                if (!$ozellik_adi) continue;

                // Değer ID'sini al veya oluştur
                $deger_id = $this->degerIdGetirVeyaOlustur($deger_adi, $ozellik_adi);

                if ($deger_id) {
                    $this->db->insert('bt_urun_varyant_degerleri', [
                        'varyant_id' => $yeni_varyant_id,
                        'deger_id' => $deger_id
                    ]);
                } else {
                    error_log("Varyant değeri için özellik bulunamadı veya oluşturulamadı: Değer='{$deger_adi}', Özellik='{$ozellik_adi}'");
                }
            }
        }
    }

    /**
     * DÜZELTİLDİ: Değer adına ve özellik adına göre ID'yi bulur. Eğer yoksa, oluşturur.
     */
    private function degerIdGetirVeyaOlustur($deger_adi, $ozellik_adi)
    {
        // 1. Özellik adına göre ID'yi bul
        $ozellik = $this->db->fetch("SELECT id FROM bt_varyant_ozellikleri WHERE ozellik_adi = :ad", ['ad' => $ozellik_adi]);
        if (!$ozellik) {
            error_log("Varyant özelliği bulunamadı: " . $ozellik_adi);
            return null;
        }
        $ozellik_id = $ozellik['id'];

        // 2. Değer adına VE özellik ID'sine göre değeri ara (daha kesin sonuç)
        $deger = $this->db->fetch(
            "SELECT id FROM bt_varyant_degerleri WHERE deger = :deger AND ozellik_id = :ozellik_id",
            ['deger' => $deger_adi, 'ozellik_id' => $ozellik_id]
        );

        if ($deger) {
            return $deger['id']; // Varsa mevcut ID'yi döndür
        }

        // 3. Değer yoksa, bu özelliğe bağlı olarak yeni bir değer oluştur
        $yeni_deger_data = [
            'ozellik_id' => $ozellik_id,
            'deger' => $deger_adi,
            'sira' => 0 // Varsayılan sıra
        ];
        return $this->db->insert('bt_varyant_degerleri', $yeni_deger_data);
    }

    private function jsonYanit($success, $message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
