define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'ko',
    'Iranimij_Badger/js/form/element/rules-form',
    'prototype'
], function (Abstract, $, ko, VarienRulesForm) {
    'use strict';

    return Abstract.extend({
        defaults: {
            formContent: '',
            elementTmpl: 'Iranimij_Badger/form/element/rule-conditions-chooser',
            newFormChildUrl: '',
            conditionsFormId: 'iranimij_badger_conditions_fieldset'
        },

        getFormContent: function () {
            return this.formContent;
        },

        initForm: function () {
            window[this.conditionsFormId] = new VarienRulesForm(this.conditionsFormId, this.newFormChildUrl);
            this.processRuleFormChange();
        },

        processRuleFormChange: function () {
            var formValue     = $('#' + this.conditionsFormId).serializeArray(),
                parsedValue   = this.parseFormValue(formValue);

            this.value(parsedValue);
        },

        /**
         * Convert jQuery.serializeArray() result to a nested object mirroring PHP's
         * bracket-notation array parsing, e.g.:
         *   rule[conditions][1][type] -> {rule:{conditions:{1:{type:value}}}}
         */
        parseFormValue: function (formValue) {
            var result = {};

            formValue.forEach(function (part) {
                var flatKey  = part['name'],
                    value    = part['value'],
                    keyParts = flatKey.split('[').map(function (raw) {
                        return raw === ']' ? '[]' : raw.replace(/]$/, '');
                    });

                this.setValue(result, keyParts.reverse(), value);
            }.bind(this));

            return result;
        },

        setValue: function (object, keysPathParts, value) {
            var key, nextKey, newObject;

            if (keysPathParts.length > 0) {
                key = keysPathParts.pop();

                if (keysPathParts.length === 0) {
                    if (object instanceof Array) {
                        object.push(value);
                    } else {
                        object[key] = value;
                    }
                } else {
                    nextKey   = keysPathParts.pop();
                    newObject = nextKey === '[]' ? [] : {};
                    newObject = object[key] || newObject;
                    keysPathParts.push(nextKey);
                    object[key] = newObject;
                    this.setValue(newObject, keysPathParts, value);
                }
            }
        }
    });
});
