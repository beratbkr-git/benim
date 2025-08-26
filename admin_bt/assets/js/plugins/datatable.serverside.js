/**
 * Tamamen dinamik sunucu tarafı Datatable sınıfı.
 * Verilen yapılandırma objesine göre tabloyu oluşturur ve yönetir.
 * CRUD operasyonları, arama, filtreleme, butonlar ve modal işlemleri
 * gibi tüm özellikler tek bir sınıf içinde birleştirilmiştir.
 */
class ServerSideDataTable {
  /**
   * @param {Object} config - Datatable için tüm yapılandırma ayarlarını içeren nesne.
   * @param {string} config.tableSelector - HTML'deki Datatable'ın seçicisi (örn. '#urunlerTable').
   * @param {Object} config.api - CRUD işlemleri için API uç noktalarını içeren nesne.
   * @param {string} config.api.read - Veri okuma (listeleme) için API adresi.
   * @param {string} [config.api.add] - Veri ekleme için API adresi.
   * @param {string} [config.api.update] - Veri güncelleme için API adresi.
   * @param {string} [config.api.delete] - Veri silme için API adresi.
   * @param {Array<Object>} config.columns - Datatable kolon ayarlarını içeren dizi.
   * @param {string} config.columns[].data - Gelen JSON verisindeki ilgili alanın adı.
   * @param {string} config.columns[].title - Kolon başlığı.
   * @param {string|Function} [config.columns[].render] - Kolonun nasıl render edileceği. String ise HTML şablonu, fonksiyon ise dinamik render metodu.
   * @param {Object} [config.customOptions] - Datatable için ek, özel ayarlar.
   */
  constructor(config) {
    if (!jQuery().DataTable) {
      console.error("Hata: jQuery veya Datatable kütüphanesi bulunamadı!");
      return;
    }

    this._config = config;
    this._tableSelector = config.tableSelector;

    this._rowToEdit = null;
    this._datatable = null;
    this._currentState = null;
    this._addEditModal = null;
    this._deleteModal = null;

    this._createInstance();
    this._initBootstrapModal();
    this._addListeners();
    this._updateButtonStates(); // Başlangıçta buton durumlarını güncelle
  }

