<!-- Navigation Menu -->
<?php
global $db; ?>
<div class="header__main-menu d-none d-xl-block">
    <nav>
        <ul>
            <?php
            $menucek = $db->fetchAll("SELECT * FROM bt_menuler WHERE durum = 'Aktif' ORDER BY sira ASC");
            foreach ($menucek as $menu) {
                if ($menu['ust_menu_id'] == 0) {
                    echo '<li><a href="' . $menu['url'] . '">' . $menu['menu_adi'] . '</a></li>';
                } else { ?>
                    <li class="has-dropdown"><a href="' . $menu['url'] . '">$menu['menu_adi']</a>
                        <?php
                        $alt_menuler = $db->fetchAll("SELECT * FROM bt_menuler WHERE alt_menu = :alt_menu AND durum = 'Aktif' ORDER BY sira ASC", ['alt_menu' => $menu['id']]);
                        foreach ($alt_menuler as $alt_menu) { ?>
                            <ul class="submenu">
                                <li><a href="<?= $alt_menu['url'] ?>"><?= $alt_menu['menu_adi'] ?></a></li>
                            </ul>
                <?php }
                        echo '</li>';
                    }
                } ?>

                <?php if ($this->isLoggedIn()): ?>
                    <li class="has-dropdown">
                        <a href="/hesap">Hesabım</a>
                        <ul class="submenu">
                            <li><a href="/hesap">Hesap Özeti</a></li>
                            <li><a href="/hesap/siparisler">Siparişlerim</a></li>
                            <li><a href="/hesap/profil">Profil Bilgileri</a></li>
                            <li><a href="/giris/cikis">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="/giris">Giriş Yap</a></li>
                    <li><a href="/kayit">Üye Ol</a></li>
                <?php endif; ?>
        </ul>
    </nav>
</div>