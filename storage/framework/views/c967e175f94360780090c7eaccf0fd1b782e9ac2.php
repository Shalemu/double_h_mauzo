<style>
/* Register / Edit Unit modal — compact */
.unit-dialog { max-width:520px; }
.unit-modal { border:0; border-radius:.9rem; overflow:hidden; box-shadow:0 18px 48px rgba(15,23,42,.22); }
.unit-modal-header { gap:.65rem; padding:.9rem 1.25rem; border-bottom:1px solid #f0f1f3; }
.unit-modal-header .modal-title { font-size:1.05rem; font-weight:600; flex-grow:1; }
.unit-modal-icon {
    width:34px; height:34px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:rgba(131,22,25,.1); color:#831619;
}
.unit-modal .modal-body { padding:1.1rem 1.25rem; }
.unit-modal .form-label { font-size:.82rem; font-weight:500; color:#4b5563; margin-bottom:.3rem; }
.unit-modal .modal-body > .mb-3 .form-control, .unit-modal .row .form-control, .unit-modal .unit-segment { height:38px; }
.unit-modal .form-control, .unit-modal .form-select { border-radius:.5rem; }
.unit-modal .form-control::placeholder { color:#b8bec7; }
.unit-modal .form-control:focus, .unit-modal .form-select:focus { border-color:#c97a7d; box-shadow:0 0 0 .2rem rgba(131,22,25,.12); }
.unit-segment { display:flex; border:1px solid #dee2e6; border-radius:.5rem; padding:2px; background:#f8f9fa; }
.unit-segment label {
    flex:1; display:flex; align-items:center; justify-content:center; border-radius:.4rem;
    font-size:.88rem; color:#6b7280; cursor:pointer; transition:.15s; margin:0;
}
.unit-segment .btn-check:checked + label { background:#fff; color:#831619; font-weight:600; box-shadow:0 1px 3px rgba(15,23,42,.12); }
.unit-segment .btn-check:focus-visible + label { outline:2px solid #831619; outline-offset:1px; }
.unit-multiple { border-top:1px dashed #e5e7eb; padding-top:.9rem; }
.unit-switch .form-check-label { font-size:.9rem; font-weight:500; color:#374151; }
.unit-switch .form-check-input { cursor:pointer; }
.unit-switch .form-check-input:checked { background-color:#831619; border-color:#831619; }
.unit-switch .form-check-input:focus { box-shadow:0 0 0 .2rem rgba(131,22,25,.15); }
.unit-equation {
    display:flex; align-items:center; gap:.5rem; margin-top:.75rem;
    padding:.55rem .65rem; border-radius:.6rem; background:#fbf6f6; border:1px solid #f0dcdd;
}
.unit-eq-one {
    width:30px; height:30px; border-radius:8px; background:#831619; color:#fff; flex-shrink:0;
    display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem;
}
.unit-eq-name { font-weight:600; color:#1f2937; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.unit-eq-sign { font-weight:700; color:#831619; }
.unit-eq-multiplier { width:70px; flex-shrink:0; text-align:center; font-weight:600; }
.unit-eq-base { flex:1; min-width:0; }
.unit-preview { margin-top:.5rem; font-size:.8rem; color:#15803d; }
.unit-note { margin-top:.5rem; font-size:.8rem; color:#92400e; }
.unit-modal .modal-footer { padding:.7rem 1.25rem; border-top:1px solid #f0f1f3; }

/* Units list */
.unit-conversion {
    display:inline-flex; align-items:center; gap:.3rem; margin-top:.2rem;
    font-size:.75rem; color:#831619; background:rgba(131,22,25,.07); border-radius:999px; padding:.1rem .55rem;
}
.unit-base-tag { font-size:.72rem; color:#6b7280; background:#f3f4f6; border-radius:999px; padding:.1rem .55rem; }
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\units\_index_styles.blade.php ENDPATH**/ ?>