require(
[
'jquery',
'mage/url'
],
  function($,urlBuilder) {

	function getStatusFromUrlPath() {
		var path = window.location.pathname; 
		var parts = path.split('/').filter(Boolean);

		var statusIndex = parts.indexOf('status');
		if (statusIndex !== -1 && parts[statusIndex + 1]) {
			return parts[statusIndex + 1];
		}
		return '';
	}
    $(".back_btn").hide();

    $(".search_empty").hide();
    $(".blank_roll").hide();
    $(".blank_username").hide();
    $(".blank_password").hide();

    $(".location_hint").hide();
    $(".board_hint").hide();
    $(".invalid_email").hide();

    $('.user_pass').hide();
    $('.username_list').hide();
    $('.password_list').hide();
    $('.roll_numbers').hide();

    $(".username_not_valied").hide();
    $(".roll_not_valied").hide();

    urlBuilder.setBaseUrl(BASE_URL);
    var fronturl = urlBuilder.build('schoolzone_search/Index');


    var url_string = window.location.href; // www.test.com?filename=test
    var url = new URL(url_string);
    var paramValue = url.searchParams.get("name");
    
    if(paramValue != null) {
          var param={ school_name:paramValue, type:'pre_school_search' }
          var customurl = fronturl+'/Search';
             $.ajax({
                  // showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                      console.log(response.responseJSON.type);
                      var school_type = parseInt(response.responseJSON.type);
                      var school_name_text = response.responseJSON.name;
		      var displaybookstore = response.responseJSON.displaybookstore;
               if(school_type != 0 && school_type != undefined && school_type !=null) {
               $(".school_name_list").val(school_name_text);
               $(".urlResult").text(response.responseJSON.name);
               $('.search_btn_list').text('Explore Your Books');
	       if(displaybookstore == false && displaybookstore != undefined && displaybookstore !=null) {
			        $('#school_zone').hide();
	       }
               school_type = response.responseJSON.type;
                if(school_type == 2){
                  console.log('school_type 2');
          $('.username_list').show();
          $('.password_list').show();
          $('.roll_numbers').hide();
        }
                      if(school_type == 3){
          $('.roll_numbers').show();
         $('.username_list').hide();
          $('.password_list').hide();
      }
}

                  },
                  error:function(xhr,status,errorThrown){
                  }
              });
    }
  
      $(".search_btn").click(function(event) {
        var location=''; 
        var board='';
        var school='';

        location= $(".location_hint").val();
        board= $(".board_hint").val();
        school = $(".res_entity_id").val();
     
        var category_key = '';
            if(location){
              // console.log('location');
              // console.log(location);
              category_key = location;
            }
            if(school && board==null){

              // console.log('school');
              // console.log(school);
              category_key = school;
            }
            if(board && location==null){

              // console.log('board');
              // console.log(board);
              category_key = board;
            }
            if(category_key != null){
              var param={ category_key:category_key, type:'category_key' }
              var customurl = fronturl+'/Search';
              // console.log(customurl);
              $.ajax({
                    // showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                      // console.log(response.responseText);
                      window.location.href = response.responseText;
                      // var array = response.responseJSON;
                      // $(".result_hint").text("");
                      // $(".result_hint").append(response.responseJSON);
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });
            }
      });
    
      $(".school_name").val('');
    $(".school_name").on("keydown", function(e) {
      $(".board_hint").empty();
      $(".location_hint").empty();
      $(".result_hint").show();
            
      var name = $(".school_name").val();
    
      console.log(fronturl);
      
        var param={ name:name, type:'search' }
        var customurl = fronturl+'/Search';
        // console.log(customurl);
           $.ajax({
                // showLoader: true,
                url: customurl,
                data: param,
                type: "POST",
                dataType: 'json',
                complete:function(response){
                   $(".result_hint").text('');
                  // var array = response.responseJSON;
                 
                  // $(".result_hint").text("");
                  $(".result_hint").append(response.responseJSON);
                },
                error:function(xhr,status,errorThrown){
                }
            });
      // e.preventDefault();
  });

    $(".result_hint").click(function(event) {
        var id = $(".responce_hint").val();
        $('.user_pass').hide();
         $('.roll_numbers').hide();

        var school_type = $(".school_type").val();
        // console.log(school_type);

        if(school_type == 3){
          $('.roll_numbers').show();
          $('.user_pass').hide();
        }
        if(school_type == 2){
          $('.user_pass').show();
          $('.roll_numbers').hide();
        }

          var sujjession = $(".responce_hint").val();
          $(".school_name").val(sujjession);
          // console.log(sujjession);
          $(".result_hint").hide();
          $(".board_hint").hide();
          $(".location_hint").hide();

          var entity_id = $(".entity_id").val();
          var param={ entity_id:entity_id,id:id, type:'board' }
          var customurl = fronturl+'/Search';
          
             $.ajax({
                  // showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    // var array = response.responseJSON;
                    // console.log(response.responseJSON);
                    // $(".board_hint").text("");
                    if(response.responseJSON != null){
                       // console.log(id);
                      // console.log(response.responseJSON);
                      $(".board_hint").show();
                      $(".board_hint").append(response.responseJSON);
                    }
                  },
                  error:function(xhr,status,errorThrown){
                  }
              });
        });
     
        $(".board_hint").change(function(event) {
          var entity_id= $(".board_hint").val();
          $(".location_hint").hide();
          
            // $(".school_board").val(sujjession);
            // $(".board_hint").hide();
             // console.log(sujjession);
            $(".location_hint").empty();
            var id = $(".responce_hint").val();
            // console.log(entity_id);

           
            var param={ entity_id:entity_id, id:id,location:'location' }
            var customurl = fronturl+'/Search';

             $.ajax({
                  // showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    // var array = response.responseJSON;
                    // console.log(response.responseJSON);
                    
                    // $(".location_hint").text("");

                    if(response.responseJSON != null){
                        $(".location_hint").show();
                        $(".location_hint").append(response.responseJSON);
                    }else{
                      $(".location_hint").hide();
                    }
                  },
                  error:function(xhr,status,errorThrown){
                  }
              });
        });

         $(".location_hint").change(function(event) {
           $(".school_location").prop("selected", false);

            var sujjession = $(".location_hint").val();
            // console.log(sujjession);
            // $(".location_hint").hide();
         });
    
       $(".register_open").click(function(event) {
            //$(".option_entity_id").val();
            var param={ type:'register_school' }
            var customurl = fronturl+'/Search';

              $.ajax({
                  // showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    // var array = response.responseJSON;
                    // console.log(response.responseJSON);
                    window.location.href = response.responseText;
                  },
                  error:function(xhr,status,errorThrown){
                  }
              });

       });
       $(".register_notify").click(function(event) {
        $(".invalid_email").hide();
          var regi_email = $(".regi_email").val();
          var regi_school = $(".regi_school").val();
          var regi_board = $(".regi_board").val();
          var regi_city = $(".regi_city").val();
          var regi_area = $(".regi_area").val();

          var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

          var email = regex.test(regi_email);


          if(email == true && regi_school!= '' && regi_board !='' && regi_city!= '' && regi_area !=''){
            $(".invalid_email").hide();
            $(".option_entity_id").val();


            var param={ 
                regi_email:regi_email,
                regi_school:regi_school,
                regi_board:regi_board,
                regi_city:regi_city,
                regi_area:regi_area,
                type:'notify_school' 
              }
            var customurl = fronturl+'/Search';

              $.ajax({
                  showLoader: true,
                  url: customurl,
                  data: param,
                  type: "POST",
                  dataType: 'json',
                  complete:function(response){
                    // var array = response.responseJSON;
                    // console.log(response.responseText);
                    // window.location.href = response.responseText;
                    if(response.responseText == 'yes'){
                      alert("Notification sent Successfully");  
                        var regi_email = $(".regi_email").val('');
                        var regi_school = $(".regi_school").val('');
                        var regi_board = $(".regi_board").val('');
                        var regi_city = $(".regi_city").val('');
                        var regi_area = $(".regi_area").val('');
                    } 
                    if(response.responseText == 'no'){
                      alert("Something Went wrong sending email");
                    }

                  },
                  error:function(xhr,status,errorThrown){
                  }
              });
        
          }else{
            $(".invalid_email").show();
          }
       });

   // $(".register_back").click(function(event) {
   //            var param={ 
   //                type:'back' 
   //              }
   //            var customurl = fronturl+'/Search';
   //            console.log('back');

   //              $.ajax({
   //                  showLoader: true,
   //                  url: customurl,
   //                  data: param,
   //                  type: "POST",
   //                  dataType: 'json',
   //                  complete:function(response){
   //                    console.log(response.responseText);
   //                     window.location.href = response.responseText;
   //                  },
   //                  error:function(xhr,status,errorThrown){
   //                  }
   //              });
          
       
   //       });
    $(".search_order_btn").click(function(event) {
      
      $(".search_result").empty();
      $(".back_btn").show();
      var orderId = $(".search_by_order").val();
      var rollNumber = $(".search_by_roll_number").val();
      var customerEmail = $(".search_by_customeremail").val();
      var phoneNumber = $(".search_by_phonenumber").val();
      var searchClass = $("#search_by_class :selected").val();
      var sdatepicker = $("#sdatepicker").val();
      var edatepicker = $("#edatepicker").val();
      var status = $('#selectStatus :selected').val();
	    var searchSchool = $('#search_by_school :selected').val();
      var page = url.searchParams.get("p");
		if (!status) {
			status = getStatusFromUrlPath();
		}
              var param={ 
                  orderId:orderId,
                  status: status,
                  rollNumber:rollNumber,
		              customerEmail:customerEmail,
		              phoneNumber:phoneNumber,
		              sdatepicker:sdatepicker,
		              edatepicker:edatepicker,
                  searchClass:searchClass,
		              searchSchool:searchSchool,
                  type:'search_by_order',
                  p: $("#current_page").val() 
                }
              var customurl = fronturl+'/Search';
	      console.log(" Justin URL :"+ customurl);
              console.log('back');

                $.ajax({
                    showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                      console.log("New Change : "+ response.responseJSON.html);
		      console.log("FIlter Count : "+ response.responseJSON.filterCount);

		      if (orderId || rollNumber || customerEmail || phoneNumber || searchClass || sdatepicker || edatepicker || status || searchSchool) {
                            if(response.responseJSON.filterCount >= 0){
                             $(".customer-index-btn.total-orders").prop('disabled', true);
                             $(".customer-index-btn.total-orders").hide();
                             $(".customer-index-btn.filter-orders").text("Total Orders: " + response.responseJSON.filterCount);
			     $(".customer-index-btn.filter-orders").show();
                            }
		      }else {
                            $(".customer-index-btn.filter-orders").prop('disabled', true);
                          $(".customer-index-btn.filter-orders").hide();
                          $(".customer-index-btn.total-orders").prop('disabled', false);
                          $(".customer-index-btn.total-orders").show();
                      }

                      $(".sales-list").html(response.responseJSON.html);
                      $(".order-products-toolbar").html(response.responseJSON.pager);
		     
	   var url = fronturl+'/Search';
	   var exportUrl = fronturl+'/export?' +
                'orderId=' + encodeURIComponent(orderId) +
                '&status=' + encodeURIComponent(status) +
                '&rollNumber=' + encodeURIComponent(rollNumber) +
                '&customerEmail=' + encodeURIComponent(customerEmail) +
                '&phoneNumber=' + encodeURIComponent(phoneNumber) +
                '&sdatepicker=' + encodeURIComponent(sdatepicker) +
                '&edatepicker=' + encodeURIComponent(edatepicker) +
                '&searchClass=' + encodeURIComponent(searchClass) +
                '&searchSchool=' + encodeURIComponent(searchSchool);
	  console.log(" Export URL update by justin : "+ exportUrl);
            $(".customer-index-btn.export-btn").attr("href", exportUrl);
                    },
                    error:function(xhr,status,errorThrown){
                    }
                });
         });
         /* pagination click */
          $(document).on('click', '.order-products-toolbar .pager a', function(e) {
              e.preventDefault();
              var href = $(this).attr('href');
              if (!href) return;
              var url = new URL(href, window.location.origin);
              var page = url.searchParams.get("p");
              if (page) {
                  $("#current_page").val(page);
              }else{
                $("#current_page").val(1);
              }
        
              // Trigger search button click
              $(".search_order_btn").trigger("click");
          });
         $("#search_order_btn").click(function(event) {
	   var ordersData = [];
           var salesOrderList = document.querySelectorAll('.sales-id');
	   salesOrderList.forEach(function(salesOrder) {
       		 ordersData.push(salesOrder.innerText.trim());
    	   });

	   var param={
       		 ordersData:ordersData
           }
	   var orderExportUrl = fronturl+'/SchoolExport';
	    $("#loader").show();
                    $.ajax({
                    url: orderExportUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
		       $("#loader").hide();
                      var value = JSON.parse(response.responseText);
                      if(value.status == 'success'){
			  var downloadUrl = value.fileUrl.replace(/\\\//g, '/');
			  console.log("file URL : "+ downloadUrl);
			var link = document.createElement('a');
			link.href = downloadUrl;
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link); 
                      }else {
                       alert('Error generating CSV: ' + response.status);
                     }
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });
         });


        $(".search_order_export").click(function(event) {
          var orderId = $(".search_by_order").val();
          var rollNumber = $(".search_by_roll_number").val();
          var searchClass = $("#search_by_class :selected").val();
          var status = $('#selectStatus :selected').val();
		   var searchSchool = $('#search_by_school :selected').val();
                var param={ 
                    orderId:orderId,
                    status: status,
                    rollNumber:rollNumber,
                    searchClass:searchClass,
					searchSchool:searchSchool,
                    type:'search_order_export'
                  }
                var customurl = fronturl+'/Search';
                  $.ajax({
                      showLoader: true,
                      url: customurl,
                      data: param,
                      type: "POST",
                      dataType: 'json',
                      complete:function(response){
                        console.log(response.responseText);
                         $(location).prop('href',response.responseText);
                      },
                      error:function(xhr,status,errorThrown){
                      }
                  });
           });
      
         $(".back_btn").click(function(event) {
              var param={ 
                  type:'back_orders' 
                }
              var customurl = fronturl+'/Search';
              console.log('back');

                $.ajax({
                    showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                      console.log(response.responseText);
                       window.location.href = response.responseText+'/schoolzone_customer/index/Display';
                    },
                    error:function(xhr,status,errorThrown){
                    }
                });
          
       
         });



    $(".school_name_list").val('');
    $('.school_name_list').on('keyup', function(e) {
    //$(".school_name_list").on("input", function(e) {
	//$('.school_name_list').change(function(e) {
	//$('.school_name_list').keypress(function(event) {
		var words = $(this).val();
		//console.log(words.length);
		if(words.length > 3) {
			//console.log('keyup search');

			$(".search_empty").hide();
			$(".blank_roll").hide();
			$(".blank_username").hide();
			$(".username_not_valied").hide();
			$(".blank_password").hide();

			$(".roll_not_valied").hide();

			$(".username_list").val("");
			$(".password_list").val("");
			$(".roll_numbers").val("");

			$(".result_hint_list").show();

			var name = $(".school_name_list").val().trim();
			var param={ name:name, type:'search' }
			var customurl = fronturl+'/Search';
            $.ajax({
                // showLoader: true,
                url: customurl,
                data: param,
                type: "POST",
                dataType: 'json',
                complete:function(response){
                   $(".result_hint_list").text('');
                  // var array = response.responseJSON;
                 
                  // $(".result_hint").text("");
                  $(".result_hint_list").append(response.responseJSON);
                  // $('.schoolRes').click(function(event){console.log($(this).find('input[type="text"]').val())});

                  $(".schoolRes").click(function(event) {
                      $(".username_list").val("");
                      $(".password_list").val("");
                      $(".roll_numbers").val("");

                      $(".username_not_valied").hide();
                      $(".roll_not_valied").hide();

                     
                      $('.username_list').hide();
                      $('.password_list').hide();
                      $('.roll_numbers').hide();
                      
                      // var school_type = $(".school_type").val();
                      var school_type = $(this).find('.school_type').val();
                      console.log(school_type);
                      if(school_type == 3){
                        $('.roll_numbers').show();
						$('.roll_numbers').attr("placeholder", "Admission Numbers");
                        $('.username_list').hide();
                        $('.password_list').hide();
                      }
                      if(school_type == 2){
                        $('.username_list').show();
                        $('.password_list').show();
                        $('.roll_numbers').hide();
                      }

                      var sujjession = $(this).find('input[type="text"]').val();
                      console.log(sujjession);
                      
                        $(".school_name_list").val(sujjession);
                        $(".result_hint_list").hide();

                        /*var param={  type:'board' }
                        var customurl = fronturl+'/Search';
                        
                        $.ajax({
                            url: customurl,
                            data: param,
                            type: "POST",
                            dataType: 'json',
                            complete:function(response){
                              if(response.responseJSON != null){
                                  // console.log(id);
                                // console.log(response.responseJSON);
                                // $(".board_hint").show();
                                // $(".board_hint").append(response.responseJSON);
                              }
                            },
                            error:function(xhr,status,errorThrown){
                            }
                        });*/
                      });
                      // Dynamic function ends

                },
                error:function(xhr,status,errorThrown){
                }
            });
        // e.preventDefault();
		}
    });

  $('.schoolRes').click(function(event){console.log($(this).find('input[type="text"]').val())});
  // $(".schoolRes").click(function(){
  //   $(this).css("background-color", "#cccccc");
  // });

  // $('.responce_hint').focus(function(){
  //     console.log('abc');
  // });
    // $(".result_hint_list").click(function(event) {
    //     $(".username_list").val("");
    //     $(".password_list").val("");
    //     $(".roll_numbers").val("");

    //     $(".username_not_valied").hide();
    //     $(".roll_not_valied").hide();


    //     var id = $(".responce_hint").val();
    //     $('.username_list').hide();
    //     $('.password_list').hide();
    //     $('.roll_numbers').hide();

        // var school_type = $(".school_type").val();
        // console.log(school_type);
    //  if(school_type == 3){
    //       $('.roll_numbers').show();
    //       $('.username_list').hide();
    //       $('.password_list').hide();
    //     }
    //     if(school_type == 2){
    //       $('.username_list').show();
    //       $('.password_list').show();
    //       $('.roll_numbers').hide();
    //     }

    //       var sujjession = $(".responce_hint").val();
    //       $(".school_name_list").val(sujjession);
    //       // console.log(sujjession);
    //       $(".result_hint_list").hide();

    //       // var entity_id = $(".entity_id").val();
    //       var param={  type:'board' }
    //       var customurl = fronturl+'/Search';
          
    //          $.ajax({
    //               // showLoader: true,
    //               url: customurl,
    //               data: param,
    //               type: "POST",
    //               dataType: 'json',
    //               complete:function(response){
    //                 // var array = response.responseJSON;
    //                 // console.log(response.responseJSON);
    //                 // $(".board_hint").text("");
    //                 if(response.responseJSON != null){
    //                    // console.log(id);
    //                   // console.log(response.responseJSON);
    //                   // $(".board_hint").show();
    //                   // $(".board_hint").append(response.responseJSON);
    //                 }
    //               },
    //               error:function(xhr,status,errorThrown){
    //               }
    //           });
        // });
     $(".search_btn_list").click(function(event) {
        var search_box = $('.school_name_list').val().trim();
        var childCount = $('.result_hint_list').children().length;
        if((search_box == '') && (childCount == 0)){
          $(".search_empty").text('Please enter school name');
          $(".search_empty").show();
        }else if((search_box != '') && (childCount == 0) && ($('.urlResult').text() == '')){
          
            $(".search_empty").text('Please enter valid school name');
            $(".search_empty").show();
          
        }else{
          $(".search_empty").hide();

        $(".blank_roll").hide();
        $(".blank_username").hide();
        $(".blank_password").hide();
        
        var school_name= $(".school_name_list").val();
        var username= $(".username_list").val();
        var password = $(".password_list").val();
        var roll_numbers = $(".roll_numbers").val();

        $(".username_not_valied").hide();
        $(".roll_not_valied").hide();
    
              var param={ 
                    school_name:school_name,
                    username:username,
                    password:password,
                    roll_numbers:roll_numbers,
                    type:'search_list' 
                }
              var customurl = fronturl+'/Search';
              if($('.username_list').is(':hidden') && $('.roll_numbers').is(':hidden')){
                   $.ajax({
                    // showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){

                      // console.log(response.responseText);
                      
                      if(response.responseText != '"roll_not_valied"' && response.responseText != '"roll_valied"' && response.responseText != '"username_not_valied"' && response.responseText != '"username_valied"'){
                      // var form = '';
                      // var args={'school_name':school_name, 'username': username,'password':password,'roll_numbers':roll_numbers};
                      // $.each(args, function( key, value ) {
                      //   form += '<input type="hidden" name="'+key+'" value="'+value+'">';
                      // });
                      
                      // $('<form action="'+response.responseText+'" method="POST">'+form+'</form>').appendTo('body').submit();
                       window.location.href = response.responseText;
                      }  
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });
              }else{

              if($('.username_list').is(':hidden')){
                if($(".roll_numbers").val() != ''){

                     $.ajax({
                    // showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){

                      // console.log(response.responseText);
                      
                      if(response.responseText != '"roll_not_valied"' && response.responseText != '"roll_valied"' && response.responseText != '"username_not_valied"' && response.responseText != '"username_valied"'){
                      var form = '';
                      var args={'school_name':school_name, 'username': username,'password':password,'roll_numbers':roll_numbers};
                      // $.each(args, function( key, value ) {
                      //   form += '<input type="hidden" name="'+key+'" value="'+value+'">';
                      // });
                      
                      // $('<form action="'+response.responseText+'" method="POST">'+form+'</form>').appendTo('body').submit();
                       window.location.href = response.responseText;
                      }  
                     
                      if(response.responseText == '"roll_not_valied"' || response.responseText == '"roll_valied"'){
                        if(roll_numbers == ''){
                          $(".blank_roll").show();
                        }else{
                          $(".blank_roll").hide();
                          if(response.responseText == '"roll_not_valied"'){
                            
                            $(".roll_not_valied").show();
                          }else{
                           
                          }
                        }
                          
                      }
                      if(response.responseText == '"username_not_valied"' || response.responseText == '"username_valied"'){
                        $blank=1;
                        if(username == ''){
                          
                          $blank=0;
                          $(".blank_username").show();

                        }else{
                          
                          $(".blank_username").hide();
                        }
                        if(password == ''){
                          
                          $blank=0;
                          $(".blank_password").show();

                        }else{
                         
                          $(".blank_password").hide();
                        }

                        console.log($blank);
                        if($blank=1){
                          $(".blank_username").hide();
                          $(".blank_password").hide();
                        
                          if(response.responseText == '"username_not_valied"'){
                            $(".username_not_valied").show();
                          }else{
                           
                          }
                        }
                  
                      }
                      // window.location.href = response.responseText;
                      // var array = response.responseJSON;
                      // $(".result_hint").text("");
                      // $(".result_hint").append(response.responseJSON);
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });
                }else{
                  $(".roll_not_valied").show();
                  console.log('no');
                }
              }else{
                var user= $(".username_list").val();
                var pass = $(".password_list").val()

                if($(".username_list").val() != ''){
                  $blankuser = 1;
                }else{
                  $(".blank_username").show();
                  $blankuser = 0;
                }

                if($(".password_list").val() != ''){
                  $blankpass = 1;
                }else{
                  $blankpass = 0;
                    $(".blank_password").show();
                }

                if($blankuser == 1 && $blankpass== 1)
                {
                     $.ajax({
                    // showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){

                      // console.log(response.responseText);
                      
                      if(response.responseText != '"roll_not_valied"' && response.responseText != '"roll_valied"' && response.responseText != '"username_not_valied"' && response.responseText != '"username_valied"'){
                      var form = '';
                      var args={'school_name':school_name, 'username': username,'password':password,'roll_numbers':roll_numbers};
                  
                       window.location.href = response.responseText;
                      }  
                     
                      if(response.responseText == '"roll_not_valied"' || response.responseText == '"roll_valied"'){
                        if(roll_numbers == ''){
                          $(".blank_roll").show();
                        }else{
                          $(".blank_roll").hide();
                          if(response.responseText == '"roll_not_valied"'){
                            
                            $(".roll_not_valied").show();
                          }else{
                           
                          }
                        }
                          
                      }
                      if(response.responseText == '"username_not_valied"' || response.responseText == '"username_valied"'){
                        $blank=1;
                        if(username == ''){
                          
                          $blank=0;
                          // $(".blank_username").show();

                        }else{
                          
                          $(".blank_username").hide();
                        }
                        if(password == ''){
                          
                          $blank=0;
                          // $(".blank_password").show();

                        }else{
                         
                          $(".blank_password").hide();
                        }

                        console.log($blank);
                        if($blank=1){
                          $(".blank_username").hide();
                          $(".blank_password").hide();
                        
                          if(response.responseText == '"username_not_valied"'){
                            $(".username_not_valied").show();
                          }else{
                           
                          }
                        }
                  
                      }
                      // window.location.href = response.responseText;
                      // var array = response.responseJSON;
                      // $(".result_hint").text("");
                      // $(".result_hint").append(response.responseJSON);
                    },
                    error:function(xhr,status,errorThrown){
                    }
                  });
                }else{
                   
                }
              }
            }
            // }
         }
      });

      $('.notify-submit').click(function(e){
        e.preventDefault();
        if (jQuery('#notify-form').valid()) {
          var notify_name = $(".notify-name").val();
          var notify_phone = $(".notify-phone").val();
          var notify_email = $('.notify-email').val();
          var notify_schoolname = $(".notify-schoolname").val();
          var notify_schooladdress = $(".notify-schooladdress").val();
          var notify_message = $(".notify-message").val();

          var param={ 
            notify_name:notify_name,
            notify_phone:notify_phone,
            notify_email:notify_email,
            notify_schoolname:notify_schoolname,
            notify_schooladdress:notify_schooladdress,
            notify_message: notify_message,
            type:'notify_school' 
          }
          var customurl = fronturl+'/Search';
          $('body').trigger('processStart');
          $.ajax({
            url:customurl,
            data : param,
            type : "POST",
            dataType : 'json',
            complete : function(response){
              $('body').trigger('processStop');
              console.log(response);
              if(response.responseText == 'yes'){
                // $('#success-notify-modal').modal('openModal');
                // alert('Thank you for your request');
                $('#notify-modal').modal('openModal');
                // window.location.href = BASE_URL; 
              }else{
                alert('Something went wrong!.. Please try again later.');
              }
            },
            error:function(xhr,status,errorThrown){
              $('body').trigger('processStop');
            }
          });
      }
      });

      $('.result_hint_list').click(function(){
        $('.search_empty').hide();
      });

      $('.notify_back').click(function(){
        window.location.href = BASE_URL;
      });

      $("#notify-modal").on("hidden", function () {
        $('#notify-form')[0].reset();
        window.location.href = BASE_URL;
      });
      
     

      
});

