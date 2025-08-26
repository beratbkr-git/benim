<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class KategoriController extends BaseController
{
    public function detay($kategori_slug = null)
    {
        // Slug yoksa veya geçersizse ürünler sayfasına yönlendir.
        if (!$kategori_slug) {
            $this->redirect('/urunler');
            return;
        }

        // Kategori verisini veritabanından slug'a göre çek.
        $kategori = $this->db->fetch("
            SELECT * FROM bt_kategoriler 
            WHERE slug = :slug AND durum = 'Aktif'
        ", ['slug' => $kategori_slug]);

        if (!$kategori) {
            http_response_code(404);
            require_once(FRONTEND_VIEW_DIR . "404.php");
            return;
        }

        $kategori_id = $kategori['id'];
        $sayfa = $_GET['sayfa'] ?? 1;
        $siralama = $_GET['siralama'] ?? 'yeni';
        $limit = 12;
        $offset = ($sayfa - 1) * $limit;

        // match ifadesi yerine switch-case kullanıldı
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

        $urunler = $this->db->fetchAll("
            SELECT DISTINCT u.*, m.marka_adi,
                   (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id) as min_fiyat,
                   (SELECT MAX(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id) as max_fiyat,
                   (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
            FROM bt_urunler u
            LEFT JOIN bt_markalar m ON u.marka_id = m.id
            JOIN bt_urun_kategori_iliski uk ON u.id = uk.urun_id
            WHERE uk.kategori_id = :kategori_id AND u.durum = 'Aktif'
            ORDER BY {$order_clause}
            LIMIT {$limit} OFFSET {$offset}
        ", ['kategori_id' => $kategori_id]);

        $toplam_urun = $this->db->fetch("
            SELECT COUNT(DISTINCT u.id) as toplam
            FROM bt_urunler u
            JOIN bt_urun_kategori_iliski uk ON u.id = uk.urun_id
            WHERE uk.kategori_id = :kategori_id AND u.durum = 'Aktif'
        ", ['kategori_id' => $kategori_id])['toplam'];

        $alt_kategoriler = $this->db->fetchAll("
            SELECT * FROM bt_kategoriler 
            WHERE ust_kategori_id = :kategori_id AND durum = 'Aktif'
            ORDER BY sira ASC
        ", ['kategori_id' => $kategori_id]);

        $this->loadView('kategoriler/kategori.php', [
            'title' => $kategori['seo_title'] ?? ($kategori['kategori_adi'] . ' - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila')),
            'description' => $kategori['seo_description'] ?? ($kategori['aciklama'] ?? ''),
            'kategori' => $kategori,
            'urunler' => $urunler,
            'alt_kategoriler' => $alt_kategoriler,
            'toplam_urun' => $toplam_urun,
            'sayfa' => $sayfa,
            'toplam_sayfa' => ceil($toplam_urun / $limit),
            'siralama' => $siralama,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    // `index` metodu artık `detay` metoduna yönlendirildiği için bu metodun içeriği güncellendi.
    public function index()
    {
        $this->redirect('/urunler');
    }
}
