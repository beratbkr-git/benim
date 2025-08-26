<?php
// yonetim.php - Otomatik Parola Guncelleme ve Guvenli Giris Mantigi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// dosya yollari icin sabitleri tanimla
define("YONETIM_DIR", "admin_bt/");
define("CONTROLLER_DIR", YONETIM_DIR . "controllers/");
define("VIEW_DIR", YONETIM_DIR . "views/");

global $yonetimurl, $p1, $p2, $p3, $p4, $db;


// yetki kontrol fonksiyonu - PDO'ya uyumlu hale getirildi
function yonetimKontrol()
{
    global $db;
    if (isset($_SESSION['kullanici_id'])) {
        $kullanici = $db->fetch("SELECT * FROM bt_kullanicilar WHERE id = :id AND aktif_mi = 1", ['id' => $_SESSION['kullanici_id']]);
        if ($kullanici) {
            $_SESSION['kullanici'] = $kullanici;
            return true;
        }
    }
    if (isset($_COOKIE['remember_me'])) {
        @list($selector, $validator) = explode(':', $_COOKIE['remember_me'], 2);
        if ($selector && $validator) {
            $token = $db->fetch("SELECT * FROM bt_auth_tokens WHERE selector = :selector AND expires >= NOW()", ['selector' => $selector]);
            if ($token && hash_equals(hash('sha256', $validator), $token['hashed_validator'])) {
                session_regenerate_id(true);
                $_SESSION['kullanici_id'] = $token['kullanici_id'];
                $_SESSION['kullanici'] = $db->fetch("SELECT * FROM bt_kullanicilar WHERE id = :id AND aktif_mi = 1", ['id' => $token['kullanici_id']]);
                $db->delete('bt_auth_tokens', 'id = :id', ['id' => $token['id']]);
                hatirlamaTokeniOlustur($token['kullanici_id']);
                return true;
            }
        }
        if (isset($token['id'])) $db->delete('bt_auth_tokens', 'id = :id', ['id' => $token['id']]);
        deleteSecureCookie('remember_me');
    }

    // yonetimKontrol() fonksiyonu artik oturumu yok etmeyecek, sadece false donecek.
    // oturumun yok edilmesi login/cikis islemlerinde yapilacak.
    return false;
}

function hatirlamaTokeniOlustur($kullanici_id)
{
    global $db;
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $expires = (new DateTime('now'))->add(new DateInterval('P30D'));
    $token_data = [
        'kullanici_id' => $kullanici_id,
        'selector' => $selector,
        'hashed_validator' => hash('sha256', $validator),
        'expires' => $expires->format('Y-m-d H:i:s')
    ];
    $db->insert('bt_auth_tokens', $token_data);
    setSecureCookie('remember_me', $selector . ':' . $validator, 30);
}


// --- ROUTER MANTIGI ---

// bu kisim, bir AJAX istegi olup olmadigini belirlemek icin kullanilir.
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'  || $_SERVER['REQUEST_METHOD'] == 'POST' || $_POST  || $_SERVER['REQUEST_METHOD'] == "DELETE";
if (isset($_GET["request"]) == "request") {
    $is_ajax = true;
}

