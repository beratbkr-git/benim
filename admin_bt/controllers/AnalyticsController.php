<?php

// admin_bt/controllers/AnalyticsController.php

class AnalyticsController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    /**
     * Dashboard için gerekli tüm analitik verilerini toplar ve döndürür.
     * @return array
     */
    public function dashboard()
    {
        // Yetki kontrolü (sadece Editör ve üzeri yetkiye sahip olanlar görebilir)
        if (!hasPermission('Editör')) {
            return [];
        }

        $bugun = date('Y-m-d');
        $dun = date('Y-m-d', strtotime('-1 day'));
        $otuz_gun_once = date('Y-m-d', strtotime('-30 days'));

        // Günlük satış istatistikleri
        $dailyStats = $this->db->fetchAll(
            "SELECT
                tarih,
                toplam_siparis AS siparis_sayisi,
                toplam_gelir AS toplam_gelir,
                ortalama_sepet_tutari AS ortalama_sepet
             FROM bt_satis_analitiği
             WHERE tarih IN (:bugun, :dun)",
            [':bugun' => $bugun, ':dun' => $dun]
        );

        $todayData = ['siparis_sayisi' => 0, 'toplam_gelir' => 0, 'ortalama_sepet' => 0];
        $yesterdayData = ['siparis_sayisi' => 0, 'toplam_gelir' => 0, 'ortalama_sepet' => 0];

        foreach ($dailyStats as $stat) {
            if ($stat['tarih'] == $bugun) {
                $todayData = $stat;
            } else {
                $yesterdayData = $stat;
            }
        }

        $siparisDegisim = $yesterdayData['siparis_sayisi'] > 0 ? round((($todayData['siparis_sayisi'] - $yesterdayData['siparis_sayisi']) / $yesterdayData['siparis_sayisi']) * 100, 2) : 0;
        $gelirDegisim = $yesterdayData['toplam_gelir'] > 0 ? round((($todayData['toplam_gelir'] - $yesterdayData['toplam_gelir']) / $yesterdayData['toplam_gelir']) * 100, 2) : 0;

        // Aylık satış istatistikleri
        $monthlyStats = $this->db->fetch(
            "SELECT
                SUM(toplam_siparis) as siparis_sayisi,
                SUM(toplam_gelir) as toplam_gelir
             FROM bt_satis_analitiği
             WHERE YEAR(tarih) = YEAR(CURDATE()) AND MONTH(tarih) = MONTH(CURDATE())"
        );

        // Son 30 gün için gelir grafiği verileri
        $revenueChartData = $this->db->fetchAll(
            "SELECT
                tarih,
                toplam_gelir AS revenue,
                toplam_siparis AS orders
             FROM bt_satis_analitiği
             WHERE tarih >= CURDATE() - INTERVAL 30 DAY
             ORDER BY tarih ASC"
        );

        // En çok satan 5 ürün
        $topProducts = $this->db->fetchAll(
            "SELECT
                T1.urun_adi,
                SUM(T2.adet) AS toplam_satis,
                SUM(T2.toplam_fiyat) AS toplam_gelir
             FROM bt_urunler T1
             JOIN bt_siparis_detaylari T2 ON T1.id = T2.urun_id
             GROUP BY T1.id
             ORDER BY toplam_satis DESC
             LIMIT 5"
        );

        // Son 10 sipariş
        $recentOrders = $this->db->fetchAll(
            "SELECT
                T1.id,
                T1.siparis_kodu,
                T1.odenen_tutar,
                T1.siparis_durumu,
                T1.olusturma_tarihi,
                T2.ad_soyad AS musteri_adi,
                T2.eposta AS musteri_eposta
             FROM bt_siparisler T1
             LEFT JOIN bt_musteriler T2 ON T1.musteri_id = T2.id
             ORDER BY T1.olusturma_tarihi DESC
             LIMIT 10"
        );

        return [
            'daily_stats' => [
                'today' => $todayData,
                'yesterday' => $yesterdayData,
                'siparis_degisim' => $siparisDegisim,
                'gelir_degisim' => $gelirDegisim,
            ],
            'monthly_stats' => [
                'current' => $monthlyStats
            ],
            'revenue_chart' => [
                'labels' => array_column($revenueChartData, 'tarih'),
                'revenue' => array_column($revenueChartData, 'revenue'),
                'orders' => array_column($revenueChartData, 'orders')
            ],
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ];
    }
}
