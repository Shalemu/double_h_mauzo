
<script>
(function () {
    if (window.__tableExportReady) return;
    window.__tableExportReady = true;

    function cleanTable(selector) {
        const source = document.querySelector(selector);
        if (!source) return null;
        const table = source.cloneNode(true);
        table.querySelectorAll('button, input, select, form, .no-export, script').forEach(el => el.remove());
        table.querySelectorAll('[style]').forEach(el => el.removeAttribute('style'));
        table.querySelectorAll('a').forEach(a => a.replaceWith(document.createTextNode(a.textContent.trim())));
        // Drop placeholder rows such as "No products found".
        table.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.cells.length === 1 && tr.cells[0].colSpan > 1) tr.remove();
        });
        return table;
    }

    function fileName(title, ext) {
        return (title || 'export').replace(/[^\w\- ]+/g, '').trim().replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.' + ext;
    }

    function exportExcel(table, title) {
        const html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">'
            + '<head><meta charset="UTF-8"></head><body>'
            + '<h3>' + (title || '') + '</h3>' + table.outerHTML + '</body></html>';
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob(['﻿' + html], { type: 'application/vnd.ms-excel' }));
        link.download = fileName(title, 'xls');
        document.body.appendChild(link);
        link.click();
        setTimeout(() => { URL.revokeObjectURL(link.href); link.remove(); }, 1000);
    }

    function exportPdf(table, title) {
        const win = window.open('', '_blank');
        if (!win) { alert('Please allow pop-ups to export PDF.'); return; }
        win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + (title || 'Export') + '</title>'
            + '<style>body{font-family:Arial,sans-serif;font-size:11px;color:#222;margin:16px}'
            + 'h3{color:#831619;margin:0 0 4px}.meta{color:#666;margin-bottom:10px}'
            + 'table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:4px 6px;text-align:left}'
            + 'th{background:#f3e8e8}@page{size:A4 landscape;margin:12mm}</style></head><body>'
            + '<h3>' + (title || '') + '</h3><div class="meta">Double H Cosmetics · ' + new Date().toLocaleString() + '</div>'
            + table.outerHTML + '</body></html>');
        win.document.close();
        win.focus();
        let printed = false;
        const print = () => { if (!printed) { printed = true; win.print(); } };
        win.onload = print;
        setTimeout(print, 600);
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-export]');
        if (!btn) return;
        e.preventDefault();
        const table = cleanTable(btn.dataset.exportTable);
        if (!table) { alert('Nothing to export.'); return; }
        (btn.dataset.export === 'pdf' ? exportPdf : exportExcel)(table, btn.dataset.exportTitle);
    });
})();
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\components\table_export.blade.php ENDPATH**/ ?>