<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define("FRONTEND_DIR", "frontend/");
define("FRONTEND_CONTROLLER_DIR", FRONTEND_DIR . "controllers/");
define("FRONTEND_VIEW_DIR", FRONTEND_DIR . "views/");
define("FRONTEND_ASSETS_DIR", "frontend/assets/");

global $db, $p1, $p2, $p3, $p4, $site_ayarlari;

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax']);

$frontend_controller_map = [
    '' => 'AnasayfaController',
    'anasayfa' => 'AnasayfaController',
    'urunler' => 'UrunController',
    'kategori' => 'KategoriController',
    'urun' => 'UrunDetayController',
    'sepet' => 'SepetController',
    'istek-listesi' => 'IstekListesiController',
    'odeme' => 'OdemeController',
    'hesap' => 'HesapController',
    'giris' => 'GirisController',
    'kayit' => 'KayitController',
    'sifremi-unuttum' => 'SifreController',
    'iletisim' => 'IletisimController',
    'hakkimizda' => 'HakkimizdaController',
    'ajax' => 'AjaxController'
];

$default_page = 'anasayfa';
$page_slug = $p1 ?? $default_page;

if (isset($frontend_controller_map[$page_slug])) {
    $controller_name = $frontend_controller_map[$page_slug];
    $controller_file = FRONTEND_CONTROLLER_DIR . $controller_name . '.php';

    if (file_exists($controller_file)) {
        require_once($controller_file);

        // Controller sınıfının bir örneğini oluştur ve global yap
        $controller = new $controller_name($db);
        global $controller; // Bu satır, controller nesnesini global yapar

        $method_name = 'index';
        $params = [];

        if (!empty($p2)) {
            $action_slug = $p2;
            if ($page_slug === 'kategori' || $page_slug === 'urun') {
                $method_name = 'detay';
                $params = [$action_slug];
            } else {
                $method_name = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action_slug))));
                if (isset($p3)) $params[] = $p3;
                if (isset($p4)) $params[] = $p4;
            }
        }

        if (method_exists($controller, $method_name)) {
            call_user_func_array([$controller, $method_name], $params);
        } else {
            http_response_code(404);
            require_once(FRONTEND_VIEW_DIR . "404.php");
        }
    } else {
        http_response_code(404);
        require_once(FRONTEND_VIEW_DIR . "404.php");
    }
} else {
    global $db;
    $sayfa = $db->fetch("SELECT * FROM bt_sayfa WHERE slug = :slug AND durum = 'Aktif'", ['slug' => $page_slug]);

    if ($sayfa) {
        $title = $sayfa["meta_title"] ?? $sayfa["sayfa_adi"];
        $description = $sayfa["meta_description"] ?? sinirliKarakter(strip_tags($sayfa["icerik"]), 160);

        require_once(FRONTEND_VIEW_DIR . "includes/header.php");
        echo "<main><div class='container mt-5 mb-5'><div class='row'><div class='col-lg-12'>";
        echo "<h1>" . htmlspecialchars($sayfa["sayfa_adi"]) . "</h1>";
        echo $sayfa["icerik"];
        echo "</div></div></div></main>";
        require_once(FRONTEND_VIEW_DIR . "includes/footer.php");
    } else {
        http_response_code(404);
        require_once(FRONTEND_VIEW_DIR . "404.php");
    }
}
