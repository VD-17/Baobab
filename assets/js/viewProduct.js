document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('#images img');
    // const mainVideo = document.getElementById('main-video');
    const thumbnails = document.querySelectorAll('.small-img');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            mainImage.src = this.src;
            mainImage.alt = this.alt;
        });
    });
});