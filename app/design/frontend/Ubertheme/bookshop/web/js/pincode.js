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
			var pincode = $("#zipcode").val().trim();

			if(pincode.length == 0){
				$(".msgZipAvailabity").text('Please provide pincode');
				$(".msgZipAvailabityYes").hide();
				$(".msgZipAvailabity").show();

			}else if(pincode.length != 6){
				$(".msgZipAvailabity").text('Please provide valid pincode(6 digits)');
				$(".msgZipAvailabityYes").hide();
				$(".msgZipAvailabity").show();
			}else{
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
					   if(response.responseText == 'yes'){
						   $(".msgZipAvailabityYes").show();
						   $(".msgZipAvailabity").hide();
					   }
					   if(response.responseText == 'no'){
							$(".msgZipAvailabity").text('Cash on Delivery is not available in your area!');
							$(".msgZipAvailabity").show();
							$(".msgZipAvailabityYes").hide();
					   }
				  },
				  error:function(xhr,status,errorThrown){
				  }
			  });
			}
			// e.preventDefault();
		
	});
});
