require(
[
'jquery',
'Magento_Ui/js/modal/modal',
'mage/url'
],
  function($,modal,urlBuilder) {
  	urlBuilder.setBaseUrl(BASE_URL);
	var fronturl = urlBuilder.build('retailinsights_postcode/Index');
		$(".msgZipAvailabity").hide();
		$(".msgZipAvailabityYes").hide();
		

		$(".btnZipCheck").on("click", function(e) {
      e.preventDefault();
			var pincode = $("#zipcode").val();
			console.log(pincode);
			  var param={ pincode:pincode }
			  var customurl = fronturl+'/PincodeCheck';
			  console.log(customurl);
           $.ajax({
                showLoader: true,
                url: customurl,
                data: param,
                type: "POST",
                dataType: 'json',
                complete:function(response){
                	console.log(response.responseText);
                	 if(response.responseText == 'yes'){
                	 	$(".msgZipAvailabityYes").show();
                	 	$(".msgZipAvailabity").hide();
                	 }
                	 if(response.responseText == 'no'){
                  		$(".msgZipAvailabity").show();
                  		$(".msgZipAvailabityYes").hide();
                	 }

                },
                error:function(xhr,status,errorThrown){
                }
            });
			// e.preventDefault();
		
	});
});
