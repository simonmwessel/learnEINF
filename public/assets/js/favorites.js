// Favorite star icon functionality.
document.addEventListener('DOMContentLoaded', function() {
    // Load favorites from localStorage or initialize as empty array.
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    // Initialize star icons based on favorites and add toggle functionality.
    const starIcons = document.querySelectorAll('.favorite-star');
    starIcons.forEach(icon => {
        const qid = icon.getAttribute('data-qid');
        if (favorites.includes(qid)) {
            icon.classList.remove('bi-star');
            icon.classList.add('bi-star-fill');
        }
        icon.addEventListener('click', function(e) {
            // Prevent accordion toggle on star click.
            e.stopPropagation();
            e.preventDefault();

            const currentQid = icon.getAttribute('data-qid');
            if (favorites.includes(currentQid)) {
                favorites = favorites.filter(id => id !== currentQid);
                icon.classList.remove('bi-star-fill');
                icon.classList.add('bi-star');
            } else {
                favorites.push(currentQid);
                icon.classList.remove('bi-star');
                icon.classList.add('bi-star-fill');
            }
            localStorage.setItem('favorites', JSON.stringify(favorites));
        });
    });
});