  _createInstance() {
    const _this = this;

    if ($.fn.dataTable.isDataTable(this._tableSelector)) {
      $(this._tableSelector).DataTable().destroy();
    }

    this._datatable = jQuery(this._tableSelector).DataTable({
      // Sunucu tarafı işlemeyi etkinleştirir
      processing: true,
      serverSide: true,
      scrollX: true,
      pageLength: 10,

      // Butonları buraya ekliyoruz
      buttons: ["copy", "excel", "csv", "print"],

      // Dışarıdan gelen customOptions'ı birleştirir
      ...this._config.customOptions,

      // AJAX konfigürasyonu
      ajax: {
        url: this._config.api.read,
        type: "POST",
        data: function (d) {
          return {
            draw: d.draw,
            start: d.start,
            length: d.length,
            search: d.search.value,
            order: d.order,
          };
        },
        error: function (xhr, error, code) {
          console.error("Datatable Ajax Hatası:", xhr, error, code);
          const errorMessage =
            "Veriler yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
          const errorContainer = jQuery(_this._tableSelector)
            .closest(".dataTables_wrapper")
            .find(".dataTables_processing");
          if (errorContainer.length > 0) {
            errorContainer.html(errorMessage).css("color", "red");
          } else {
            // Eğer processing elemanı yoksa, hatayı başka bir yere yazabiliriz.
            console.error(
              "Datatable hata mesajı gösterilemedi: .dataTables_processing bulunamadı."
            );
          }
        },
      },

      // Kolon ve render ayarları tamamen dışarıdan gelir
      columns: this._config.columns,
      columnDefs: this._config.columns.map((col, index) => {
        const def = { targets: index };
        if (col.render) {
          if (typeof col.render === "function") {
            def.render = col.render;
          } else if (typeof col.render === "string") {
            def.render = (data, type, row) => {
              // {data} yerine gelen veriyi, {id} yerine satır ID'sini koyar
              return col.render
                .replace(/{data}/g, data)
                .replace(/{id}/g, row.id || ""); // row.id olmayabilir ihtimaline karşı boş string
            };
          }
        }
        // Checkbox rendering artık doğrudan urunlerConfig.columns içinde yapılacak
        // Bu yüzden burada özel bir render tanımına gerek yok.
        return def;
      }),

      sDom:
        '<"row"' +
        '<"col-12 table-responsive"t>' +
        ">" +
        '<"row align-items-center mt-2"' +
        '<"col-md-4 col-sm-12 text-start"i>' +
        '<"col-md-4 col-sm-12 text-center"p>' +
        '<"col-md-4 col-sm-12 text-end">' +
        ">",
      scrollY: "600px",

      language: {
        paginate: {
          previous: '<i class="cs-chevron-left"></i>',
          next: '<i class="cs-chevron-right"></i>',
        },
        info: "_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor",
        emptyTable: "Tabloda hiç veri yok",
        zeroRecords: "Eşleşen kayıt bulunamadı",
        processing: "Yükleniyor...",
        search: "Ara:",
      },
      initComplete: function () {
        // DataTables yüklendiğinde buton durumlarını güncelle
        _this._updateButtonStates();
      },
      drawCallback: function () {
        // Her çizimde buton durumlarını ve "Hepsini Seç" checkbox'ını güncelle
        _this._updateButtonStates();
        _this._controlCheckAll();

        // Her satırdaki gizli checkbox'ın 'selected' sınıfıyla senkronizasyonunu sağla
        _this._datatable
          .rows()
          .nodes()
          .each(function (rowNode) {
            const checkbox = $(rowNode).find(
              'input[type="checkbox"].row-selector-checkbox'
            );
            if (checkbox.length) {
              checkbox.prop("checked", $(rowNode).hasClass("selected"));
            }
          });
      },
    });
  }

  _initBootstrapModal() {
    const addEditModalElement = document.getElementById("addEditModal");
    const deleteModalElement = document.getElementById("deleteModal");

    if (addEditModalElement) {
      this._addEditModal = new bootstrap.Modal(addEditModalElement);
    } else {
      console.warn(
        "Uyarı: 'addEditModal' ID'sine sahip HTML elementi bulunamadı. Ekleme/Düzenleme modalı çalışmayabilir."
      );
    }

    if (deleteModalElement) {
      this._deleteModal = new bootstrap.Modal(deleteModalElement);
    } else {
      console.warn(
        "Uyarı: 'deleteModal' ID'sine sahip HTML elementi bulunamadı. Silme modalı çalışmayabilir."
      );
    }
  }

