define(['jquery', 'Magento_Ui/js/modal/modal'], function ($,modal) {
    'use strict';

    return function (config) { 
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Cancel Order',
            buttons: [{
                text: $.mage.__('GO BACK'),
                class: 'primary',
                click: function () {
                    this.closeModal();
                }},{
                text: $.mage.__('OK'),
                class: 'block-footer',
                click: function () {
                    cancelOrderOnClick();
                    this.closeModal();
                }
            }]
        };
        
        $("#cancel"+config.referenceNumber).on('click',function(){ 
            $('#popup-modal').css("display","block");
            $('#popup-modal').modal(options).modal('openModal');
            $(".action-close").css("border",0);
            $('.action-close').css("outline",0);
            $('.action-close').css("padding","20px");
            $('.modal-content').css("border",0);
            $('.modal-content').css("padding-top", "10px");
            $('.block-footer').css("background-color", "#007bdb");
            $('.block-footer').css("color","white");
            $(".modal-footer").css("border-top","0");
            $(".modal-popup._inner-scroll .modal-inner-wrap").css("width", 500);
            $(".modal-popup._inner-scroll .modal-inner-wrap").css("height", 200 );
        });
        
        async function cancelOrderOnClick  () {
            window.location.reload();
            const url = config.baseUrl + '/api/ecommerce/cancelconsignment';
            const referenceNumberList = [config.referenceNumber];
            const cookieValue = Object.fromEntries(document.cookie.split('; ').map(c => {
                const [ key, ...v ] = c.split('=');
                return [ key, v.join('=') ];
            }));
            let response =  await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'organisation-id': config.orgId,
                    'shop-url': config.shopUrl,
                    'shop-origin':'magento',
                    'customer-id': cookieValue['customer-id'],
                    'access-token': cookieValue['access-token-shipsy']
                },
                body: JSON.stringify({'referenceNumberList': referenceNumberList})
            });
            let data = await response.json();
            const cancelButton = document.querySelector('#cancel'+config.referenceNumber);
            // Create anchor element.
            if (data.success) {
                window.location.reload();
                const cancelledButton = document.createElement('button');
                cancelledButton.classList.add("btn", "btn-outline-danger");
                const cancelledButtonText = document.createTextNode("CANCELLED"); 
                cancelledButton.appendChild(cancelledButtonText);
                cancelledButton.disabled = true;
                cancelButton.parentNode.replaceChild(cancelledButton, cancelButton);
                $('#label'+config.referenceNumber).css("display", "none");
            }
            else {
                alert(data.failures[0].message);
            }
        };   
    };
});