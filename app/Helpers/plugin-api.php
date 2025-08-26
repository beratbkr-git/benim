<?php
// function/plugin-api.php - Eklenti API'sinin çekirdeği

// Aksiyonlar ve Filtreler için depolama alanı
global $berat_plugin_actions;
global $berat_plugin_filters;
$berat_plugin_actions = [];
$berat_plugin_filters = [];

/**
 * Belirli bir aksiyon noktasına fonksiyon ekler.
 * @param string $hook_name Aksiyonun adı (örn: 'admin_menu', 'before_header')
 * @param callable $callback Fonksiyon veya metodun adı
 * @param int $priority Fonksiyonun çalışma önceliği (düşük sayı = yüksek öncelik)
 */
function add_action($hook_name, $callback, $priority = 10)
{
    global $berat_plugin_actions;
    $berat_plugin_actions[$hook_name][] = ['callback' => $callback, 'priority' => $priority];
    // Prioriteye göre sırala
    usort($berat_plugin_actions[$hook_name], function ($a, $b) {
        return $a['priority'] <=> $b['priority'];
    });
}

/**
 * Belirli bir aksiyon noktasındaki fonksiyonları çalıştırır.
 * @param string $hook_name Çalıştırılacak aksiyonun adı
 * @param mixed ...$args Fonksiyonlara geçirilecek argümanlar
 */
function do_action($hook_name, ...$args)
{
    global $berat_plugin_actions;
    if (isset($berat_plugin_actions[$hook_name])) {
        foreach ($berat_plugin_actions[$hook_name] as $action) {
            if (is_callable($action['callback'])) {
                call_user_func_array($action['callback'], $args);
            }
        }
    }
}

/**
 * Belirli bir filtre noktasına fonksiyon ekler.
 * @param string $hook_name Filtrenin adı (örn: 'post_content', 'menu_items')
 * @param callable $callback Fonksiyon veya metodun adı
 * @param int $priority Fonksiyonun çalışma önceliği
 */
function add_filter($hook_name, $callback, $priority = 10)
{
    global $berat_plugin_filters;
    $berat_plugin_filters[$hook_name][] = ['callback' => $callback, 'priority' => $priority];
    // Prioriteye göre sırala
    usort($berat_plugin_filters[$hook_name], function ($a, $b) {
        return $a['priority'] <=> $b['priority'];
    });
}

/**
 * Belirli bir filtre noktasındaki fonksiyonları sırayla çalıştırır ve değeri değiştirir.
 * @param string $hook_name Çalıştırılacak filtrenin adı
 * @param mixed $value Filtrelecek başlangıç değeri
 * @param mixed ...$args Fonksiyonlara geçirilecek ek argümanlar
 * @return mixed Filtrelenmiş değer
 */
function apply_filters($hook_name, $value, ...$args)
{
    global $berat_plugin_filters;
    if (isset($berat_plugin_filters[$hook_name])) {
        foreach ($berat_plugin_filters[$hook_name] as $filter) {
            if (is_callable($filter['callback'])) {
                $value = call_user_func_array($filter['callback'], array_merge([$value], $args));
            }
        }
    }
    return $value;
}

// Eklenti meta verilerini okuyan fonksiyon
function get_plugin_data($file_path)
{
    global $db; // GÜNCELLEME: Yeni veritabanı nesnesini kullan
    $contents = @file_get_contents($file_path);
    if ($contents === false) {
        return false;
    }

    $default_headers = [
        'Plugin Name' => 'name',
        'Plugin URI' => 'uri',
        'Description' => 'description',
        'Version' => 'version',
        'Author' => 'author',
        'Author URI' => 'author_uri',
    ];

    $plugin_data = [];
    foreach ($default_headers as $field => $key) {
        if (preg_match('/^[ \t\/*#@]*' . preg_quote($field, '/') . ':\s*(.+)$/mi', $contents, $match)) {
            $plugin_data[$key] = trim($match[1]);
        }
    }

    if (empty($plugin_data['name'])) {
        return false;
    }

    $plugin_data['plugin_file'] = $file_path;
    $plugin_data['plugin_slug'] = basename(dirname($file_path));

    // GÜNCELLEME: Veritabanından eklenti durumunu PDO ile güvenli bir şekilde çek
    if (isset($db)) {
        $db_addon_info = $db->fetch(
            "SELECT is_active FROM bt_plugins WHERE plugin_slug = :slug LIMIT 1",
            ['slug' => $plugin_data['plugin_slug']]
        );
        $plugin_data['is_active'] = $db_addon_info['is_active'] ?? 0;
    } else {
        $plugin_data['is_active'] = 0;
    }

    return $plugin_data;
}

