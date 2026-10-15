(function ($) {
    'use strict';
    if (!$ || !$.fn.dataTable) return;
    $.extend(true, $.fn.dataTable.defaults, { pagingType: 'simple_numbers' });
    function stylePagination() {
        document.querySelectorAll('.dataTables_paginate, .dt-paging').forEach(function (pager) {
            ['previous', 'next', 'first', 'last'].forEach(function (direction) {
                pager.querySelectorAll('.' + direction).forEach(function (control) {
                    var link = control.matches('a,button') ? control : control.querySelector('a,button');
                    if (!link) return;
                    var left = direction === 'previous' || direction === 'first';
                    var path = left ? 'M10 4 6 8l4 4' : 'm6 4 4 4-4 4';
                    var extra = direction === 'first' ? '<path d="M3 4v8"/>' : direction === 'last' ? '<path d="M13 4v8"/>' : '';
                    link.innerHTML = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + path + '"/>' + extra + '</svg>';
                    link.setAttribute('aria-label', direction.charAt(0).toUpperCase() + direction.slice(1) + ' page');
                });
            });
        });
    }
    $(document).on('init.dt draw.dt', stylePagination);
    $(stylePagination);
})(window.jQuery);
