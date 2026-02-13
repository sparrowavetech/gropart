(function () {
  const i18n = window.ProductBundlesI18n || {};
  const t = function (key, fallback) {
    return (i18n && typeof i18n[key] === 'string' && i18n[key]) ? i18n[key] : (fallback || key);
  };

  const tf = function (key, params, fallback) {
    let s = t(key, fallback);
    if (!params) return s;
    Object.keys(params).forEach(function (k) {
      s = s.replaceAll(':' + k, String(params[k]));
    });
    return s;
  };
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function closest(el, sel) {
    while (el && el.nodeType === 1) {
      if (el.matches(sel)) return el;
      el = el.parentElement;
    }
    return null;
  }

  async function postJson(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    });

    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
      const msg = json && json.message ? json.message : t('request_failed', 'Request failed.');
      throw new Error(msg);
    }
    return json;
  }

  function setMsg(card, text, isError) {
    const box = card.querySelector('.pb-msg');
    if (!box) return;
    box.textContent = text || '';
    box.classList.toggle('is-error', !!isError);
    box.classList.toggle('is-ok', !isError);
  }

  document.addEventListener('click', async function (e) {
    const btnCardAdd = e.target.closest('.pb-bundle-add-cart');
    if (btnCardAdd) {
      e.preventDefault();
      const bundleId = parseInt(btnCardAdd.getAttribute('data-bundle-id') || '0', 10);
      const section = closest(btnCardAdd, '.pb-groups');
      const addUrl = section ? section.getAttribute('data-add-url') : null;
      if (!bundleId || !addUrl) return;

      btnCardAdd.classList.add('is-loading');
      try {
        const json = await postJson(addUrl, { bundle_id: bundleId });
        try { document.dispatchEvent(new CustomEvent('product-bundles:added', { detail: { bundleId } })); } catch (_) {}
        try { document.dispatchEvent(new CustomEvent('bb:cart-updated')); } catch (_) {}
        try { document.dispatchEvent(new CustomEvent('cart:updated')); } catch (_) {}
        try { window.dispatchEvent(new Event('cart-updated')); } catch (_) {}
        try {
          if (window.BB && window.BB.cart && typeof window.BB.cart.refresh === 'function') {
            window.BB.cart.refresh();
          }
        } catch (_) {}
        try {
          if (typeof window.refreshCart === 'function') {
            window.refreshCart();
          }
        } catch (_) {}

        // Optional toast if theme has one
        try {
          if (window.showToast && typeof window.showToast === 'function') {
            window.showToast(json.message || t('bundle_added', 'Added to cart.'), 'success');
          }
        } catch (_) {}
      } catch (err) {
        // Best effort alert
        try {
          if (window.showToast && typeof window.showToast === 'function') {
            window.showToast(err.message || t('failed_to_add', 'Failed to add.'), 'error');
          } else {
            alert(err.message || t('failed_to_add', 'Failed to add.'));
          }
        } catch (_) {}
      } finally {
        btnCardAdd.classList.remove('is-loading');
      }

      return;
    }

    const btnFixed = e.target.closest('.pb-add-fixed');
    const btnMix = e.target.closest('.pb-add-mix');
    const btn = btnFixed || btnMix;
    if (!btn) return;

    const card = closest(btn, '.pb-card');
    const wrap = closest(btn, '.pb-wrap');
    if (!card || !wrap) return;

    const addUrl = wrap.getAttribute('data-add-url');
    const bundleId = parseInt(btn.getAttribute('data-bundle-id') || card.getAttribute('data-bundle-id'), 10);

    if (!addUrl || !bundleId) return;

    setMsg(card, '', false);
    btn.disabled = true;

    try {
      const payload = { bundle_id: bundleId };

      if (btnMix) {
        const selections = {};
        const groups = card.querySelectorAll('.pb-group');
        let hasError = false;

        groups.forEach(group => {
          const gid = group.getAttribute('data-group-id');
          const min = parseInt(group.getAttribute('data-min') || '0', 10);
          const max = parseInt(group.getAttribute('data-max') || '999', 10);

          const picked = Array.from(group.querySelectorAll('input[type="checkbox"]:checked'))
            .map(i => parseInt(i.value, 10))
            .filter(Boolean);

          if (picked.length < min || picked.length > max) {
            hasError = true;
            setMsg(card, tf('group_between', { min: min, max: max }, `Selection for a group must be between ${min} and ${max}.`), true);
          }

          selections[gid] = picked;
        });

        if (hasError) {
          btn.disabled = false;
          return;
        }

        payload.selections = selections;
      }

      const json = await postJson(addUrl, payload);
      setMsg(card, json.message || t('bundle_added', 'Added to cart.'), false);

      // Trigger a few common events so themes can refresh mini-cart/qty counters.
      // Different Botble themes listen to different event names.
      try { document.dispatchEvent(new CustomEvent('product-bundles:added', { detail: { bundleId } })); } catch (_) {}
      try { document.dispatchEvent(new CustomEvent('bb:cart-updated')); } catch (_) {}
      try { document.dispatchEvent(new CustomEvent('cart:updated')); } catch (_) {}
      try { window.dispatchEvent(new Event('cart-updated')); } catch (_) {}

      // Best-effort direct refresh hooks (if the theme exposes one)
      try {
        if (window.BB && window.BB.cart && typeof window.BB.cart.refresh === 'function') {
          window.BB.cart.refresh();
        }
      } catch (_) {}
      try {
        if (typeof window.refreshCart === 'function') {
          window.refreshCart();
        }
      } catch (_) {}
    } catch (err) {
      setMsg(card, err.message || t('failed_to_add', 'Failed to add.'), true);
    } finally {
      btn.disabled = false;
    }
  });
})();
