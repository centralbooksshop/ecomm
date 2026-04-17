define(['jquery'], function ($) {
    'use strict';

    return function (config) { 
        $('#setupSubmitButton').prop('disabled', false);
        const orange = '#eb5202';
        $('#useForwardCheck').on('change', function(){
            if(this.checked) {
                $('#reverse-address').find("*").prop('disabled', true);
            }
            else {
                $('#reverse-address').find("*").prop('disabled', false);
            }
        });   
        $('#forward-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.nameErrorText', '#forward-name');
            }
            else {
                enableFieldStyle('.nameErrorText', '#forward-name');
            }
        });
        $('#forward-phone').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.phoneErrorText', '#forward-phone');

            }
            else {
                enableFieldStyle('.phoneErrorText', '#forward-phone');

            }
        });

        $('#forward-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.addressErrorText', '#forward-line-1');
            }
            else {
                enableFieldStyle('.addressErrorText', '#forward-line-1');
            }
        });

        // $('#forward-city').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.cityErrorText', '#forward-city');
        //     }
        //     else {
        //         enableFieldStyle('.cityErrorText', '#forward-city');
        //     }
        // });

        // $('#forward-state').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.stateErrorText', '#forward-state');
        //     }
        //     else {
        //         enableFieldStyle('.stateErrorText', '#forward-state');
        //     }
        // });

        // $('#forward-country').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.countryErrorText', '#forward-country');
        //     }
        //     else {
        //         enableFieldStyle('.countryErrorText', '#forward-country');
        //     }
        // });

        // $('#forward-pincode').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.pincodeErrorText', '#forward-pincode');
        //     }
        //     else {
        //         enableFieldStyle('.pincodeErrorText', '#forward-pincode');
        //     }
        // });

        //reverse

        $('#reverse-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.rNameErrorText', '#reverse-name');
            }
            else {
                enableFieldStyle('.rNameErrorText', '#reverse-name');
            }
        });

        $('#reverse-phone').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.rPhoneErrorText', '#reverse-phone');
            }
            else {
                enableFieldStyle('.rPhoneErrorText', '#reverse-phone');
            }
        });

        $('#reverse-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.rAddressErrorText', '#reverse-line-1');
            }
            else {
                enableFieldStyle('.rAddressErrorText', '#reverse-line-1');
            }
        });

        // $('#reverse-country').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.rCountryErrorText', '#reverse-country');
        //     }
        //     else {
        //         enableFieldStyle('.rCountryErrorText', '#reverse-country');
        //     }
        // });

        // $('#reverse-city').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.rCityErrorText', '#reverse-city');
        //     }
        //     else {
        //         enableFieldStyle('.rCityErrorText', '#reverse-city');
        //     }
        // });

        // $('#reverse-state').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.rStateErrorText', '#reverse-state');
        //     }
        //     else {
        //         enableFieldStyle('.rStateErrorText', '#reverse-state');
        //     }
        // });

        // $('#reverse-pincode').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.rPincodeErrorText', '#reverse-pincode');
        //     }
        //     else {
        //         enableFieldStyle('.rPincodeErrorText', '#reverse-pincode');
        //     }
        // });

        //return address
        $('#return-details-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.reNameErrorText', '#return-details-name');
            }
            else {
                enableFieldStyle('.reNameErrorText', '#return-details-name');
            }
        });

        $('#return-details-phone').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.rePhoneErrorText', '#return-details-phone');
            }
            else {
                enableFieldStyle('.rePhoneErrorText', '#return-details-phone');
            }
        });

        $('#return-details-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.reAddressErrorText', '#return-details-line-1');
            }
            else {
                enableFieldStyle('.reAddressErrorText', '#return-details-line-1');
            }
        });

        // $('#return-details-city').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.reCityErrorText', '#return-details-city');
        //     }
        //     else {
        //         enableFieldStyle('.reCityErrorText', '#return-details-city');
        //     }
        // });

        // $('#return-details-state').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.reStateErrorText', '#return-details-state');
        //     }
        //     else {
        //         enableFieldStyle('.reStateErrorText', '#return-details-state');
        //     }
        // });

        // $('#return-details-country').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.reCountryErrorText', '#return-details-country');
        //     }
        //     else {
        //         enableFieldStyle('.reCountryErrorText', '#return-details-country');
        //     }
        // });

        // $('#return-details-pincode').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.rePincodeErrorText', '#return-details-pincode');
        //     }
        //     else {
        //         enableFieldStyle('.rePincodeErrorText', '#return-details-pincode');
        //     }
        // });
        //exception return

        $('#exp-return-name').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.eNameErrorText', '#exp-return-name');
            }
            else {
                enableFieldStyle('.eNameErrorText', '#exp-return-name');
            }
        });

        $('#exp-return-phone').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.ePhoneErrorText', '#exp-return-phone');
            }
            else {
                enableFieldStyle('.ePhoneErrorText', '#exp-return-phone');
            }
        });

        $('#exp-return-line-1').keyup(function(){
            if($(this).val() == ''){
                disableFieldStyle('.eAddressErrorText', '#exp-return-line-1');
            }
            else {
                enableFieldStyle('.eAddressErrorText', '#exp-return-line-1');
            }
        });

        // $('#exp-return-city').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.eCityErrorText', '#exp-return-city');
        //     }
        //     else {
        //         enableFieldStyle('.eCityErrorText', '#exp-return-city');
        //     }
        // });

        // $('#exp-return-state').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.eStateErrorText', '#exp-return-state');
        //     }
        //     else {
        //         enableFieldStyle('.eStateErrorText', '#exp-return-state');
        //     }
        // });

        // $('#exp-return-country').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.eCountryErrorText', '#exp-return-country');
        //     }
        //     else {
        //         enableFieldStyle('.eCountryErrorText', '#exp-return-country');
        //     }
        // });

        // $('#exp-return-pincode').keyup(function(){
        //     if($(this).val() == ''){
        //         disableFieldStyle('.ePincodeErrorText', '#exp-return-pincode');
        //     }
        //     else {
        //         enableFieldStyle('.ePincodeErrorText', '#exp-return-pincode');
        //     }
        // });

        function disableFieldStyle(errorTextClass, selectedFieldId){
            $(errorTextClass).css({display:'block'});
            $(selectedFieldId).css("border-color", "red");
            $('#setupSubmitButton').prop('disabled', true);
            $('#setupSubmitButton').css("border-color", "grey");
            $('#setupSubmitButton').css("background-color", "grey");
        }

        function enableFieldStyle(errorTextClass , selectedFieldId){
            $(errorTextClass).css({display : "none"});
            $(selectedFieldId).css("border-color", "grey");
            $('#setupSubmitButton').prop('disabled', false);
            $('#setupSubmitButton').css("border-color", '#eb5202');
            $('#setupSubmitButton').css("background-color", '#eb5202');
        }
    };
});