<script>
document.addEventListener('DOMContentLoaded', function () {
    const people = @json($people);
    const oldIds = @json(array_map('intval', (array) old('ids', [])));
    const form = document.getElementById('smsForm');
    const shop = document.getElementById('smsShop');
    const peopleBox = document.getElementById('peopleBox');
    const customBox = document.getElementById('customBox');
    const choose = document.getElementById('choosePeople');
    const list = document.getElementById('peopleList');
    const search = document.getElementById('peopleSearch');
    const message = document.getElementById('smsMessage');
    const custom = document.getElementById('customNumbers');
    const preview = document.getElementById('smsPreview');
    const selected = new Set(oldIds);

    const audience = () => form.querySelector('[name=audience]:checked').value;
    const scope = () => form.querySelector('[name=scope]:checked').value;
    const firstName = n => (n || '').trim().split(/\s+/)[0].replace(/^./, c => c.toUpperCase()).replace(/(?<=.)./g, c => c.toLowerCase());

    function inShop() {
        const a = audience();
        if (a === 'custom') return [];
        return people[a].filter(p => !shop.value || String(p.shop_id) === shop.value);
    }

    function recipients() {
        if (audience() === 'custom') {
            return custom.value.split(/[\s,;]+/).filter(Boolean).map(n => ({ name: null, phone: n }));
        }
        const pool = inShop();
        return scope() === 'all' ? pool : pool.filter(p => selected.has(p.id));
    }

    function renderList() {
        const term = search.value.trim().toLowerCase();
        list.replaceChildren();
        const pool = inShop().filter(p => !term || p.name.toLowerCase().includes(term) || String(p.phone).includes(term));
        if (!pool.length) {
            const empty = document.createElement('div');
            empty.className = 'p-3 small text-muted text-center';
            empty.textContent = 'No one with a phone number here.';
            list.appendChild(empty);
        }
        pool.forEach(function (p) {
            const wrap = document.createElement('div');
            wrap.className = 'form-check';
            const box = document.createElement('input');
            box.type = 'checkbox';
            box.className = 'form-check-input';
            box.name = 'ids[]';
            box.value = p.id;
            box.id = 'p-' + audience() + '-' + p.id;
            box.checked = selected.has(p.id);
            box.addEventListener('change', () => { box.checked ? selected.add(p.id) : selected.delete(p.id); update(); });
            const label = document.createElement('label');
            label.className = 'form-check-label d-flex justify-content-between w-100';
            label.htmlFor = box.id;
            const name = document.createElement('span');
            name.textContent = p.name;
            const meta = document.createElement('small');
            meta.className = 'text-muted';
            meta.textContent = p.phone + ' · ' + p.shop;
            label.append(name, meta);
            wrap.append(box, label);
            list.appendChild(wrap);
        });
    }

    function update() {
        const a = audience();
        peopleBox.hidden = a === 'custom';
        customBox.hidden = a !== 'custom';
        custom.required = a === 'custom';
        choose.hidden = a === 'custom' || scope() !== 'selected';

        const rec = recipients();
        const text = message.value;
        const parts = text.length ? Math.ceil(text.length / 160) : 0;
        document.getElementById('cChars').textContent = text.length;
        document.getElementById('cParts').textContent = parts;
        document.getElementById('cRecipients').textContent = rec.length;
        document.getElementById('cTotal').textContent = parts * rec.length;

        const sample = rec.find(r => r.name);
        document.getElementById('previewWho').textContent = sample ? 'as ' + sample.name + ' sees it' : '';
        if (text.trim()) {
            preview.textContent = text.replaceAll('{name}', sample ? firstName(sample.name) : 'Customer');
        } else {
            preview.innerHTML = '<span class="text-muted">Your message will appear here.</span>';
        }
    }

    form.querySelectorAll('[name=audience]').forEach(r => r.addEventListener('change', () => { selected.clear(); search.value = ''; renderList(); update(); }));
    form.querySelectorAll('[name=scope]').forEach(r => r.addEventListener('change', () => { renderList(); update(); }));
    shop.addEventListener('change', () => { renderList(); update(); });
    search.addEventListener('input', renderList);
    [message, custom].forEach(el => el.addEventListener('input', update));

    document.getElementById('toggleAllPeople').addEventListener('click', function () {
        const boxes = Array.from(list.querySelectorAll('input[type=checkbox]'));
        const allOn = boxes.length && boxes.every(b => b.checked);
        boxes.forEach(b => { b.checked = !allOn; const id = parseInt(b.value, 10); allOn ? selected.delete(id) : selected.add(id); });
        update();
    });

    document.getElementById('insertName').addEventListener('click', function () {
        const pos = message.selectionStart ?? message.value.length;
        message.value = message.value.slice(0, pos) + '{name}' + message.value.slice(message.selectionEnd ?? pos);
        message.focus();
        update();
    });
    // ---- Templates (Kiswahili / English) ----
    const templates = {
        general: [
            { label: { sw: 'Asante', en: 'Thank you' },
              sw: 'Habari {name}, asante kwa kununua Double H Cosmetics! Tunakuthamini sana, karibu tena.',
              en: 'Hello {name}, thank you for shopping with Double H Cosmetics! We appreciate you.' },
            { label: { sw: 'Mzigo mpya', en: 'New stock' },
              sw: 'Habari {name}, mzigo mpya umewasili Double H Cosmetics. Karibu dukani leo upate bidhaa mpya!',
              en: 'Hello {name}, new stock has arrived at Double H Cosmetics. Visit us today for the latest products!' },
            { label: { sw: 'Ukumbusho wa deni', en: 'Payment reminder' },
              sw: 'Habari {name}, tunakukumbusha kwa upole kuhusu salio la deni lako Double H Cosmetics. Asante.',
              en: 'Hello {name}, this is a friendly reminder about your outstanding balance at Double H Cosmetics. Thank you.' },
            { label: { sw: 'Ofa', en: 'Offer' },
              sw: 'Habari {name}, Double H Cosmetics tuna ofa maalum leo! Karibu dukani upate punguzo kwenye bidhaa zetu.',
              en: 'Hello {name}, Double H Cosmetics has a special offer today! Visit us for discounts on our products.' },
            { label: { sw: 'Taarifa kwa wafanyakazi', en: 'Staff notice' },
              sw: 'Habari {name}, tafadhali fahamu kwamba: ',
              en: 'Hello {name}, please note: ' },
        ],
        // month/day for fixed-date holidays; movable ones (Eid, Easter, Maulid) have no date.
        holidays: [
            { m: 1, d: 1, sw_name: 'Mwaka Mpya', en_name: 'New Year',
              sw: 'Heri ya Mwaka Mpya {name}! Double H Cosmetics inakutakia mwaka wenye afya, furaha na mafanikio. Asante kwa kuwa nasi.',
              en: 'Happy New Year {name}! Double H Cosmetics wishes you a healthy, happy and successful year. Thank you for being with us.' },
            { m: 1, d: 12, sw_name: 'Mapinduzi ya Zanzibar', en_name: 'Zanzibar Revolution Day',
              sw: 'Heri ya Sikukuu ya Mapinduzi ya Zanzibar {name}! Double H Cosmetics inakutakia sikukuu njema.',
              en: 'Happy Zanzibar Revolution Day {name}! Best wishes from Double H Cosmetics.' },
            { m: 4, d: 7, sw_name: 'Siku ya Karume', en_name: 'Karume Day',
              sw: '{name}, katika Siku ya Karume, Double H Cosmetics inaungana nawe kumkumbuka Hayati Sheikh Abeid Amani Karume.',
              en: '{name}, on Karume Day, Double H Cosmetics joins you in remembering the late Sheikh Abeid Amani Karume.' },
            { m: 4, d: 26, sw_name: 'Muungano', en_name: 'Union Day',
              sw: 'Heri ya Sikukuu ya Muungano {name}! Double H Cosmetics inakutakia sikukuu njema. Tudumishe Muungano wetu.',
              en: 'Happy Union Day {name}! Double H Cosmetics wishes you a wonderful Union Day.' },
            { m: 5, d: 1, sw_name: 'Mei Mosi', en_name: "Workers' Day",
              sw: 'Heri ya Mei Mosi {name}! Double H Cosmetics inakutakia Siku njema ya Wafanyakazi. Asante kwa juhudi zako.',
              en: "Happy Workers' Day {name}! Double H Cosmetics thanks you for your hard work." },
            { m: 7, d: 7, sw_name: 'Saba Saba', en_name: 'Saba Saba',
              sw: 'Heri ya Sikukuu ya Saba Saba {name}! Double H Cosmetics inakutakia siku njema.',
              en: 'Happy Saba Saba {name}! Best wishes from Double H Cosmetics.' },
            { m: 8, d: 8, sw_name: 'Nane Nane', en_name: 'Nane Nane (Farmers Day)',
              sw: 'Heri ya Sikukuu ya Nane Nane {name}! Double H Cosmetics inawatakia wakulima na wafugaji sikukuu njema.',
              en: 'Happy Nane Nane {name}! Double H Cosmetics wishes all farmers a great Farmers Day.' },
            { m: 10, d: 14, sw_name: 'Siku ya Nyerere', en_name: 'Nyerere Day',
              sw: '{name}, katika Siku ya Kumbukumbu ya Mwalimu Julius K. Nyerere, Double H Cosmetics inaungana nawe kumuenzi Baba wa Taifa. Tudumishe umoja na amani.',
              en: '{name}, on Nyerere Day Double H Cosmetics joins you in honouring Mwalimu Julius K. Nyerere, the Father of the Nation. Let us keep unity and peace.' },
            { m: 12, d: 9, sw_name: 'Uhuru', en_name: 'Independence Day',
              sw: 'Heri ya Sikukuu ya Uhuru {name}! Double H Cosmetics inakutakia sikukuu njema ya Uhuru wa Tanzania.',
              en: 'Happy Independence Day {name}! Double H Cosmetics wishes you a joyful Tanzania Independence Day.' },
            { m: 12, d: 25, sw_name: 'Krismasi', en_name: 'Christmas',
              sw: 'Heri ya Krismasi {name}! Double H Cosmetics inakutakia wewe na familia yako sikukuu njema yenye amani na furaha.',
              en: 'Merry Christmas {name}! Double H Cosmetics wishes you and your family a peaceful and joyful Christmas.' },
            { m: 12, d: 26, sw_name: 'Siku ya Kufungua Zawadi', en_name: 'Boxing Day',
              sw: 'Heri ya Siku ya Kufungua Zawadi {name}! Double H Cosmetics inakutakia sikukuu njema. Karibu dukani!',
              en: 'Happy Boxing Day {name}! Best wishes from Double H Cosmetics. Visit us for holiday offers!' },
            { sw_name: 'Eid al-Fitr', en_name: 'Eid al-Fitr',
              sw: 'Eid Mubarak {name}! Double H Cosmetics inakutakia wewe na familia yako Sikukuu njema ya Eid al-Fitr.',
              en: 'Eid Mubarak {name}! Double H Cosmetics wishes you and your family a blessed Eid al-Fitr.' },
            { sw_name: 'Eid al-Adha', en_name: 'Eid al-Adha',
              sw: 'Eid Mubarak {name}! Double H Cosmetics inakutakia Sikukuu njema ya Eid al-Adha yenye baraka tele.',
              en: 'Eid Mubarak {name}! Double H Cosmetics wishes you a blessed Eid al-Adha.' },
            { sw_name: 'Pasaka', en_name: 'Easter',
              sw: 'Heri ya Pasaka {name}! Double H Cosmetics inakutakia sikukuu njema ya Pasaka yenye amani na baraka.',
              en: 'Happy Easter {name}! Double H Cosmetics wishes you a peaceful and blessed Easter.' },
            { sw_name: 'Maulid', en_name: 'Maulid',
              sw: 'Heri ya Maulid {name}! Double H Cosmetics inakutakia sikukuu njema yenye baraka.',
              en: 'Happy Maulid {name}! Best wishes from Double H Cosmetics.' },
        ],
    };
    const months = { sw: ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ago', 'Sep', 'Okt', 'Nov', 'Des'], en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] };
    const lang = () => form.querySelector('[name=tpl_lang]:checked').value;

    function useTemplate(text) {
        message.value = text;
        message.focus();
        update();
    }

    // Days until a fixed-date holiday (0 = today), rolling over to next year.
    function daysUntil(h) {
        const today = new Date(); today.setHours(0, 0, 0, 0);
        let next = new Date(today.getFullYear(), h.m - 1, h.d);
        if (next < today) next = new Date(today.getFullYear() + 1, h.m - 1, h.d);
        return Math.round((next - today) / 86400000);
    }

    function renderTemplates() {
        const l = lang();
        const box = document.getElementById('generalTemplates');
        box.replaceChildren();
        templates.general.forEach(function (t) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-sm btn-light border sms-template';
            b.textContent = t.label[l];
            b.addEventListener('click', () => useTemplate(t[l]));
            box.appendChild(b);
        });

        const select = document.getElementById('holidayTemplate');
        select.replaceChildren(new Option(l === 'sw' ? '🎉 Salamu za sikukuu…' : '🎉 Holiday wishes…', ''));
        const fixed = templates.holidays.filter(h => h.m).sort((a, b) => daysUntil(a) - daysUntil(b));
        const movable = templates.holidays.filter(h => !h.m);
        const g1 = document.createElement('optgroup');
        g1.label = l === 'sw' ? 'Zijazo (kwa tarehe)' : 'Coming up (by date)';
        fixed.forEach(function (h) {
            const days = daysUntil(h);
            const when = days === 0 ? (l === 'sw' ? 'leo' : 'today') : h.d + ' ' + months[l][h.m - 1];
            g1.appendChild(new Option(h[l + '_name'] + ' — ' + when, templates.holidays.indexOf(h)));
        });
        const g2 = document.createElement('optgroup');
        g2.label = l === 'sw' ? 'Tarehe hubadilika' : 'Date changes each year';
        movable.forEach(h => g2.appendChild(new Option(h[l + '_name'], templates.holidays.indexOf(h))));
        select.append(g1, g2);

        const next = fixed[0];
        const days = daysUntil(next);
        document.getElementById('nextHoliday').textContent = (l === 'sw' ? 'Inayofuata: ' : 'Next: ') + next[l + '_name']
            + (days === 0 ? (l === 'sw' ? ' (leo)' : ' (today)') : ' (' + (l === 'sw' ? 'siku ' + days : days + ' days') + ')');
    }

    document.getElementById('holidayTemplate').addEventListener('change', function () {
        if (this.value === '') return;
        useTemplate(templates.holidays[this.value][lang()]);
    });
    form.querySelectorAll('[name=tpl_lang]').forEach(r => r.addEventListener('change', renderTemplates));
    renderTemplates();

    // Confirm before sending real (paid) messages.
    document.getElementById('smsReview').addEventListener('click', function () {
        if (!form.reportValidity()) return;
        const rec = recipients();
        if (!rec.length) { Swal.fire('No recipients', 'Choose at least one person or type a number.', 'warning'); return; }
        const parts = Math.ceil(message.value.length / 160);
        Swal.fire({
            icon: 'question',
            title: 'Send ' + rec.length + ' SMS?',
            html: '<div class="text-start small">'
                + '<div class="d-flex justify-content-between"><span>Recipients</span><strong>' + rec.length + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>SMS per person</span><strong>' + parts + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Total SMS (credits)</span><strong>' + (parts * rec.length) + '</strong></div>'
                + '</div>',
            showCancelButton: true,
            confirmButtonText: 'Yes, send',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#831619',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const btn = document.getElementById('smsReview');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
            form.submit();
        });
    });

    renderList();
    update();
});
</script>
