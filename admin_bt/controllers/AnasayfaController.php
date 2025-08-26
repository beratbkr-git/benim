<?php

class AnasayfaController
{
    private $db;

    public function __construct($db_nesnesi)
    {
        $this->db = $db_nesnesi;
    }

    public function anasayfa()
    {
        require_once(VIEW_DIR . "header.php");

        require_once(VIEW_DIR . "anasayfa.php");
        require_once(VIEW_DIR . "footer.php");
    }

    public function dashboardData()
    {
        header('Content-Type: application/json');

        require_once(YONETIM_DIR . "controllers/AnalyticsController.php");
        require_once(YONETIM_DIR . "controllers/InventoryController.php");

        $analytics = new AnalyticsController($this->db);
        $inventory = new InventoryController($this->db);

        $dashboard_data = $analytics->dashboard();
        $inventory_data = $inventory->dashboard();

        $response = [
            'success' => true,
            'data' => [
                'stats' => [
                    'today_orders' => $dashboard_data['daily_stats']['today']['siparis_sayisi'],
                    'today_revenue' => $dashboard_data['daily_stats']['today']['toplam_gelir'],
                    'orders_change' => $dashboard_data['daily_stats']['siparis_degisim'],
                    'revenue_change' => $dashboard_data['daily_stats']['gelir_degisim'],
                ],
                'revenue_chart' => $dashboard_data['revenue_chart'],
                'order_status_chart' => $dashboard_data['order_status_chart'],
                'top_products' => $dashboard_data['top_products'],
                'low_stock_products' => $inventory_data['low_stock_products'],
                'inventory_summary' => $inventory_data['stock_summary'],
                'recent_orders' => $dashboard_data['recent_orders'],
            ]
        ];

        echo json_encode($response);
        exit;
    }
}
