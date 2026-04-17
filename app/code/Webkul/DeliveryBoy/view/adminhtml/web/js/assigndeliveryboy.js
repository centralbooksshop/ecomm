/**
 * Webkul Software.
 * 
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'mage/translate',
    ],
    function ($, mageTemplate) {
        'use strict';

        $.widget(
            'mage.assigndeliveryboy', {
                options: {},
                _create: function () {
                    var self = this;
                    $("body").on(
                        "click", "#assign",
                        function () {
                            var id = $("body #deliveryboy").val();
                            var orderId = $("body  #orderIncrementId").val();
                            $("body .success_msg").html("");
                            if (id) {
                                jQuery.ajax({
                                    url: self.options.url,
                                    data: {
                                        deliveryboyId: id,
                                        incrementId: orderId
                                    },
                                    type: "POST",
                                    dataType: 'json',
                                    showLoader: true,
                                }).done(
                                    function (result) {
                                        $("body .success_msg").html(self.options.successMessage);
                                        if (result.success) {
                                            setTimeout(window.location.reload(), 4000);
                                        } else {
                                            const message =
                                              "<span style='color:red'>" + result.message + "</span>";
                                            $("body .success_msg").html(message);
                                        }
                                    }
                                );
                            } else {
                                $("body .success_msg").html(self.options.emptyMessage);
                            }
                        }
                    );
                }
            }
        );
        return $.mage.assigndeliveryboy;
    }
);