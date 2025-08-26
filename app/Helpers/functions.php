<?php

/**
 * E-Ticaret Projesi - Merkezi Yardımcı Fonksiyonlar
 * * Bu dosya, veritabanı işlemleri HARİÇ tüm genel yardımcı fonksiyonları içerir.
 * Veritabanı işlemleri için app/core/Database.php sınıfı kullanılmalıdır.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================================================================
// GÜVENLİK VE YETKİLENDİRME
// ======================================================================

/**
 * Kullanıcının belirtilen yetki seviyesine sahip olup olmadığını kontrol eder.
 * Hiyerarşi: Admin (3) > Yönetici (2) > Editör (1)
 * @param string $required_role Gereken minimum yetki seviyesi.
 * @return bool Yetkiye sahipse true, değilse false.
 */
function hasPermission($required_role)
{
    $user_role = $_SESSION['kullanici']['yetki_seviyesi'] ?? 'Misafir';
    $roles_hierarchy = ['Admin' => 3, 'Yönetici' => 2, 'Editör' => 1, 'Misafir' => 0];
    $user_level = $roles_hierarchy[$user_role] ?? 0;
    $required_level = $roles_hierarchy[$required_role] ?? 99;
    return $user_level >= $required_level;
}

/**
 * Kullanıcının IP adresini döndürür.
 * @return string|null Kullanıcının IP adresi veya bulunamazsa null.
 */
function Get_User_Ip()
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
    return null;
}

/**
 * Veriyi AES-256-CBC ile şifreler. DİKKAT: Parolalar için KULLANMAYIN!
 * @param string $data Şifrelenecek veri.
 * @return string Şifrelenmiş ve Base64 kodlanmış veri.
 */
function aes_sifrele($data)
{
    global $berat_reis_aes_sifre;
    $key = $berat_reis_aes_sifre;
    $cipher = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

/**
 * AES-256-CBC ile şifrelenmiş veriyi çözer.
 * @param string $data Şifrelenmiş veri.
 * @return string|false Çözülmüş veri veya hata.
 */
function aes_sifre_coz($data)
{
    global $berat_reis_aes_sifre;
    $key = $berat_reis_aes_sifre;
    $cipher = 'aes-256-cbc';
    @list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    if (!$iv || strlen($iv) !== openssl_cipher_iv_length($cipher)) return false;
    return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
}


// ======================================================================
// METİN, TARİH VE VERİ FORMATLAMA
// ======================================================================

/**
 * Verilen metni SEO dostu URL formatına (slug) çevirir.
 * @param string $text Çevrilecek metin.
 * @return string SEO uyumlu metin.
 */
function generateSef($text)
{
    $text = trim($text);
    $search = ['Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü', ' ', "'", "&", "?", "+", "."];
    $replace = ['c', 'c', 'g', 'g', 'i', 'i', 'o', 'o', 's', 's', 'u', 'u', '-', '', 've', '', '', ''];
    $new_text = str_replace($search, $replace, $text);
    $new_text = preg_replace('/-+/', '-', $new_text);
    $new_text = preg_replace('/[^a-zA-Z0-9-]/', '', $new_text);
    $new_text = trim($new_text, '-');
    $new_text = strtolower($new_text);
    return $new_text;
}


/**
 * Metni belirtilen kelime sayısıyla sınırlar.
 * @param string $text Sınırlandırılacak metin.
 * @param int $limit Kelime limiti.
 * @return string Sınırlandırılmış metin.
 */
function sinirliKelime($text, $limit)
{
    $words = explode(" ", $text);
    if (count($words) > $limit) {
        return implode(" ", array_slice($words, 0, $limit)) . "...";
    }
    return $text;
}

/**
 * Metni belirtilen karakter sayısıyla sınırlar.
 * @param string $text Sınırlandırılacak metin.
 * @param int $limit Karakter limiti.
 * @return string Sınırlandırılmış metin.
 */
function sinirliKarakter($text, $limit)
{
    if (mb_strlen($text, 'UTF-8') > $limit) {
        return mb_substr($text, 0, $limit, 'UTF-8') . "...";
    }
    return $text;
}

/**
 * Tarihi Türkçe ay ve gün isimleriyle formatlar: '12 Ağustos 2025, Salı'
 * @param string $dateString Formatlanacak tarih ('Y-m-d H:i:s').
 * @return string Formatlanmış tarih.
 */
function formatFullTurkishDate($dateString)
{
    if (empty($dateString) || strtotime($dateString) === false) return '';
    $date = new DateTime($dateString);
    $turkishMonths = [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'];
    $turkishDays = ['Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba', 'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'];
    return $date->format('d') . ' ' . $turkishMonths[(int)$date->format('m')] . ' ' . $date->format('Y') . ', ' . $turkishDays[$date->format('l')];
}

/**
 * Telefon numarasını uluslararası formata çevirir: +90 (XXX) XXX XX XX
 * @param string $telefon Ham telefon numarası.
 * @return string Formatlanmış telefon numarası.
 */
function telefonYazdir($telefon)
{
    $telefon = preg_replace('/\D/', '', $telefon);
    if (strlen($telefon) === 10) $telefon = '90' . $telefon;
    if (strlen($telefon) === 11 && substr($telefon, 0, 1) === '0') $telefon = '90' . substr($telefon, 1);
    if (strlen($telefon) === 12 && substr($telefon, 0, 2) === '90') {
        return "+90 (" . substr($telefon, 2, 3) . ") " . substr($telefon, 5, 3) . " " . substr($telefon, 8, 2) . " " . substr($telefon, 10, 2);
    }
    return $telefon;
}

/**
 * Sayıyı para birimi formatında yazar: 1.234,56
 * @param float $sayi Formatlanacak sayı.
 * @return string Formatlanmış sayı.
 */
function formatKur($sayi)
{
    return number_format($sayi, 2, ',', '.');
}


// ======================================================================
// DOSYA YÖNETİMİ
// ======================================================================

/**
 * Gelişmiş dosya yükleme fonksiyonu. Resimleri WebP'ye çevirir.
 * @param array $file $_FILES'dan gelen dosya.
 * @param string $target_dir Hedef klasör.
 * @return string|false Başarılı ise dosya yolu, değilse false.
 */
function dosyaYukle($file, $target_dir)
{
    if (empty($file["name"]) || $file['error'] !== UPLOAD_ERR_OK) return false;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "png", "jpeg", "webp", "svg", "gif", "pdf", "doc", "docx", "xls", "xlsx"];

    if (!in_array($file_extension, $allowed_types) || $file["size"] > 10 * 1024 * 1024) return false;

    $unique_filename = uniqid('file_', true) . '.' . $file_extension;
    $target_file = $target_dir . $unique_filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        if (in_array($file_extension, ["jpg", "jpeg", "png"]) && function_exists('imagewebp')) {
            $webp_path = $target_dir . pathinfo($unique_filename, PATHINFO_FILENAME) . '.webp';
            if (imageconvertowebp($target_file, $webp_path)) {
                unlink($target_file);
                return "/" . $webp_path;
            }
        }
        return "/" . $target_file;
    }
    return false;
}

