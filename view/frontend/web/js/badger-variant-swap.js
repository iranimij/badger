define(['jquery'], function ($) {
    'use strict';

    function refreshFor(productId) {
        const stacks = document.querySelectorAll(
            '.iranimij-badger-stack[data-product-id="' + productId + '"]'
        );
        stacks.forEach(function (stack) {
            stack.classList.add('iranimij-badger-stack--swapping');
            stack.dispatchEvent(new CustomEvent('iranimij:badger:refresh', { bubbles: true }));
        });
    }

    return function () {
        $(document).on('changeProduct', function (e, data) {
            if (data && data.productId) {
                refreshFor(String(data.productId));
            }
        });
    };
});
