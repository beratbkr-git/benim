<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class UrunDetayController extends BaseController
{
    public function detay($urun_slug = null)
    {
        if (!$urun_slug) {
            $this->redirect('/');
            return;
        }

        $urun = $this->db->fetch("
            SELECT u.*, m.marka_adi 
            FROM bt_urunler u
            LEFT JOIN bt_markalar m ON u.marka_id = m.id
            WHERE u.slug = :slug AND u.durum = 'Aktif'
        ", ['slug' => $urun_slug]);

        if (!$urun) {
            http_response_code(404);
            require_once(FRONTEND_VIEW_DIR . "404.php");
            return;
        }

        $urun_id = $urun['id'];

        // Ürün görsellerini çekme
        $gorseller = $this->db->fetchAll("
            SELECT * FROM bt_urun_gorselleri WHERE urun_id = :urun_id ORDER BY sira ASC
        ", ['urun_id' => $urun_id]);

        // Ürün varyantlarını çekme
        $varyantlar = $this->db->fetchAll("
            SELECT v.id, v.fiyat, v.indirimli_fiyat, v.stok_adedi, v.varyant_kodu, GROUP_CONCAT(d.deger ORDER BY d.sira ASC SEPARATOR ' / ') AS varyant_adi
            FROM bt_urun_varyantlari v
            JOIN bt_urun_varyant_degerleri vd ON v.id = vd.varyant_id
            JOIN bt_varyant_degerleri d ON vd.deger_id = d.id
            WHERE v.urun_id = :urun_id
            GROUP BY v.id
            ORDER BY v.fiyat ASC
        ", ['urun_id' => $urun_id]);

        // Ürün yorumlarını çekme
        $yorumlar = $this->db->fetchAll("
            SELECT t1.*, t2.ad_soyad as musteri_adi
            FROM bt_urun_yorumlari t1
            LEFT JOIN bt_musteriler t2 ON t1.musteri_id = t2.id
            WHERE t1.urun_id = :urun_id AND t1.durum = 'Onaylandı'
            ORDER BY t1.olusturma_tarihi DESC
        ", ['urun_id' => $urun_id]);

        // Benzer ürünleri çekme
        // İki farklı yer tutucu kullanılarak parametre uyuşmazlığı hatası giderildi.
        $benzer_urunler = $this->db->fetchAll("
            SELECT u.*,
                   (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel,
                   CASE
                       WHEN u.varyant_var_mi = 1 THEN (SELECT MIN(fiyat) FROM bt_urun_varyantlari WHERE urun_id = u.id)
                       ELSE u.satis_fiyati
                   END as min_fiyat
            FROM bt_urunler u
            LEFT JOIN bt_urun_kategori_iliski uk ON u.id = uk.urun_id
            WHERE uk.kategori_id IN (SELECT kategori_id FROM bt_urun_kategori_iliski WHERE urun_id = :urun_id_1)
            AND u.id != :urun_id_2 AND u.durum = 'Aktif'
            GROUP BY u.id
            LIMIT 4
        ", ['urun_id_1' => $urun_id, 'urun_id_2' => $urun_id]);

        $this->loadView('urunler/detay.php', [
            'title' => $urun['meta_title'] ?? ($urun['urun_adi'] . ' - ' . ($this->site_ayarlari['site_adi'] ?? 'Ketchila')),
            'description' => $urun['meta_description'] ?? sinirliKarakter(strip_tags($urun['aciklama']), 160),
            'urun' => $urun,
            'gorseller' => $gorseller,
            'varyantlar' => $varyantlar,
            'yorumlar' => $yorumlar,
            'benzer_urunler' => $benzer_urunler,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }
}
