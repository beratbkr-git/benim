<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class OdemeController extends BaseController
{
    public function index()
    {
        $sepet_urunleri = $this->getSepetUrunleri();

        if (empty($sepet_urunleri)) {
            $this->redirect('/sepet');
            return;
        }

        $sepet_toplam = $this->getSepetToplam();
        $kargo_ucreti = $sepet_toplam >= 500 ? 0 : 29.90;
        $genel_toplam = $sepet_toplam + $kargo_ucreti;

        $odeme_yontemleri = $this->db->fetchAll(
            "SELECT * FROM bt_odeme_yontemleri WHERE durum = 'Aktif'"
        );

        $kargo_yontemleri = $this->db->fetchAll(
            "SELECT * FROM bt_kargo_yontemleri WHERE durum = 'Aktif'"
        );

        $adresler = [];
        if ($this->isLoggedIn()) {
            $adresler = $this->db->fetchAll(
                "SELECT * FROM bt_musteri_adresleri WHERE musteri_id = :id",
                ['id' => $_SESSION['musteri_id']]
            );
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->siparisOlustur();
            return;
        }

        $this->loadView('odeme/odeme.php', [
            'title' => 'Ödeme - ' . ($this->site_ayarlari['site_baslik'] ?? 'Ketchila'),
            'sepet_urunleri' => $sepet_urunleri,
            'sepet_toplam' => $sepet_toplam,
            'kargo_ucreti' => $kargo_ucreti,
            'genel_toplam' => $genel_toplam,
            'odeme_yontemleri' => $odeme_yontemleri,
            'kargo_yontemleri' => $kargo_yontemleri,
            'adresler' => $adresler,
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }

    private function siparisOlustur()
    {
        $sepet_urunleri = $this->getSepetUrunleri();
        $sepet_toplam = $this->getSepetToplam();
        $kargo_ucreti = $sepet_toplam >= 500 ? 0 : 29.90;
        $genel_toplam = $sepet_toplam + $kargo_ucreti;

        $adres_id = $_POST['adres_id'] ?? 'yeni';
        $ad_soyad = $_POST['ad_soyad'] ?? '';
        $eposta = $_POST['eposta'] ?? '';
        $telefon = $_POST['telefon'] ?? '';
        $adres = $_POST['adres'] ?? '';
        $il = $_POST['il'] ?? '';
        $ilce = $_POST['ilce'] ?? '';
        $posta_kodu = $_POST['posta_kodu'] ?? '';
        $fatura_farkli = isset($_POST['fatura_farkli']);
        $fatura_adres_id = $_POST['fatura_adres_id'] ?? ($fatura_farkli ? 'yeni' : $adres_id);
        $odeme_yontemi_id = $_POST['odeme_yontemi'] ?? 0;
        $musteri_notu = $_POST['musteri_notu'] ?? '';

        $hatalar = [];
        if (empty($ad_soyad)) $hatalar[] = 'Ad Soyad zorunludur.';
        if (empty($eposta)) $hatalar[] = 'E-posta zorunludur.';
        if (empty($telefon)) $hatalar[] = 'Telefon zorunludur.';
        if ($adres_id === 'yeni') {
            if (empty($adres)) $hatalar[] = 'Adres zorunludur.';
            if (empty($il)) $hatalar[] = 'İl zorunludur.';
            if (empty($ilce)) $hatalar[] = 'İlçe zorunludur.';
        }
        if ($fatura_farkli && $fatura_adres_id === 'yeni') {
            if (empty($_POST['fatura_adres'])) $hatalar[] = 'Fatura adresi zorunludur.';
        }

        if (!empty($hatalar)) {
            $_SESSION['hata'] = implode('<br>', $hatalar);
            return;
        }

        try {
            $this->db->beginTransaction();

            $musteri_id = null;
            if ($this->isLoggedIn()) {
                $musteri_id = $_SESSION['musteri_id'];
            } else {
                $mevcut_musteri = $this->db->fetch("SELECT id FROM bt_musteriler WHERE eposta = :eposta", ['eposta' => $eposta]);
                if ($mevcut_musteri) {
                    $musteri_id = $mevcut_musteri['id'];
                } else {
                    $musteri_data = [
                        'ad_soyad' => $ad_soyad,
                        'eposta' => $eposta,
                        'telefon' => $telefon,
                        'durum' => 'Aktif'
                    ];
                    $musteri_id = $this->db->insert('bt_musteriler', $musteri_data);
                }
            }

            if ($adres_id === 'yeni') {
                $adres_id = $this->db->insert('bt_musteri_adresleri', [
                    'musteri_id' => $musteri_id,
                    'adres_baslik' => 'Sipariş Adresi',
                    'ad_soyad' => $ad_soyad,
                    'adres' => $adres,
                    'il' => $il,
                    'ilce' => $ilce,
                    'posta_kodu' => $posta_kodu,
                    'telefon' => $telefon,
                    'varsayilan_adres' => 0
                ]);
            }

            if ($fatura_farkli) {
                if ($fatura_adres_id === 'yeni') {
                    $fatura_adres_id = $this->db->insert('bt_musteri_adresleri', [
                        'musteri_id' => $musteri_id,
                        'adres_baslik' => 'Fatura Adresi',
                        'ad_soyad' => $ad_soyad,
                        'adres' => $_POST['fatura_adres'],
                        'il' => $_POST['fatura_il'] ?? '',
                        'ilce' => $_POST['fatura_ilce'] ?? '',
                        'posta_kodu' => $_POST['fatura_posta_kodu'] ?? '',
                        'telefon' => $telefon,
                        'varsayilan_adres' => 0
                    ]);
                }
            } else {
                $fatura_adres_id = $adres_id;
            }

            $siparis_kodu = 'SP' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $siparis_data = [
                'musteri_id' => $musteri_id,
                'siparis_kodu' => $siparis_kodu,
                'toplam_tutar' => $sepet_toplam,
                'kargo_ucreti' => $kargo_ucreti,
                'odenen_tutar' => $genel_toplam,
                'siparis_durumu' => 'Yeni',
                'odeme_yontemi_id' => $odeme_yontemi_id,
                'musteri_notu' => $musteri_notu,
                'teslimat_adresi_id' => $adres_id,
                'fatura_adresi_id' => $fatura_adres_id,
                'olusturma_tarihi' => date('Y-m-d H:i:s')
            ];

            $siparis_id = $this->db->insert('bt_siparisler', $siparis_data);

            foreach ($sepet_urunleri as $item) {
                $detay_data = [
                    'siparis_id' => $siparis_id,
                    'urun_id' => $item['urun']['id'],
                    'varyant_id' => $item['varyant']['id'] ?? null,
                    'urun_adi' => $item['urun']['urun_adi'],
                    'adet' => $item['adet'],
                    'birim_fiyat' => $item['fiyat'],
                    'toplam_fiyat' => $item['toplam'],
                    'varyant_bilgisi' => $item['varyant'] ? json_encode(['varyant_adi' => $item['varyant']['varyant_adi']]) : null
                ];

                $this->db->insert('bt_siparis_detaylari', $detay_data);

                if ($item['varyant']) {
                    $this->db->query("
                        UPDATE bt_urun_varyantlari 
                        SET stok_adedi = stok_adedi - :adet 
                        WHERE id = :varyant_id
                    ", ['adet' => $item['adet'], 'varyant_id' => $item['varyant']['id']]);
                }
            }

            $this->db->commit();

            unset($_SESSION['sepet']);

            $_SESSION['basari'] = 'Siparişiniz başarıyla oluşturuldu. Sipariş kodunuz: ' . $siparis_kodu;
            $this->redirect('/hesap/siparisler');
        } catch (Exception $e) {
            $this->db->rollback();
            $_SESSION['hata'] = 'Sipariş oluşturulurken bir hata oluştu.';
        }
    }

    private function getSepetUrunleri()
    {
        if (!isset($_SESSION['sepet']) || empty($_SESSION['sepet'])) {
            return [];
        }

        $sepet_urunleri = [];
        foreach ($_SESSION['sepet'] as $key => $item) {
            $urun = $this->db->fetch("
                SELECT u.*, 
                       (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id AND kapak_mi = 1 LIMIT 1) as kapak_gorsel
                FROM bt_urunler u 
                WHERE u.id = :id
            ", ['id' => $item['urun_id']]);

            if ($urun) {
                $varyant = null;
                if ($item['varyant_id']) {
                    $varyant = $this->db->fetch("SELECT * FROM bt_urun_varyantlari WHERE id = :id", ['id' => $item['varyant_id']]);
                }

                $fiyat = $varyant ? $varyant['fiyat'] : $this->db->fetch("SELECT MIN(fiyat) as fiyat FROM bt_urun_varyantlari WHERE urun_id = :id", ['id' => $item['urun_id']])['fiyat'];

                $sepet_urunleri[] = [
                    'key' => $key,
                    'urun' => $urun,
                    'varyant' => $varyant,
                    'adet' => $item['adet'],
                    'fiyat' => $fiyat,
                    'toplam' => $fiyat * $item['adet']
                ];
            }
        }

        return $sepet_urunleri;
    }

    private function getSepetToplam()
    {
        $sepet_urunleri = $this->getSepetUrunleri();
        return array_sum(array_column($sepet_urunleri, 'toplam'));
    }
}
