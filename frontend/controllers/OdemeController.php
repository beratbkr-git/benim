<?php
// frontend/controllers/OdemeController.php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class OdemeController extends BaseController
{
    public function index()
    {
        $sepet = getSepet();
        $sepet_urunleri = $sepet['urunler'];

        if (empty($sepet_urunleri)) {
            $this->redirect('/sepet');
            return;
        }

        $sepet_toplam = $sepet['toplam_tutar'];

        $kargo_yontemleri = $this->db->fetchAll(
            "SELECT * FROM bt_kargo_yontemleri WHERE durum = 'Aktif'"
        );
        $varsayilan_kargo_ucreti = $kargo_yontemleri[0]['temel_ucret'] ?? 0;
        $kargo_ucreti = $varsayilan_kargo_ucreti;
        $genel_toplam = $sepet_toplam + $kargo_ucreti;

        $odeme_yontemleri = $this->db->fetchAll(
            "SELECT * FROM bt_odeme_yontemleri WHERE durum = 'Aktif'"
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
        $sepet = getSepet();
        $sepet_urunleri = $sepet['urunler'];
        $sepet_toplam = $sepet['toplam_tutar'];

        $kargo_yontemi_id = $_POST['kargo_yontemi'] ?? null;
        $kargo = $kargo_yontemi_id ? $this->db->fetch("SELECT * FROM bt_kargo_yontemleri WHERE id = :id", ['id' => $kargo_yontemi_id]) : null;
        $kargo_ucreti = $kargo['temel_ucret'] ?? 0;
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
                'indirim_tutari' => 0.00,
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
                    'urun_id' => $item['id'],
                    'varyant_id' => $item['varyant_id'] ?? null,
                    'urun_adi' => $item['urun_adi'],
                    'adet' => $item['adet'],
                    'birim_fiyat' => $item['fiyat'],
                    'toplam_fiyat' => $item['toplam'],
                    'varyant_bilgisi' => !empty($item['varyant_bilgisi']) ? json_encode(['varyant_adi' => $item['varyant_bilgisi']]) : null
                ];

                $this->db->insert('bt_siparis_detaylari', $detay_data);

                if (!empty($item['varyant_id'])) {
                    $this->db->query(
                        "
                        UPDATE bt_urun_varyantlari
                        SET stok_adedi = stok_adedi - :adet
                        WHERE id = :varyant_id
                    ",
                        ['adet' => $item['adet'], 'varyant_id' => $item['varyant_id']]
                    );
                }
            }

            $this->db->commit();

            unset($_SESSION['sepet']);

            $odeme_yontemi = $this->db->fetch(
                "SELECT yontem_kodu, gateway_ayarlari FROM bt_odeme_yontemleri WHERE id = :id",
                ['id' => $odeme_yontemi_id]
            );

            if ($odeme_yontemi && $odeme_yontemi['yontem_kodu'] === 'paytr') {
                $token = $this->initPaytrPayment(
                    $siparis_kodu,
                    $genel_toplam,
                    $ad_soyad,
                    $eposta,
                    $telefon,
                    $adres,
                    $odeme_yontemi['gateway_ayarlari'],
                    $sepet_urunleri
                );

                if ($token) {
                    $_SESSION['paytr_token'] = $token;
                    $_SESSION['paytr_oid'] = $siparis_kodu;
                    $this->redirect('/odeme/paytr');
                    return;
                }
            }

            $_SESSION['basari'] = 'Siparişiniz başarıyla oluşturuldu. Sipariş kodunuz: ' . $siparis_kodu;
            $this->redirect('/hesap/siparisler');
        } catch (Exception $e) {
            $this->db->rollback();
            $_SESSION['hata'] = 'Sipariş oluşturulurken bir hata oluştu.';
        }
    }

    public function paytr()
    {
        if (empty($_SESSION['paytr_token']) || empty($_SESSION['paytr_oid'])) {
            $this->redirect('/');
            return;
        }

        $token = $_SESSION['paytr_token'];
        $this->loadView('odeme/paytr.php', [
            'title' => 'Ödeme - PayTR',
            'token' => $token
        ]);
    }

    private function initPaytrPayment($siparis_kodu, $tutar, $ad_soyad, $eposta, $telefon, $adres, $ayarlar_json, $sepet_urunleri)
    {
        $ayarlar = json_decode($ayarlar_json, true);
        $merchant_id = $ayarlar['merchant_id'] ?? '';
        $merchant_key = $ayarlar['merchant_key'] ?? '';
        $merchant_salt = $ayarlar['merchant_salt'] ?? '';
        if (!$merchant_id || !$merchant_key || !$merchant_salt) {
            return null;
        }

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $merchant_oid = $siparis_kodu;
        $payment_amount = (int) round($tutar * 100);
        $hash_str = $merchant_id . $user_ip . $merchant_oid . $eposta . $payment_amount . $merchant_salt;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_key, $merchant_salt, true));

        $basket = [];
        foreach ($sepet_urunleri as $urun) {
            $basket[] = [
                $urun['urun_adi'],
                (float) $urun['fiyat'],
                (int) $urun['adet']
            ];
        }

        $post_vals = [
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $eposta,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_name' => $ad_soyad,
            'user_address' => $adres,
            'user_phone' => $telefon,
            'merchant_ok_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/odeme/paytr-basarili',
            'merchant_fail_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/odeme/paytr-hata',
            'timeout_limit' => 30,
            'no_installment' => 0,
            'max_installment' => 0,
            'currency' => 'TL',
            'test_mode' => 1,
            'basket' => base64_encode(json_encode($basket))
        ];

        $ch = curl_init('https://www.paytr.com/odeme/api/get-token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            return $result['token'];
        }
        return null;
    }
}
