document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('fullscreen-overlay');
    const fullscreenImage = document.getElementById('fullscreen-image');
    const fullscreenCaption = document.getElementById('fullscreen-caption');
    const viewWebsiteButton = document.getElementById('view-website-button');

    document.querySelectorAll('.portfolio-thumb').forEach(item => {
        item.addEventListener('click', function() {
            const bigImageSrc = this.getAttribute('data-big-image');
            const link = this.getAttribute('data-link');
            const caption = this.getAttribute('data-caption');

            fullscreenImage.src = bigImageSrc;
            fullscreenImage.setAttribute('data-link', link);
            fullscreenCaption.innerText = caption;

            overlay.style.display = 'flex';
        });
    });

    viewWebsiteButton.addEventListener('click', function() {
        const link = fullscreenImage.getAttribute('data-link');
        window.open(link, '_blank');
    });

    overlay.addEventListener('click', function(event) {
        if (event.target !== fullscreenImage && event.target !== viewWebsiteButton) {
            overlay.style.display = 'none';
        }
    });
});
