<?php

// admin_bt/controllers/InventoryController.php

class InventoryController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Dashboard için envanter verilerini toplar.
     * @return array
     */
    public function dashboard()
    {
        // Yetki kontrolü
        if (!hasPermission('Editör')) {
            return ['stock_summary' => ['dusuk_stok' => 0, 'stokta_yok' => 0], 'low_stock_products' => []];
        }

        // Düşük stoklu ürün sayısını çek
        $lowStockCount = $this->db->fetch("SELECT COUNT(id) as count FROM bt_dusuk_stok_uyarilari WHERE durum = 'Aktif'")['count'];

        // Stokta olmayan ürün sayısını çek
        $outOfStockCount = $this->db->fetch(
            "SELECT COUNT(id) as count
             FROM bt_urunler
             WHERE stok_takibi = 1 AND stok_miktari <= 0 AND varyant_var_mi = 0"
        )['count'];

        // Düşük stoklu ürünlerin listesini çek (ilk 5 tanesi)
        $lowStockProducts = $this->db->fetchAll(
            "SELECT urun_adi, stok_miktari
             FROM bt_urunler
             WHERE stok_takibi = 1 AND stok_miktari <= minimum_stok_miktari AND varyant_var_mi = 0
             LIMIT 5"
        );

        // Varyantlı ürünlerde düşük stoklu varyantları listele (ilk 5 tanesi)
        $lowStockVariants = $this->db->fetchAll(
            "SELECT T1.urun_adi, T2.stok_adedi AS stok_miktari
             FROM bt_urunler T1
             JOIN bt_urun_varyantlari T2 ON T1.id = T2.urun_id
             WHERE T1.varyant_var_mi = 1 AND T2.stok_adedi <= T1.minimum_stok_miktari
             LIMIT 5"
        );

        // İki listeyi birleştirip benzersiz hale getir
        $finalLowStockList = array_merge($lowStockProducts, $lowStockVariants);

        // Toplam düşük stok sayısını hesapla
        $totalLowStockCount = $lowStockCount + count($lowStockVariants);

        // Stokta olmayan varyantları say
        $outOfStockVariantsCount = $this->db->fetch(
            "SELECT COUNT(T2.id) as count
             FROM bt_urunler T1
             JOIN bt_urun_varyantlari T2 ON T1.id = T2.urun_id
             WHERE T1.varyant_var_mi = 1 AND T2.stok_adedi <= 0"
        )['count'];

        $totalOutOfStockCount = $outOfStockCount + $outOfStockVariantsCount;

        return [
            'stock_summary' => [
                'dusuk_stok' => $totalLowStockCount,
                'stokta_yok' => $totalOutOfStockCount
            ],
            'low_stock_products' => $finalLowStockList
        ];
    }
}