if ($p2 == "giris") {
    if (yonetimKontrol()) {
        header("Location: /$yonetimurl");
        exit;
    }
    require_once(VIEW_DIR . "login/giris.php");
    exit;
} else if ($p2 == "giriskontrol") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $eposta = $_POST["eposta"] ?? '';
        $parola = $_POST["parola"] ?? '';
        $remember_me = isset($_POST['remember_me']);

        if (empty($eposta) || empty($parola)) {
            $_SESSION["hata"] = "E-posta ve parola alanları zorunludur.";
            header("Location: /$yonetimurl/giris");
            exit;
        }

        $kullanici = $db->fetch("SELECT * FROM bt_kullanicilar WHERE eposta = :eposta AND aktif_mi = 1", ['eposta' => $eposta]);

        if ($kullanici) {
            $login_success = false;
            // yeni ve guvenli yontemle kontrol et
            if (password_verify($parola, $kullanici['parola'])) {
                $login_success = true;
            }
            // eski (duz metin) yontemle kontrol et (geriye donuk uyumluluk icin)
            elseif ($kullanici['parola'] === $parola) {
                $login_success = true;
                // eski parola tespit edildi! hemen yeni formata guncelle.
                $yeni_parola_hash = password_hash($parola, PASSWORD_DEFAULT);
                $db->update('bt_kullanicilar', ['parola' => $yeni_parola_hash], 'id = :id', ['id' => $kullanici['id']]);
            }

            if ($login_success) {
                session_regenerate_id(true);
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici'] = $kullanici;

                if ($remember_me) {
                    $db->delete('bt_auth_tokens', 'kullanici_id = :id', ['id' => $kullanici['id']]);
                    hatirlamaTokeniOlustur($kullanici['id']);
                }

                header("Location: /$yonetimurl");
                exit;
            }
        }
        $_SESSION["hata"] = "E-posta veya parola hatalı.";
        header("Location: /$yonetimurl/giris");
        exit;
    }
    header("Location: /$yonetimurl/giris");
    exit;
} else if ($p2 == "cikis") {
    if (isset($_COOKIE['remember_me'])) {
        @list($selector) = explode(':', $_COOKIE['remember_me'], 2);
        if ($selector) $db->delete('bt_auth_tokens', 'selector = :selector', ['selector' => $selector]);
        deleteSecureCookie('remember_me');
    }
    session_unset();
    session_destroy();
    header("Location: /$yonetimurl/giris");
    exit;
} else {
    // once yetki kontrolu yap
    if (!yonetimKontrol()) {
        header("Location: /$yonetimurl/giris");
        exit;
    }
    // Yönlendirme işlemi
    $controller_map = [
        'anasayfa' => 'AnasayfaController',
        'menuler' => 'MenuController',
        'gorseller' => 'GorselController',
        'sayfalar' => 'SayfaController',
        'urunler' => 'UrunController',
        'urun-yorumlari' => 'UrunYorumController',
        'kategoriler' => 'KategoriController',
        'markalar' => 'MarkaController',
        'varyantlar' => 'VaryantController',
        'siparisler' => 'SiparisController',
        'kargolar' => 'KargoController',
        'musteriler' => 'MusteriController',
        'musteri-segmentleri' => 'MusteriSegmentiController',
        'bayiler' => 'BayiController',
        // 'kampanyalar' => 'KampanyaController',
        'kampanyalar' => 'KampanyaController',
        'raporlar' => 'RaporController',
        'kullanicilar' => 'KullaniciController',
        'ayarlar' => 'AyarController',
        'envanter' => 'EnvanterController',
        'bildirimler' => 'BildirimController',
        'eposta-sablonlari' => 'EpostaSablonController'
    ];
    $controller_name = ($p2 && isset($controller_map[$p2])) ? $controller_map[$p2] : 'AnasayfaController';
    $controller_file = CONTROLLER_DIR . $controller_name . '.php';

    // Yeni kontrolcü dosyalarını dahil et

    require_once(CONTROLLER_DIR . 'AnasayfaController.php');
    require_once(CONTROLLER_DIR . 'AnalyticsController.php');
    require_once(CONTROLLER_DIR . 'InventoryController.php');
    require_once(CONTROLLER_DIR . 'BildirimController.php');
    if (file_exists($controller_file)) {
        require_once($controller_file);



        // ... (yetki kontrolü ve metot çağırma kısmı aynı kalacak) ...

        $min_permission_map = [
            'anasayfa' => 'Editör',
            'menuler' => 'Editör',
            'sayfalar' => 'Editör',
            'gorseller' => 'Editör',
            'urunler' => 'Editör',
            'kategoriler' => 'Editör',
            'markalar' => 'Editör',
            'varyantlar' => 'Editör',
            'siparisler' => 'Editör',
            'kargolar' => 'Yönetici',
            'musteriler' => 'Editör',
            'bayiler' => 'Yönetici',
            'kampanyalar' => 'Editör',
            'raporlar' => 'Editör',
            'kullanicilar' => 'Admin',
            'ayarlar' => 'Yönetici',
            'envanter' => 'Editör',
            'eposta-sablonlari' => 'Yönetici'
        ];

        if (isset($min_permission_map[$p2]) && !hasPermission($min_permission_map[$p2])) {
            $_SESSION["hata"] = "Bu sayfaya erişim yetkiniz yok.";
            header("Location: /$yonetimurl");
            exit;
        }

        $controller = new $controller_name($db);

        $method_name = $p3 ?? 'anasayfa';

        $method_name = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $method_name))));

        $params = [];
        if (isset($p4)) $params[] = $p4;
        if (isset($p5)) $params[] = $p5;

        if (method_exists($controller, $method_name)) {
            // Sadece view'leri render eden metotlar için header/footer dahil et
            call_user_func_array([$controller, $method_name], $params);
        } else {
            http_response_code(404);
            echo "<h1>404 - Sayfa Bulunamadı</h1>";
        }
    } else if (!$p2 || $p2 === 'anasayfa') {
        require_once(VIEW_DIR . "header.php");
        require_once(VIEW_DIR . "anasayfa.php");
        require_once(VIEW_DIR . "footer.php");
    } else {
        http_response_code(404);
        echo "<h1>404 - Sayfa Bulunamadı</h1>";
    }
}
