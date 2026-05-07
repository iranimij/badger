define([], function () {
    'use strict';

    function findTooltipHost() {
        return document.querySelector('.iranimij-badger-tooltip');
    }

    function ensureBubble() {
        var bubble = document.querySelector('.iranimij-badger-tooltip__bubble');
        if (!bubble) {
            bubble = document.createElement('div');
            bubble.className = 'iranimij-badger-tooltip__bubble';
            bubble.style.position = 'fixed';
            bubble.style.display = 'none';
            bubble.style.padding = '6px 10px';
            bubble.style.borderRadius = '4px';
            bubble.style.fontSize = '12px';
            bubble.style.pointerEvents = 'none';
            bubble.style.zIndex = '9999';
            document.body.appendChild(bubble);
        }
        return bubble;
    }

    return function () {
        var host = findTooltipHost();
        if (!host) {
            return;
        }
        var bg = host.dataset.bg || '#222222';
        var fg = host.dataset.fg || '#ffffff';
        var bubble = ensureBubble();
        bubble.style.background = bg;
        bubble.style.color = fg;

        document.addEventListener('mouseover', function (e) {
            var badger = e.target.closest('.iranimij-badger');
            if (!badger) {
                return;
            }
            var text = badger.dataset.tooltip;
            if (!text) {
                return;
            }
            bubble.textContent = text;
            bubble.style.display = 'block';
        });

        document.addEventListener('mousemove', function (e) {
            if (bubble.style.display !== 'block') {
                return;
            }
            bubble.style.left = (e.clientX + 12) + 'px';
            bubble.style.top = (e.clientY + 12) + 'px';
        });

        document.addEventListener('mouseout', function (e) {
            if (e.target.closest('.iranimij-badger')) {
                bubble.style.display = 'none';
            }
        });
    };
});
