<style>
.dashboard-card{
    background:linear-gradient(135deg, #ffffff 0%, #f7f9fc 100%);
    border-radius:10px;
    border:1px solid #e8ebf1;
    padding:18px 20px;
    transition:.25s;
    height:100%;
    display:flex;
    align-items:center;
    gap:16px;
    color:inherit;
    text-decoration:none;
}
.dashboard-card:hover{
    box-shadow:0 10px 24px rgba(31,41,55,.08);
    border-color:#dce2eb;
}
.dashboard-icon{
    width:44px;
    height:44px;
    flex:0 0 44px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}
.dashboard-title{
    color:#6b7280;
    font-size:12.5px;
    font-weight:500;
    margin:0 0 3px;
}
.dashboard-number{
    font-size:24px;
    font-weight:700;
    line-height:1.1;
    color:#111827;
    white-space:nowrap;
}
.dashboard-card-content{
    flex:1;
    min-width:0;
}
.dashboard-card-heading{
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:3px;
}
.dashboard-card-heading .dashboard-title{
    align-self:flex-end;
    text-align:right;
}
.dashboard-link{
    display:inline-block;
    margin-top:9px;
    text-decoration:none;
    font-size:12.5px;
    font-weight:500;
}
.bg-blue{ background:#eef2ff; color:#4f46e5; }
.bg-green{ background:#e9f9ef; color:#16a34a; }
.bg-orange{ background:#fef3e2; color:#d97706; }
.bg-red{ background:#fdeceb; color:#ef4444; }

/* "View all" button under each stock-alert tab, tinted to match its tab */
.alert-view-all{ display:inline-flex; align-items:center; padding:7px 16px; border-radius:8px; font-weight:600; font-size:13.5px; border:1px solid transparent; transition:.15s; }
.alert-view-all .bi-arrow-right{ transition:transform .15s; }
.alert-view-all:hover .bi-arrow-right{ transform:translateX(3px); }
.alert-view-expired{ background:#fdeceb; color:#b91c1c; border-color:#f8d4d1; }
.alert-view-expired:hover{ background:#dc2626; color:#fff; }
.alert-view-running_out{ background:#fef3e2; color:#b45309; border-color:#fbe0b8; }
.alert-view-running_out:hover{ background:#d97706; color:#fff; }
.alert-view-expiring{ background:#eef2ff; color:#4338ca; border-color:#dbe1fe; }
.alert-view-expiring:hover{ background:#4f46e5; color:#fff; }
.alert-view-out_of_stock{ background:#f1f5f9; color:#334155; border-color:#e2e8f0; }
.alert-view-out_of_stock:hover{ background:#475569; color:#fff; }

.dashboard-panel{
    background:#fff;
    border-radius:16px;
    border:1px solid #edf0f7;
    padding:16px 18px;
    height:100%;
}
.dashboard-panel-title{
    font-size:14px;
    font-weight:700;
    color:#1d2433;
    margin-bottom:12px;
}

.quick-action-btn{
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #edf0f7;
    color:#1d2433;
    text-decoration:none;
    font-size:13px;
    font-weight:500;
    transition:.2s;
    height:100%;
}
.quick-action-btn:hover{
    background:#f7f9fc;
    border-color:#dde3ee;
    color:#1d2433;
}
.quick-action-btn i{
    font-size:16px;
    color:#1677ff;
}
</style>