/**
 * Verilen bir resmi WebP formatına dönüştürür.
 * @param string $source Kaynak dosya.
 * @param string $destination Hedef dosya (.webp).
 * @param int $quality Kalite (0-100).
 * @return bool Başarı durumu.
 */
function imageconvertowebp($source, $destination, $quality = 80)
{
    $image_type = exif_imagetype($source);
    $img = null;
    if ($image_type === IMAGETYPE_JPEG) $img = imagecreatefromjpeg($source);
    elseif ($image_type === IMAGETYPE_PNG) $img = imagecreatefrompng($source);
    else return false;

    if ($img === false) return false;

    imagepalettetotruecolor($img);
    imagealphablending($img, true);
    imagesavealpha($img, true);
    $result = imagewebp($img, $destination, $quality);
    imagedestroy($img);
    return $result;
}

/**
 * CKEditor için özel, basit resim yükleme fonksiyonu.
 * @param array $resim CKEditor'dan gelen dosya.
 * @param string $target_dir Hedef klasör.
 * @return string|false Başarılı ise dosya yolu, değilse false.
 */
function ckeditorileyukle($resim, $target_dir)
{
    if (empty($resim["name"]) || $resim['error'] !== UPLOAD_ERR_OK) return false;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = strtolower(pathinfo($resim["name"], PATHINFO_EXTENSION));
    $allowed_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($file_extension, $allowed_formats) || $resim["size"] > 5 * 1024 * 1024) return false; // 5MB limit

    $unique_filename = uniqid('cke_', true) . '.' . $file_extension;
    $target_file = $target_dir . $unique_filename;

    if (move_uploaded_file($resim["tmp_name"], $target_file)) {
        return "/" . $target_file;
    }
    return false;
}

// ======================================================================
// GENEL YARDIMCILAR VE DİĞERLERİ
// ======================================================================

/**
 * Mevcut sayfanın tam URL'sini döndürür.
 * @return string Tam URL.
 */
function currentUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Bir diziden belirtilen anahtara göre değeri güvenli bir şekilde alır.
 * @param array $data Dizi.
 * @param string $key Anahtar.
 * @return mixed Değer veya null.
 */
function beratreis($data, $key)
{
    return is_array($data) ? ($data[$key] ?? null) : null;
}