  _addListeners() {
    // Genel buton dinleyicileri
    document
      .getElementById("addEditConfirmButton")
      ?.addEventListener("click", this._addEditFromModalClick.bind(this));
    document
      .querySelectorAll(".add-datatable")
      .forEach((el) =>
        el.addEventListener("click", this._onAddRowClick.bind(this))
      );
    document
      .querySelectorAll(".delete-datatable")
      .forEach((el) =>
        el.addEventListener("click", this._showDeleteModal.bind(this))
      );
    document
      .querySelectorAll(".edit-datatable")
      .forEach((el) =>
        el.addEventListener("click", this._onEditButtonClick.bind(this))
      );
    document
      .getElementById("addEditModal")
      ?.addEventListener("hidden.bs.modal", this._clearModalForm.bind(this));

    document
      .querySelector("#deleteModal .btn-danger")
      ?.addEventListener("click", this._onDeleteConfirm.bind(this));

    // Dinamik durum güncelleme butonları için dinleyici
    document
      .querySelectorAll(".status-update")
      .forEach((el) =>
        el.addEventListener("click", this._onStatusUpdate.bind(this))
      );

    // DataTables olayları için dinleyiciler
    this._datatable.on("draw", this._updateButtonStates.bind(this));

    // Hepsini Seç (Check All) checkbox'ı için dinleyici
    const datatableCheckAll = document.getElementById("datatableCheckAll");
    if (datatableCheckAll) {
      datatableCheckAll.addEventListener(
        "click",
        this._onCheckAllClick.bind(this)
      );
    }

    // Her bir satırdaki gizli checkbox için dinleyici (event delegation ile)
    jQuery(this._tableSelector).on(
      "click",
      'input[type="checkbox"].row-selector-checkbox',
      this._onRowCheckboxClick.bind(this)
    );

    // Satır tıklama dinleyicisi: DataTables'ın select eklentisi kullanılmadığı için manuel yönetim
    jQuery(this._tableSelector).on(
      "click",
      "tbody tr",
      this._onRowClick.bind(this)
    );

    // Arama kutusu için özel dinleyici
    let searchInput = document.querySelector(".datatable-search");
    if (searchInput) {
      let typingTimer;
      const doneTypingInterval = 500; // 500ms gecikme
      searchInput.addEventListener("keyup", (e) => {
        clearTimeout(typingTimer);
        const searchValue = searchInput.value;
        if (searchValue) {
          typingTimer = setTimeout(() => {
            this._datatable.search(searchValue).draw();
          }, doneTypingInterval);
        } else {
          // Arama kutusu boşaldığında aramayı temizle
          this._datatable.search("").draw();
        }
      });

      // Arama kutusunu temizleme ikonu için dinleyici
      let searchDeleteIcon = document.querySelector(".search-delete-icon");
      if (searchDeleteIcon) {
        searchDeleteIcon.addEventListener("click", () => {
          searchInput.value = "";
          this._datatable.search("").draw();
        });
      }
    }

    // Export listeners
    document
      .querySelectorAll(".datatable-export .dropdown-item")
      .forEach((el) => {
        el.addEventListener("click", this._onExportClick.bind(this));
      });

    // Print listeners
    document.querySelectorAll(".datatable-print").forEach((el) => {
      el.addEventListener("click", this._onPrintClick.bind(this));
    });

    // Length listeners (sayfa uzunluğu)
    document
      .querySelectorAll(".datatable-length .dropdown-item")
      .forEach((el) => {
        el.addEventListener("click", this._onLengthClick.bind(this));
      });
  }

  /**
   * "Hepsini Seç" checkbox'ına tıklandığında çalışır.
   * Tüm görünür satırları seçer veya seçimlerini kaldırır.
   * @param {Event} event - Click olayı.
   */
  _onCheckAllClick(event) {
    const isChecked = event.target.checked;

    this._datatable
      .rows({ page: "current" })
      .nodes()
      .each(function (rowNode) {
        const checkbox = $(rowNode).find(
          'input[type="checkbox"].row-selector-checkbox'
        );
        if (checkbox.length) {
          checkbox.prop("checked", isChecked);
        }
        if (isChecked) {
          $(rowNode).addClass("selected");
        } else {
          $(rowNode).removeClass("selected");
        }
      });
    this._updateButtonStates(); // Buton durumlarını güncelle
  }

  /**
   * Bir satırdaki gizli checkbox'a tıklandığında çalışır.
   * İlgili satırı seçer veya seçimini kaldırır.
   * @param {Event} event - Click olayı.
   */
  _onRowCheckboxClick(event) {
    const checkbox = event.target;
    const rowNode = $(checkbox).closest("tr");

    // Checkbox'ın yeni durumuna göre satırın 'selected' sınıfını toggle et
    if (checkbox.checked) {
      $(rowNode).addClass("selected");
    } else {
      $(rowNode).removeClass("selected");
    }

    this._updateButtonStates(); // Buton durumlarını ve genel checkbox'ı güncelle

    // Checkbox'a tıklanmasıyla aynı anda satırın kendisinin tıklanmasını (ve _onRowClick'i) engelle
    event.stopPropagation();
  }

