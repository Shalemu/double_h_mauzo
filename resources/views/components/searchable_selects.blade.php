{{--
    Every dropdown (<select>) in the app becomes searchable: click it and type
    to filter the options. Uses Tom Select (public/vendor/tom-select), which
    keeps the original <select> in the page and fires its normal change/input
    events, so existing page scripts and forms work unchanged.

    - New dropdowns (pop-ups, repeaters, AJAX-loaded forms) are picked up
      automatically.
    - When page code changes a dropdown (select.value = …, adding/removing
      options, form.reset()), the searchable box follows.
    - Opt out on a single dropdown with data-no-search.
--}}
<link rel="stylesheet" href="{{ asset('vendor/tom-select/tom-select.bootstrap5.min.css') }}">
<style>
/* The theme draws the border and arrow on the outer box (it keeps the select's
   form-select / form-control classes); only the list's stacking needs help. */
.ts-dropdown { z-index: 1060; }
body > .ts-dropdown { margin-top: 2px; border-radius: .375rem; box-shadow: 0 .5rem 1rem rgba(0,0,0,.12); }
.ts-dropdown .dropdown-input-wrap { padding: .4rem; border-bottom: 1px solid var(--bs-border-color); }
.ts-dropdown .dropdown-input { width: 100%; padding: .35rem .6rem; border: 1px solid var(--bs-border-color); border-radius: .375rem; outline: 0; }
.ts-dropdown .dropdown-input:focus { border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }
.ts-dropdown .ts-dropdown-content { max-height: 260px; }
.ts-dropdown .option { padding: .4rem .75rem; }
.ts-dropdown .no-results { padding: .5rem .75rem; color: var(--bs-secondary-color); }
.ts-wrapper.disabled .ts-control { background-color: var(--bs-secondary-bg); opacity: 1; }
.ts-wrapper.is-invalid .ts-control, select.is-invalid + .ts-wrapper .ts-control { border-color: var(--bs-form-invalid-border-color, #dc3545); }
html[data-bs-theme="dark"] .ts-control, html[data-bs-theme="dark"] .ts-dropdown { background: #1f2937; color: #e5e7eb; border-color: #374151; }
html[data-bs-theme="dark"] .ts-dropdown .active { background: #334155; color: #fff; }
</style>
<script src="{{ asset('vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script>
(function () {
    if (typeof TomSelect === 'undefined') return;

    // Dropdowns left alone: table page-size pickers, already-enhanced ones, list boxes, opt-outs.
    const SKIP = '[data-no-search], [size], .tomselected, .ts-hidden-accessible, .select2-hidden-accessible, '
        + '.dataTables_length select, .dt-length select, .swal2-container select';

    // ---- Keeping the searchable box in step with the real <select> ----
    // Page code changes dropdowns in many ways (select.value = …, jQuery .val(),
    // adding options, form.reset()). Each of those schedules a check; the check
    // re-syncs only when the options or the selected value really differ from
    // what the box shows. That also stops loops: Tom Select itself re-orders the
    // chosen <option> when it saves a choice, which changes nothing we compare.
    const pending = new Set();
    let scheduled = false;

    function schedule(select) {
        if (!select || !select.tomselect) return;
        pending.add(select);
        if (!scheduled) {
            scheduled = true;
            queueMicrotask(function () {
                scheduled = false;
                const list = Array.from(pending);
                pending.clear();
                list.forEach(refresh);
            });
        }
    }

    const SEP = '\u0001';
    function optionSignature(select) {
        return Array.from(select.options)
            .map(o => o.value + SEP + o.text + SEP + (o.disabled ? 1 : 0))
            .sort().join('\u0002');
    }
    function domValue(select) {
        return select.multiple
            ? Array.from(select.selectedOptions).map(o => o.value).sort().join(SEP)
            : select.value;
    }
    function boxValue(ts) {
        const v = ts.getValue();
        return Array.isArray(v) ? v.slice().sort().join(SEP) : v;
    }

    function refresh(select) {
        const ts = select.tomselect;
        if (!ts) return;
        const signature = optionSignature(select);
        const optionsChanged = signature !== select._tsSignature;
        if (!optionsChanged && domValue(select) === boxValue(ts)) return; // already in step
        select._tsSignature = signature;
        // sync() only adds options; when the list was replaced, forget the old ones first
        // (clearOptions only touches Tom Select's own list, not the real <select>).
        if (optionsChanged) ts.clearOptions(() => false);
        ts.sync(); // silent: re-reads options + value, fires no change events
        // sync() reads "the empty option is selected" as "nothing selected" and leaves the box
        // blank; show the empty option's label ("-- select product --") like on page load.
        if (!select.multiple && select.value === '' && ts.items.length === 0 && ('' in ts.options)) {
            ts.addItem('', true);
        }
    }

    function enhance(select) {
        if (!(select instanceof HTMLSelectElement) || select.tomselect || select.matches(SKIP)) return;
        if (select.closest('[data-no-search], template, .swal2-container')) return;

        let ts;
        try {
            ts = new TomSelect(select, {
                // A search box inside the opened list. (Without it, a box that already shows
                // a choice — even "-- select --" — ignores typing.)
                plugins: ['dropdown_input'],
                maxOptions: null,              // show every option, not just the first 50
                refreshThrottle: 100,          // filter 0.1 s after typing (default 0.3 s feels slow)
                allowEmptyOption: true,        // "-- select --" / "—" stay pickable (to clear a choice)
                create: false,
                hideSelected: false,
                closeAfterSelect: true,
                selectOnTab: true,
                searchField: ['text'],
                // On normal pages the list is placed on the page body, so cards and tables that
                // clip their content (overflow: hidden / scrolling) can't cut it off.
                // In a pop-up it stays inside the pop-up, which keeps focus on the search box.
                dropdownParent: select.closest('.modal') ? null : 'body',
                render: {
                    no_results: (data, escape) => '<div class="no-results">No match for "' + escape(data.input) + '"</div>',
                },
                onInitialize: function () {
                    const ts = this;
                    ts.control_input.placeholder = 'Type to search…';
                    ts.control_input.setAttribute('aria-label', 'Search the list');

                    // Box focused (Tab, or typing right after a click) but the search box isn't yet:
                    // letters start the search, Enter / Space / ↓ open the list.
                    ts.control.addEventListener('keydown', function (e) {
                        if (ts.isDisabled || document.activeElement === ts.control_input) return;
                        const printable = e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && e.key !== ' ';
                        if (!printable && !['Enter', ' ', 'ArrowDown'].includes(e.key)) return;
                        e.preventDefault();
                        if (!ts.isOpen) ts.open();
                        ts.control_input.focus();
                        if (printable) {
                            ts.control_input.value += e.key;
                            ts.control_input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                },
            });
        } catch (e) {
            return; // leave the plain dropdown if anything goes wrong
        }

        select._tsSignature = optionSignature(select);

        // Options added, removed, replaced or renamed by page code.
        new MutationObserver(function () { schedule(select); })
            .observe(select, { childList: true, subtree: true, characterData: true });

        // Disabled toggled by page code. Only act on a real change: disable()/enable()
        // set the attribute themselves, which would otherwise call this again forever.
        const followDisabled = function () {
            if (select.disabled && !ts.isDisabled) ts.disable();
            else if (!select.disabled && ts.isDisabled) ts.enable();
        };
        new MutationObserver(followDisabled).observe(select, { attributes: true, attributeFilter: ['disabled'] });
        followDisabled();
    }

    // Value changes made in code: select.value / selectedIndex = …, option.selected = … (jQuery .val()).
    function hook(proto, prop, selectOf) {
        const native = Object.getOwnPropertyDescriptor(proto, prop);
        if (!native || !native.set) return;
        Object.defineProperty(proto, prop, {
            configurable: true,
            enumerable: native.enumerable,
            get: function () { return native.get.call(this); },
            set: function (v) {
                native.set.call(this, v);
                schedule(selectOf(this));
            },
        });
    }
    hook(HTMLSelectElement.prototype, 'value', el => el);
    hook(HTMLSelectElement.prototype, 'selectedIndex', el => el);
    hook(HTMLOptionElement.prototype, 'selected', el => el.closest('select'));

    // A list placed on the body follows its box when any inner panel scrolls
    // (Tom Select itself only listens to the window scrolling).
    document.addEventListener('scroll', function () {
        document.querySelectorAll('select.tomselected').forEach(function (s) {
            if (s.tomselect && s.tomselect.isOpen) s.tomselect.positionDropdown();
        });
    }, { capture: true, passive: true });

    // form.reset() puts the original dropdowns back; follow them.
    document.addEventListener('reset', function (e) {
        setTimeout(function () {
            e.target.querySelectorAll('select').forEach(schedule);
        });
    }, true);

    function enhanceWithin(root) {
        if (root.matches && root.matches('select')) enhance(root);
        root.querySelectorAll && root.querySelectorAll('select').forEach(enhance);
    }

    function start() {
        enhanceWithin(document);
        // Dropdowns that appear later: pop-ups, repeaters, AJAX-loaded forms.
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && !node.closest('.ts-wrapper, .ts-dropdown')) enhanceWithin(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    // After the page's own scripts have set up their dropdowns.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(start); });
    } else {
        setTimeout(start);
    }
})();
</script>
