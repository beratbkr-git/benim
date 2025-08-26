/**
 *
 * EditableRows
 *
 * Interface.Plugins.Datatables.EditableRows page content scripts. Initialized from scripts.js file.
 *
 *
 */
class EditableRows {
  constructor() {
    if (!jQuery().DataTable) {
      console.log("DataTable is null!");
      return;
    }
    this._datatable;
    this._currentState;
    this._datatableExtend;
    this._staticHeight = 62;
    this._deleteModal = new bootstrap.Modal(
      document.getElementById("deleteModal")
    );
    this._createInstance();
    this._addListeners();
    this._extend();
    this._initBootstrapModal();
  }
  _createInstance() {
    const _this = this;
    const columnHeaders = [];
    const noIndexColumns = [];
    // Kolon başlıklarını al ve .no-index olanları tespit et
    jQuery("#datatableRows thead th").each(function (index) {
      columnHeaders.push({ data: jQuery(this).text().trim() });
      if (jQuery(this).hasClass("no-index")) {
        noIndexColumns.push(index);
      }
    });
    this._datatable = jQuery("#datatableRows").DataTable({
      scrollX: true,
      scrollY: "600px",
      scrollCollapse: true,
      buttons: [
        {
          extend: "copy",
          text: "Kopyala",
          exportOptions: {
            columns: function (idx, data, node) {
              return !jQuery(node).hasClass("no-index");
            },
          },
        },
        {
          extend: "excel",
          text: "Excel",
          exportOptions: {
            columns: function (idx, data, node) {
              return !jQuery(node).hasClass("no-index");
            },
          },
        },
        {
          extend: "csv",
          text: "CSV",
          exportOptions: {
            columns: function (idx, data, node) {
              return !jQuery(node).hasClass("no-index");
            },
          },
        },
        {
          extend: "print",
          text: "Yazdır",
          exportOptions: {
            columns: function (idx, data, node) {
              return !jQuery(node).hasClass("no-index");
            },
          },
        },
      ],
      info: true,
      order: [],
      sDom:
        '<"row"' +
        '<"col-12 table-responsive"t>' +
        ">" +
        '<"row align-items-center mt-2"' +
        '<"col-md-4 col-sm-12 text-start"i>' +
        '<"col-md-4 col-sm-12 text-center"p>' +
        '<"col-md-4 col-sm-12 text-end">' +
        ">",
      pageLength: 30,
      columns: columnHeaders,
      language: {
        paginate: {
          previous: '<i class="cs-chevron-left"></i>',
          next: '<i class="cs-chevron-right"></i>',
          info: "_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor",
        },
      },
      initComplete: function () {
        _this._setInlineHeight();
      },
      drawCallback: function () {
        _this._setInlineHeight();
      },
      columnDefs: [
        {
          targets: 0,
          render: function (data) {
            return data;
          },
        },
      ],
    });
    _this._setInlineHeight();
  }
  _addListeners() {
    document
      .querySelectorAll(".delete-datatable")
      .forEach((el) =>
        el.addEventListener("click", this._showDeleteModal.bind(this))
      );
    document
      .querySelectorAll(".tag-done")
      .forEach((el) =>
        el.addEventListener("click", () => this._updateTag("Done"))
      );
    document
      .querySelectorAll(".tag-new")
      .forEach((el) =>
        el.addEventListener("click", () => this._updateTag("New"))
      );
    document
      .querySelectorAll(".tag-sale")
      .forEach((el) =>
        el.addEventListener("click", () => this._updateTag("Sale"))
      );
    // Listener for the confirmation modal's delete button
    document
      .querySelector("#deleteModal .btn-danger")
      .addEventListener("click", this._onDeleteConfirm.bind(this));
  }
  _extend() {
    this._datatableExtend = new DatatableExtend({
      datatable: this._datatable,
      anySelectCallback: this._onAnySelect.bind(this),
      noneSelectCallback: this._onNoneSelect.bind(this),
    });
  }
  _initBootstrapModal() {
    this._addEditModal = new bootstrap.Modal(
      document.getElementById("addEditModal")
    );
  }
  _setInlineHeight() {
    // boş
  }

  _showDeleteModal() {
    const selected = this._datatableExtend.getSelectedRows();
    if (selected.count() === 0) {
      alert("Lütfen silmek için en az bir öğe seçin!");
      return;
    }
    this._deleteModal.show();
  }
  _onDeleteConfirm() {
    const selectedRows = this._datatableExtend.getSelectedRows();
    if (selectedRows.count() === 0) {
      alert("Lütfen silmek için en az bir öğe seçin!");
      return;
    }
    let selectedIds = [];
    selectedRows.every(function () {
      selectedIds.push(this.node().querySelector('input[name="id"]').value);
    });
    if (selectedIds.length === 0) {
      alert("Silinecek öğe bulunamadı!");
      return;
    }
    var sil_ajax_url = $("#datatableRows").data("ajax-sil");

    fetch(sil_ajax_url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ids: selectedIds }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          selectedRows.remove().draw();
          this._datatableExtend.controlCheckAll();
          this._deleteModal.hide();
          beratbabatoast("success", "Seçilen öğeler başarıyla silindi!");
        } else {
          beratbabatoast("danger", "Silme işlemi başarısız!");
        }
      })
      .catch((error) => {
        console.error("Silme hatası:", error);
        alert("Bir hata oluştu, lütfen tekrar deneyin!");
      });
  }
  _updateTag(tag) {
    const selected = this._datatableExtend.getSelectedRows();
    const _this = this;
    selected.every(function () {
      const data = this.data();
      data.Tag = tag;
      _this._datatable.row(this).data(data).draw();
    });
    this._datatableExtend.unCheckAllRows();
    this._datatableExtend.controlCheckAll();
  }
  _onAnySelect() {
    document
      .querySelectorAll(".delete-datatable")
      .forEach((el) => el.classList.remove("disabled"));
    document
      .querySelectorAll(".tag-datatable")
      .forEach((el) => el.classList.remove("disabled"));
  }
  _onNoneSelect() {
    document
      .querySelectorAll(".delete-datatable")
      .forEach((el) => el.classList.add("disabled"));
    document
      .querySelectorAll(".tag-datatable")
      .forEach((el) => el.classList.add("disabled"));
  }
}
document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    $(window).trigger("resize");
  }, 110);
});
