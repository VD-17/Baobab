document.addEventListener('DOMContentLoaded', () => {
    const accordions = document.querySelectorAll('#faq .accordion');
    if (!accordions.length) {
        console.error('No accordion elements found. Check #faq in DOM.');
        return;
    }
    accordions.forEach((accordion, index) => {
        const panel = accordion.nextElementSibling;
        panel.id = `panel${index + 1}`;
        accordion.setAttribute('aria-controls', `panel${index + 1}`);
        accordion.setAttribute('aria-expanded', 'false');

        accordion.addEventListener('click', () => {
            const isActive = accordion.classList.contains('active');
            accordions.forEach(acc => {
                acc.classList.remove('active');
                acc.setAttribute('aria-expanded', 'false');
                const p = acc.nextElementSibling;
                p.classList.remove('active');
                p.style.maxHeight = null;
            });
            if (!isActive) {
                accordion.classList.add('active');
                accordion.setAttribute('aria-expanded', 'true');
                panel.classList.add('active');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        });
    });
});