  /**
   * "Hepsini Seç" checkbox'ının durumunu kontrol eder ve günceller.
   * Tüm görünür satırlar seçiliyse "Hepsini Seç"i işaretler, hiçbirisi seçili değilse işaretini kaldırır,
   * bazıları seçiliyse "belirsiz" (indeterminate) duruma getirir.
   */
  _controlCheckAll() {
    const datatableCheckAll = document.getElementById("datatableCheckAll");
    if (!datatableCheckAll) return;

    // Sadece mevcut sayfadaki görünür satırları ve seçili olanları say
    const allVisibleRows = this._datatable.rows({ page: "current" }).nodes();
    const selectedVisibleRows = this._datatable
      .rows(".selected", { page: "current" })
      .nodes();

    const selectedCount = selectedVisibleRows.length;
    const totalCount = allVisibleRows.length;

    if (totalCount > 0 && selectedCount === totalCount) {
      // Tüm satırlar seçili
      datatableCheckAll.checked = true;
      datatableCheckAll.indeterminate = false;
    } else if (selectedCount > 0) {
      // Bazı satırlar seçili (belirsiz durum)
      datatableCheckAll.checked = false;
      datatableCheckAll.indeterminate = true;
    } else {
      // Hiçbir satır seçili değil
      datatableCheckAll.checked = false;
      datatableCheckAll.indeterminate = false;
    }
  }

  /**
   * Seçilen satır sayısına göre CRUD ve durum butonlarının etkin/devre dışı durumunu günceller.
   */
  _updateButtonStates() {
    // DataTables'ın kendi select eklentisi kullanılmadığı için
    // seçili satır sayısını '.selected' sınıfına göre belirleriz.
    const selectedRowsCount = this._datatable.rows(".selected").count();
    const deleteButtons = document.querySelectorAll(".delete-datatable");
    const tagButtons = document.querySelectorAll(".tag-datatable"); // Durum değiştirme butonu
    const editButtons = document.querySelectorAll(".edit-datatable");

    if (selectedRowsCount > 0) {
      deleteButtons.forEach((el) => el.classList.remove("disabled"));
      tagButtons.forEach((el) => el.classList.remove("disabled"));
      // Sadece bir satır seçiliyse düzenleme butonunu etkinleştir
      if (selectedRowsCount === 1) {
        editButtons.forEach((el) => el.classList.remove("disabled"));
      } else {
        editButtons.forEach((el) => el.classList.add("disabled"));
      }
    } else {
      // Hiçbir satır seçili değilse tüm ilgili butonları devre dışı bırak
      deleteButtons.forEach((el) => el.classList.add("disabled"));
      tagButtons.forEach((el) => el.classList.add("disabled"));
      editButtons.forEach((el) => el.classList.add("disabled"));
    }
    this._controlCheckAll(); // Her buton durumu güncellemesinde "Hepsini Seç" checkbox'ı da kontrol et
  }

