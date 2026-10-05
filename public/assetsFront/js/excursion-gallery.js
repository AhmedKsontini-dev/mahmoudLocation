const gallery = document.querySelector('[data-excursion-gallery]');
const modal = document.querySelector('[data-gallery-modal]');

if (gallery && modal) {
    const images = JSON.parse(gallery.dataset.galleryImages);
    const modalImage = modal.querySelector('[data-gallery-image]');
    const caption = modal.querySelector('[data-gallery-caption]');
    const count = modal.querySelector('[data-gallery-count]');
    const dialog = modal.querySelector('[data-gallery-dialog]');
    let currentIndex = 0;
    let previouslyFocused = null;

    const showImage = (index) => {
        currentIndex = (index + images.length) % images.length;
        const image = images[currentIndex];

        modalImage.src = image.src;
        modalImage.alt = image.alt || '';
        caption.textContent = image.caption || '';
        caption.hidden = !image.caption;
        count.textContent = `${currentIndex + 1} / ${images.length}`;
    };

    const openModal = (index) => {
        previouslyFocused = document.activeElement;
        showImage(index);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        modal.querySelector('[data-gallery-close]').focus();
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        previouslyFocused?.focus();
    };

    gallery.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-gallery-trigger]');
        if (!trigger) return;

        const source = new URL(trigger.dataset.gallerySrc || trigger.src, document.baseURI).href;
        const index = images.findIndex((image) => new URL(image.src, document.baseURI).href === source);
        if (index !== -1) openModal(index);
    });

    gallery.addEventListener('keydown', (event) => {
        if ((event.key !== 'Enter' && event.key !== ' ') || !event.target.matches('[data-gallery-trigger]')) return;

        event.preventDefault();
        const source = new URL(event.target.dataset.gallerySrc || event.target.src, document.baseURI).href;
        const index = images.findIndex((image) => new URL(image.src, document.baseURI).href === source);
        if (index !== -1) openModal(index);
    });

    modal.querySelector('[data-gallery-close]').addEventListener('click', closeModal);
    modal.querySelector('[data-gallery-previous]').addEventListener('click', () => showImage(currentIndex - 1));
    modal.querySelector('[data-gallery-next]').addEventListener('click', () => showImage(currentIndex + 1));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (modal.classList.contains('hidden')) return;

        if (event.key === 'Escape') closeModal();
        if (event.key === 'ArrowLeft') showImage(currentIndex - 1);
        if (event.key === 'ArrowRight') showImage(currentIndex + 1);
    });
}