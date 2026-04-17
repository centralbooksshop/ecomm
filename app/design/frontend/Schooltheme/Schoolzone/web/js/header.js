
require(

  [
  
  'jquery',
  
  'Magento_Ui/js/modal/modal',
  
  'mage/url'
  
  ],
  //QUICK LOGIN BEGIN
    function($,modal,urlBuilder) {
        urlBuilder.setBaseUrl(BASE_URL);
        var fronturl = urlBuilder.build('retailinsights_register/Index');
        var mobile_number='';
  
        $("#not_registered").hide();
        $("#mob_validation").hide();
        $("#otp_sent").hide();
        $("#wrong_otp").hide();
		$("#notconfirmed_error").hide();
        $('#popup-otp').hide();
        $('#userLoginDiv').show();
  
      var options = {
          type: 'popup',
          responsive: true,
          innerScroll: true,
          buttons:false
      };
  
      var popup = modal(options, $('#popup-modal'));
  
    
      $(".login_modal").on("click", function(){
        $('#mob_validation').hide();
        $('#popup-otp').hide();
        $('#userLoginDiv').show();
        $('#mobile').val('');
        $('#popup-modal').modal('openModal');
        $('#popup-modal').closest('.modal-popup').addClass('loginreg');
      });
  
  
      function isEmail(email) {
  
        var regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;  //   /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
           return false;
        }else{
           return true;
          }
        }
        
        $(".btnlogin").click(function(event) {
          var mess = '';
          $('#otp_sent').hide();
           $('#wrong_otp').hide();
		   $("#notconfirmed_error").hide();
          $("#mob_validation").hide();
          $("#not_registered").hide();
  
          var inputVal = $('#mobile').val();
          
          var flag = true;
          var isPhone = '';
          if(isEmail(inputVal)){
            isPhone = 'false';
            var param={ mobile:inputVal,code:'sendotp',type:"login",isPhone: false};
          }else if($.isNumeric(inputVal) && (inputVal.length == 10)){
            isPhone = 'true';
            var param={ mobile:inputVal,code:'sendotp',type:"login",isPhone: true};
          }else{
            if(($.isNumeric(inputVal)) && (inputVal.length != 10)){
              isPhone = 'true';
              mess = 'Please enter valid mobile number';
            }else if(!isEmail(inputVal)){
              isPhone = 'false';
              mess = 'Invalid email';
            }
            flag = false;
            $("#mob_validation").text(mess);
            $("#mob_validation").show();
          }
          console.log(param);
          if(flag){
              var customurl = fronturl+'/Sendotp';
  
               $.ajax({
                    showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                       // window.location.reload(); 
                      console.log(response.responseText);
                      if((response.responseText == 'registered') || (response.responseText == 'mail sent') || response.responseText == '') {
                          $('#userLoginDiv').hide();
                          $('#otp').val('');
                          $('#popup-otp').show();
                      }
                      /*if(response.responseText == 'new_user'){
                        if(isPhone == 'true'){
                          $("#not_registered").text("Mobile doesn't exists. Please register to create new account.");
                        }else{
                          $("#not_registered").text("Email doesn't exists. Please register to create new account.");
                        }
                        $("#not_registered").show();
                      }*/
                        if(response.responseText == 'new_user'){
                            /*$('#registered').hide();
							$('#mobile_number').val('');
							$("#otp_error").hide();
							$('#otp_sent').hide();
							$('#wrong_otp').hide();
							$("#notconfirmed_error").hide();
							$("#mob_validation").hide();
							$("#not_registered").hide();
							$('#newUserRegister').show();*/

							
							//var mobile = $("#mobile_number").val();
							var inputVal = $('#mobile').val();
							console.log(inputVal);
							 var flag = true;
							  var isPhone = '';
							  if(isEmail(inputVal)){
								isPhone = 'false';
								var param={ mobile:inputVal,code:'sendotp',type:"register",isPhone: false};
							  }else if($.isNumeric(inputVal) && (inputVal.length == 10)){
								isPhone = 'true';
								var param={ mobile:inputVal,code:'sendotp',type:"register",isPhone: true};
							  }else{
								if(($.isNumeric(inputVal)) && (inputVal.length != 10)){
								  isPhone = 'true';
								  mess = 'Please enter valid mobile number';
								}else if(!isEmail(inputVal)){
								  isPhone = 'false';
								  mess = 'Invalid email';
								}
								flag = false;
								$("#mob_validation").text(mess);
								$("#mob_validation").show();
							  }
							  console.log(param);
							 if(flag){
							//if(mobile.length == 10){
								$("#registered").hide();
								var customurl = fronturl+'/Sendotp';
								$.ajax({
								  showLoader: true,
								  url: customurl,
								  data: param,
								  type: "POST",
								  dataType: 'json',
								  complete:function(response){
									console.log(response.responseText);
									if((response.responseText == 'new_user') || (response.responseText == 'mail sent')) {
									    $('#popup-modal').modal('closeModal');// login
										$('#modal-mobile').modal('openModal');
										$('#newUserRegister').hide();
										$('#modal-otp').hide();
										$('#popup-modal-regi').hide();
										//$('.loginformtext').show();
										$('#modal-mobile').closest('.modal-popup').addClass('loginreg');

										$('#otp_sent_regi').hide();
										$('#otp_error').hide();
										$("#invalied_mobile").hide();
									    $('#modal-otp').show();
									}
								  },
								  error:function(xhr,status,errorThrown){     
								  }
								});
							} 
					    }
                    },
                    error:function(xhr,status,errorThrown){
                  }
                });
          }
       });
  
  
  
        $(".btnotp").click(function(event) {
          $("#wrong_otp").hide();
		  $("#notconfirmed_error").hide();
          $("#otp_sent").hide();
          var inputVal = $('#mobile').val();
          
          console.log(isEmail(inputVal));
          var flag = true;
          var otp = document.getElementById('otp').value;
  
          if(isEmail(inputVal)){
            var param={ otp:otp,mobile:inputVal,isPhone: false }
            // var param={ mobile:inputVal,code:'sendotp',type:"login",isPhone: false};
          }else if(inputVal.length == 10){
            var param={ otp:otp,mobile:inputVal,isPhone: true }
            // var param={ mobile:inputVal,code:'sendotp',type:"login",isPhone: true};
          }else{
            flag = false;
            $("#mob_validation").show();
          }
          var customurl = fronturl+'/Otpverification';
  
          $.ajax({
              showLoader: true,
              url: customurl,
              data: param,
              type: "POST",
              dataType: 'json',
              complete:function(response){
                console.log(response.responseText);
                if(response.responseText == 'yes'){
                  $('#popup-modal').modal('closeModal');
                  window.location.reload(); 
                }
                if(response.responseText == 'no'){
                  $("#wrong_otp").show();
                } 
				if(response.responseText == 'notconfirmed') {
                    $("#notconfirmed_error").show();
                }
              },
              error:function(xhr,status,errorThrown){                    
              }
          });
       });
  
        
        $("#resend_otp").click(function(event) {
          $('#wrong_otp').hide();
		  $("#notconfirmed_error").hide();
          $("#otp_sent").hide();
          $('#otp').val('');
          var mobile = $('#mobile').val();
          var flag = true;
          var isPhoneflag;
           if(isEmail(mobile)){
             isPhoneflag=false;
          }else if($.isNumeric(mobile) && (mobile.length == 10)){
             isPhoneflag=true;
          }else{
            flag = false;
            $("#mob_validation").show();
          }
          var param={ mobile:mobile, code:'resend_otp',type:'login',isPhone:isPhoneflag }
          var customurl = fronturl+'/Sendotp';
          if(flag){
            $.ajax({
                showLoader: true,
                url: customurl,
                data: param,
                type: "POST",
                dataType: 'json',
                complete:function(response){
                  $("#otp_sent").show();
                },
                error:function(xhr,status,errorThrown){
                }
            });
          }
       });
  
  
      $(".register").click(function(event) {
        /*$('#registered').hide();
        $('#mobile_number').val('');
        $("#otp_error").hide();
        $('#otp_sent').hide();
        $('#wrong_otp').hide();
		$("#notconfirmed_error").hide();
        $("#mob_validation").hide();
        $("#not_registered").hide();
          $('#popup-modal').modal('closeModal');// login
        $('#modal-mobile').modal('openModal');
        $('#newUserRegister').show();
        $('#modal-otp').hide();
        $('#popup-modal-regi').hide();
        $('#modal-mobile').closest('.modal-popup').addClass('loginreg');*/
		  
			$("#popup-modal").modal('openModal');
            $('#modal-mobile').modal('closeModal');
            $('#popup-modal').closest('.modal-popup').addClass('loginreg');
      });
          //QUICK LOGIN END
  
          // QUICK REGISTER BEGIN
  
      urlBuilder.setBaseUrl(BASE_URL);
      var fronturl = urlBuilder.build('retailinsights_registers/Index');
  
      $('#newUserRegister').show();
      $('#modal-otp').hide();
      $('#popup-modal-regi').hide();
  
      $("#otp_sent_regi").hide();
      $("#register_error").hide();
	  $("#register_success").hide();
      $("#invalied_mobile").hide();
      $("#registered").hide();
      $("#otp_error").hide();
  
      $("#fname_required").hide();
      $("#lname_required").hide();
      $("#email_required").hide();
	  $("#mobile_required").hide();
      $("#not_valied_email").hide();
      var mobile_number='';
      var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons:false
        };
        var popup = modal(options, $('#modal-mobile'));
      
        $(".register_modal").on("click", function(){
          $('#modal-mobile').modal('openModal');
          $('#newUserRegister').show();
          $('#modal-otp').hide();
          $('#popup-modal-regi').hide();
          $('#modal-mobile').closest('.modal-popup').addClass('loginreg');
        });
  
        /*$(".mobile_check").click(function(event) {
            $('#otp_sent_regi').hide();
            $('#otp_error').hide();
            $('#mobile_otp').val('');
              $("#invalied_mobile").hide();
              var mobile = $("#mobile_number").val();
              if(mobile.length == 10){
                  $("#registered").hide();
                  var mobile = document.getElementById('mobile_number').value;
                  mobile_number = mobile;
                  var param={ mobile:mobile,type:"register",code:"sendotp",isPhone: true}
                  var customurl = fronturl+'/Sendotp';
                  $.ajax({
                      showLoader: true,
                      url: customurl,
                      data: param,
                      type: "POST",
                      dataType: 'json',
                      complete:function(response){
                        console.log(response.responseText);
  
                        if(response.responseText == 'registered'){
                            $("#registered").show();
                        }
                        if(response.responseText == 'new_user'){
                          $('#newUserRegister').hide();
                          $('#modal-otp').show();
                        }
                      },
                      error:function(xhr,status,errorThrown){     
                      }
                  });
              }else{
                  $("#invalied_mobile").show();
              }
        });*/
  
        $(".otp_check").click(function(event) {

		 $("#otp_sent_regi").hide();
		 var inputVal = $('#mobile').val();
          
          console.log('otp_check ' + inputVal);
          var flag = true;
          var otp = document.getElementById('mobile_otp').value;
  
          if(isEmail(inputVal)){
            var param={ otp:otp,type:'register',isPhone: false }
            $("#email").val(inputVal);
			$('#email').prop('disabled', true);
			jQuery(".form_submit").prop('disabled', true);
          }else if(inputVal.length == 10){
            var param={ otp:otp,type:'register',isPhone: true }
			$("#mobile-mobileget").val(inputVal);
			$('#mobile-mobileget').prop('disabled', true);
			$(".model-bottom").css('display','none');
			jQuery(".form_submit").prop('disabled', false);

          }else{
            flag = false;
            $("#mob_validation").show();
          }
         
            var customurl = fronturl+'/Otpverification';
             $.ajax({
                  showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    console.log(response.responseText);
                    if(response.responseText == 'yes'){
                      $('#mobile_otp').val('');
                      $('#newUserRegister').hide();
					  $("#mobile_required").hide();
                      $('#modal-otp').hide();
                      $('#popup-modal-regi').show();                      
                    }
                    if(response.responseText == 'no'){
                      $("#otp_error").show();
                    }
					
                  },
                  error:function(xhr,status,errorThrown){
                      
                  }
              });
     
       });
  
         $(".login").click(function(event) {
            $("#popup-modal").modal('openModal');
            $('#modal-mobile').modal('closeModal');
            $('#popup-modal').closest('.modal-popup').addClass('loginreg');
         });
  
       $(".form_submit").click(function(event) {
          $("#register_error").hide();
		  $("#register_success").hide();
          $("#email_required").hide();
          $("#not_valied_email").hide();
          $("#lname_required").hide();
          $("#fname_required").hide();
		  $("#mobile_required").hide();
		  
        var first_name = document.getElementById('first_name').value.trim();
        var last_name = document.getElementById('last_name').value.trim();
		//var mobilenumber = document.getElementById('#mobile-mobileget').value;
		var mobilenumber = jQuery("#mobile-mobileget").val();
        var email = document.getElementById('email').value.trim();
        var param={ 
          first_name:first_name, 
          last_name:last_name, 
          email:email,
	      mobile:mobilenumber
        }
  
        $key='';
        if(/^[a-zA-Z0-9- ]*$/.test(first_name) == false) {
          $("#fname_required").show();
        }
        if(/^[a-zA-Z0-9- ]*$/.test(last_name) == false) {
          $("#lname_required").show();
        }
        
        if(first_name.length == 0){
          $("#fname_required").show();
        }
        if(last_name.length == 0){
          $("#lname_required").show();
        }

		if(mobilenumber.length == 0){
          $("#mobile_required").show();
        }
  
        if(email.length == 0){
          $("#email_required").show();
        }else{ 
          var pattern = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/);
          if(!pattern.test(email)){
              $key = '';
              $("#not_valied_email").show();
          }else{
              $key = 3;
          }
        }
         
        if(first_name=='' || last_name=='' || email== '' || $key == ''){
          console.log("empty");
        }else{
              var customurl = fronturl+'/Registration';
             $.ajax({
                  showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
					          require(['Magento_Customer/js/customer-data'], function(customerData) {
								customerData.reload(['customer', 'cart'], true); // Reload 'customer' and 'cart' sections
							});
                     //window.location.reload();
					 //$(document).on('customer-data-reload',function(e, arg){
						//console.log(arg);
					//});
                    console.log(response.responseText);
                    if(response.responseText == 'yes') {
						$('#first_name').val('');
						$('#last_name').val('');
						$('#email').val('');
						$('#modal-mobile').modal('closeModal');
						//$("#popup-modal-regi").hide();
						//location.reload(true);

						$("#register_success").show();
						//$('#popup-modal-regi').modal('closeModal');
						window.location.reload();
					}
                    if(response.responseText == 'no'){
                       $("#register_error").show();
                    }
                  },
                  error:function(xhr,status,errorThrown){                    
                  }
              });
          console.log("not empty");
        }
       });

	      
	   /*Start */
	jQuery(".create-account-resend-otp").click(function (e) {
		jQuery(".regi-sendotp").trigger('click');
		jQuery("#reg-sms-please-wait").css('display','none');
	});
	jQuery(".mobileverifyotp").click(function (e) {
		var otp =  jQuery("#mobile-otp").val();
		var param={ otp:otp,type:'register',isPhone: true }
		var mobile = jQuery("#mobile-mobileget").val();
		//alert(mobile);
		jQuery(".blankotperror").css('display','none');
		jQuery(".error").css('display','none');
		if(isBlank(otp) == false){
			jQuery(".blankotperror").css('display','block');
			return false;
		}
		jQuery(".checkotperror").css('display','none');
		jQuery("#reg-otp-verify-please-wait").css('display','block');
		jQuery(".verifyotp").css('display','none');
		jQuery(this).prop('disabled',true);
		jQuery.ajax({
			showLoader: true,
			url: fronturl+'/Otpverification',
			type: 'POST',
			dataType: 'json',
			data: param,
			//data:{otp:otp,mobile:mobile},
			//success: function (data) {
			complete:function(response){
				console.log(response.responseText);
				jQuery(".verifyotp").css('display','block');
				jQuery("#reg-otp-verify-please-wait").css('display','none');
				if(response.responseText == 'yes'){
					jQuery("#createotp").val(otp);
					jQuery(".otpverify").css('display','none');
					//jQuery(".registraionform").css('display','block');
					//jQuery(".submit").prop('disabled', false);
					jQuery(".form_submit").prop('disabled', false);

				}else{
					jQuery(".checkotperror").css('display','block');
				}
				jQuery(".blankotperror").css('display','none');
				jQuery('.mobileverifyotp').prop('disabled',false);
			},
			error: function () {
				jQuery("#reg-otp-verify-please-wait").css('display','none');
				jQuery(".verifyotp").css('display','block');
				jQuery(this).prop('disabled',false);
			}
		});
	});
	/*end */
	   
	jQuery(".sendotp").click(function (e) {
		var regmobile = jQuery("#mobileget").val();
		//alert(regmobile);
		//var countrycode=jQuery('.reg-mobile .selected-flag .selected-dial-code').text().replace('+','');
		//var mobile=countrycode.concat(regmobile);
		//jQuery(".otpverify .otp-content .massage").html("********"+mobile.substr(8));
		var url = jQuery(".setdotpurl").val();
		jQuery(".blankerror").css('display','none');
		jQuery(".mobileNotValid").css('display','none');
		jQuery(".mobileotpsenderror").css('display','none');
		jQuery(".mobileExist").css('display','none');
		jQuery(".mobileNotValid").html("Please Enter Valid Mobile Number");

		jQuery(".reg-please-wait").css('display','block');

		if(!regmobile){
			jQuery(".blankerror").css('display','block');
			jQuery(".reg-please-wait").css('display','none');
			return false;
		}
		if(jQuery('#reg-mob-error-msg').text()!=""){
			jQuery(".mobileNotValid").css('display','block');
			jQuery(".mobileNotValid").html(jQuery('#reg-mob-error-msg').text());
			jQuery(".send-otp-button").css('display','block');
			jQuery(".reg-please-wait").css('display','none');
			return false;
		}

		jQuery(".send-otp-button").css('display','none');
		jQuery(".reg-please-wait").css('display','block');

		jQuery.ajax({
			url: url,
			type:'GET',
			data:{mobile:mobile},
			success: function (data) {
				jQuery(".send-otp-button").css('display','block');

				if(data == 'true'){
					jQuery("#createmobile").val(mobile);
					jQuery(".mobileget").css('display','none');
					$("#mobileget").prop("readonly", true);
					$(".verifyotp").prop("disabled", false);
					jQuery(".otpverify").css('display','block');
					jQuery(".reg-please-wait").css('display','none');
					jQuery(".otp-content").css('display','block');
					jQuery(".verifyotp").css('display','block');
					jQuery(".sendotp").val("Resend OTP");

				}else if(data == 'exist'){
					jQuery(".mobileExist").css('display','block');
					jQuery(".reg-please-wait").css('display','none');
				}else{
					jQuery(".mobileotpsenderror").css('display','block');

				}
			},
			error: function () {
				jQuery(".send-otp-button").css('display','block');
				jQuery(".reg-please-wait").css('display','none');
			}

		});
	});

	/* for register form */
	jQuery(".regi-sendotp").click(function (e) {

		var mobile = jQuery("#mobile-mobileget").val();
		//alert(mobile);
		var url = fronturl+'/Sendotp';
		//var url = jQuery(".setdotpurl").val();
		jQuery(".blankerror").css('display','none');
		jQuery(".mobileNotValid").css('display','none');
		jQuery(".mobileotpsenderror").css('display','none');
		jQuery(".mobileExist").css('display','none');

		jQuery(".resend").css('display','none');
		jQuery(".sending").css('display','block');


		if(!mobile){
			jQuery(".blankerror").css('display','block');

			return false;
		}
		if(validateMobile(mobile) == false){
			jQuery(".mobileNotValid").css('display','block');
			return false;
		}
		isPhone = 'true';
        var param={ mobile:mobile,code:'sendotp',type:"register",isPhone: true};
		
		jQuery(".sendotp").css('display','none');
		jQuery("#reg-sms-please-wait").css('display','block');
		jQuery(this).prop('disabled',true);
		jQuery.ajax({
			showLoader: true,
			url: url,
			type:'POST',
			data:param,
			dataType: 'json',
			complete:function(response){
			//success: function (response) {
				jQuery(".sendotp").css('display','block');
				jQuery("#reg-sms-please-wait").css('display','none');
                console.log(response.responseText);
				if(response.responseText == 'new_user'){
					jQuery("#createmobile").val(mobile);
					jQuery(".mobileget").css('display','block');
					jQuery(".regi-sendotp").css('display','none');
					document.getElementById("mobile-mobileget").readOnly = true;
					jQuery(".otpverify").css('display','block');
					jQuery(".resend").css('display','block');
					jQuery(".sending").css('display','none');
					jQuery("#regi-mobilenumber").val(mobile);

				}else if(response.responseText == 'registered'){
					jQuery(".mobileExist").css('display','block');
				}else{
					jQuery(".mobileotpsenderror").css('display','block');
				}
				jQuery('.regi-sendotp').prop('disabled',false);
			},
			error: function () {

				jQuery(".sendotp").css('display','block');
				jQuery("#reg-sms-please-wait").css('display','none');
				jQuery(this).prop('disabled',false);
			}
		});

	});



  
       $("#resend_otp_regi").click(function(event) {
        $("#otp_error").hide();
        $('#mobile_otp').val('');
          $("#otp_sent_regi").hide();
          var mobile = $('#mobile_number').val();
            var param={ mobile:mobile, code:'resend_otp',type:'register',isPhone:true}
            var customurl = fronturl+'/Sendotp';
             $.ajax({
                  showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    $("#otp_sent_regi").show();
                  },
                  error:function(xhr,status,errorThrown){
                      
                  }
              });
       });


  
       $('#loginBackSchool').click(function(){
        $('#popup-otp').hide();
        $('#userLoginDiv').show();
       });
  
       $('#schoolRegBack').click(function(){

        $('#modal-otp').hide();
        //$('#newUserRegister').show();
		$('#modal-mobile').modal('closeModal');// register

		 $('#mob_validation').hide();
        $('#popup-otp').hide();
        $('#userLoginDiv').show();
        $('#mobile').val('');
        $('#popup-modal').modal('openModal');
        $('#popup-modal').closest('.modal-popup').addClass('loginreg');
       });

	function validateMobile(mobile)
	{
		var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;

		if (filter.test(mobile)) {
			if(mobile.length >= 10 && mobile.length <= 13){
				var validate = true;
			} else {
				var validate = false;
			}
		}
		else {
			var validate = false;
		}
		return validate;
	}
	function isBlank(value)
	{
		if(!value)
		{
			return false;
		}
	}
	jQuery(".mobnumber").keydown(function (e) {
		// Allow: backspace, delete, tab, escape, enter and .
		if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
				// Allow: Ctrl+A
			(e.keyCode == 65 && e.ctrlKey === true) ||
				// Allow: Ctrl+C
			(e.keyCode == 67 && e.ctrlKey === true) ||
				// Allow: Ctrl+X
			(e.keyCode == 88 && e.ctrlKey === true) ||
				// Allow: home, end, left, right
			(e.keyCode >= 35 && e.keyCode <= 39)) {
			// let it happen, don't do anything
			return;
		}
		// Ensure that it is a number and stop the keypress
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) 					        {
			e.preventDefault();
		}
	});
       //QUICK REGISTER CLOSE
       //Add to cart
        // $(".buynow").on("click", function(){
        //     $(".add_to_cart").html('Add to Cart');
        //     window.location.href = BASE_URL+ "/checkout/cart";
        //   });
      //  console.log($('.message-error div').text());
    }
  
  );