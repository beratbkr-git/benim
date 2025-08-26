<?php
if (!defined('YONETIM_DIR')) {
    header("Location: /404");
    exit();
}
if (!hasPermission('Yönetici')) {
    header("Location: /{$yonetimurl}");
    exit();
}

global $yonetimurl, $p3, $p4, $db;

$is_edit = ($p3 === 'duzenle' && isset($p4) && (int)$p4 > 0);
$page_title = $is_edit ? 'E-posta Şablonu Düzenle' : 'Yeni E-posta Şablonu Ekle';
$form_action = "/{$yonetimurl}/eposta-sablonlari/" . ($is_edit ? "duzenle-kontrol" : "ekle-kontrol");
$sablon_id = $is_edit ? (int)$p4 : 0;

$sablon = [
    'id' => 0,
    'sablon_adi' => '',
    'sablon_kodu' => '',
    'konu' => '',
    'html_icerik' => '',
    'metin_icerik' => '',
    'degiskenler' => '[]',
    'durum' => 'Aktif',
];

if ($is_edit) {
    $sablon_data = $db->fetch("SELECT * FROM bt_eposta_sablonlari WHERE id = :id", ['id' => $sablon_id]);
    if ($sablon_data) {
        $sablon = array_merge($sablon, $sablon_data);
    } else {
        $_SESSION["hata"] = "E-posta şablonu bulunamadı.";
        header("Location: /{$yonetimurl}/eposta-sablonlari/liste");
        exit();
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">

<style>
    .tab-container {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1rem;
        height: 500px;
        display: flex;
        flex-direction: column;
    }

    .tab-buttons {
        display: flex;
        justify-content: flex-end;
        background-color: #f7f7f7;
        border-bottom: 1px solid #ddd;
        padding: 5px;
    }

    .tab-button {
        padding: 8px 15px;
        cursor: pointer;
        border: none;
        background-color: transparent;
        font-weight: bold;
        color: #555;
        transition: all 0.2s ease-in-out;
    }

    .tab-button.active {
        background-color: #e9e9e9;
        color: #333;
        border-radius: 5px;
    }

    .tab-content-wrapper {
        flex-grow: 1;
        position: relative;
    }

    .tab-pane {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 0;
        box-sizing: border-box;
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .CodeMirror {
        height: 100%;
        border-radius: 0 0 8px 8px;
    }

    #previewFrame {
        width: 100%;
        height: 100%;
        border: none;
    }
</style>

<main>
    <div class="container">
        <div class="page-title-container">
            <div class="row g-0">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="/<?= $yonetimurl ?>/eposta-sablonlari/liste" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                            <span class="text-small align-middle">E-posta Şablonları</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-4" id="title"><?= $page_title ?></h1>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-end justify-content-end">
                    <button type="submit" form="sablonForm" class="btn btn-primary btn-icon btn-icon-start w-100 w-md-auto">
                        <i data-acorn-icon="save"></i>
                        <span>Kaydet</span>
                    </button>
                </div>
            </div>
        </div>

        <form id="sablonForm" action="<?= $form_action ?>" method="POST" class="tooltip-end-bottom" novalidate>
            <input type="hidden" name="id" value="<?= $sablon_id ?>">
            <input type="hidden" name="degiskenler" value="<?= $sablon["degiskenler"] ?>">
            <div class="row">
                <div class="col-xl-8">
                    <div class="mb-5">
                        <h2 class="small-title">Şablon Bilgileri</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="sablon_adi">Şablon Adı</label>
                                    <input type="text" class="form-control" id="sablon_adi" name="sablon_adi" value="<?= htmlspecialchars($sablon['sablon_adi']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="sablon_kodu">Şablon Kodu</label>
                                    <input type="text" class="form-control" id="sablon_kodu" name="sablon_kodu" value="<?= htmlspecialchars($sablon['sablon_kodu']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="konu">Konu</label>
                                    <input type="text" class="form-control" id="konu" name="konu" value="<?= htmlspecialchars($sablon['konu']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="html_icerik">HTML İçerik</label>
                                    <div class="tab-container">
                                        <div class="tab-buttons">
                                            <button type="button" class="tab-button active" data-tab="code">Kaynak Kodu</button>
                                            <button type="button" class="tab-button" data-tab="preview">Önizleme</button>
                                        </div>
                                        <div class="tab-content-wrapper">
                                            <div id="code-tab-pane" class="tab-pane active">
                                                <textarea name="html_icerik" id="htmlEditor"><?= htmlspecialchars_decode($sablon['html_icerik']) ?></textarea>
                                            </div>
                                            <div id="preview-tab-pane" class="tab-pane">
                                                <iframe id="previewFrame"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label" for="metin_icerik">Metin İçerik</label>
                                    <textarea name="metin_icerik" class="form-control" rows="5"><?= htmlspecialchars($sablon['metin_icerik']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="mb-5">
                        <h2 class="small-title">Yayınlama</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="durum">Durum</label>
                                    <select class="form-select select2Basic" id="durum" name="durum">
                                        <option value="Aktif" <?= ($sablon['durum'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Pasif" <?= ($sablon['durum'] == 'Pasif') ? 'selected' : '' ?>>Pasif</option>
                                    </select>
                                </div>
                                <?php if ($is_edit) : ?>
                                    <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#deleteModal">Sil</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="mb-3">Kullanılabilir Değişkenler</h5>
                                <div id="degiskenler" class="small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<?php if ($is_edit) : ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">E-posta Şablonunu Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    "<?= htmlspecialchars($sablon['sablon_adi']) ?>" adlı şablonu kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" id="deleteConfirmButton" class="btn btn-danger">Evet, Sil</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const sablonId = <?= $sablon_id ?>;
        const yonetimUrl = '<?= $yonetimurl ?>';
        const sablonForm = document.getElementById('sablonForm');

        const htmlEditorInstance = CodeMirror.fromTextArea(document.getElementById('htmlEditor'), {
            mode: "htmlmixed",
            theme: "monokai",
            lineNumbers: true,
            lineWrapping: true
        });

        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        const iframe = document.getElementById("previewFrame");

        // Tab değişimi
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                tabPanes.forEach(pane => pane.classList.remove('active'));
                document.getElementById(`${targetTab}-tab-pane`).classList.add('active');

                if (targetTab === 'code') {
                    htmlEditorInstance.refresh();
                    htmlEditorInstance.focus();
                } else if (targetTab === 'preview') {
                    // Önizlemeyi hemen güncelle
                    iframe.contentDocument.open();
                    iframe.contentDocument.write(htmlEditorInstance.getValue());
                    iframe.contentDocument.close();
                }
            });
        });

        // **Canlı önizleme**: Kod değişince anlık iframe güncelle
        // This function already provides live preview from the CodeMirror editor to the iframe.
        htmlEditorInstance.on("change", function() {
            const previewTab = document.getElementById('preview-tab-pane');
            if (previewTab.classList.contains('active')) {
                iframe.contentDocument.open();
                iframe.contentDocument.write(htmlEditorInstance.getValue());
                iframe.contentDocument.close();
            }
        });

        sablonForm.addEventListener('submit', function() {
            htmlEditorInstance.save();
        });

        const degiskenlerAlani = document.getElementById('degiskenler');
        const degiskenler = JSON.parse('<?= $sablon['degiskenler'] ?>');
        if (degiskenler && degiskenler.length > 0) {
            degiskenler.forEach(degisken => {
                degiskenlerAlani.innerHTML += `<code>{${degisken}}</code> `;
            });
        }

        <?php if ($is_edit) : ?>
            document.getElementById('deleteConfirmButton')?.addEventListener('click', function() {
                this.disabled = true;
                fetch(`/${yonetimUrl}/eposta-sablonlari/sil`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: [sablonId]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            beratbabatoast("success", "Şablon başarıyla silindi. Yönlendiriliyorsunuz...");
                            setTimeout(() => {
                                window.location.href = `/${yonetimUrl}/eposta-sablonlari/liste`;
                            }, 1500);
                        } else {
                            beratbabatoast("danger", "Silme işlemi başarısız: " + data.message);
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        beratbabatoast("danger", "Silme işlemi sırasında bir sunucu hatası oluştu.");
                        this.disabled = false;
                    });
            });
        <?php endif; ?>

    });
</script>