define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Retailinsights_Orders/column/acknowledgement'
        },

        /**
         * Open file upload modal
         */
        uploadFile: function(itemId) {
            var self = this;
            var content = '<input type="file" id="ackFileUpload"/>';
			alert('rrr');
            
            var options = {
                title: 'Upload Acknowledgement',
                type: 'popup',
                modalClass: 'acknowledgement-upload-popup',
                buttons: [{
                    text: 'Upload',
                    class: 'action-primary',
                    click: function () {
                        var fileInput = $('#ackFileUpload')[0];
                        if(fileInput.files.length === 0){
                            alert('Please select a file!');
                            return;
                        }
                        var formData = new FormData();
                        formData.append('file', fileInput.files[0]);
                        formData.append('item_id', itemId);

                        $.ajax({
                            url: '/cbsadmin/retailinsights_admin/orders/upload', // your controller path
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (res) {
                                alert('File uploaded successfully!');
                                location.reload(); // refresh grid
                            },
                            error: function () {
                                alert('Error uploading file!');
                            }
                        });
                        this.closeModal();
                    }
                },{
                    text: 'Cancel',
                    class: 'action-secondary',
                    click: function () { this.closeModal(); }
                }]
            };

            var popup = $('<div/>').html(content).modal(options);
            popup.modal('openModal');
        }
    });
});
