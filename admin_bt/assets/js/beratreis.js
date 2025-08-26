const currentPath = window.location.pathname;
if (currentPath.includes("/cms-yonetim/cms/ekle")) {
  document.addEventListener("DOMContentLoaded", function () {
    let hizmetInput = document.querySelector("#hizmetsecimleri");
    let tarihSection = document.querySelector("#tarih-section");
    let hizmetTarihleri = {}; // Tarihleri saklayan obje

    tarihSection.innerHTML = `<div class="text-center text-muted py-3">Lütfen önce müşteriye verdiğiniz hizmetleri yazın.</div>`;

    if (hizmetInput && !hizmetInput.classList.contains("tagified")) {
      let tagify = new Tagify(hizmetInput);
      hizmetInput.classList.add("tagified"); // Tagify tekrar yüklenmesini engelle

      function renderTarihAlanlari() {
        tarihSection.innerHTML = ""; // Önceki içeriği temizle

        Object.keys(hizmetTarihleri).forEach((hizmetId) => {
          let hizmet = hizmetTarihleri[hizmetId].name;
          let baslangicTarihi =
            hizmetTarihleri[hizmetId].baslangic ||
            new Date().toLocaleDateString("tr-TR");
          let bitisTarihi = hizmetTarihleri[hizmetId].bitis || "";
          let isDevam = hizmetTarihleri[hizmetId].devam || false;

          let tarihHTML = `
                        <div class="col-12 col-sm-12 row" id="tarih#${hizmetId}">
                            <h2 class="small-title">${hizmet}</h2>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">${hizmet} - İşe Başlama Tarihi</label>
                                <input value="${baslangicTarihi}" id="baslangic-${hizmetId}" name="ise_baslangic_tarihi[]" required placeholder="Bir tarih girin!" readonly type="text" class="form-control date-picker-orientation">
                            </div>
                            <div class="mb-4 col-md-6">
                                <label class="form-label">${hizmet} - İşi Bitirme Tarihi</label>
                                <input type="hidden" name="is_devam_ediyor[]" value="0">
                                <input required id="bitis-${hizmetId}" name="is_bitis_tarihi[]" value="${
            isDevam ? "İş Devam Ediyor" : bitisTarihi
          }" placeholder="Bir tarih girin!" readonly type="text" class="form-control date-picker-orientation">
                                <div class="mt-2 form-check form-switch">
                                    <input id="hizmetkardas-${hizmetId}" class="form-check-input isdevamediyormu" type="checkbox" data-hizmet="${hizmetId}" ${
            isDevam ? "checked" : ""
          }>
                                    <label for="hizmetkardas-${hizmetId}" class="form-check-label">İş Devam Ediyor</label>
                                </div>
                            </div>
                        </div>
                    `;

          tarihSection.innerHTML += tarihHTML;
        });

        //  Datepicker ekle ve tarih seçildiğinde kaydet
        $(".date-picker-orientation")
          .datepicker({
            format: "dd.mm.yyyy",
            autoclose: true,
          })
          .on("changeDate", function () {
            let hizmetId = this.closest(".row").id.replace("tarih#", "");
            if (this.id.includes("baslangic")) {
              hizmetTarihleri[hizmetId].baslangic = this.value;
            } else if (this.id.includes("bitis")) {
              hizmetTarihleri[hizmetId].bitis = this.value;
            }
          });

        // ✅ "İş Devam Ediyor" seçiliyse datepicker kaldır
        Object.keys(hizmetTarihleri).forEach((hizmetId) => {
          if (hizmetTarihleri[hizmetId].devam) {
            $(`#bitis-${hizmetId}`).datepicker("destroy");
          }
        });
      }
      function generateHizmetId(hizmet) {
        return hizmet
          .normalize("NFD") // Türkçe karakterleri sadeleştir
          .replace(/[\u0300-\u036f]/g, "") // Özel işaretleri kaldır
          .replace(/\s+/g, "_") // Boşlukları alt çizgiyle değiştir
          .toLowerCase(); // Küçük harfe çevir
      }

      tagify.on("add", function (e) {
        let hizmet = e.detail.data.value;
        beratbabatoast("success", `"${hizmet}" Başarıyla eklendi.`);

        let hizmetId = generateHizmetId(hizmet);

        if (!hizmetTarihleri[hizmetId]) {
          hizmetTarihleri[hizmetId] = {
            name: hizmet,
            baslangic:
              hizmetTarihleri[hizmetId]?.baslangic ||
              new Date().toLocaleDateString("tr-TR"),
            bitis: hizmetTarihleri[hizmetId]?.bitis || "",
            devam: hizmetTarihleri[hizmetId]?.devam || false,
          };
        }

        renderTarihAlanlari();
      });

      tagify.on("remove", function (e) {
        let hizmet = e.detail.data.value;
        let hizmetId = generateHizmetId(hizmet);

        delete hizmetTarihleri[hizmetId];
        beratbabatoast("warning", `"${hizmet}" Başarıyla silindi`);
        if (Object.keys(hizmetTarihleri).length === 0) {
          tarihSection.innerHTML = `<div class="text-center text-muted py-3">Lütfen önce müşteriye verdiğiniz hizmetleri yazın.</div>`;
        } else {
          renderTarihAlanlari();
        }
      });

      document.addEventListener("change", function (e) {
        if (e.target.classList.contains("isdevamediyormu")) {
          let hizmetId = e.target.getAttribute("data-hizmet");
          let bitisInput = document.querySelector(`#bitis-${hizmetId}`);

          if (e.target.checked) {
            bitisInput.value = "İş Devam Ediyor";
            $(`#bitis-${hizmetId}`).datepicker("destroy");
          } else {
            bitisInput.value = hizmetTarihleri[hizmetId]?.bitis || "";
            $(`#bitis-${hizmetId}`).datepicker({
              format: "dd.mm.yyyy",
              autoclose: true,
            });
          }

          hizmetTarihleri[hizmetId].devam = e.target.checked;
          renderTarihAlanlari();
        }
      });
    }
  });
}

  document.addEventListener("DOMContentLoaded", function () {
    var input = document.querySelector("#phone");

    var errorText = document.createElement("p");
    errorText.style.color = "red";
    errorText.style.marginTop = "5px";
    errorText.style.fontSize = "14px";
    errorText.style.display = "none"; 
    errorText.innerText = "Geçersiz telefon numarası!";

    input.parentNode.insertBefore(errorText, input.nextSibling);

    var iti = window.intlTelInput(input, {
      initialCountry: "tr",
      separateDialCode: true,
      preferredCountries: ["tr", "us", "de"],
      nationalMode: false,
      formatOnDisplay: true,
      autoPlaceholder: "aggressive",
      utilsScript:
        "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });

    input.addEventListener("input", function () {
      let formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL);
      if (formattedNumber) {
        input.value = formattedNumber;
      }

      if (!iti.isValidNumber()) {
        errorText.style.display = "contents";
      } else {
        errorText.style.display = "none";
      }
    });

    input.addEventListener("blur", function () {
      let fullNumber = iti.getNumber();
      console.log("Seçilen Ülke: " + iti.getSelectedCountryData().name);
      console.log("Tam Telefon Numarası: " + fullNumber);

      if (iti.isValidNumber()) {
        errorText.style.display = "none";
      } else {
        errorText.style.display = "contents";
      }
    });
  });