/**
 * Boş veya null değerler için HTML span etiketi içinde hata mesajı döndürür.
 * @param string|null $text Kontrol edilecek metin.
 * @return string Mesaj veya metnin kendisi.
 */
function spanbaba($text = null)
{
    if (empty($text)) {
        return "<span class='text-muted' style='font-style: italic;'>Bilgi Yok</span>";
    }
    return htmlspecialchars($text);
}

// ======================================================================
// DIŞ API FONKSİYONLARI (İsteğe Bağlı)
// ======================================================================

/**
 * Piyasa verilerini çeker ve önbellekler.
 * @return array Piyasa verileri dizisi.
 */
function getAllPiyasaVerileri()
{
    $cacheFile = __DIR__ . '/../view/cache/piyasa_verileri.json';
    $cacheTime = 1800; // 30 dakika

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $veriler = [];
    // ... Veri çekme mantığı (TCMB, Bigpara vb.) buraya eklenebilir ...

    if (!empty($veriler)) {
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($cacheFile, json_encode($veriler));
    }
    return $veriler;
}

/**
 * Güvenli ve HTTPOnly bir çerez (cookie) oluşturur.
 * @param string $name Çerezin adı.
 * @param string $value Çerezin değeri.
 * @param int $expireDays Geçerlilik süresi (gün olarak).
 * @return bool
 */
