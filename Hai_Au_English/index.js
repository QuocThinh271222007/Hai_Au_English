const faqItems = document.querySelectorAll('.faq-item');

faqItems.forEach((item) => {
    const button = item.querySelector('.faq-question');
    button.addEventListener('click', () => {
        // Toggle the active class on the clicked item
        item.classList.toggle('active');
        
        // Close all other items
        faqItems.forEach(i => {
            if (i !== item) {
                i.classList.remove('active');
            }
        });
    });
});