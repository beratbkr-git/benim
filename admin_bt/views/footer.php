<!-- Layout Footer Start -->
<footer>
    <div class="footer-content">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-6">
                    <p class="mb-0 text-muted text-medium"><?= $site_ayarlari["site_adi"] ?> <?= date("Y") ?></p>
                </div>
                <!-- <div class="col-sm-6 d-none d-sm-block">
                    <ul class="breadcrumb pt-0 pe-0 mb-0 float-end">
                        <li class="breadcrumb-item mb-0 text-medium">
                            <a href="https://1.envato.market/BX5oGy" target="_blank" class="btn-link">Review</a>
                        </li>
                        <li class="breadcrumb-item mb-0 text-medium">
                            <a href="https://1.envato.market/BX5oGy" target="_blank" class="btn-link">Purchase</a>
                        </li>
                        <li class="breadcrumb-item mb-0 text-medium">
                            <a href="https://acorn-html-docs.coloredstrategies.com/" target="_blank" class="btn-link">Docs</a>
                        </li>
                    </ul>
                </div> -->
            </div>
        </div>
    </div>
</footer>
<!-- Layout Footer End -->
</div>
<!-- Theme Settings Modal Start -->
<div class="modal fade modal-right scroll-out-negative" id="settings" data-bs-backdrop="true" tabindex="-1"
    role="dialog" aria-labelledby="Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable full" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tema Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="scroll-track-visible">
                    <div class="mb-5" id="color">
                        <label class="mb-3 d-inline-block form-label">Renk</label>
                        <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                            <a href="#" class="flex-grow-1 w-50 option col" data-value="light-blue-ajans" data-parent="color">
                                <div class="card rounded-md p-3 mb-1 no-shadow color">
                                    <div class="light-blue-ajans"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">AÇIK LACİVERT</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-blue-ajans" data-parent="color">
                                <div class="card rounded-md p-3 mb-1 no-shadow color">
                                    <div class="dark-blue-ajans"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">KOYU LACİVERT</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mb-5" id="navcolor">
                        <label class="mb-3 d-inline-block form-label">Menü Rengini Ayarla</label>
                        <div class="row d-flex g-3 justify-content-between flex-wrap">
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="default" data-parent="navcolor">
                                <div class="card rounded-md p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">VARSAYILAN</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="light" data-parent="navcolor">
                                <div class="card rounded-md p-3 mb-1 no-shadow">
                                    <div class="figure figure-secondary figure-light top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">IŞIK</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="dark" data-parent="navcolor">
                                <div class="card rounded-md p-3 mb-1 no-shadow">
                                    <div class="figure figure-muted figure-dark top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">KARANLIK</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mb-5" id="layout">
                        <label class="mb-3 d-inline-block form-label">Düzen</label>
                        <div class="row d-flex g-3 justify-content-between flex-wrap">
                            <a href="#" class="flex-grow-1 w-50 option col" data-value="fluid" data-parent="layout">
                                <div class="card rounded-md p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">SIVI</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-50 option col" data-value="boxed" data-parent="layout">
                                <div class="card rounded-md p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom small"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">KUTULU</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mb-5" id="radius">
                        <label class="mb-3 d-inline-block form-label">Radius</label>
                        <div class="row d-flex g-3 justify-content-between flex-wrap">
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="rounded" data-parent="radius">
                                <div class="card rounded-md radius-rounded p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">YUVARLAK</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="standard" data-parent="radius">
                                <div class="card rounded-md radius-regular p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">STANDART</span>
                                </div>
                            </a>
                            <a href="#" class="flex-grow-1 w-33 option col" data-value="flat" data-parent="radius">
                                <div class="card rounded-md radius-flat p-3 mb-1 no-shadow">
                                    <div class="figure figure-primary top"></div>
                                    <div class="figure figure-secondary bottom"></div>
                                </div>
                                <div class="text-muted text-part">
                                    <span class="text-extra-small align-middle">DÜZ</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Theme Settings Modal End -->
<!-- Niches Modal End -->
<!-- Theme Settings & Niches Buttons Start -->
<div class="settings-buttons-container">
    <button type="button" class="btn settings-button btn-primary p-0" data-bs-toggle="modal" data-bs-target="#settings" id="settingsButton">
        <span class="d-inline-block no-delay" data-bs-delay="0" data-bs-offset="0,3" data-bs-toggle="tooltip" data-bs-placement="left" title="Ayarlar">
            <i data-acorn-icon="paint-roller" class="position-relative"></i>
        </span>
    </button>
</div>
<!-- Theme Settings & Niches Buttons End -->
<!-- Search Modal Start -->
<div class="modal fade modal-under-nav modal-search modal-close-out" id="searchPagesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close btn btn-icon btn-icon-only btn-foreground" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ps-5 pe-5 pb-0 border-0">
                <input id="searchPagesInput" class="form-control form-control-xl borderless ps-0 pe-0 mb-1 auto-complete" type="text" autocomplete="off" />
            </div>
            <div class="modal-footer border-top justify-content-start ps-5 pe-5 pb-3 pt-3 border-0">
                <span class="text-alternate d-inline-block m-0 me-3">
                    <i data-acorn-icon="arrow-bottom" data-acorn-size="15" class="text-alternate align-middle me-1"></i>
                    <span class="align-middle text-medium">Navigate</span>
                </span>
                <span class="text-alternate d-inline-block m-0 me-3">
                    <i data-acorn-icon="arrow-bottom-left" data-acorn-size="15" class="text-alternate align-middle me-1"></i>
                    <span class="align-middle text-medium">Select</span>
                </span>
            </div>
        </div>
    </div>
