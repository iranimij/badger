define(['Magento_Ui/js/dynamic-rows/record'], function (Record) {
    'use strict';

    var IMAGE_SHAPE = 1;
    var IMAGE_FIELDS = ['image_path', 'alt_text'];
    var TEXT_FIELDS  = ['label_text', 'bg_color', 'text_color'];

    return Record.extend({
        /**
         * initElement fires for each child as it initialises.
         * shape_kind (sortOrder 20) comes before label_text/bg_color/text_color/image_path
         * (sortOrder 50–70), so we must handle two cases:
         *   1. shape_kind initialises first  → store the observable, schedule a deferred run
         *   2. dependent fields initialise   → apply the already-known shape value immediately
         */
        initElement: function (child) {
            this._super(child);

            if (child.index === 'shape_kind') {
                this._shapeObs = child.value;       // store the KO observable
                child.on('value', this.onShapeChange.bind(this));
                // Deferred: all siblings should be initialised by then
                setTimeout(this.onShapeChange.bind(this, child.value()), 0);
                return this;
            }

            // If shape is already known when a dependent field initialises, apply immediately
            if (this._shapeObs) {
                var isImage = parseInt(this._shapeObs(), 10) === IMAGE_SHAPE;
                if (IMAGE_FIELDS.indexOf(child.index) !== -1) {
                    child.visible(isImage);
                } else if (TEXT_FIELDS.indexOf(child.index) !== -1) {
                    child.visible(!isImage);
                }
            }

            return this;
        },

        onShapeChange: function (value) {
            var isImage = parseInt(value, 10) === IMAGE_SHAPE;

            IMAGE_FIELDS.forEach(function (name) {
                var child = this.getChild(name);
                if (child) { child.visible(isImage); }
            }, this);

            TEXT_FIELDS.forEach(function (name) {
                var child = this.getChild(name);
                if (child) { child.visible(!isImage); }
            }, this);
        }
    });
});
