<style>
/* Logo maroon instead of Bootstrap blue across this screen */
.rp-page{ --rp-brand:#831619; --rp-brand-dark:#6b1215; --rp-brand-soft:rgba(131,22,25,.1); }
.rp-page .text-primary{ color:var(--rp-brand) !important; }
.rp-page .btn-link{ color:var(--rp-brand); }
.rp-page .btn-link:hover{ color:var(--rp-brand-dark); }
.rp-page .btn-primary{ background:var(--rp-brand); border-color:var(--rp-brand); }
.rp-page .btn-primary:hover, .rp-page .btn-primary:focus, .rp-page .btn-primary:active{ background:var(--rp-brand-dark) !important; border-color:var(--rp-brand-dark) !important; }
.rp-page .form-check-input:checked{ background-color:var(--rp-brand); border-color:var(--rp-brand); }
.rp-page .form-check-input:focus, .rp-page .form-control:focus{ border-color:rgba(131,22,25,.45); box-shadow:0 0 0 .2rem rgba(131,22,25,.15); }
.rp-page .form-switch .form-check-input:focus:not(:checked){ background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba(131,22,25,.45)'/%3e%3c/svg%3e"); }
.rp-page .bg-primary-subtle{ background:var(--rp-brand-soft) !important; }
html[data-bs-theme="dark"] .rp-page{ --rp-brand:#e0565b; --rp-brand-dark:#c94449; }
.rp-intro{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; background:#fff; border:1px solid #eef0f6; border-radius:12px; padding:12px 16px; margin-bottom:14px; }
.rp-intro-stat{ display:inline-flex; align-items:center; gap:6px; border:1px solid #e6e8f0; background:#f8fafc; border-radius:999px; padding:4px 12px; font-size:12px; font-weight:600; color:#45506b; }
.rp-layout{ display:grid; grid-template-columns:280px 1fr; gap:16px; align-items:start; }
@media (max-width:991px){ .rp-layout{ grid-template-columns:1fr; } }
.rp-sidebar{ background:#fff; border:1px solid #eef0f6; border-radius:14px; padding:8px; position:sticky; top:90px; }
.rp-sidebar-label{ font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#9aa3b5; padding:8px 10px 4px; }
.rp-role-btn{ display:flex; align-items:center; gap:10px; width:100%; border-radius:10px; padding:9px 10px; text-decoration:none; transition:background-color .12s; }
.rp-role-btn:hover{ background:#f8fafc; }
.rp-role-btn.active{ background:rgba(131,22,25,.08); box-shadow:inset 0 0 0 1px rgba(131,22,25,.18); }
.rp-role-avatar{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; flex-shrink:0; border-radius:9px; background:rgba(131,22,25,.12); color:var(--rp-brand); font-size:12px; font-weight:700; }
.rp-role-name{ display:block; font-size:13.5px; font-weight:600; color:#1d2433; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rp-role-btn.active .rp-role-name{ color:var(--rp-brand); }
.rp-role-sub{ font-size:11px; color:#8891a3; }
.rp-role-count{ flex-shrink:0; font-size:11px; font-weight:600; color:#9aa3b5; }
.rp-add-role-btn{ margin-top:6px; width:100%; border:1px dashed #d7dbe6; border-radius:10px; background:transparent; color:var(--rp-brand); font-size:13px; font-weight:600; padding:9px 10px; }
.rp-add-role-btn:hover{ border-color:var(--rp-brand); background:rgba(131,22,25,.05); }
.rp-panel{ background:#fff; border:1px solid #eef0f6; border-radius:14px; min-width:0; }
.rp-panel-header{ border-bottom:1px solid #eef0f6; padding:16px; }
.rp-tabs{ display:inline-flex; background:#f1f2f8; border-radius:10px; padding:3px; }
.rp-tab-btn{ border:none; background:transparent; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; color:#6b7280; }
.rp-tab-btn.active{ background:#fff; color:#1d2433; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.rp-progress{ height:6px; flex:1; border-radius:999px; background:#eef0f6; overflow:hidden; }
.rp-progress-bar{ height:100%; background:var(--rp-brand); border-radius:999px; transition:width .2s; }
.rp-module-card{ border:1px solid #eef0f6; border-radius:12px; transition:border-color .15s, background-color .15s; height:100%; }
.rp-module-card.has-checked{ border-color:rgba(131,22,25,.3); background:rgba(131,22,25,.03); }
.rp-module-card-header{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; }
.rp-module-list{ border-top:1px solid #f1f2f8; padding:6px 8px; max-height:220px; overflow-y:auto; }
.rp-perm-label{ display:flex; align-items:center; gap:8px; padding:5px 6px; border-radius:8px; font-size:13px; cursor:pointer; margin:0; }
.rp-perm-label:hover{ background:#f8fafc; }
.rp-perm-label.locked{ cursor:default; }
.rp-save-bar{ position:fixed; left:0; right:0; bottom:18px; display:flex; justify-content:center; z-index:1040; pointer-events:none; }
.rp-save-bar-inner{ pointer-events:auto; display:flex; align-items:center; gap:14px; background:#1d2433; color:#fff; border-radius:14px; padding:10px 16px; box-shadow:0 10px 24px rgba(29,36,51,.28); }
.rp-user-row{ display:flex; align-items:center; gap:10px; padding:8px 4px; border-bottom:1px solid #f1f2f8; }
.rp-user-avatar{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; background:#f1f2f8; color:#6b7280; font-size:12px; font-weight:700; flex-shrink:0; }
.rp-hidden{ display:none !important; }
html[data-bs-theme="dark"] .rp-intro, html[data-bs-theme="dark"] .rp-sidebar, html[data-bs-theme="dark"] .rp-panel{ background:#1f2937; border-color:#374151; }
html[data-bs-theme="dark"] .rp-role-name{ color:#e5e7eb; }
html[data-bs-theme="dark"] .rp-role-btn:hover, html[data-bs-theme="dark"] .rp-perm-label:hover{ background:#273449; }
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/role/_index_styles.blade.php ENDPATH**/ ?>