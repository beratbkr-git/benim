// SweetAlert2 ve özel toast stilleri için dinamik yükleme ve tanımlamalar
document.addEventListener("DOMContentLoaded", function () {
  // SweetAlert2 CSS dosyasını dinamik olarak yükle
  const swalCssLink = document.createElement("link");
  swalCssLink.rel = "stylesheet";
  swalCssLink.href =
    "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css";
  document.head.appendChild(swalCssLink);

  // SweetAlert2 JS dosyasını dinamik olarak yükle
  const swalJsScript = document.createElement("script");
  swalJsScript.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
  document.head.appendChild(swalJsScript);

  // SweetAlert2 yüklendikten sonra beratbabatoast fonksiyonunu tanımla
  swalJsScript.onload = () => {
    // Toast stillerini dinamik olarak ekle
    const style = document.createElement("style");
    style.innerHTML = `
                .colored-toast.swal2-icon-success { background-color: #a5dc86 !important; }
                .colored-toast.swal2-icon-error { background-color: #f27474 !important; }
                .colored-toast.swal2-icon-warning { background-color: #f8bb86 !important; }
                .colored-toast.swal2-icon-info { background-color: #3fc3ee !important; }
                .colored-toast.swal2-icon-question { background-color: #87adbd !important; }
                .colored-toast {
                    padding: 8px 15px !important;
                    border-radius: 12px !important;
                    min-width: 250px !important;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2) !important;
                }
                .colored-toast .swal2-title {
                    color: white;
                    font-size: 14px !important;
                }
                .custom-close-btn {
                    position: absolute;
                    top: 6px;
                    right: 10px;
                    font-size: 14px;
                    color: white;
                    cursor: pointer;
                    transition: color 0.3s;
                }
                .custom-close-btn:hover { color: #ddd; }
                .fade-out { animation: fadeOut 0.4s forwards; }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
    document.head.appendChild(style);

    // beratbabatoast fonksiyonunu window objesine ata, böylece her yerden erişilebilir
    window.beratbabatoast = (type, message, title = "") => {
      const validTypes = ["success", "error", "warning", "info", "question"];
      if (!validTypes.includes(type)) type = "info"; // Geçersiz tip ise info varsay

      Swal.mixin({
        toast: true,
        position: "top-end",
        iconColor: "white",
        customClass: { popup: "colored-toast" },
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
          const closeButton = document.createElement("span");
          closeButton.innerHTML = "&times;";
          closeButton.classList.add("custom-close-btn");
          toast.appendChild(closeButton);
          closeButton.addEventListener("click", () => {
            toast.classList.add("fade-out");
            setTimeout(() => toast.remove(), 400);
          });
        },
      }).fire({
        icon: type,
        title: message || "Bilgilendirme", // Mesaj boşsa varsayılan başlık
      });
    };
  };
});
document.addEventListener("DOMContentLoaded", function () {
  // SweetAlert2 CSS dosyasını dinamik olarak yükle
  const swalCssLink = document.createElement("link");
  swalCssLink.rel = "stylesheet";
  swalCssLink.href =
    "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css";
  document.head.appendChild(swalCssLink);

  // SweetAlert2 JS dosyasını dinamik olarak yükle
  const swalJsScript = document.createElement("script");
  swalJsScript.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
  document.head.appendChild(swalJsScript);

  // SweetAlert2 yüklendikten sonra toast stilleri ve metodları tanımla
  swalJsScript.onload = () => {
    // Özel toast stillerini ekle
    const style = document.createElement("style");
    style.innerHTML = `
      .swal2-container {
      z-index:999999999999999999999999999999;
      }
      .colored-toast.swal2-icon-success { background-color: #a5dc86 !important; }
      .colored-toast.swal2-icon-error { background-color: #f27474 !important; }
      .colored-toast.swal2-icon-warning { background-color: #f8bb86 !important; }
      .colored-toast.swal2-icon-info { background-color: #3fc3ee !important; }
      .colored-toast.swal2-icon-question { background-color: #87adbd !important; }
      .colored-toast {
        padding: 8px 15px !important;
        border-radius: 12px !important;
        min-width: 250px !important;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2) !important;
        z-index: 9999 !important;
      }
      .colored-toast .swal2-title {
        color: white;
        font-size: 14px !important;
      }
      .custom-close-btn {
        position: absolute;
        top: 6px;
        right: 10px;
        font-size: 14px;
        color: white;
        cursor: pointer;
        transition: color 0.3s;
      }
      .custom-close-btn:hover { color: #ddd; }
      .fade-out { animation: fadeOut 0.4s forwards; }
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
    `;
    document.head.appendChild(style);

    // Global olarak kullanmak için brtToast adında bir nesne tanımla
    window.brtToast = {};

    // Geçerli toast türlerini döngüyle oluştur
    ["success", "error", "warning", "info", "question"].forEach((type) => {
      window.brtToast[type] = function (message, title = "") {
        Swal.mixin({
          toast: true,
          position: "top-end",
          iconColor: "white",
          customClass: {
            popup: "colored-toast",
          },
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true,
          didOpen: (toast) => {
            const closeButton = document.createElement("span");
            closeButton.innerHTML = "&times;";
            closeButton.classList.add("custom-close-btn");
            toast.appendChild(closeButton);
            closeButton.addEventListener("click", () => {
              toast.classList.add("fade-out");
              setTimeout(() => toast.remove(), 400);
            });
          },
        }).fire({
          icon: type,
          title: message || "Bilgilendirme",
        });
      };
    });
  };
});
