<!-- bu alan panelde datatablenin liste kontrolu sayfasıdır -->
<!-- Print Button Start -->
<button
    class="btn btn-icon btn-icon-only btn-foreground-alternate shadow datatable-print"
    data-datatable="#datatableRows"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
    data-bs-delay="0"
    title="Print"
    type="button">
    <i data-acorn-icon="print"></i>
</button>
<!-- Print Button End -->
<!-- Export Dropdown Start -->
<div class="d-inline-block datatable-export" data-datatable="#datatableRows">
    <button class="btn p-0" data-bs-toggle="dropdown" type="button" data-bs-offset="0,3">
        <span
            class="btn btn-icon btn-icon-only btn-foreground-alternate shadow dropdown"
            data-bs-delay="0"
            data-bs-placement="top"
            data-bs-toggle="tooltip"
            title="Dışa Aktar">
            <i data-acorn-icon="download"></i>
        </span>
    </button>
    <div class="dropdown-menu shadow dropdown-menu-end">
        <button class="dropdown-item export-copy" type="button">Kopyala</button>
        <button class="dropdown-item export-excel" type="button">Excel</button>
        <button class="dropdown-item export-cvs" type="button">CSV</button>
    </div>
</div>
<!-- Export Dropdown End -->
<!-- Length Start -->
<div class="dropdown-as-select d-inline-block datatable-length" data-datatable="#datatableRows" data-childSelector="span">
    <button class="btn p-0 shadow" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-offset="0,3">
        <span
            class="btn btn-foreground-alternate dropdown-toggle"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-delay="0"
            title="Gösterilen Adet">
            30 Adet
        </span>
    </button>
    <div class="dropdown-menu shadow dropdown-menu-end">
        <a class="dropdown-item" href="#">20 Adet</a>
        <a class="dropdown-item active" href="#">30 Adet</a>
        <a class="dropdown-item" href="#">50 Adet</a>
        <a class="dropdown-item" href="#">100 Adet</a>
    </div>
</div>