</div>
<!-- Search Modal End -->
<!-- Vendor Scripts Start -->
<script src="/admin_bt/assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="/admin_bt/assets/js/vendor/OverlayScrollbars.min.js"></script>
<script src="/admin_bt/assets/js/vendor/autoComplete.min.js"></script>
<script src="/admin_bt/assets/js/vendor/clamp.min.js"></script>
<script src="/admin_bt/assets/icon/acorn-icons.js"></script>
<script src="/admin_bt/assets/icon/acorn-icons-interface.js"></script>
<script src="/admin_bt/assets/js/vendor/bootstrap-submenu.js"></script>
<script src="/admin_bt/assets/js/vendor/datatables.min.js"></script>
<script src="/admin_bt/assets/js/vendor/mousetrap.min.js"></script>
<!-- datepicker -->
<script src="/admin_bt/assets/js/vendor/datepicker/bootstrap-datepicker.min.js"></script>
<script src="/admin_bt/assets/js/vendor/datepicker/locales/bootstrap-datepicker.tr.min.js"></script>
<script src="/admin_bt/assets/js/vendor/tagify.min.js"></script>
<script src="/admin_bt/assets/js/vendor/dropzone.min.js"></script>
<script src="/admin_bt/assets/js/vendor/singleimageupload.js"></script>
<script src="/admin_bt/assets/js/vendor/select2.full.min.js"></script>


<script src="/admin_bt/assets/js/base/helpers.js"></script>
<script src="/admin_bt/assets/js/base/globals.js"></script>
<script src="/admin_bt/assets/js/base/nav.js"></script>
<script src="/admin_bt/assets/js/base/search.js"></script>
<script src="/admin_bt/assets/js/base/settings.js"></script>

<!-- <script src="/admin_bt/assets/js/plugins/datatable.editablerows.js"></script> -->
<script src="/admin_bt/assets/js/plugins/datatable.serverside.js"></script>
<script src="/admin_bt/assets/js/cs/datatable.extend.js"></script>

<!-- datepicker -->
<script src="/admin_bt/assets/js/forms/controls.datepicker.js"></script>
<script src="/admin_bt/assets/js/forms/controls.tag.js"></script>
<!-- intlTelInput -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/admin_bt/assets/js/cs/dropzone.templates.js"></script>
<script src="/admin_bt/assets/js/forms/controls.dropzone.js"></script>
<script src="/admin_bt/assets/js/forms/controls.select2.js"></script>

<script src="/admin_bt/assets/js/vendor/sortable.min.js"></script>

<!-- chart -->

<script src="/admin_bt/assets/js/vendor/Chart.bundle.min.js"></script>

<script src="/admin_bt/assets/js/vendor/chartjs-plugin-rounded-bar.min.js"></script>
<script src="/admin_bt/assets/js/cs/charts.extend.js"></script>
<script src="/admin_bt/assets/js/vendor/chartjs-plugin-datalabels.js"></script>
<script src="/admin_bt/assets/js/vendor/js/pages/dashboard.analytic.js"></script>
<script src="/admin_bt/assets/js/pages/dashboard.ketchila.js"></script>
<script src="/admin_bt/assets/js/pages/dashboard.default.js"></script>

<script src="/admin_bt/assets/js/common.js"></script>
<script src="/admin_bt/assets/js/scripts.js"></script>
<script src="/admin_bt/assets/js/beratreis.js"></script>

<!-- <?php include 'includes/brt-dropzone.php'; ?> -->

<script src="/admin_bt/assets/ckeditor5/ckeditor.min.js"></script>
<script>
    class MyUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file
                .then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);

                    fetch('/<?= $yonetimurl ?>/panel-post/bilader-resim-yukle', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.uploaded) {
                                resolve({
                                    default: result.url
                                });
                            } else {
                                reject(result.error ? result.error : 'Yükleme başarısız oldu.');
                            }
                        })
                        .catch(error => {
                            reject(error.message);
                        });
                }));
        }
    }

    function MyCustomUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new MyUploadAdapter(loader);
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const textareas = document.querySelectorAll('textarea.ckeditor, #editor');
        textareas.forEach(textarea => {
            ClassicEditor
                .create(textarea, {
                    extraPlugins: [MyCustomUploadAdapterPlugin, 'SourceEditing'],
                    language: 'tr',
                    link: {
                        addTargetToExternalLinks: true,
                        decorators: {
                            automaticEmail: {
                                mode: 'automatic',
                                callback: url => /^mailto:[\w.%+-]+@[A-Za-z0-9.-]+\.[A-Z]{2,}$/i.test(url),
                                message: 'E-posta adresi olarak algılandı ve otomatik bağlantı yapıldı.'
                            },
                            automaticPhone: {
                                mode: 'automatic',
                                callback: url => /^tel:\+?[0-9\s\-().]+$/.test(url),
                                message: 'Telefon numarası olarak algılandı ve otomatik bağlantı yapıldı.'
                            }
                        }
                    },
                    autoLink: true,
                    licenseKey: '',
                    removePlugins: ['Markdown'],
                    allowedContent: true,
                    htmlSupport: {
                        allow: [{
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }]
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        });
    });
</script>
<script>
    function goBackUntilDifferent() {
        const currentUrl = window.location.href;

        if (document.referrer && document.referrer !== currentUrl) {
            window.location = document.referrer;
        } else {
            history.go(-1);
        }
    };
</script>



</body>

</html>