/**
 * Eklentiyi veritabanına kaydeder/günceller ve aktif hale getirir.
 */
function activate_plugin($plugin_slug)
{
    global $db; // GÜNCELLEME: Yeni veritabanı nesnesini kullan
    $plugin_dir = __DIR__ . '/../yonetim/addons/' . $plugin_slug . '/';
    $main_file = '';

    // Ana dosyayı bul (Bu mantık aynı kalabilir)
    if (file_exists($plugin_dir . $plugin_slug . '.php')) {
        $main_file = $plugin_slug . '.php';
    } else {
        $php_files = glob($plugin_dir . '*.php');
        if (!empty($php_files)) {
            $main_file = basename($php_files[0]);
        }
    }

    if (empty($main_file)) {
        return false;
    }

    $plugin_data = get_plugin_data($plugin_dir . $main_file);
    if (!$plugin_data) {
        return false;
    }

    // GÜNCELLEME: Eklenti zaten kayıtlı mı diye kontrol et
    $existing = $db->fetch("SELECT id FROM bt_plugins WHERE plugin_slug = :slug", ['slug' => $plugin_data['plugin_slug']]);

    $data_to_save = [
        'plugin_name' => $plugin_data['name'],
        'plugin_slug' => $plugin_data['plugin_slug'],
        'description' => $plugin_data['description'],
        'version' => $plugin_data['version'],
        'author' => $plugin_data['author'],
        'plugin_main_file' => $main_file,
        'is_active' => 1,
        'activation_date' => date('Y-m-d H:i:s')
    ];

    if ($existing) {
        // Zaten varsa güncelle
        $result = $db->update('bt_plugins', $data_to_save, 'id = :id', ['id' => $existing['id']]);
    } else {
        // Yoksa yeni kayıt ekle
        $result = $db->insert('bt_plugins', $data_to_save);
    }

    if ($result) {
        do_action('plugin_activated', $plugin_slug);
        return true;
    }
    return false;
}

/**
 * Eklentiyi pasif hale getirir.
 */
function deactivate_plugin($plugin_slug)
{
    global $db; // GÜNCELLEME: Yeni veritabanı nesnesini kullan
    $update_data = ['is_active' => 0];
    $result = $db->update('bt_plugins', $update_data, 'plugin_slug = :slug', ['slug' => $plugin_slug]);

    if ($result) {
        do_action('plugin_deactivated', $plugin_slug);
    }
    return $result;
}

/**
 * Aktif eklentileri yükler.
 */
function load_active_plugins()
{
    global $db; // GÜNCELLEME: Yeni veritabanı nesnesini kullan
    if (!$db) {
        return;
    }

    // GÜNCELLEME: PDO ile güvenli sorgu
    $active_plugins_db = $db->fetchAll("SELECT * FROM bt_plugins WHERE is_active = 1");

    if (!empty($active_plugins_db)) {
        foreach ($active_plugins_db as $db_plugin) {
            $plugin_path = __DIR__ . '/../yonetim/addons/' . $db_plugin['plugin_slug'] . '/' . $db_plugin['plugin_main_file'];
            if (file_exists($plugin_path)) {
                include_once $plugin_path;
            } else {
                error_log("Aktif eklenti dosyası bulunamadı ve pasif hale getirildi: " . $plugin_path);
                // Dosya yoksa eklentiyi DB'de de pasif yap
                $db->update("bt_plugins", ["is_active" => 0], "id = :id", ['id' => $db_plugin['id']]);
            }
        }
    }
}
/**
 * ZIP dosyasından eklentiyi yükler ve kurar.
 * @param array $file $_FILES dizisinden gelen dosya bilgisi.
 * @return array Sonuç dizisi (success, message).
 */
