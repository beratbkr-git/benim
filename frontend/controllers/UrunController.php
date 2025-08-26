<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class UrunController extends BaseController
{
    public function index()
    {
        $sayfa = $_GET['sayfa'] ?? 1;
        $kategori_slugs_string = $_GET['kategori'] ?? null;
        $marka_ids_string = $_GET['marka'] ?? null;
        $arama = $_GET['q'] ?? '';
        $siralama = $_GET['siralama'] ?? 'yeni';
        $min_fiyat = $_GET['min_fiyat'] ?? null;
        $max_fiyat = $_GET['max_fiyat'] ?? null;
        $rating = $_GET['rating'] ?? null;

        $limit = 12;
        $offset = ($sayfa - 1) * $limit;

        $where_conditions = ["u.durum = 'Aktif'"];
        $params = [];

        // Kategori slug'larına göre filtreleme
        if ($kategori_slugs_string) {
            $kategori_slugs = explode(',', $kategori_slugs_string);
            $cat_placeholders = [];
            foreach ($kategori_slugs as $index => $slug) {
                $param_name = ":kategori_slug_" . $index;
                $cat_placeholders[] = $param_name;
                $params[$param_name] = $slug;
            }
            $where_conditions[] = "k.slug IN (" . implode(', ', $cat_placeholders) . ")";
        }

        // Marka ID'lerine göre filtreleme
        if ($marka_ids_string) {
            $marka_ids = explode(',', $marka_ids_string);
            $brand_placeholders = [];
            foreach ($marka_ids as $index => $id) {
                $param_name = ":marka_id_" . $index;
                $brand_placeholders[] = $param_name;
                $params[$param_name] = $id;
            }
            $where_conditions[] = "u.marka_id IN (" . implode(', ', $brand_placeholders) . ")";
        }

        if ($arama) {
            $where_conditions[] = "(u.urun_adi LIKE :arama OR u.aciklama LIKE :arama)";
            $params[':arama'] = '%' . $arama . '%';
        }

        // Derecelendirme filtresi
        if ($rating) {
            $where_conditions[] = "u.average_rating >= :rating";
            $params[':rating'] = $rating;
        }

        $where_clause = implode(' AND ', $where_conditions);

        $order_clause = '';
        switch ($siralama) {
            case 'fiyat-artan':
                $order_clause = 'min_fiyat ASC';
                break;
            case 'fiyat-azalan':
                $order_clause = 'min_fiyat DESC';
                break;
            case 'alfabetik':
                $order_clause = 'u.urun_adi ASC';
                break;
            case 'populer':
                $order_clause = 'u.view_count DESC';
                break;
            default:
                $order_clause = 'u.olusturma_tarihi DESC';
                break;
        }

        $query = "
            SELECT DISTINCT u.*, m.marka_adi,
                   (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel,
                   CASE
                       WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                       ELSE u.satis_fiyati
                   END as min_fiyat,
                   CASE
                       WHEN u.varyant_var_mi = 1 THEN (SELECT MAX(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                       ELSE u.satis_fiyati
                   END as max_fiyat
            FROM bt_urunler u
            LEFT JOIN bt_markalar m ON u.marka_id = m.id
            LEFT JOIN bt_urun_kategori_iliski uk ON u.id = uk.urun_id
            LEFT JOIN bt_kategoriler k ON uk.kategori_id = k.id
            WHERE {$where_clause}
            ORDER BY {$order_clause}
            LIMIT {$limit} OFFSET {$offset}
        ";

        $urunler = $this->db->fetchAll($query, $params);

        $toplam_urun = $this->db->fetch("
            SELECT COUNT(DISTINCT u.id) as toplam
            FROM bt_urunler u
            LEFT JOIN bt_urun_kategori_iliski uk ON u.id = uk.urun_id
            LEFT JOIN bt_kategoriler k ON uk.kategori_id = k.id
            WHERE {$where_clause}
        ", $params)['toplam'];

        $kategoriler = $this->db->fetchAll("
            SELECT k.*, 
                   (SELECT COUNT(*) FROM bt_urun_kategori_iliski uk 
                    JOIN bt_urunler u ON uk.urun_id = u.id 
                    WHERE uk.kategori_id = k.id AND u.durum = 'Aktif') as urun_sayisi
            FROM bt_kategoriler k
            WHERE k.durum = 'Aktif' AND k.ust_kategori_id IS NULL
            ORDER BY k.sira ASC
        ");

        $markalar = $this->db->fetchAll("
            SELECT m.*, 
                   (SELECT COUNT(*) FROM bt_urunler u WHERE u.marka_id = m.id AND u.durum = 'Aktif') as urun_sayisi
            FROM bt_markalar m
            WHERE m.durum = 'Aktif' 
            ORDER BY m.marka_adi ASC
        ");

        $this->loadView('urunler/liste.php', [
            'title' => 'Ürünler - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila'),
            'urunler' => $urunler,
            'kategoriler' => $kategoriler,
            'markalar' => $markalar,
            'toplam_urun' => $toplam_urun,
            'sayfa' => $sayfa,
            'limit' => $limit,
            'toplam_sayfa' => ceil($toplam_urun / $limit),
            'kategori_slug' => $kategori_slugs_string,
            'marka_id' => $marka_ids_string,
            'arama' => $arama,
            'siralama' => $siralama,
            'min_fiyat' => $min_fiyat,
            'max_fiyat' => $max_fiyat,
            'rating' => $rating,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }
}
