<style>
.products-shop-banner {
    background:linear-gradient(110deg, #2563eb 0%, #4f46e5 100%);
    border:0;
    color:#fff;
    padding:16px 18px;
    border-radius:8px;
    box-shadow:0 6px 16px rgba(37,99,235,.16);
}
.products-shop-banner strong,
.products-shop-banner i { color:#fff; }
.products-shop-banner .form-select {
    min-width:240px !important;
    min-height:46px;
    padding:9px 40px 9px 14px;
    border:1px solid #e5e7eb;
    border-radius:8px;
    color:#1f2937;
    font-size:14px;
    font-weight:600;
    box-shadow:0 2px 6px rgba(15,23,42,.08);
}
.products-shop-banner .shop-view-all-btn {
    min-height:46px;
    padding:9px 14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #fff;
    border-radius:8px;
    background:#fff;
    color:#3154c8;
    font-size:14px;
    font-weight:600;
    white-space:nowrap;
    box-shadow:0 2px 6px rgba(15,23,42,.12);
}
.products-shop-banner .shop-view-all-btn:hover {
    background:#eef2ff;
    border-color:#eef2ff;
    color:#263fa5;
}
#productTable_wrapper .dataTables_filter input,
#productTable_wrapper .dt-search input {
    min-height:44px;
    padding:8px 12px;
    border:1px solid #d9dee8;
    border-radius:8px;
}
#productTable_wrapper .dataTables_length label,
#productTable_wrapper .dt-length label {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin:0;
    white-space:nowrap;
    color:#647084;
    font-size:13px;
    font-weight:500;
}
#productTable_wrapper > .row:first-child {
    align-items:center;
    row-gap:10px;
    margin:0 0 14px;
}
#productTable_wrapper .dataTables_length,
#productTable_wrapper .dt-length,
#productTable_wrapper .dataTables_filter,
#productTable_wrapper .dt-search {
    margin:0;
}
#productTable_wrapper .dataTables_filter,
#productTable_wrapper .dt-search {
    display:flex;
    justify-content:flex-end;
    align-items:center;
}
#productTable_wrapper .dataTables_length select,
#productTable_wrapper .dt-length select {
    min-width:76px;
    min-height:40px;
    padding:6px 30px 6px 11px;
    border:1px solid #d9dee8;
    border-radius:8px;
    background-color:#fff;
    color:#263247;
    font-size:13px;
    font-weight:600;
    box-shadow:0 1px 3px rgba(15,23,42,.05);
}
#productTable_wrapper .dataTables_length select:focus,
#productTable_wrapper .dt-length select:focus {
    border-color:#93b4f5;
    outline:0;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}
html[data-bs-theme="dark"] #productTable_wrapper .dataTables_length label,
html[data-bs-theme="dark"] #productTable_wrapper .dt-length label { color:#aeb9c8; }
html[data-bs-theme="dark"] #productTable_wrapper .dataTables_length select,
html[data-bs-theme="dark"] #productTable_wrapper .dt-length select {
    background-color:#111827;
    border-color:#4b5563;
    color:#e5e7eb;
}
@media (max-width:767px) {
    #productTable_wrapper .dataTables_filter,
    #productTable_wrapper .dt-search { justify-content:flex-start; }
    #productTable_wrapper .dataTables_filter label,
    #productTable_wrapper .dt-search label { width:100%; }
    #productTable_wrapper .dataTables_filter input,
    #productTable_wrapper .dt-search input { width:100% !important; margin-left:0 !important; }
    .products-shop-banner { align-items:stretch !important; }
    .products-shop-banner > div,
    .products-shop-banner form,
    .products-shop-banner .form-select { width:100%; }
}
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\_index_styles.blade.php ENDPATH**/ ?>