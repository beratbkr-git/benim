<?php
// Oturum henüz başlatılmadıysa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Hata raporlamayı kapat (canlı ortamda önerilir)
error_reporting(0);

// Geliştirme aşamasında hataları görmek istersen:
error_reporting(E_ALL);
ini_set('display_errors', 1);
$sayfaerisimkodu = "oyleher1kes*erisemesin";
require_once("app/config/config.php");
require_once("app/Helpers/functions.php");

global $db;

$ayarlar_listesi = $db->fetchAll("SELECT ayar_anahtari, ayar_degeri FROM bt_site_ayarlari");

$site_ayarlari = array_column($ayarlar_listesi, 'ayar_degeri', 'ayar_anahtari');
global $site_ayarlari;

// URL parametrelerini al
$p1 = isset($_GET["p1"]) ? $_GET["p1"] : null;
$p2 = isset($_GET["p2"]) ? $_GET["p2"] : null;
$p3 = isset($_GET["p3"]) ? $_GET["p3"] : null;
$p4 = isset($_GET["p4"]) ? $_GET["p4"] : null;
$p5 = isset($_GET["p5"]) ? $_GET["p5"] : null;
$p6 = isset($_GET["p6"]) ? $_GET["p6"] : null;
$p7 = isset($_GET["p7"]) ? $_GET["p7"] : null;
// Referans URL'sini al (gerekirse)
$refeer_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
$yonetimurl = "yonetici";
if ($p1 == "404") {
    http_response_code(404);
    $title = "404 Sayfa Bulunamadı!";
    $description = "Aradığınız sayfa bulunamadı.";
    require_once("view/header.php");
    require_once("view/templates/404.php");
    require_once("view/footer.php");
    exit;
} else if ($p1 === $yonetimurl) {


    require_once("routing/admin.php");
    exit;
} else if ($p1 === "ajax") {
    // if ($p2 == "brt-ajax") {
    //     $type = $_POST['type'] ?? '';
    //     header('Content-Type: application/json');
    //     if ($type === "ilce") {
    //         $il_id = (int)($_GET['il_id'] ?? 0);
    //         $ilceler = query_select("ilce", "il_id = '{$il_id}'", "ad ASC");
    //         echo json_encode($ilceler);
    //         exit;
    //     } else if ($type === "semtler") {
    //         $ilce_id = (int)($_GET['ilce_id'] ?? 0);
    //         $semtler = query_select("semtler", "ilceid = '{$ilce_id}'", "semt ASC");
    //         echo json_encode($semtler);
    //         exit;
    //     }
    // }
    exit;
} else {
    // Frontend rotaları
    // Eklenti sistemi aksiyonu: 'frontend_init'
    // Bu hook, frontend başlatılmadan önce eklentilere aksiyon noktası sağlar.
    // do_action('frontend_init');
    define("VIEW_DIR", "view/");
    define("SITE_DIR", true);

    require_once("routing/site.php");
    exit;
}
// Bu satırlar genellikle gereksizdir çünkü yukarıdaki exit'ler çalışır.
// Ancak herhangi bir nedenle yukarıdaki koşullardan hiçbiri tetiklenmezse
// veya bir hata oluşursa son çare olarak 404'e yönlendirir.
header("Location: /404");
exit;