function upload_and_install_plugin($file)
{
    error_log("upload_and_install_plugin başlatıldı. Dosya: " . json_encode($file));

    // Yükleme hatası kontrolü (PHP'nin kendi hata kodları)
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error_code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = "Dosya yüklenirken bilinmeyen bir hata oluştu. Hata kodu: {$error_code}. ";
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message .= "Yüklenen dosya boyutu php.ini dosyasındaki upload_max_filesize limitini aşıyor.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "Yüklenen dosya boyutu HTML formundaki MAX_FILE_SIZE limitini aşıyor.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "Dosya kısmen yüklendi.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= "Hiç dosya yüklenmedi.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= "Geçici klasör eksik. Sunucu yapılandırmasını kontrol edin.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= "Diske yazılamadı. Sunucu izinlerini kontrol edin.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= "Bir PHP eklentisi dosya yüklemeyi durdurdu.";
                break;
        }
        error_log("upload_and_install_plugin: Yükleme hatası - " . $error_message);
        return ["success" => false, "message" => $error_message];
    }

    // Geçici dosyanın varlığını ve okunabilirliğini kontrol et
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        $error_message = "Yüklenen geçici dosya bulunamadı veya okunamıyor. Sunucu yapılandırması veya izin sorunları olabilir. Geçici yol: " . $file['tmp_name'];
        error_log("upload_and_install_plugin: Geçici dosya hatası - " . $error_message);
        return ["success" => false, "message" => $error_message];
    }

    if (!class_exists('ZipArchive')) {
        error_log("ZipArchive eklentisi yüklü değil.");
        return ["success" => false, "message" => "Sunucuda ZipArchive eklentisi yüklü değil."];
    }

    // Eklentilerin yükleneceği ana dizin yolu
    $target_dir = __DIR__ . '/../yonetim/addons/';

    // Klasörün yazılabilir olduğundan emin ol
    if (!is_writable($target_dir)) {
        error_log("yonetim/addons/ klasörü yazılabilir değil: " . $target_dir);
        return ["success" => false, "message" => "Eklenti klasörü ('yonetim/addons/') yazılabilir değil. Lütfen dosya izinlerini kontrol edin (örn. 755 veya 775)."];
    }

    $tmp_file = $file['tmp_name'];
    $file_name = $file['name'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    if ($file_ext !== 'zip') {
        error_log("Geçersiz dosya uzantısı: " . $file_ext);
        return ["success" => false, "message" => "Sadece ZIP dosyaları yüklenebilir."];
    }

    $zip = new ZipArchive;
    $zip_open_result = $zip->open($tmp_file);

    if ($zip_open_result === TRUE) {
        // ZIP içindeki ilk klasörün adını alarak eklenti slug'ını belirle
        $root_entry = $zip->getNameIndex(0);
        if ($root_entry === false) {
            $zip->close();
            error_log("ZIP dosyası boş veya ilk girişi okunamadı.");
            return ["success" => false, "message" => "ZIP dosyası boş veya geçersiz."];
        }
        $plugin_slug = basename(dirname($root_entry)); // ZIP içindeki ilk klasör adı

        if (empty($plugin_slug) || $plugin_slug === '.') { // Eğer ZIP içinde doğrudan dosyalar varsa veya kök dizin ise
            // ZIP dosyasının adını slug olarak kullanmaya çalış
            $plugin_slug = basename($file_name, '.zip');
            // Eğer ZIP dosyasının adı da geçerli bir slug değilse, hata ver
            if (empty($plugin_slug) || !preg_match('/^[a-z0-9-]+$/', $plugin_slug)) {
                $zip->close();
                error_log("ZIP dosyasının adı geçerli bir eklenti slug'ı değil: " . $file_name);
                return ["success" => false, "message" => "ZIP dosyanızın içinde bir klasör bulunmalı veya ZIP dosyasının adı geçerli bir eklenti adı olmalı (örn: 'my-plugin.zip')."];
            }
        }

        $extract_path = $target_dir . $plugin_slug . '/';

        if (is_dir($extract_path)) {
            $zip->close();
            error_log("Eklenti klasörü zaten mevcut: " . $extract_path);
            return ["success" => false, "message" => "Bu eklenti zaten yüklü görünüyor: " . htmlspecialchars($plugin_slug)];
        }

        // Klasörü oluştur ve izinlerini ayarla
        if (!mkdir($extract_path, 0755, true)) {
            $zip->close();
            error_log("Eklenti klasörü oluşturulamadı: " . $extract_path);
            return ["success" => false, "message" => "Eklenti klasörü oluşturulamadı. İzinleri kontrol edin."];
        }

        $extract_result = $zip->extractTo($extract_path);
        if ($extract_result) {
            $zip->close();
            error_log("ZIP dosyası çıkarıldı: " . $extract_path);

            // Ana PHP dosyasının yolunu bul (plugin_slug.php veya klasördeki ilk .php)
            $main_file_in_extracted_dir = $extract_path . $plugin_slug . '.php';
            if (!file_exists($main_file_in_extracted_dir)) {
                $php_files_in_dir = glob($extract_path . '*.php');
                if (!empty($php_files_in_dir)) {
                    $main_file_in_extracted_dir = $php_files_in_dir[0];
                } else {
                    rrmdir($extract_path); // Başarısız kurulumda klasörü sil
                    error_log("Eklenti ana PHP dosyası bulunamadı: " . $extract_path);
                    return ["success" => false, "message" => "ZIP dosyasında eklentinin ana PHP dosyası bulunamadı. Lütfen ZIP yapısını kontrol edin."];
                }
            }

            // Eklentiyi aktif hale getir
            $activation_result = activate_plugin($plugin_slug);

            if ($activation_result) {
                error_log(htmlspecialchars($plugin_slug) . " eklentisi başarıyla yüklendi ve aktif edildi.");
                return ["success" => true, "message" => htmlspecialchars($plugin_slug) . " eklentisi başarıyla yüklendi ve aktif edildi."];
            } else {
                rrmdir($extract_path); // Aktivasyon başarısız olursa, yüklenen dosyaları sil
                error_log(htmlspecialchars($plugin_slug) . " eklentisi yüklendi ancak aktif edilemedi. Klasör silindi.");
                return ["success" => false, "message" => htmlspecialchars($plugin_slug) . " eklentisi yüklendi ancak aktif edilemedi. Lütfen eklenti meta verilerini veya veritabanı hatalarını kontrol edin."];
            }
        } else {
            error_log("ZIP dosyası çıkarılamadı: " . $tmp_file . ". Hata kodu: " . $zip->getStatusString()); // Daha detaylı ZIP hatası
            return ["success" => false, "message" => "ZIP dosyası çıkarılamadı. Dosya bozuk olabilir veya izin sorunları olabilir. ZIP hatası: " . $zip->getStatusString()];
        }
    } else {
        error_log("ZIP dosyası açılamadı: " . $tmp_file . " Hata kodu: " . $zip_open_result);
        return ["success" => false, "message" => "ZIP dosyası açılamıyor. Dosya bozuk olabilir."];
    }
}

