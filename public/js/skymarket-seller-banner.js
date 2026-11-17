(function () {
    'use strict';
    if (window.skyMarketSellerBanner) return;
    window.skyMarketSellerBanner = true;
    var frame;
    function positionBanners() {
        document.querySelectorAll('[data-sm-seller-banner]').forEach(function (banner) {
            var grid = banner.closest('.sm-reference-grid');
            if (!grid) return;
            var products = Array.from(grid.querySelectorAll(':scope > .col'));
            if (!products.length) return;
            var style = getComputedStyle(grid);
            var columns = style.display === 'grid' ? style.gridTemplateColumns.trim().split(/\s+/).length : 1;
            var categories = grid.querySelector('.sm-first-row-categories');
            var categoryUsesSlot = categories && getComputedStyle(categories).display !== 'none'
                && categories.getBoundingClientRect().width <= products[0].getBoundingClientRect().width + 1;
            var count = Math.min(products.length, columns * 2 - (categoryUsesSlot ? 1 : 0));
            var lastProductInSecondRow = products[count - 1];
            if (lastProductInSecondRow && lastProductInSecondRow.nextElementSibling !== banner) {
                lastProductInSecondRow.after(banner);
            }
        });
    }
    function schedule() {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(positionBanners);
    }
    function start() {
        positionBanners();
        var content = document.getElementById('main-content');
        if (content) new MutationObserver(schedule).observe(content, {childList: true, subtree: true, attributes: true, attributeFilter: ['class']});
        window.addEventListener('resize', schedule);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