function setSecureCookie($name, $value, $expireDays = 30)
{
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    return setcookie(
        $name,
        $value,
        [
            'expires' => time() + (86400 * $expireDays),
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

/**
 * Güvenli bir çerezi siler.
 * @param string $name Silinecek çerezin adı.
 * @return bool
 */
function deleteSecureCookie($name)
{
    // Çerezi silmek için geçmiş bir tarih verilir.
    return setSecureCookie($name, '', -1);
}
// ======================================================================
// YÖNETİM PANELİ ARAYÜZ (UI) YARDIMCILARI
// ======================================================================

/**
 * Acorn teması için tekli resim yükleme bileşeni oluşturur.
 * @param string $name Formdaki input'un name'i.
 * @param string $yol Mevcut resmin yolu.
 * @param string $aciklama Etiket açıklaması.
 */
function getSingleImageUpload($name, $yol, $aciklama = 'Görsel')
{
    $resim_yol = !empty($yol) ? $yol : '/yonetim/assets/img/placeholder-page.webp';
?>
    <div class="d-inline-block position-relative singleImageUpload">
        <img src="<?= htmlspecialchars($resim_yol) ?>" alt="<?= htmlspecialchars($aciklama) ?>" class="border border-4 border-separator-light rounded-xl sh-11 sw-11" onerror="this.onerror=null;this.src='/yonetim/assets/img/placeholder-page.webp';" />
        <button class="btn btn-icon btn-icon-only btn-separator-light btn-sm position-absolute rounded-xl b-0 e-0" type="button">
            <i data-acorn-icon="upload"></i>
        </button>
        <input class="d-none file-upload" name="<?= htmlspecialchars($name) ?>" type="file" accept="image/*" />
    </div>
    <small class="form-text text-muted d-block mt-2">Mevcut <?= htmlspecialchars($aciklama) ?>: <code><?= !empty($yol) ? htmlspecialchars($yol) : 'Yok' ?></code></small>
<?php
}
function getImage($konum)
{
    global $db;
    $gorsel = $db->fetch("SELECT gorsel_url FROM bt_gorseller WHERE konum = :konum", ["konum" => $konum]);
    return $gorsel["gorsel_url"];
}
/**
 * Tüm e-posta gönderimlerini yöneten merkezi fonksiyon.
 * @param string $mailto Gönderilecek e-posta adresi.
 * @param string $sablon_kodu Veritabanındaki e-posta şablonunun kodu.
 * @param array $degiskenler Şablondaki dinamik değişkenler. Örn: ['{musteri_adi}' => 'Ali']
 * @return bool|string True on success, error string on failure.
 */
function epostaGonder($mailto, $sablon_kodu, $degiskenler = [])
{
    require_once("app/src/mailer/class.phpmailer.php");
    global $site_ayarlari, $db;

    $sablon = $db->fetch("SELECT * FROM bt_eposta_sablonlari WHERE sablon_kodu = :kod AND durum = 'Aktif'", ['kod' => $sablon_kodu]);
    if (!$sablon) {
        error_log("E-posta şablonu '{$sablon_kodu}' bulunamadı.");
        return "E-posta şablonu bulunamadı.";
    }

    $mail_ayarlari_raw = $db->fetchAll("SELECT ayar_anahtari, ayar_degeri FROM bt_sistem_ayarlari_gelismis WHERE ayar_anahtari IN ('smtp_sunucu', 'smtp_port', 'smtp_kullanici_adi', 'smtp_sifre')");
    $mail_ayarlari = array_column($mail_ayarlari_raw, 'ayar_degeri', 'ayar_anahtari');

    $icerik = $sablon['html_icerik'];
    $konu = $sablon['konu'];

    // Dinamik değişkenleri şablona yerleştir
    foreach ($degiskenler as $key => $value) {
        $icerik = str_replace($key, htmlspecialchars($value), $icerik);
        $konu = str_replace($key, htmlspecialchars($value), $konu);
    }

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl'; // Düzeltildi
    $mail->Host = $mail_ayarlari["smtp_sunucu"];
    $mail->Port = $mail_ayarlari["smtp_port"];
    $mail->Username = $mail_ayarlari["smtp_kullanici_adi"];
    $mail->Password = $mail_ayarlari["smtp_sifre"];
    $mail->IsHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->SetFrom($mail_ayarlari["smtp_kullanici_adi"], $site_ayarlari["site_adi"]);
    $mail->AddAddress($mailto);
    $mail->Subject = mb_encode_mimeheader($konu, 'UTF-8', 'B');
    $mail->Body = $icerik;

    if (!$mail->Send()) {
        return $mail->ErrorInfo;
    } else {
        return true;
    }
}
// Bu kod, tüm sayfalarda ortak kullanılacak ve sepet verilerini çekecek.
function getSepet()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    global $db;

    $sepet = ['urunler' => [], 'toplam_tutar' => 0];

    if (empty($_SESSION['sepet'])) {
        return $sepet;
    }

    $sepet_items = $_SESSION['sepet'];
    $urun_ids = array_unique(array_column($sepet_items, 'urun_id'));

    if (empty($urun_ids)) {
        return $sepet;
    }

    $placeholders_urun = implode(',', array_fill(0, count($urun_ids), '?'));
    $urun_listesi = $db->fetchAll("
        SELECT 
            u.id, u.urun_adi, u.slug, u.satis_fiyati, u.varyant_var_mi,
            (SELECT gorsel_url FROM bt_urun_gorselleri WHERE urun_id = u.id ORDER BY sira ASC LIMIT 1) as kapak_gorsel
        FROM bt_urunler u
        WHERE u.id IN ({$placeholders_urun})
    ", $urun_ids);

    $urunler = [];
    foreach ($urun_listesi as $u) {
        $urunler[$u['id']] = $u;
    }

    $varyant_listesi = [];
    $varyant_ids = array_filter(array_column($sepet_items, 'varyant_id'));
    if (!empty($varyant_ids)) {
        $placeholders_varyant = implode(',', array_fill(0, count($varyant_ids), '?'));
        $varyant_listesi_raw = $db->fetchAll("
            SELECT id, fiyat, varyant_kodu FROM bt_urun_varyantlari WHERE id IN ({$placeholders_varyant})
        ", $varyant_ids);
        foreach ($varyant_listesi_raw as $v) {
            $varyant_listesi[$v['id']] = $v;
        }
    }

    $toplam_tutar = 0;
    $sepet_urunleri = [];
    foreach ($sepet_items as $sepet_item) {
        $urun_id = $sepet_item['urun_id'];
        $varyant_id = $sepet_item['varyant_id'] ?? null;
        $adet = $sepet_item['adet'];

        if (isset($urunler[$urun_id])) {
            $urun = $urunler[$urun_id];
            $fiyat = $urun['satis_fiyati'];
            $varyant_bilgisi = '';

            if ($varyant_id && isset($varyant_listesi[$varyant_id])) {
                $varyant = $varyant_listesi[$varyant_id];
                $fiyat = $varyant['fiyat'];
                $varyant_bilgisi = $varyant['varyant_kodu'];
            }

            $urun_toplam = $fiyat * $adet;
            $sepet_urunleri[] = [
                'id' => $urun['id'],
                'urun_adi' => $urun['urun_adi'],
                'slug' => $urun['slug'],
                'kapak_gorsel' => $urun['kapak_gorsel'],
                'varyant_id' => $varyant_id,
                'varyant_bilgisi' => $varyant_bilgisi,
                'adet' => $adet,
                'fiyat' => $fiyat,
                'toplam' => $urun_toplam
            ];
            $toplam_tutar += $urun_toplam;
        }
    }

    $sepet['urunler'] = $sepet_urunleri;
    $sepet['toplam_tutar'] = $toplam_tutar;

    return $sepet;
}
function formatPrice($price)
{
    global $site_ayarlari;
    $kdv_orani = $site_ayarlari['kdv_orani'] ?? 0;
    $para_birimi = $site_ayarlari['varsayilan_para_birimi'] ?? '₺';

    $kdv_dahil_fiyat = $price * (1 + $kdv_orani / 100);
    return number_format($kdv_dahil_fiyat, 2, ',', '.') . ' ' . $para_birimi;
}
