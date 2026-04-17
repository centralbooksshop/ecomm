/*browser:true*/
/*global define*/
define([
  "Magento_Checkout/js/view/payment/default",
  "Magento_Checkout/js/model/quote",
  "Magento_Customer/js/model/customer",
  "jquery",
  "mage/url",
], function (Component, quote, customer, setPaymentMethod) {
  "use strict";

  return Component.extend({
    defaults: {
      template: "PayUIndia_Payu/payment/payu",
    },

    preparePayment: function () {
      var email;

      if (!customer.isLoggedIn()) {
        email = quote.guestEmail;
      }
      console.log(email);
      jQuery(function ($) {
        $.ajax({
          url: window.checkoutConfig.payment.payu.redirectUrl,
          type: "POST",
          dataType: "json",
          cache: false,
          data: {
            email: email,
          },
          showLoader: true,
          success: function (data) {
            if (data["error"]) {
              alert(data["error"]);
            }
            $("#payuloader", parent.document).html(data["html"]);
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(
              thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText
            );
          },
        });
      });
    },

    redirectAfterPlaceOrder: false,

    afterPlaceOrder: function () {
      //do nothing
    },
  });
});
