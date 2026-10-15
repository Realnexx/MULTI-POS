(function () {
    'use strict';
    var states = new WeakMap();
    var skeleton = '<div class="admin-skeleton" aria-hidden="true"><div class="admin-skeleton-bar admin-skeleton-heading"></div><div class="admin-skeleton-bar admin-skeleton-subtitle"></div><div class="admin-skeleton-cards">' + Array(3).fill('<div class="admin-skeleton-card"><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div></div>').join('') + '</div>' + Array(5).fill('<div class="admin-skeleton-row"><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div></div>').join('') + '</div>';
    document.documentElement.classList.add('admin-booting');
    function finishBoot() {
        document.documentElement.classList.remove('admin-booting');
    }
    // Fail open if another page script or an external dependency fails to initialize.
    var bootTimer = setTimeout(finishBoot, 12000);
    document.addEventListener('DOMContentLoaded', function () {
        clearTimeout(bootTimer);
        finishBoot();
    });
    window.addEventListener('pageshow', finishBoot);
    function begin(target) {
        var host = target || document.querySelector('.modal.show .modal-body') || document.querySelector('.page-content-area');
        if (!host) return function () {};
        var state = states.get(host);
        if (!state) {
            state = { count: 0, previousBusy: host.getAttribute('aria-busy') };
            states.set(host, state);
        }
        if (!state.count) state.previousBusy = host.getAttribute('aria-busy');
        if (!host.querySelector(':scope > .admin-skeleton')) host.insertAdjacentHTML('beforeend', skeleton);
        host.classList.add('admin-loading-host', 'is-data-loading');
        host.setAttribute('aria-busy', 'true');
        state.count++;
        var done = false;
        var timer = setTimeout(end, 15000);
        function end() {
            if (done) return;
            done = true;
            clearTimeout(timer);
            state.count = Math.max(0, state.count - 1);
            if (!state.count) {
                host.classList.remove('is-data-loading');
                if (state.previousBusy === null) host.removeAttribute('aria-busy');
                else host.setAttribute('aria-busy', state.previousBusy);
            }
        }
        return end;
    }
    function isRead(url, method) {
        try {
            var parsed = new URL(url || location.href, location.href);
            if (parsed.origin !== location.origin) return false;
            // Quiet background checks must not interrupt the page being used.
            if (/notification|heartbeat|keepalive|pos_pin/i.test(parsed.href)) return false;
            return /^(GET|HEAD)$/i.test(method || 'GET');
        } catch (_) { return false; }
    }
    window.AdminLoading = { begin: begin };
    if (window.jQuery) {
        // Registered in the head so page-specific ready handlers are covered too.
        window.jQuery.ajaxPrefilter(function (options, original, xhr) {
            if (options.global === false || options.skeleton === false || !isRead(options.url, options.type)) return;
            var end = begin(options.loadingTarget ? document.querySelector(options.loadingTarget) : null);
            xhr.always(end);
        });
        window.jQuery(document).on('processing.dt', function (event, settings, processing) {
            if (!settings || !settings.nTableWrapper) return;
            if (processing && !settings.adminSkeletonEnd) settings.adminSkeletonEnd = begin(settings.nTableWrapper);
            if (!processing && settings.adminSkeletonEnd) {
                settings.adminSkeletonEnd();
                settings.adminSkeletonEnd = null;
            }
        });
    }
    if (window.fetch) {
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var url = typeof input === 'string' || input instanceof URL ? input : input.url;
            var method = init && init.method || input && input.method || 'GET';
            if (!isRead(url, method) || init && init.skeleton === false) return originalFetch.apply(this, arguments);
            var end = begin();
            try {
                return originalFetch.apply(this, arguments).then(function (response) { end(); return response; }, function (error) { end(); throw error; });
            } catch (error) { end(); throw error; }
        };
    }
})();
