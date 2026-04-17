define([
    'jquery',
    'uiElement',
], function ($) {
    'use strict';

    return function (config) { 
        $(document).on("click",`input[id^=label${config.referenceNumber}]`, async () => {
            const cookieValue = Object.fromEntries(document.cookie.split('; ').map(cookie => {
                const [ key, ...v ] = cookie.split('=');
                return [ key, v.join('=') ];
            }));

            const labelButtons = document.querySelectorAll(`input[id*="label"]`);
            // Process each label button asynchronously
            for (let labelButton of labelButtons) {
                let awbNumber = labelButton.id.split('label')[1];
                const labelUrl = config.baseUrl + '/api/ecommerce/shippinglabel/link?reference_number=' + awbNumber;
                
                // Fetch data for each label button
                let labelResponse = await fetch(labelUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'organisation-id': config.orgId,
                        'shop-url': config.shopUrl,
                        'shop-origin': 'magento',
                        'customer-id': cookieValue['customer-id'],
                        'access-token': cookieValue['access-token-shipsy']
                    }
                });

                let labelData = await labelResponse.json();
                if ('data' in labelData){
                    const requiredData = labelData.data;

                    // Create download button
                    const downloadLabelButton = document.createElement('a');
                    downloadLabelButton.classList.add("btn", "btn-success");
                    downloadLabelButton.innerText = "Download";
                    downloadLabelButton.setAttribute('target', '_blank');
                    downloadLabelButton.setAttribute('href', requiredData.url);
    
                    // Replace original input element with download button
                    labelButton.parentNode.replaceChild(downloadLabelButton, labelButton);
                }
                else {
                    alert("Error occurred while generating label: " + data.error.message);
                }
            }

            
        });   
    };
});