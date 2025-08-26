<?php
require_once(FRONTEND_CONTROLLER_DIR . 'BaseController.php');

class HakkimizdaController extends BaseController
{
    public function index()
    {
        $this->loadView('hakkimizda/hakkimizda.php', [
            'title' => 'Hakkımızda - ' . ($this->site_ayarlari['site_baslik'] ?? 'Ketchila'),
            'sepet_adet' => $this->getSepetAdet()
        ]);
    }
}
