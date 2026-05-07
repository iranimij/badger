define([], function () {
    'use strict';

    function ensurePositioned(stack) {
        var parent = stack.parentElement;
        if (!parent) { return; }

        // If a previous positioner version moved the stack into an inline anchor,
        // undo that: clear inline overflow/position, move stack to product-item-info level.
        if (parent.tagName === 'A') {
            parent.style.overflow = '';
            parent.style.position = '';
            var itemInfo = parent.closest('.product-item-info, .item.product');
            if (itemInfo) {
                itemInfo.style.position = 'relative';
                itemInfo.style.overflow = 'hidden';
                itemInfo.appendChild(stack);
            }
            return;
        }

        // Normal case: CSS :has() makes .product-item-details static so the stack
        // is positioned relative to .product-item-info. Just ensure it's positioned.
        var itemInfoEl = stack.closest('.product-item-info, .item.product');
        if (itemInfoEl) {
            if (window.getComputedStyle(itemInfoEl).position === 'static') {
                itemInfoEl.style.position = 'relative';
                itemInfoEl.style.overflow = 'hidden';
            }
            return;
        }

        // PDP: stack is inside product.info.media (already position:relative typically)
        if (window.getComputedStyle(parent).position === 'static') {
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
