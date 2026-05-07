define([
    'jquery',
    'Magento_Rule/rules',
    'prototype'
], function (jQuery, Rules) {
    'use strict';

    return Class.create(Rules, {
        removeRuleEntry: function ($super, container, event) {
            $super(container, event);
            this.getCurrentForm().trigger('change');
        },

        showParamInputField: function ($super, container, event) {
            var result = $super(container, event);

            if (result !== false) {
                this.getCurrentForm().trigger('change');
            }
        },

        showChooserElement: function ($super, chooser) {
            $super(chooser);
            jQuery(chooser).on('click', function () {
                this.getCurrentForm().trigger('change');
            }.bind(this));
        },

        getCurrentForm: function () {
            return jQuery(this.parent);
        }
    });
});
