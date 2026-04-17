/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

/*global byteConvert*/

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/cookies',
    'mage/translate',
    'varien/js',
    'Plumrocket_RMA/js/lib/uppy.min'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('prrma.uploader', {

        /**
         *
         * @private
         */
        _create: function () {
            let self = this,
                uploaderElement = '#' + this.options.fieldName + '-uploader',
                targetElement = this.element.find('.fileinput-button.form-buttons')[0],
                uploadUrl = $(uploaderElement).attr('data-url'),
                progressTmpl = mageTemplate('[data-template="' + this.options.fieldName + '-uploader"]'),
                formKey = window.FORM_KEY !== undefined ? window.FORM_KEY : $.cookie('form_key'),
                allowedResize = false,
                allowedToResizeExt = ['jpeg', 'jpg', 'png', 'gif'],
                options = {
                    proudlyDisplayPoweredByUppy: false,
                    target: targetElement,
                    hideUploadButton: true,
                    hideRetryButton: true,
                    hideCancelButton: true,
                    inline: true,
                    showRemoveButtonAfterComplete: false,
                    showProgressDetails: false,
                    showSelectedFiles: false,
                    hideProgressAfterFinish: true
                };

            $(document).on('click', uploaderElement ,function (e) {
                e.preventDefault();
                $(uploaderElement).closest('.fileinput-button.form-buttons')
                    .find('.uppy-Dashboard-browse')
                    .trigger('click');
            });

            const uppy = new Uppy.Uppy({
                autoProceed: true,

                onBeforeFileAdded: (currentFile, files) => {
                    if (self.element.find('.file-row').length >= self.options.maxFilesCount) {
                        alert({content: $.mage.__('Maximum count of attached files is ' + self.options.maxFilesCount)});
                        return false;
                    }

                    allowedResize = $.inArray(currentFile.extension, allowedToResizeExt) !== -1;
                    let fileId = Math.random().toString(33).substr(2, 18),
                        fileSize = typeof currentFile.size == 'undefined'
                            ? $.mage.__('We could not detect a size.')
                            : byteConvert(currentFile.size);

                    let tmpl = progressTmpl({
                        data: {
                            name: currentFile.name,
                            size: fileSize,
                            id: fileId
                        }
                    });

                    const modifiedFile = {
                        ...currentFile,
                        id:  currentFile.id + '-' + fileId,
                        tempFileId:  fileId
                    };

                    $(tmpl).appendTo(self.element);

                    return modifiedFile;
                },

                meta: {
                    'form_key': formKey,
                    isAjax : true
                }
            });

            uppy.use(Uppy.Dashboard, options);

            uppy.use(Uppy.Compressor, {
                maxWidth: this.options.maxWidth,
                maxHeight: this.options.maxHeight,
                quality: 0.92,
                beforeDraw() {
                    if (! allowedResize) {
                        this.abort();
                    }
                }
            });

            uppy.use(Uppy.DropTarget, {
                target: targetElement,
            });

            // upload files on server
            uppy.use(Uppy.XHRUpload, {
                endpoint: uploadUrl,
                fieldName: this.options.fieldName
            });

            uppy.on('upload-success', (file, response) => {
                let progressSelector = '#' + file.tempFileId + ' .progressbar-container .progressbar';
                if (response.body && !response.body.error) {
                    $('#' + file.tempFileId).addClass('done');
                    $(progressSelector).removeClass('upload-progress').addClass('upload-success');
                    // self.element.find('#' + file.tempFileId + ' .file-delete').show();
                    self.element.find('#' + file.tempFileId + ' input.filename').val(response.body.file);
                    self.element.trigger('addItem', response.body);
                } else {
                    var error = $.mage.__('We don\'t recognize or support this file extension type.');

                    if (-1 === response.body.errorcode && response.body.error) {
                        error = response.body.error;
                    }

                    alert({
                        content: error
                    });

                    // $(progressSelector).removeClass('upload-progress').addClass('upload-failure');
                    self.element.find('#' + file.tempFileId).remove();
                }
            })

            uppy.on('upload-progress', (file, progress) => {
                let progressWidth = parseInt(progress.bytesUploaded / progress.bytesTotal * 100, 10),
                    progressSelector = '#' + file.tempFileId + ' .progressbar-container .progressbar';
                self.element.find(progressSelector).css('width', progressWidth + '%');
            });

            uppy.on('upload-error', (error, file) => {
                let progressSelector = '#' + file.tempFileId;

                self.element.find(progressSelector)
                    .removeClass('upload-progress')
                    .addClass('upload-failure')
                    .delay(2000)
                    .hide('highlight')
                    .remove();
            });

            // File delete event.
            this.element.on('click', '.file-delete', function () {
                $(this).closest('.file-row').remove();
                return false;
            });

            // Autofill uploaded files after page refresh.
            if (self.options.fileList) {
                $.each(self.options.fileList, function (index, file) {
                    var tmpl = progressTmpl({
                        data: {
                            name: file.name,
                            size: file.size,
                            id: index,
                            filename: file.filename,
                            rowclass: 'done'
                        }
                    });

                    $(tmpl).appendTo(self.element);
                });
            }
        }
    });

    return $.prrma.uploader;
});
