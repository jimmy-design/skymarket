(function () {
    'use strict';
    function fallback(image) {
        if (!image || !image.matches || !image.matches('img[data-sm-image-fallback]')) return;
        var url = image.dataset.smImageFallback;
        delete image.dataset.smImageFallback;
        if (url && image.src !== url) image.src = url;
    }
    document.addEventListener('error', function (event) { fallback(event.target); }, true);
    document.querySelectorAll('img[data-sm-image-fallback]').forEach(function (image) {
        if (image.complete && image.naturalWidth === 0) fallback(image);
    });
})();
