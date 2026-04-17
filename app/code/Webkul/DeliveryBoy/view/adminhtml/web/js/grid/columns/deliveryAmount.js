define([
  'Magento_Ui/js/grid/columns/column',
  'jquery',
  'mage/template',
  'mage/validation',
  'text!Webkul_DeliveryBoy/template/grid/cells/deliveryAmount.html',
  'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, validation, deliveryAmountTemplate) {
  'use strict';

  return Column.extend({
    defaults: {
    bodyTmpl: 'ui/grid/cells/html',
    fieldClass: {
      'data-grid-html-cell': true
    }
  },
  gethtml: function (row) { return row['html']; },
  getFormaction: function (row) { return row['formaction']; },
  getFormkey: function (row) { return row['formkry']; },
  getId: function (row) { return row['id']; },
  getLabel: function (row) { return row['html'] },
  getTitle: function (row) { return row['title'] },
  getIncrementId: function (row) { return row['incrementid'] },
  getPackageItems: function (row) { return row['packageitems'] ?? "" },
  getDeliveryAmount: function (row) { return row['deliveryamount'] },
  getComments: function (row) { return row['comments'] },
  getPriceforcover: function (row) { return row['priceforcover'] },
  getPriceforbox: function (row) { return row['priceforbox'] },
  preview: function (row) {
  var modalHtml = mageTemplate(
   deliveryAmountTemplate,
   {
     html: this.gethtml(row),
     title: this.getTitle(row),
     label: this.getLabel(row),
     formaction: this.getFormaction(row),
     formakey: this.getFormkey(row),
     incrementid: this.getIncrementId(row),
     noofcovers: parseInt(this.getPackageItems(row).split(",")[0]),
     noofboxes: parseInt(this.getPackageItems(row).split(",")[1]),
     deliveryamount: this.getDeliveryAmount(row),
     comments: this.getComments(row),
     priceforcover: this.getPriceforcover(row),
     priceforbox: this.getPriceforbox(row),
     id: this.getId(row)
   }
  );
  var previewPopup = $('<div/>').html(modalHtml);
  previewPopup.modal({
    title: $.mage.__( this.getTitle(row)),
    innerScroll: true,
    modalClass: '_email-box',
    buttons: [{
      type:'submit',
      text: $.mage.__('Update'),
      class: 'action close-popup wide',
      click: function () {
        $("form").validation().submit();
     }}
    ]}).trigger('openModal');
   },
    getFieldHandler: function (row) {
       return this.preview.bind(this, row);
    }
  });
});

function calculateDelivery(id) {
  var priceforcover = document.getElementById("priceforcover").value;
  var priceforbox = document.getElementById("priceforbox").value;
  var noofcovers = document.getElementById("noofcovers-"+id).value;
  var noofboxes = document.getElementById("noofboxes-"+id).value;
  document.getElementById("delivery_amount-"+id).value = noofcovers * priceforcover + noofboxes * priceforbox;
}