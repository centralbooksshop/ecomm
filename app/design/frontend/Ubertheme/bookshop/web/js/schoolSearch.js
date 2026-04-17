require(
[
'jquery',
'mage/url'
],
  function($,urlBuilder) {

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
        if(school_type == 1){
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

    $(".school_name_list").val('');
    $(".school_name_list").on("keydown", function(e) {

      $(".username_list").val("");
      $(".password_list").val("");
      $(".roll_numbers").val("");
     
      $(".result_hint_list").show();
            
      var name = $(".school_name_list").val();
    
      console.log(fronturl);
      
        var param={ name:name, type:'search' }
        var customurl = fronturl+'/Search';
        console.log(customurl);
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
                },
                error:function(xhr,status,errorThrown){
                }
            });
      // e.preventDefault();
  });
    $(".result_hint_list").click(function(event) {
        $(".username_list").val("");
        $(".password_list").val("");
        $(".roll_numbers").val("");

        $(".username_not_valied").hide();
        $(".roll_not_valied").hide();


        var id = $(".responce_hint").val();
        $('.username_list').hide();
        $('.password_list').hide();
        $('.roll_numbers').hide();

        var school_type = $(".school_type").val();
        console.log(school_type);
        if(school_type == 1){
          $('.roll_numbers').show();
          $('.username_list').hide();
          $('.password_list').hide();
        }
        if(school_type == 2){
          $('.username_list').show();
          $('.password_list').show();
          $('.roll_numbers').hide();
        }



          var sujjession = $(".responce_hint").val();
          $(".school_name_list").val(sujjession);
          // console.log(sujjession);
          $(".result_hint_list").hide();

          // var entity_id = $(".entity_id").val();
          var param={  type:'board' }
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
                      // $(".board_hint").show();
                      // $(".board_hint").append(response.responseJSON);
                    }
                  },
                  error:function(xhr,status,errorThrown){
                  }
              });
        });
     $(".search_btn_list").click(function(event) {
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
              // console.log(customurl);
              $.ajax({
                    // showLoader: true,
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){

                      // console.log(response.responseText);
                      
                      if(response.responseText != '"roll_not_valied"' && response.responseText != '"roll_valied"' && response.responseText != '"username_not_valied"' && response.responseText != '"username_valied"'){
                        window.location.href = response.responseText+'?school_name='+school_name+'&username='+username+'&password='+password+'&roll_numbers='+roll_numbers;
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
            // }
      });

      
});
