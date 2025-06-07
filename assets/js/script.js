document.addEventListener('DOMContentLoaded', () => {
    // Section 1 auto-scroll
    const carousel = document.querySelector('#category-carousel');
    if (carousel) {
        const categoryItems = document.querySelectorAll('.cat');
        const itemWidth = categoryItems[0].offsetWidth + 20;
        let autoScroll;
        function startAutoScroll() {
            autoScroll = setInterval(() => {
                if (carousel.scrollLeft >= carousel.scrollWidth - carousel.clientWidth) {
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    carousel.scrollBy({ left: itemWidth * 3, behavior: 'smooth' });
                }
            }, 5000);
        }
        function stopAutoScroll() {
            clearInterval(autoScroll);
        }
        carousel.addEventListener('mouseenter', stopAutoScroll);
        carousel.addEventListener('mouseleave', startAutoScroll);
        startAutoScroll();
    }

    // Section 5 auto-scroll
    const reviewContainer = document.querySelector('#reviews');
    if (reviewContainer) {
        const reviewItems = document.querySelectorAll('#reviews .review-item');
        const reviewWidth = reviewItems[0].offsetWidth + 20;
        let reviewAutoScroll;
        function startReviewAutoScroll() {
            reviewAutoScroll = setInterval(() => {
                if (reviewContainer.scrollLeft >= reviewContainer.scrollWidth - reviewContainer.clientWidth) {
                    reviewContainer.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    reviewContainer.scrollBy({ left: reviewWidth * 2, behavior: 'smooth' });
                }
            }, 5000);
        }
        function stopReviewAutoScroll() {
            clearInterval(reviewAutoScroll);
        }
        reviewContainer.addEventListener('mouseenter', stopReviewAutoScroll);
        reviewContainer.addEventListener('mouseleave', startReviewAutoScroll);
        startReviewAutoScroll();
    }

    // Section 6 accordion functionality
    const accordions = document.querySelectorAll('#faq .accordion');
    accordions.forEach((accordion, index) => {
        // Add unique IDs for accessibility
        const panel = accordion.nextElementSibling;
        panel.id = `panel${index + 1}`;
        accordion.setAttribute('aria-controls', `panel${index + 1}`);
        accordion.setAttribute('aria-expanded', 'false');

        accordion.addEventListener('click', () => {
            const isActive = accordion.classList.contains('active');

            // Close all panels
            accordions.forEach(acc => {
                acc.classList.remove('active');
                acc.setAttribute('aria-expanded', 'false');
                const p = acc.nextElementSibling;
                p.classList.remove('active');
                p.style.maxHeight = null;
            });

            // Toggle current panel
            if (!isActive) {
                accordion.classList.add('active');
                accordion.setAttribute('aria-expanded', 'true');
                panel.classList.add('active');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        });
    });
});