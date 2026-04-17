define(['jquery'], function ($) {
    'use strict';

    return function (config) { 
        $('#softdataSubmitButton').prop('disabled', false);
        const shippingAddress = config.shippingAddress;
        const forwardAddress = config.forwardAddress;
        const reverseAddress = config.reverseAddress;
        const shippingAddressCountry = config.shippingAddressCountry;
        $('#select-consignment-type').change( function() {
            const selectedValue = $(this).val();
            if (selectedValue === 'reverse') {
                /*
                For reverse consignment type
                Origin details - Shipping Address
                Destination details - Reverse Address (getting value for Shipsy)
                */
                document.getElementById("origin-name").value = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                document.getElementById("origin-number").value = shippingAddress.telephone;
                document.getElementById("origin-alt-number").value = '';
                document.getElementById("origin-line-1").value = shippingAddress.street;
                document.getElementById("origin-line-2").value = '';
                document.getElementById("origin-city").value = shippingAddress.city;
                document.getElementById("origin-state").value = shippingAddress.region;
                document.getElementById("origin-country").value = shippingAddressCountry || '';
                document.getElementById("origin-pincode").value = shippingAddress.postcode;
                document.getElementById("origin-what3word-code").value = shippingAddress.what3word || '';

                document.getElementById("destination-name").value = reverseAddress.name;
                document.getElementById("destination-number").value = reverseAddress.phone;
                document.getElementById("destination-alt-number").value = reverseAddress.alternate_phone;
                document.getElementById("destination-line-1").value = reverseAddress.address_line_1;
                document.getElementById("destination-line-2").value = reverseAddress.address_line_2;
                document.getElementById("destination-city").value = reverseAddress.city;
                document.getElementById("destination-state").value = reverseAddress.state;
                document.getElementById("destination-country").value = reverseAddress.country;
                document.getElementById("destination-pincode").value = reverseAddress.pincode;
                document.getElementById("destination-what3word-code").value = reverseAddress.what3word || '';
            }
            else if (selectedValue === 'forward') {
                /*
                For forward consignment type
                Origin details - Forward Address (getting value for Shipsy)
                Destination details - Shipping Address 
                */
               document.getElementById("origin-name").value = forwardAddress.name;
               document.getElementById("origin-number").value = forwardAddress.phone;
               document.getElementById("origin-alt-number").value = forwardAddress.alternate_phone;
               document.getElementById("origin-line-1").value = forwardAddress.address_line_1;
               document.getElementById("origin-line-2").value = forwardAddress.address_line_2;
               document.getElementById("origin-city").value = forwardAddress.city;
               document.getElementById("origin-state").value = forwardAddress.state;
               document.getElementById("origin-country").value = forwardAddress.country;
               document.getElementById("origin-pincode").value = forwardAddress.pincode;
               document.getElementById("origin-what3word-code").value = forwardAddress.what3word || '';


               document.getElementById("destination-name").value = shippingAddress.firstname + ' ' + shippingAddress.lastname;
               document.getElementById("destination-number").value = shippingAddress.telephone;
               document.getElementById("destination-alt-number").value = '';
               document.getElementById("destination-line-1").value = shippingAddress.street;
               document.getElementById("destination-line-2").value = '';
               document.getElementById("destination-city").value = shippingAddress.city;
               document.getElementById("destination-state").value = shippingAddress.region;
               document.getElementById("destination-country").value = shippingAddressCountry || '';
               document.getElementById("destination-pincode").value = shippingAddress.postcode;
               document.getElementById("destination-what3word-code").value = shippingAddress.what3word || '';
            }
        });

        $('#origin-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.nameErrorText', '#origin-name');
            }
            else {
                enableFieldStyle('.nameErrorText', '#origin-name');
            }
        });

        $('#origin-number').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.phoneErrorText', '#origin-number');
            }
            else {
                enableFieldStyle('.phoneErrorText', '#origin-number');
            }
        });

        $('#origin-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.addressErrorText', '#origin-line-1');
            }
            else {
                enableFieldStyle('.addressErrorText', '#origin-line-1');
            }
        });

        $('#origin-city').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.cityErrorText', '#origin-city');
            }
            else {
                enableFieldStyle('.cityErrorText', '#origin-city');
            }
        });

        $('#origin-state').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.stateErrorText', '#origin-state');
            }
            else {
                enableFieldStyle('.stateErrorText', '#origin-state');
            }
        });

        $('#origin-country').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.countryErrorText', '#origin-country');
            }
            else {
                enableFieldStyle('.countryErrorText', '#origin-country');
            }
        });

        $('#origin-pincode').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.pincodeErrorText', '#origin-pincode');
            }
            else {
                enableFieldStyle('.pincodeErrorText', '#origin-pincode');
            }
        });

        //destination


        $('#destination-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dNameErrorText', '#destination-name');
            }
            else {
                enableFieldStyle('.dNameErrorText', '#destination-name');
            }
        });

        $('#destination-number').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dPhoneErrorText', '#destination-number');
            }
            else {
                enableFieldStyle('.dPhoneErrorText', '#destination-number');
            }
        });

        $('#destination-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dAddressErrorText', '#destination-line-1');
            }
            else {
                enableFieldStyle('.dAddressErrorText', '#destination-line-1');
            }
        });

        $('#destination-city').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dCityErrorText', '#destination-city');
            }
            else {
                enableFieldStyle('.dCityErrorText', '#destination-city');
            }
        });

        $('#destination-state').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dStateErrorText', '#destination-state');
            }
            else {
                enableFieldStyle('.dStateErrorText', '#destination-state');
            }
        });

        $('#destination-country').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dCountryErrorText', '#destination-country');
            }
            else {
                enableFieldStyle('.dCountryErrorText', '#destination-country');
            }
        });

        $('#destination-pincode').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.dPincodeErrorText', '#destination-pincode');
            }
            else {
                enableFieldStyle('.dPincodeErrorText', '#destination-pincode');
            }
        });

        $('#customer-reference-number').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.orderText', '#customer-reference-number');
            }
            else {
                enableFieldStyle('.orderText', '#customer-reference-number');
            }
        });

        $('.description-tag').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.descText', '');
            }
            else {
                enableFieldStyle('.descText', '');
            }
        });

        $('#select-cod-collection-mode').on('change', function(){
            if($(this).val() == 'cheque' || $(this).val() == 'dd'){
                $('.cod-favour-text').css({display : 'inline-flex'});
                $('.codFavourText').css({display : 'block'});
                disableSubmitButton();
            } else {
                $('.cod-favour-text').css({display : 'none'});
                $('.codFavourText').css({display : 'none'});
                $('#cod-favor-of').css("border-color", "grey");
                enableSubmitButton();

            }
        });

        $('#cod-favor-of').keyup(function(){
            var codModeVal = $('#select-cod-collection-mode').val();
            if(codModeVal == 'cheque' || codModeVal == 'dd'){
                if($(this).val()== ''){
                    disableFieldStyle('.codFavourText', '#cod-favor-of');
                }
                else {
                    enableFieldStyle('.codFavourText', '#cod-favor-of');
                }
            }
        });



        $('#num-pieces').on('change keyup', function(){
            if($(this).val() == 0){
                disableFieldStyle('.numpiecesError', '#num-pieces');
            }
            else {
                enableFieldStyle('.numpiecesError', '#num-pieces');
            }
            /*
            var checklength  = $("#piece-det > div").length;
            var $pieceDetail1 = $('#piece-detail-1');
            var diff  = $(this).val() - checklength;
            var multicheckval = $('#multiPieceCheck').prop('checked');
            if($(this).val()>0  && !multicheckval){
                if(diff > 0){
                    var curr = checklength+1;
                        for(var i =0 ;i<diff;i++){
                            $pieceDetail1.clone().attr('id', 'piece-detail-'+ curr).appendTo("#piece-det");
                            $('#piece-detail-'+curr).find('input:text').val('');
                            $('#piece-detail-'+curr).find('input:text').attr('id', 'description'+curr);
                            $('#piece-detail-'+curr).find("input[name^='quantity']").val('1');
                            $('#piece-detail-'+curr).find("input[name^='weight']").val('0');
                            $('#piece-detail-'+curr).find("input[name^='length']").val('1');
                            $('#piece-detail-'+curr).find("input[name^='width']").val('1');
                            $('#piece-detail-'+curr).find("input[name^='height']").val('1');
                            $('#piece-detail-'+curr).find("input[name^='declared-value']").val('0');
                            curr++;
                        }
                } else {
                    var rem = $("#piece-det > div").length;
                    for(var i=0;i<Math.abs(diff);i++){
                            $('#piece-detail-'+rem).remove();
                            rem--;
                    }
                }
            }*/
            
        });

        $('#multiPieceCheck').on('change', function(){
            console.log(this.checked);
            if(this.checked) {
                var divlength =  $("#piece-det > div").length;
                if(divlength -1 >0){
                    var flag = divlength;
                    for(var i=0;i<divlength-1;i++){
                        $('#piece-detail-'+flag).remove();
                        flag -- ;
                    }
                }
            } else {
                const numpieceval  = $('#num-pieces').val();
                var $pieceDet1 = $('#piece-detail-1');
                if(numpieceval>0){
                     var newCount = 2;
                     for(var i=0;i<numpieceval-1;i++){
                        $pieceDet1.clone().attr('id', 'piece-detail-'+ newCount).appendTo("#piece-det");
                            $('#piece-detail-'+newCount).find('input:text').val('');
                            $('#piece-detail-'+newCount).find('input:text').attr('id', 'description'+newCount);
                            $('#piece-detail-'+newCount).find("input[name^='weight']").val('0');
                            $('#piece-detail-'+newCount).find("input[name^='length']").val('1');
                            $('#piece-detail-'+newCount).find("input[name^='width']").val('1');
                            $('#piece-detail-'+newCount).find("input[name^='height']").val('1');
                            $('#piece-detail-'+newCount).find("input[name^='declared-value']").val('0');
                            newCount++;

                     }
                }
            }
        }); 

        function disableFieldStyle(errorTextClass, selectedFieldId){
            $(errorTextClass).css({display:'block'});
            $(selectedFieldId).css("border-color", "red");
            $('#softdataSubmitButton').prop('disabled', true);
            $('#softdataSubmitButton').css("border-color", "grey");
            $('#softdataSubmitButton').css("background-color", "grey");
        }

        function enableFieldStyle(errorTextClass , selectedFieldId){
            $(errorTextClass).css({display : "none"});
            $(selectedFieldId).css("border-color", "grey");
            $('#softdataSubmitButton').prop('disabled', false);
            $('#softdataSubmitButton').css("border-color", '#eb5202');
            $('#softdataSubmitButton').css("background-color", '#eb5202');
        }

        function disableSubmitButton(){
            $('#softdataSubmitButton').prop('disabled', true);
            $('#softdataSubmitButton').css("border-color", "grey");
            $('#softdataSubmitButton').css("background-color", "grey");
        }

        function enableSubmitButton(){
            $('#softdataSubmitButton').prop('disabled', false);
            $('#softdataSubmitButton').css("border-color", '#eb5202');
            $('#softdataSubmitButton').css("background-color", '#eb5202');
        }
    };
});