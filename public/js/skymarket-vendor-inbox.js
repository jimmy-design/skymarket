(function () {
    'use strict';
    function start() {
        if (!window.jQuery || !window.jQuery.fn.dataTable || window.skyMarketVendorInbox) return;
        window.skyMarketVendorInbox = true;
        function refresh() {
            if (document.hidden || document.querySelector('.modal.show')) return;
            window.jQuery.fn.dataTable.tables({api: true}).tables().every(function () {
                var settings = this.settings()[0];
                if (settings.ajax && (!settings.jqXHR || settings.jqXHR.readyState === 4)) this.ajax.reload(null, false);
            });
        }
        var timer = setInterval(refresh, 10000);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });
        window.addEventListener('pagehide', function () { clearInterval(timer); });
        window.addEventListener('pageshow', function (event) { if (event.persisted) { timer = setInterval(refresh, 10000); refresh(); } });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