  /**
   * Bir satıra tıklandığında (checkbox'a değilse) çalışır.
   * Satırın seçili durumunu değiştirir.
   * @param {Event} event - Click olayı.
   */
  _onRowClick(event) {
    const currentTarget = event.currentTarget; // Tıklanan <tr> elementi
    // Checkbox'ı satır içinde genel bir seçici ile bul
    const checkbox = $(currentTarget).find(
      'input[type="checkbox"].row-selector-checkbox'
    );

    // Eğer tıklanan öğe bir bağlantı, buton veya dropdown elementi ise
    // ve satır seçili değilse, satırı seç ve olayın yayılmasına izin ver.
    if ($(event.target).is("a, button, .dropdown-toggle, .dropdown-item")) {
      // Eğer tıklanan element zaten etkisiz sınıfına sahipse (bir link)
      // ve bu bir dropdown butonu değilse, sadece olayın normal yayılımına izin ver.
      if (
        $(event.target).hasClass("etkisiz") &&
        !$(event.target).hasClass("dropdown-toggle")
      ) {
        return true;
      }

      // Eğer bir dropdown butonuna tıklanırsa, checkbox'ı seçili hale getir
      // ve satırı seçili yap, sonra olayın yayılmasına izin ver.
      if ($(event.target).closest(".table-dropdown").length) {
        if (checkbox.length && !checkbox.prop("checked")) {
          checkbox.prop("checked", true);
          $(currentTarget).addClass("selected");
          this._updateButtonStates();
          event.stopPropagation();
        }
        return true; // Dropdown'ın açılmasına izin ver
      }

      // Diğer interaktif elementler için satır seçiliyse dokunma
      // Değilse, seç ve olay yayılımına izin ver.
      if (checkbox.length && !$(currentTarget).hasClass("selected")) {
        $(currentTarget).addClass("selected");
        checkbox.prop("checked", true);
        this._updateButtonStates();
        return true;
      }
      return;
    }

    // Eğer tıklanan öğe gizli checkbox ise, _onRowCheckboxClick zaten bu olayı işler.
    if ($(event.target).is('input[type="checkbox"].row-selector-checkbox')) {
      return;
    }

    event.preventDefault(); // Varsayılan DataTables satır tıklama davranışını engelle

    // Satırın 'selected' sınıfını toggle et
    $(currentTarget).toggleClass("selected");

    // Gizli checkbox'ın durumunu satırın 'selected' sınıfıyla senkronize et
    if (checkbox.length) {
      checkbox.prop("checked", $(currentTarget).hasClass("selected"));
    }

    this._updateButtonStates(); // Buton durumlarını güncelle
  }

  _addEditFromModalClick() {
    if (this._currentState === "add") {
      this._addNewRowFromModal();
    } else {
      this._editRowFromModal();
    }
    this._addEditModal.hide();
  }

  _onEditButtonClick(event) {
    if (event.currentTarget.classList.contains("disabled")) {
      return;
    }
    const selectedRows = this._datatable.rows(".selected");
    if (selectedRows.count() === 1) {
      this._onEditRowClick(selectedRows.row(0));
    } else {
      console.warn(
        "Düzenlenecek bir satır seçilmedi veya birden fazla satır seçili."
      );
      this._displayMessage(
        "Lütfen düzenlemek için tek bir satır seçin.",
        "warning"
      );
    }
  }

  _onEditRowClick(rowToEdit) {
    this._rowToEdit = rowToEdit;
    this._showModal("edit", "Düzenle", "Kaydet");
    this._setForm();
  }

