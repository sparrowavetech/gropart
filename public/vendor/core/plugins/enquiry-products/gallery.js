(() => {
    const main = document.querySelector('.ep-gallery-main');
    const dialog = document.querySelector('.ep-lightbox');
    if (!main || !dialog) return;
    const buttons = [...document.querySelectorAll('[data-ep-image]')];
    const images = buttons.length ? buttons.map(button => button.dataset.epImage) : [main.src];
    const stage = dialog.querySelector('.ep-lightbox-stage');
    const image = stage.querySelector('img');
    const zoom = dialog.querySelector('[data-ep-zoom]');
    let index = 0, oldOverflow = '';
    function show(next) {
        index = (next + images.length) % images.length;
        image.src = images[index]; main.src = images[index];
        stage.classList.remove('is-zoomed'); zoom.setAttribute('aria-pressed', 'false');
        stage.scrollTop = stage.scrollLeft = 0;
        buttons.forEach((button, i) => button.setAttribute('aria-pressed', String(i === index)));
        dialog.querySelector('.ep-lightbox-count').textContent = `${index + 1} / ${images.length}`;
    }
    function open() { show(index); oldOverflow = document.body.style.overflow; document.body.style.overflow = 'hidden'; dialog.showModal(); }
    main.addEventListener('click', open);
    main.addEventListener('keydown', event => { if (['Enter', ' '].includes(event.key)) { event.preventDefault(); open(); } });
    buttons.forEach((button, i) => button.addEventListener('click', () => show(i)));
    dialog.querySelector('[data-ep-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { document.body.style.overflow = oldOverflow; main.focus(); });
    dialog.addEventListener('click', event => { if (event.target === dialog || event.target === stage) dialog.close(); });
    dialog.querySelector('.ep-lightbox-prev').addEventListener('click', () => show(index - 1));
    dialog.querySelector('.ep-lightbox-next').addEventListener('click', () => show(index + 1));
    dialog.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); show(index + (event.key === 'ArrowRight' ? 1 : -1)); }
    });
    function toggleZoom() { zoom.setAttribute('aria-pressed', String(stage.classList.toggle('is-zoomed'))); }
    zoom.addEventListener('click', toggleZoom); image.addEventListener('click', toggleZoom);
    if (images.length < 2) dialog.querySelectorAll('.ep-lightbox-prev,.ep-lightbox-next').forEach(button => button.hidden = true);
})();
