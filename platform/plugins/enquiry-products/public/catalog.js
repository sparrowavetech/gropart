(() => {
    const form = document.querySelector('[data-ep-catalog]');
    if (!form || form.dataset.ready) return;
    form.dataset.ready = '1';
    const field = form.querySelector('[name="category"]');
    document.querySelectorAll('.ep-category-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(expanded)); button.textContent = expanded ? '−' : '+';
            button.parentElement.nextElementSibling.hidden = !expanded;
        });
    });
    document.querySelector('#ep-sort').addEventListener('change', () => form.requestSubmit());
    document.querySelectorAll('[data-ep-view]').forEach(button => button.addEventListener('click', () => {
        document.querySelector('.ep-results').classList.toggle('ep-list-view', button.dataset.epView === 'list');
        document.querySelectorAll('[data-ep-view]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    }));
    const input = form.querySelector('#ep-search');
    const box = form.querySelector('#ep-suggestions');
    const status = form.querySelector('.ep-search-status');
    let timer, controller, active = -1, version = 0;
    function close() { clearTimeout(timer); controller?.abort(); version++; box.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1; status.textContent = ''; }
    field.addEventListener('change', close);
    input.addEventListener('input', () => {
        close(); const q = input.value.trim(); if (q.length < 2) return;
        const current = version;
        timer = setTimeout(async () => {
            controller = new AbortController();
            const url = new URL(form.dataset.suggestions, location.href);
            url.searchParams.set('q', q); if (field.value) url.searchParams.set('category', field.value);
            try {
                const response = await fetch(url, {signal: controller.signal, headers: {'Accept':'application/json'}});
                if (!response.ok) throw new Error('Search unavailable');
                const items = await response.json(); if (current !== version) return;
                box.replaceChildren();
                items.forEach((item, index) => {
                    const link = document.createElement('a'); link.href = item.url; link.className = 'ep-suggestion'; link.id = `ep-option-${index}`; link.setAttribute('role', 'option'); link.setAttribute('aria-selected', 'false');
                    const img = document.createElement('img'); img.src = item.image; img.alt = ''; img.width = 64; img.height = 64;
                    const detail = document.createElement('span'); const name = document.createElement('strong'); name.textContent = item.name; detail.append(name);
                    if (item.seller) {
                        const seller = document.createElement('span'); seller.className = 'ep-seller'; seller.textContent = item.seller;
                        if (item.verified) { const tick = document.createElement('span'); tick.className = 'ep-verified'; tick.textContent = '✓'; tick.title = form.dataset.verified; tick.setAttribute('aria-label', form.dataset.verified); seller.append(' ', tick); }
                        if (item.badge) { const badge = document.createElement('span'); badge.className = 'ep-seller-badge'; badge.textContent = item.badge; seller.append(' ', badge); }
                        detail.append(seller);
                    }
                    link.append(img, detail); box.append(link);
                });
                box.hidden = !items.length; input.setAttribute('aria-expanded', String(!!items.length));
                status.textContent = items.length ? form.dataset.available.replace(':count', items.length) : form.dataset.empty;
            } catch (error) { if (error.name !== 'AbortError' && current === version) status.textContent = form.dataset.unavailable; }
        }, 250);
    });
    input.addEventListener('keydown', event => {
        const links = [...box.querySelectorAll('a')];
        if (event.key === 'Escape') { close(); return; }
        if (box.hidden || !links.length) return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault(); active = (active + (event.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
            links.forEach((link, i) => link.setAttribute('aria-selected', String(i === active)));
            input.setAttribute('aria-activedescendant', links[active].id); links[active].scrollIntoView({block:'nearest'});
        } else if (event.key === 'Enter' && active >= 0) { event.preventDefault(); links[active].click(); }
    });
    document.addEventListener('click', event => { if (!form.contains(event.target)) close(); });
    form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) close(); });
})();
