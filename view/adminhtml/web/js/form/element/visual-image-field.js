/**
 * Image-path field for a badger visual row.
 *
 * Stores a plain path string (e.g. "iranimij/badger/user/foo.png") as its value.
 * Provides:
 *  - drag-and-drop / click upload → AJAX to the Upload controller
 *  - a predefined-images gallery modal
 *  - live preview of the current image
 *  - visibility bound to the sibling shape_kind field (shown only when IMAGE=1)
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'ko',
    'mage/translate',
    'mage/backend/notification'
], function (Abstract, $, ko, $t) {
    'use strict';

    var IMAGE_KIND = 1; // ShapeKind::IMAGE

    return Abstract.extend({
        defaults: {
            template: 'Iranimij_Badger/form/element/visual-image-field',

            // Injected by the form XML (resolved server-side)
            uploadUrl: '',
            predefinedUrl: '',
            mediaBaseUrl: '',

            // Internal state defaults (made observable in initObservable)
            previewSrc: '',
            isUploading: false,
            showGallery: false,
            predefinedImages: [],
            galleryLoaded: false,
            shapeKind: null,

            listens: {
                value: 'onValueChange',
                shapeKind: 'onShapeKindChange'
            },

            imports: {
                shapeKind: '${ $.parentName }.shape_kind:value'
            }
        },

        initialize: function () {
            this._super();
            this.visible(false); // hidden until shape_kind is known
            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(['previewSrc', 'isUploading', 'showGallery', 'predefinedImages']);
            return this;
        },

        onValueChange: function (path) {
            if (!path) {
                this.previewSrc('');
            } else if (/^https?:\/\//.test(path)) {
                this.previewSrc(path);
            } else {
                this.previewSrc(this.mediaBaseUrl.replace(/\/$/, '') + '/' + path.replace(/^\//, ''));
            }
        },

        onShapeKindChange: function (kind) {
            this.visible(Number(kind) === IMAGE_KIND);
        },

        // ── Upload ──────────────────────────────────────────────────────────

        onFileInputChange: function (component, event) {
            var file = event.target.files && event.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        },

        uploadFile: function (file) {
            var self = this,
                formData = new FormData();

            formData.append('image', file);
            formData.append('form_key', window.FORM_KEY);

            self.isUploading(true);
            $.ajax({
                url: self.uploadUrl,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json'
            }).done(function (resp) {
                if (resp.error) {
                    self.addError(resp.error);
                } else {
                    self.value(resp.path);
                    self.previewSrc(resp.url);
                }
            }).fail(function () {
                self.addError($t('Upload failed. Please try again.'));
            }).always(function () {
                self.isUploading(false);
            });
        },

        triggerFileInput: function () {
            $(document).find('[data-role="badger-file-input-' + this.uid + '"]').trigger('click');
        },

        removeImage: function () {
            this.value('');
            this.previewSrc('');
        },

        // ── Predefined gallery ───────────────────────────────────────────────

        openGallery: function () {
            var self = this;
            if (!self.galleryLoaded && self.predefinedUrl) {
                $.getJSON(self.predefinedUrl, {form_key: window.FORM_KEY}).done(function (resp) {
                    if (Array.isArray(resp.images)) {
                        self.predefinedImages(resp.images);
                    }
                    self.galleryLoaded = true;
                    self.showGallery(true);
                }).fail(function () {
                    self.showGallery(true); // show even if empty
                });
            } else {
                self.showGallery(!self.showGallery());
            }
        },

        selectPredefined: function (image) {
            this.value(image.path);
            this.previewSrc(image.url);
            this.showGallery(false);
        },

        closeGallery: function () {
            this.showGallery(false);
        },

        // ── Drag-and-drop ────────────────────────────────────────────────────

        onDragOver: function (component, event) {
            event.preventDefault();
            $(event.currentTarget).addClass('_drag-over');
        },

        onDragLeave: function (component, event) {
            $(event.currentTarget).removeClass('_drag-over');
        },

        onDrop: function (component, event) {
            event.preventDefault();
            $(event.currentTarget).removeClass('_drag-over');
            var file = event.originalEvent.dataTransfer.files[0];
            if (file) {
                this.uploadFile(file);
            }
        },

        addError: function (msg) {
            this.error(msg);
            this.bubble('error', msg);
        }
    });
});