// Rekürsif klasör silme fonksiyonu (install/uninstall için)
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }
}


// Tüm eklentileri (klasörleri tarayarak) alır (Eklenti yönetim sayfasında listelemek için)
function get_all_installed_plugins()
{
    $plugins = [];
    // Eklentilerin bulunduğu dizin yolu düzeltildi
    $plugin_dir = __DIR__ . '/../yonetim/addons/';

    if (!is_dir($plugin_dir)) {
        return $plugins;
    }

    $directories = glob($plugin_dir . '/*', GLOB_ONLYDIR);

    foreach ($directories as $dir) {
        $plugin_slug = basename($dir);
        // Ana dosyayı tahmin et (slug.php veya klasördeki ilk php)
        $main_file_path = $dir . '/' . $plugin_slug . '.php';
        if (!file_exists($main_file_path)) {
            $php_files = glob($dir . '/*.php');
            if (!empty($php_files)) {
                $main_file_path = $php_files[0];
            } else {
                continue; // Ana PHP dosyası yoksa atla
            }
        }

        $plugin_data = get_plugin_data($main_file_path);
        if ($plugin_data) {
            $plugins[$plugin_data['plugin_slug']] = $plugin_data;
        }
    }
    return $plugins;
}



// Eklentiler yüklendikten sonra çalışacak aksiyon noktası
do_action('plugins_loaded'); // Tüm eklentiler yüklendikten sonra tetiklenir
