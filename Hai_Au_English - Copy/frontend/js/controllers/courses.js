// Courses page logic: filtering and FAQ toggles
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const courseCards = document.querySelectorAll('.course-card');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');
            courseCards.forEach(card => {
                if (filter === 'all') { card.classList.remove('hide'); card.classList.add('show'); }
                else { const category = card.getAttribute('data-category'); if (category === filter) { card.classList.remove('hide'); card.classList.add('show'); } else { card.classList.add('hide'); card.classList.remove('show'); } }
            });
        });
    });

    // FAQ functionality
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question?.addEventListener('click', function() {
            faqItems.forEach(otherItem => { if (otherItem !== item) otherItem.classList.remove('active'); });
            item.classList.toggle('active');
        });
    });
});


