<?php
// frontend/controllers/AnasayfaController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class AnasayfaController extends BaseController
{
    public function index()
    {
        // Ana sayfadaki dinamik verileri çeken ana metot

        // En Çok Satan Ürünler
        // Sipariş detaylarından toplam satış adedine göre sıralanıyor
        $top_selling_products = $this->db->fetchAll("
            SELECT 
                u.*, 
                m.marka_adi,
                (SELECT SUM(adet) FROM bt_siparis_detaylari WHERE urun_id = u.id) as toplam_satis,
                CASE
                    WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                    ELSE u.satis_fiyati
                END as min_fiyat,
                CASE
                    WHEN u.varyant_var_mi = 1 THEN (SELECT MAX(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                    ELSE u.satis_fiyati
                END as max_fiyat,
                (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
            FROM bt_urunler u 
            LEFT JOIN bt_markalar m ON u.marka_id = m.id 
            WHERE u.durum = 'Aktif'
            GROUP BY u.id
            ORDER BY toplam_satis DESC
            LIMIT 12
        ");

        // Öne Çıkan Kategoriler
        $kategoriler = $this->db->fetchAll("
            SELECT k.*, 
                   (SELECT COUNT(*) FROM bt_urun_kategori_iliski uk 
                    JOIN bt_urunler u ON uk.urun_id = u.id 
                    WHERE uk.kategori_id = k.id AND u.durum = 'Aktif') as urun_sayisi,
                   (SELECT gorsel_url FROM bt_gorseller WHERE konum = CONCAT('kategori_', k.id) AND durum = 'Aktif' LIMIT 1) as kategori_gorsel
            FROM bt_kategoriler k
            WHERE k.durum = 'Aktif' AND k.featured = 1 AND k.ust_kategori_id IS NULL
            ORDER BY k.sira ASC 
            LIMIT 8
        ");

        // Sirke Kategorisindeki ürünler
        $sirke_kategorisindeki_urunler = $this->db->fetchAll("
            SELECT 
                u.*, 
                m.marka_adi, 
                CASE
                    WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                    ELSE u.satis_fiyati
                END as min_fiyat,
                (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
            FROM bt_urunler u
            LEFT JOIN bt_markalar m ON u.marka_id = m.id
            INNER JOIN bt_urun_kategori_iliski uk ON uk.urun_id = u.id
            INNER JOIN bt_kategoriler k ON uk.kategori_id = k.id
            WHERE u.durum = 'Aktif' AND (k.kategori_adi LIKE :sirke_adi OR k.slug = :sirke_slug)
            GROUP BY u.id
            ORDER BY u.olusturma_tarihi DESC 
            LIMIT 12
        ", ['sirke_adi' => '%Sirke%', 'sirke_slug' => 'sirkeler']);

        // Özler Kategorisindeki ürünler
        $ozler_kategorisindeki_urunler = $this->db->fetchAll("
            SELECT 
                u.*, 
                m.marka_adi, 
                CASE
                    WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                    ELSE u.satis_fiyati
                END as min_fiyat,
                (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
            FROM bt_urunler u
            LEFT JOIN bt_markalar m ON u.marka_id = m.id
            INNER JOIN bt_urun_kategori_iliski uk ON uk.urun_id = u.id
            INNER JOIN bt_kategoriler k ON uk.kategori_id = k.id
            WHERE u.durum = 'Aktif' AND (k.kategori_adi LIKE :ozler_adi OR k.slug = :ozler_slug)
            GROUP BY u.id
            ORDER BY u.olusturma_tarihi DESC 
            LIMIT 12
        ", ['ozler_adi' => '%Öz%', 'ozler_slug' => 'ozler']);


        // Yeni Ürünler
        $yeni_urunler = $this->db->fetchAll("
            SELECT 
                u.*, 
                m.marka_adi, 
                CASE
                    WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                    ELSE u.satis_fiyati
                END as min_fiyat,
                (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
            FROM bt_urunler u 
            LEFT JOIN bt_markalar m ON u.marka_id = m.id 
            WHERE u.durum = 'Aktif' 
            ORDER BY u.olusturma_tarihi DESC 
            LIMIT 8
        ");

        // Slider Görselleri
        $slider_gorseller = $this->db->fetchAll("
            SELECT * FROM bt_gorseller 
            WHERE konum = 'slider_anasayfa' AND durum = 'Aktif' 
            ORDER BY sira ASC
        ");

        // View'e gönderilecek veriler
        $data = [
            'title' => $this->site_ayarlari['site_adi'] ?? 'Ketchila E-Ticaret',
            'description' => $this->site_ayarlari['site_aciklama'] ?? '',
            'top_selling_products' => $top_selling_products,
            'sirke_urunler' => $sirke_kategorisindeki_urunler,
            'ozler_urunler' => $ozler_kategorisindeki_urunler,
            'kategoriler' => $kategoriler,
            'slider_gorseller' => $slider_gorseller,
            'yeni_urunler' => $yeni_urunler,
            'sepet_adet' => $this->getSepetAdet()
        ];

        $this->loadView('anasayfa.php', $data);
    }
}
