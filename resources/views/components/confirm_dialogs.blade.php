{{--
    "Are you sure?" questions in the app's own style instead of the browser's
    plain confirm box (the one titled "127.0.0.1:8000 says").

    - Buttons/forms written as onsubmit="return confirm('…')" or
      onclick="return confirm('…')" are switched over automatically.
    - New markup can use data-confirm="Delete this?" on a <form>, <button> or <a>
      (optional: data-confirm-button="Yes, delete").
    - Scripts can use: if (await confirmDialog('Delete this?')) { … }
--}}
<script>
(function () {
    // Words that make it a destructive action: red button.
    const DANGER = /^\s*(delete|remove|reject|cancel|discard|purge|clear|suspend|deactivate)/i;

    // Show the question; resolves true when the user confirms.
    window.confirmDialog = function (message, options) {
        options = options || {};
        if (!window.Swal) return Promise.resolve(window.confirm(message)); // last resort
        const danger = options.danger ?? DANGER.test(message);
        const verb = (message.match(/^\s*(\w+)/) || [])[1] || '';
        return Swal.fire({
            icon: danger ? 'warning' : 'question',
            title: options.title || 'Are you sure?',
            text: message,
            showCancelButton: true,
            confirmButtonText: options.confirmText || (danger && verb ? 'Yes, ' + verb.toLowerCase() : 'Yes, continue'),
            cancelButtonText: options.cancelText || 'No, go back',
            confirmButtonColor: danger ? '#dc3545' : '#831619',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: danger, // Enter on a destructive question means "No"
        }).then(result => !!result.isConfirmed);
    };

    // onsubmit/onclick="return confirm('…')"  →  data-confirm="…"
    const INLINE = /^\s*return\s+confirm\(\s*(['"`])([\s\S]*)\1\s*\)\s*;?\s*$/;
    function convert(root) {
        (root.querySelectorAll ? root.querySelectorAll('[onsubmit*="confirm("], [onclick*="confirm("]') : []).forEach(el => {
            ['onsubmit', 'onclick'].forEach(attr => {
                const code = el.getAttribute(attr);
                const m = code && code.match(INLINE);
                if (!m) return; // anything more complex is left as it is
                el.setAttribute('data-confirm', m[2].replace(/\\(.)/g, '$1')); // undo addslashes()
                el.removeAttribute(attr);
            });
        });
    }

    // Forms: ask before sending.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;
        if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        const submitter = e.submitter;
        confirmDialog(form.dataset.confirm, { confirmText: form.dataset.confirmButton }).then(ok => {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            else form.submit();
        });
    }, true);

    // Buttons and links: ask before the click goes through.
    document.addEventListener('click', function (e) {
        const el = e.target.closest('button[data-confirm], a[data-confirm], input[type=submit][data-confirm]');
        if (!el || el.disabled) return;
        if (el.dataset.confirmed === '1') { delete el.dataset.confirmed; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        confirmDialog(el.dataset.confirm, { confirmText: el.dataset.confirmButton }).then(ok => {
            if (!ok) return;
            if (el.tagName === 'A' && el.href) { window.location.href = el.href; return; }
            el.dataset.confirmed = '1';
            // A submit button: its form must not ask again.
            if (el.form && el.form.dataset.confirm) el.form.dataset.confirmed = '1';
            el.click();
        });
    }, true);

    function start() {
        convert(document);
        new MutationObserver(ms => ms.forEach(m => m.addedNodes.forEach(n => { if (n.nodeType === 1) convert(n.parentNode || n); })))
            .observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
</script>
