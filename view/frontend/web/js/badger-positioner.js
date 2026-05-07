define([], function () {
    'use strict';

    function ensurePositioned(stack) {
        // The CSS :has() rule handles category pages via product-item-info.
        // This JS fallback ensures the nearest block positioned ancestor is set
        // for edge cases (widgets, cart cross-sell) where CSS :has() may not apply.
        var itemInfo = stack.closest('.product-item-info, .item.product');
        if (itemInfo) {
            if (window.getComputedStyle(itemInfo).position === 'static') {
                itemInfo.style.position = 'relative';
                itemInfo.style.overflow = 'hidden';
            }
            return;
        }

        // PDP: stack is already inside product.info.media which is position:relative
        var parent = stack.parentElement;
        if (parent && window.getComputedStyle(parent).position === 'static') {
            parent.style.position = 'relative';
        }
    }

    function init() {
        document.querySelectorAll('.iranimij-badger-stack').forEach(ensurePositioned);
    }

    function setup() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, {once: true});
        } else {
            init();
        }
        window.addEventListener('load', init, {once: true});
        document.addEventListener('contentUpdated', init);
    }

    setup();

    return setup;
});