  _editRowFromModal() {
    const data = this._rowToEdit.data();
    const formData = Object.assign(data, this._getFormData());
    this._addSpinner();
    fetch(this._config.api.update, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((err) => {
            throw new Error(err.message || "Network response was not ok");
          });
        }
        return response.json();
      })
      .then(() => {
        this._removeSpinner();
        this._datatable.draw();
        this._displayMessage("Kayıt başarıyla güncellendi.", "success");
      })
      .catch((error) => {
        console.error("Error:", error);
        this._removeSpinner();
        this._displayMessage(
          "Güncelleme sırasında bir hata oluştu: " + error.message,
          "danger"
        );
      });
  }

  _addNewRowFromModal() {
    const data = this._getFormData();
    this._addSpinner();
    fetch(this._config.api.add, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((err) => {
            throw new Error(err.message || "Network response was not ok");
          });
        }
        return response.json();
      })
      .then(() => {
        this._removeSpinner();
        this._datatable.draw();
        this._displayMessage("Yeni kayıt başarıyla eklendi.", "success");
      })
      .catch((error) => {
        console.error("Error:", error);
        this._removeSpinner();
        this._displayMessage(
          "Yeni kayıt eklenirken bir hata oluştu: " + error.message,
          "danger"
        );
      });
  }

  _showDeleteModal() {
    const selected = this._datatable.rows(".selected");
    if (selected.count() === 0) {
      console.warn("Lütfen silmek için en az bir öğe seçin!");
      this._displayMessage(
        "Lütfen silmek için en az bir öğe seçin!",
        "warning"
      );
      return;
    }
    this._deleteModal.show();
  }

  _onDeleteConfirm() {
    const selectedRows = this._datatable.rows(".selected");
    if (selectedRows.count() === 0) {
      console.warn("Lütfen silmek için en az bir öğe seçin!");
      this._displayMessage(
        "Lütfen silmek için en az bir öğe seçin!",
        "warning"
      );
      return;
    }

    const data = selectedRows.data();
    const idsToDelete = { ids: [] };
    for (let i = 0; i < data.length; i++) {
      if (data[i] && data[i].id !== undefined) {
        idsToDelete.ids.push(data[i].id);
      } else {
        console.warn("Silinecek satırda 'id' alanı bulunamadı:", data[i]);
      }
    }

    if (idsToDelete.ids.length === 0) {
      console.warn("Silinecek geçerli öğe bulunamadı!");
      this._displayMessage("Silinecek geçerli öğe bulunamadı!", "warning");
      this._deleteModal.hide();
      return;
    }

    this._addSpinner();
    fetch(this._config.api.delete, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(idsToDelete),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((err) => {
            throw new Error(err.message || "Network response was not ok");
          });
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          this._datatable.draw();
          this._deleteModal.hide();
          this._displayMessage("Seçilen öğeler başarıyla silindi!", "success");
        } else {
          console.error("Silme işlemi başarısız!");
          this._displayMessage("Silme işlemi başarısız!", "danger");
        }
        this._removeSpinner();
      })
      .catch((error) => {
        console.error("Error:", error);
        this._removeSpinner();
        this._displayMessage(
          "Silme sırasında bir hata oluştu: " + error.message,
          "danger"
        );
      })
      .finally(() => {
        // Modalın arka planının kalmamasını sağlamak için ek önlem
        setTimeout(() => {
          $(".modal-backdrop").remove();
          $("body").removeClass("modal-open");
        }, 300); // Küçük bir gecikme ekleyerek Bootstrap'ın kendi işini bitirmesini bekle
        this._datatable.rows().nodes().removeClass("selected");
        this._datatable
          .rows()
          .nodes()
          .find('input[type="checkbox"].row-selector-checkbox')
          .prop("checked", false);
        this._updateButtonStates();
      });
  }

  _onAddRowClick() {
    this._showModal("add", "Yeni Ekle", "Ekle");
  }

  _showModal(objective, title, button) {
    if (this._addEditModal) {
      this._addEditModal.show();
      this._currentState = objective;
      const modalTitleElement = document.getElementById("modalTitle");
      const confirmButtonElement = document.getElementById(
        "addEditConfirmButton"
      );

      if (modalTitleElement) modalTitleElement.innerHTML = title;
      if (confirmButtonElement) confirmButtonElement.innerHTML = button;
    } else {
      console.error("Add/Edit modal objesi oluşturulamadı.");
    }
  }

  _setForm() {
    const data = this._rowToEdit ? this._rowToEdit.data() : null;
    if (!data) {
      console.warn("Düzenlenecek satır verisi bulunamadı.");
      return;
    }

    this._config.columns.forEach((col) => {
      // Checkbox sütunu özel durumu (formda genelde yer almaz)
      // Gizli checkboxlar ve null/boş başlığa sahip kolonlar formda işlenmez.
      if (
        col.data === null ||
        col.title === "" ||
        $(`#addEditModal [name="${col.data}"]`).hasClass(
          "row-selector-checkbox"
        )
      ) {
        return;
      }

      const input = document.querySelector(
        `#addEditModal [name="${col.data}"]`
      );
      if (input) {
        if (input.type === "radio") {
          const radio = document.querySelector(
            `#addEditModal [name="${col.data}"][value="${data[col.data]}"]`
          );
          if (radio) radio.checked = true;
        } else {
          input.value =
            data[col.data] !== undefined && data[col.data] !== null
              ? data[col.data]
              : "";
        }
      }
    });
  }

  _getFormData() {
    const data = {};
    this._config.columns.forEach((col) => {
      // Gizli checkboxlar ve null/boş başlığa sahip kolonlar formdan değer alırken atlanır.
      if (
        col.data === null ||
        col.title === "" ||
        $(`#addEditModal [name="${col.data}"]`).hasClass(
          "row-selector-checkbox"
        )
      ) {
        return;
      }
      const input = document.querySelector(
        `#addEditModal [name="${col.data}"]`
      );
      if (input) {
        if (input.type === "radio") {
          const checkedRadio = document.querySelector(
            `#addEditModal [name="${col.data}"]:checked`
          );
          data[col.data] = checkedRadio ? checkedRadio.value : "";
        } else if (input.type === "checkbox") {
          data[col.data] = input.checked;
        } else {
          data[col.data] = input.value;
        }
      }
    });
    return data;
  }

  _clearModalForm() {
    document.querySelector("#addEditModal form")?.reset();
  }

  _addSpinner() {
    document.body.classList.add("spinner");
  }

  _removeSpinner() {
    document.body.classList.remove("spinner");
  }

  _onStatusUpdate(event) {
    const newStatus = event.currentTarget.dataset.status;
    this._updateTag(newStatus);
  }

  _updateTag(tag) {
    const _this = this;

    const selectedNodes = this._datatable.rows(".selected").nodes();
    if (selectedNodes.length === 0) {
      this._displayMessage(
        "Durumunu değiştirmek için lütfen en az bir öğe seçin!",
        "warning"
      );
      return;
    }

    const idsToUpdate = { ids: [], status: tag };
    selectedNodes.each(function (node) {
      const row = _this._datatable.row(node);
      const data = row.data();

      if (data && data.durum !== undefined) {
        data.durum = tag;
        row.data(data);
      }

      if (data && data.id !== undefined) {
        idsToUpdate.ids.push(data.id);
      }
    });

    if (idsToUpdate.ids.length === 0) {
      this._displayMessage("Güncellenecek geçerli öğe bulunamadı!", "warning");
      return;
    }

    this._addSpinner();
    const updateApiUrl =
      this._config.api.updateStatus || this._config.api.update;

    fetch(updateApiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(idsToUpdate),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Durum güncelleme hatası!");
        return response.json();
      })
      .then((apiData) => {
        if (apiData.success) {
          this._datatable.draw(false);
          this._displayMessage(
            "Seçili öğelerin durumu başarıyla güncellendi.",
            "success"
          );
        } else {
          this._displayMessage("Durum güncelleme başarısız oldu!", "danger");
        }
      })
      .catch((error) => {
        console.error("Durum güncelleme hatası:", error);
        this._displayMessage(
          "Durum güncelleme sırasında bir hata oluştu: " + error.message,
          "danger"
        );
      })
      .finally(() => {
        this._removeSpinner();
        this._datatable.rows().nodes().removeClass("selected");
        this._datatable
          .rows()
          .nodes()
          .find('input[type="checkbox"].row-selector-checkbox')
          .prop("checked", false);
        this._updateButtonStates();
      });
  }

  /**
   * Kullanıcıya mesaj göstermek için basit bir yardımcı metod.
   * Bootstrap toast veya custom bir modal kullanılabilir.
   * Şimdilik console.log/error yerine geçici bir çözüm.
   * @param {string} message - Gösterilecek mesaj.
   * @param {string} type - Mesaj tipi (success, warning, danger, info).
   */
  _displayMessage(message, type) {
    if (typeof beratbabatoast === "function") {
      beratbabatoast(type, message);
    } else {
      console.log(`[${type.toUpperCase()}] ${message}`);
    }
  }
}
