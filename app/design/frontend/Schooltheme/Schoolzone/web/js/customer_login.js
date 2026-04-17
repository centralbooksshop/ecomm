require(
[
'jquery',
'mage/url'
],
  function($,urlBuilder) {
 urlBuilder.setBaseUrl(BASE_URL);
    var fronturl = urlBuilder.build('schoolzone_customer/Index');

    $("#btnGet").click(function () {
      var array = [];
      var markedCheckbox = document.getElementsByName('schools');
      for (var checkbox of markedCheckbox) {
        if (checkbox.checked)
          array.push(checkbox.value);
      }

      console.log(array);
      var confirmDelete = window.confirm("Are you sure you want to delete the selected records?");
      if (confirmDelete) {
      var param={
        array:array, 
        key:'student_delete'
      }

      var customurl = fronturl+'/Save';
                    $.ajax({
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                      var value = JSON.parse(response.responseText);
                      if(value.deletStatus == 'deleted'){
                        alert("Total: ("+value.count+") records Are Deleted");
                        location.reload();
                      }
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });
	}
    });

    $('#selectAll').click(function() {
        var isChecked = $(this).prop('checked'); 
        $('input[name="schools"]').prop('checked', isChecked);
    });

    function toggleSelectAll() {
        var hasRecords = $('input[name="schools"]').length > 0;
        $('#selectAll').prop('disabled', !hasRecords);
        if (!hasRecords) {
            $('#selectAll').prop('checked', false);
        }
    }

    toggleSelectAll();
    
    $(".error-message-container").hide();
    $('.saved').hide();
    $('.failed').hide();
    $('.already-exists').hide();

     $(".student_save").click(function(event) {
            $(".error-message-container").hide();
            $('.saved').hide();
            $('.failed').hide();
            $('.already-exists').hide();

            var studentId= $(".studentId").val();
            var school_name= $(".school_name_add").val();
            var student_name= $(".student_name_add").val();
            var student_class= $(".student_class").val();
            var admission_id= $(".admission_no").val();
            var student_username= $(".stud_username").val();
            var stud_password= $(".stud_password").val();
            $validation = 0;

              var param={
                studentId:studentId, 
                school_name:school_name, 
                student_name:student_name,
                student_class:student_class,
                admission_id:admission_id,
                student_username:student_username,
                stud_password:stud_password,
                key:'student_save'
              }

               // if(admission_id == undefined){
               //      if(school_name == '' || student_name == '' || student_class == '' || student_username == '' || stud_password == ''){
               //      $validation = 0;
               //    }else{
               //      $validation = 1;
               //    }
               //  }
	     //
	    	console.log("student data : "+student_username+" "+stud_password+" "+school_name+" "+student_name+" "+student_class+" "+admission_id);
                if(student_username == undefined || stud_password == undefined){
                  if(!school_name || student_name == '' || student_class == '' || admission_id == ''){
                    $validation = 0;
                  }else{
                    $validation = 1;
                  }
		console.log($validation);
                }
                if(student_username != undefined || stud_password != undefined){
                  if(school_name == '' || student_name == '' || student_class == '' || student_username == '' || stud_password == ''|| admission_id == ''){
                      $validation = 0;
                    }else{
                      $validation = 1;
                    }
                }
             
              if($validation == 1){
              $(".error-message-container").hide();
              var customurl = fronturl+'/Save';
              $.ajax({
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){
                      if(response.responseText == 'saved'){
                        $('.failed').hide();
                        $('.already-exists').hide();
                        $('.saved').show();
                        $(".student_name_add").val('');
                        $(".student_class").val('');
                        $(".admission_no").val('');
                        $(".stud_username").val('');
                        $(".stud_password").val('');
                      }
                      if(response.responseText == 'failed'){
                        $('.already-exists').hide();
                        $('.failed').show();
                      }
                      if(response.responseText == 'admission-exists'){
                        $('.failed').hide();
                        $('.already-exists').text('Admission id already exists');
                        $('.already-exists').show();
                      }
                      if(response.responseText == 'user-exists'){
                        $('.failed').hide();
                        $('.already-exists').text('User info already exists');
                        $('.already-exists').show();
                      }
                    },
                    error:function(xhr,status,errorThrown){
                    }
              });

              }else{
                  $(".error-message-container").show();
              }

     });
    
    $('.blank_user').hide();
    $('.invalied_user').hide();

   
    var fronturlSearch = urlBuilder.build('schoolzone_customer/Index');
  
      $(".customer_login").click(function(event) {

        var username= $(".customer_username").val();
        var password = $(".customer_password").val();

        if(username == '' || password == ''){
            $('.blank_user').show();
            $('.invalied_user').hide();
        }else{
            $('.blank_user').hide();
            $('.invalied_user').hide();
               $('body').trigger('processStart');
               var param={ username:username, password:password, type:'category_key' }
              var customurl = fronturlSearch+'/Search';
                $.ajax({
                    url: customurl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    complete:function(response){

                        if(response.responseText == 'invalied_user'){
                           $('body').trigger('processStop');
                          $('.invalied_user').text(' User Is Not registered');
                          $('.invalied_user').show();
                        }else if(response.responseText == 'invalied_school_user'){
                          $('body').trigger('processStop');
                          $('.invalied_user').text('School is not assigned to User');
                          $('.invalied_user').show();
                        }else{
                          $('.invalied_user').hide();
                           $('body').trigger('processStop');
                          window.location.href = response.responseText;
                        }
                    },
                    error:function(xhr,status,errorThrown){
                      $('body').trigger('processStop');
                    }
              });
        }  
      });
});
