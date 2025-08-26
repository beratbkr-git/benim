<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blue Ajans Haber Paneli - Giriş</title>
    <link rel="stylesheet" type="text/css" href="/admin_bt/views/login/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/admin_bt/views/login/assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="/admin_bt/views/login/assets/css/iofrm-style.css">
    <link rel="stylesheet" type="text/css" href="/admin_bt/views/login/assets/css/iofrm-theme1.css">
    <style>
        .form-content {
            padding: 50px 30px;
            /* İçerik paddingini artır */
        }

        .alert {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="form-body">
        <div class="iofrm-layout">
            <div class="img-holder">
                <div class="bg"></div>
                <div class="info-holder">
                </div>
            </div>
            <div class="form-holder">
                <div class="form-content">
                    <div class="form-items">
                        <!-- <img style="max-width: 15rem;" class="logo-size" src="/admin_bt/views/login/assets/img/blue-ajans-logo.webp" alt="Blue Ajans Logo"> -->
                        <h3>Yönetim Sistemi</h3>
                        <div class="page-links">
                            <a href="javascript::void;" class="active">Giriş Yap</a>
                        </div>
                        <?php
                        // Hata mesajını göster
                        if (isset($_SESSION["hata"])) {
                            echo '<div class="alert alert-danger">' . $_SESSION["hata"] . '</div>';
                            unset($_SESSION["hata"]); // Mesajı gösterdikten sonra temizle
                        }
                        ?>
                        <form method="POST" id="brt-baba-form" action="/<?= $yonetimurl ?>/giriskontrol">
                            <input class="form-control" type="email" name="eposta" placeholder="E-Posta Adresi" required>
                            <input class="form-control" type="password" name="parola" placeholder="Parola" required>
                            <div class="form-button">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" value="1">
                                    <label class="form-check-label" for="rememberMe">Beni Hatırla</label>
                                </div>
                                <button id="submit" type="submit" class="ibtn">Giriş Yap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/admin_bt/views/login/assets/js/jquery.min.js"></script>
    <script src="/admin_bt/views/login/assets/js/popper.min.js"></script>
    <script src="/admin_bt/views/login/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/admin_bt/views/login/assets/js/main.js"></script>
</body>

</html>